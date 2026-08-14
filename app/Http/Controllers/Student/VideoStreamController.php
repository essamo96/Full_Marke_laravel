<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Student\Concerns\AuthorizesStudentResourceAccess;
use App\Http\Controllers\Controller;
use App\Models\DocumentAccessLog;
use App\Models\SubjectResource;
use App\Models\VideoAccessLog;
use App\Support\StudentDeviceLock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class VideoStreamController extends Controller
{
    use AuthorizesStudentResourceAccess;

    /**
     * Files that make up an HLS rendition all live flat inside the same
     * per-resource directory, so we only need to allow the extensions that
     * legitimately appear there.
     */
    private const ALLOWED_EXTENSIONS = ['m3u8', 'key', 'ts'];

    /** Minimum grace after the video length so buffering / rewatches still fit. */
    private const TOKEN_MIN_GRACE_SECONDS = 30 * 60;

    /** Hard ceiling so a missing/wrong duration cannot mint a forever token. */
    private const TOKEN_MAX_TTL_SECONDS = 8 * 60 * 60;

    /** Fallback TTL when duration_seconds is unknown. */
    private const TOKEN_DEFAULT_TTL_SECONDS = 4 * 60 * 60;

    private function authorizeAccess(SubjectResource $resource): void
    {
        $this->authorizeStudentResource($resource);
    }

    /**
     * Token lives for the lesson length + grace (25%, min 30 minutes),
     * capped at 8 hours. Unknown duration → 4 hours.
     */
    private function tokenExpiresAt(SubjectResource $resource): \Illuminate\Support\Carbon
    {
        $duration = $this->resolveDurationSeconds($resource);

        if ($duration <= 0) {
            $ttl = self::TOKEN_DEFAULT_TTL_SECONDS;
        } else {
            $grace = max(self::TOKEN_MIN_GRACE_SECONDS, (int) round($duration * 0.25));
            $ttl = $duration + $grace;
        }

        $ttl = min($ttl, self::TOKEN_MAX_TTL_SECONDS);

        return now()->addSeconds($ttl);
    }

    /**
     * Prefer stored duration_seconds; if missing, probe the file once and cache it.
     */
    private function resolveDurationSeconds(SubjectResource $resource): int
    {
        $stored = max(0, (int) ($resource->duration_seconds ?? 0));
        if ($stored > 0) {
            return $stored;
        }

        if (! $resource->url || ! Storage::disk('protected_videos')->exists($resource->url)) {
            return 0;
        }

        try {
            $seconds = (int) round(
                \ProtoneMedia\LaravelFFMpeg\Support\FFMpeg::fromDisk('protected_videos')
                    ->open($resource->url)
                    ->getDurationInSeconds()
            );

            if ($seconds > 0) {
                $resource->forceFill(['duration_seconds' => $seconds])->save();

                return $seconds;
            }
        } catch (\Throwable) {
            // FFmpeg unavailable / corrupt file — fall back to default TTL.
        }

        return 0;
    }

    /**
     * Starts a playback session bound to this browser's device cookie.
     * Students with max_devices > 1 may hold one active stream per allowed
     * device (up to that limit); starting on a device replaces only that
     * device's previous open session — other allowed devices keep watching.
     */
    public function startSession(Request $request, SubjectResource $resource)
    {
        $this->authorizeAccess($resource);

        abort_unless($resource->isVideo() && $resource->isReady(), 409, 'الفيديو غير جاهز للعرض بعد.');

        $student = Auth::guard('student')->user();
        $deviceId = (string) $request->cookie(StudentDeviceLock::COOKIE);
        abort_unless($deviceId !== '', 403, 'الجهاز غير معرّف — أعد تسجيل الدخول.');

        $allowedDevices = $student->locked_device_ids;
        // Mid-session first visit before login stamped a device should still
        // match; if the account already has locks, require membership.
        if (! empty($allowedDevices)) {
            abort_unless(in_array($deviceId, $allowedDevices, true), 403, 'هذا الجهاز غير مصرّح له بتشغيل الفيديو.');
        }

        $maxDevices = max(1, min(5, (int) ($student->max_devices ?: 1)));

        // Close any previous open session on THIS device only.
        VideoAccessLog::where('student_id', $student->id)
            ->where('device_id', $deviceId)
            ->whereNull('ended_at')
            ->update(['ended_at' => now()]);

        // Also expire stale open rows (TTL elapsed but ended_at still null).
        VideoAccessLog::where('student_id', $student->id)
            ->whereNull('ended_at')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update(['ended_at' => now()]);

        // Cap concurrent streams across devices to max_devices (one per slot).
        $activeOther = VideoAccessLog::where('student_id', $student->id)
            ->whereNull('ended_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->where(function ($q) use ($deviceId) {
                $q->whereNull('device_id')->orWhere('device_id', '!=', $deviceId);
            })
            ->orderBy('created_at')
            ->get();

        $overflow = $activeOther->count() - ($maxDevices - 1);
        if ($overflow > 0) {
            $activeOther->take($overflow)->each(function (VideoAccessLog $log) {
                $log->update(['ended_at' => now()]);
            });
        }

        $expiresAt = $this->tokenExpiresAt($resource);

        $log = VideoAccessLog::create([
            'subject_resource_id' => $resource->id,
            'student_id' => $student->id,
            'session_token' => Str::random(48),
            'device_id' => $deviceId,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'last_seen_at' => now(),
            'expires_at' => $expiresAt,
        ]);

        return response()->json([
            'success' => true,
            'session_token' => $log->session_token,
            'expires_at' => $expiresAt->toIso8601String(),
            'expires_in' => max(0, $expiresAt->getTimestamp() - now()->getTimestamp()),
            'stream_url' => route('student.video.stream', ['resource' => $resource]).'?st='.$log->session_token,
        ]);
    }

    /**
     * Streams the MP4 file securely using Range requests, referer locks,
     * device binding, and a duration-based token expiry.
     */
    public function stream(Request $request, SubjectResource $resource)
    {
        $this->authorizeAccess($resource);

        abort_unless($resource->isVideo() && $resource->url, 404);

        $referer = $request->headers->get('referer');
        $host = $request->getHost();
        if (! $referer || ! str_contains(parse_url($referer, PHP_URL_HOST) ?? '', $host)) {
            abort(403, 'Unauthorized access: Invalid Referer.');
        }

        $student = Auth::guard('student')->user();
        $token = (string) $request->query('st');
        $deviceId = (string) $request->cookie(StudentDeviceLock::COOKIE);

        $log = VideoAccessLog::where('student_id', $student->id)
            ->where('session_token', $token)
            ->whereNull('ended_at')
            ->first();

        abort_unless($log, 403, 'انتهت هذه الجلسة — على الأرجح تم تشغيل الفيديو من جهاز آخر أو انتهت صلاحية الرابط.');

        if ($log->isExpired()) {
            $log->update(['ended_at' => now()]);
            abort(403, 'انتهت صلاحية رابط التشغيل حسب مدة الفيديو — أعد فتح الدرس.');
        }

        // Copied stream URL on a different allowed (or foreign) device fails.
        if ($log->device_id && $log->device_id !== $deviceId) {
            abort(403, 'رابط التشغيل مربوط بجهاز آخر ولا يمكن استخدامه هنا.');
        }

        if (! $log->last_seen_at || $log->last_seen_at->diffInSeconds(now()) >= 10) {
            $log->update(['last_seen_at' => now()]);
        }

        abort_unless(Storage::disk('protected_videos')->exists($resource->url), 404);

        $path = Storage::disk('protected_videos')->path($resource->url);

        return response()->file($path, [
            'Content-Type' => 'video/mp4',
            'Cache-Control' => 'no-store, private',
            'Accept-Ranges' => 'bytes',
        ]);
    }

    /**
     * Serves the raw bytes of a document attachment from the private disk to
     * the in-page canvas viewer (never as a native browser PDF plugin, whose
     * built-in toolbar would trivially bypass the watermark/deterrents). Each
     * view is logged for an audit trail — the same "make leaks traceable"
     * approach used for videos, since outright preventing a save/screenshot
     * of rendered content isn't technically possible.
     */
    public function file(Request $request, SubjectResource $resource): Response
    {
        $this->authorizeAccess($resource);

        abort_if($resource->isVideo(), 404);
        if ($resource->isExternalLink()) {
            return redirect()->away($resource->url);
        }
        abort_if(! $resource->url, 404);
        abort_unless(Storage::disk('protected_videos')->exists($resource->url), 404);

        // Referer Protection: Block direct URL sharing or IDM downloads for documents and PDFs
        $referer = $request->headers->get('referer');
        $host = $request->getHost();
        if (!$referer || !str_contains(parse_url($referer, PHP_URL_HOST) ?? '', $host)) {
            abort(403, 'Unauthorized access: Invalid Referer.');
        }

        $student = Auth::guard('student')->user();

        DocumentAccessLog::create([
            'subject_resource_id' => $resource->id,
            'student_id' => $student->id,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        $extension = strtolower(pathinfo($resource->url, PATHINFO_EXTENSION));
        $contentType = match ($extension) {
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'application/octet-stream',
        };

        // PDFs and images get rendered through the in-page watermarked canvas viewer, so only
        // they're safe to serve as a bare stream; other office formats have no such viewer
        // and must still open their native app, which needs a normal inline/download response.
        $headers = [
            'Content-Type' => $contentType,
            'Cache-Control' => 'no-store, private',
            'X-Content-Type-Options' => 'nosniff',
        ];

        if ($extension !== 'pdf' && ! $resource->isImage()) {
            $filename = $resource->original_filename ?: basename($resource->url);
            $headers['Content-Disposition'] = 'inline; filename="'.$filename.'"';
        }

        return response(Storage::disk('protected_videos')->get($resource->url), 200, $headers);
    }

    /**
     * Resolves an external link (YouTube, Zoom, ...) only after the same
     * auth+enrollment check every other resource goes through, and only over
     * an authenticated fetch — never as a plain <a href> in the page's static
     * HTML. That alone stops the trivial "view source / right-click copy
     * link" path for pulling a paid link out and forwarding it to someone
     * who never registered; it can't stop a determined user from reading the
     * URL out of the network tab once they're legitimately viewing it, same
     * boundary as everywhere else in this system.
     */
    public function link(Request $request, SubjectResource $resource)
    {
        $this->authorizeAccess($resource);

        abort_unless($resource->isExternalLink(), 404);

        return response()->json([
            'url' => $resource->url,
            'embed_url' => $this->toYoutubeEmbed($resource->url),
        ]);
    }

    /**
     * Downloads the original resource file if allowed by the admin.
     */
    public function download(Request $request, SubjectResource $resource)
    {
        $this->authorizeAccess($resource);

        abort_unless($resource->allow_download, 403, 'غير مسموح بالتحميل');

        if ($resource->isExternalLink()) {
            return redirect()->away($resource->url);
        }

        abort_unless($resource->url, 404);

        if ($resource->isVideo()) {
            abort_unless(Storage::disk('protected_videos')->exists($resource->url), 404, 'الملف الأصلي غير متوفر للتحميل');
        } else {
            abort_unless(Storage::disk('protected_videos')->exists($resource->url), 404, 'الملف غير متوفر');
        }

        return Storage::disk('protected_videos')->download(
            $resource->url,
            $resource->original_filename ?: basename($resource->url)
        );
    }

    private function toYoutubeEmbed(string $url): ?string
    {
        if (preg_match('#youtu\.be/([A-Za-z0-9_-]{6,})#i', $url, $m)
            || preg_match('#youtube\.com/watch\?v=([A-Za-z0-9_-]{6,})#i', $url, $m)
            || preg_match('#youtube\.com/embed/([A-Za-z0-9_-]{6,})#i', $url, $m)
            || preg_match('#youtube\.com/shorts/([A-Za-z0-9_-]{6,})#i', $url, $m)
        ) {
            return 'https://www.youtube-nocookie.com/embed/'.$m[1]
                .'?rel=0&modestbranding=1&iv_load_policy=3&playsinline=1';
        }

        return null;
    }
}
