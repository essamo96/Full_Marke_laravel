<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\DocumentAccessLog;
use App\Models\SubjectResource;
use App\Models\VideoAccessLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class VideoStreamController extends Controller
{
    /**
     * Files that make up an HLS rendition all live flat inside the same
     * per-resource directory, so we only need to allow the extensions that
     * legitimately appear there.
     */
    private const ALLOWED_EXTENSIONS = ['m3u8', 'key', 'ts'];

    private function authorizeAccess(SubjectResource $resource): void
    {
        $student = Auth::guard('student')->user();

        // A copied stream URL opened in a different browser (or logged out
        // entirely) has no student session to authorize against at all —
        // fail that cleanly instead of crashing on a null->registrations() call.
        abort_unless($student, 403);

        abort_unless($resource->is_active, 404);

        $isRegistered = $student->registrations()
            ->where('subject_id', $resource->subject_id)
            ->whereIn('status', ['pending', 'partially_paid', 'fully_paid'])
            ->exists();

        abort_unless($isRegistered, 403);
    }

    /**
     * Starts (and exclusively claims) a playback session for this resource.
     * Any other session the student had open — on this resource or any
     * other — is closed immediately, so a shared login can only ever stream
     * one video at a time.
     */
    public function startSession(Request $request, SubjectResource $resource)
    {
        $this->authorizeAccess($resource);

        abort_unless($resource->isVideo() && $resource->isReady(), 409, 'الفيديو غير جاهز للعرض بعد.');

        $student = Auth::guard('student')->user();

        VideoAccessLog::where('student_id', $student->id)
            ->whereNull('ended_at')
            ->update(['ended_at' => now()]);

        $log = VideoAccessLog::create([
            'subject_resource_id' => $resource->id,
            'student_id' => $student->id,
            'session_token' => Str::random(48),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'last_seen_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'session_token' => $log->session_token,
            'stream_url' => route('student.video.stream', ['resource' => $resource]) . '?st=' . $log->session_token,
        ]);
    }

    /**
     * Streams the MP4 file securely using Range requests and referer locks.
     */
    public function stream(Request $request, SubjectResource $resource)
    {
        $this->authorizeAccess($resource);

        abort_unless($resource->isVideo() && $resource->url, 404);

        // Referer Protection: Block direct URL sharing or IDM downloads
        $referer = $request->headers->get('referer');
        $host = $request->getHost();
        if (!$referer || !str_contains(parse_url($referer, PHP_URL_HOST) ?? '', $host)) {
            abort(403, 'Unauthorized access: Invalid Referer.');
        }

        $student = Auth::guard('student')->user();
        $token = (string) $request->query('st');

        $log = VideoAccessLog::where('student_id', $student->id)
            ->where('session_token', $token)
            ->whereNull('ended_at')
            ->first();

        abort_unless($log, 403, 'انتهت هذه الجلسة — على الأرجح تم تشغيل الفيديو من جهاز آخر بنفس الحساب.');

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
            return 'https://www.youtube-nocookie.com/embed/'.$m[1].'?rel=0&modestbranding=1';
        }

        return null;
    }
}
