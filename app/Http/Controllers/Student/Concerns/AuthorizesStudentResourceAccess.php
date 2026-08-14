<?php

namespace App\Http\Controllers\Student\Concerns;

use App\Models\SubjectResource;
use Illuminate\Support\Facades\Auth;

trait AuthorizesStudentResourceAccess
{
    protected function authorizeStudentResource(SubjectResource $resource): void
    {
        $student = Auth::guard('student')->user();
        abort_unless($student, 403);
        abort_unless($resource->is_active, 404);

        $isRegistered = $student->registrations()
            ->where('subject_id', $resource->subject_id)
            ->whereIn('status', ['pending', 'partially_paid', 'fully_paid'])
            ->exists();

        abort_unless($isRegistered, 403);
    }

    /**
     * @return array{kind: string, youtube_id?: string, embed_url?: string, open_url?: string}
     */
    protected function resolveMediaPayload(SubjectResource $resource): array
    {
        if ($resource->isVideo()) {
            abort_unless($resource->isReady(), 409, 'الفيديو غير جاهز للعرض بعد.');

            return ['kind' => 'mp4'];
        }

        $url = (string) $resource->url;
        if ($url !== '' && ! preg_match('#^https?://#i', $url)) {
            $url = 'https://'.ltrim($url, '/');
        }

        if (preg_match('#youtu(?:be\.com|\.be)#i', $url)) {
            $videoId = null;
            if (preg_match('#[?&]v=([a-zA-Z0-9_-]{6,})#', $url, $m)) {
                $videoId = $m[1];
            } elseif (preg_match('#youtu\.be/([a-zA-Z0-9_-]{6,})#', $url, $m)) {
                $videoId = $m[1];
            } elseif (preg_match('#youtube\.com/(?:shorts|live|embed)/([a-zA-Z0-9_-]{6,})#', $url, $m)) {
                $videoId = $m[1];
            }

            abort_unless($videoId, 422, 'رابط يوتيوب غير صالح');

            return [
                'kind' => 'youtube',
                'youtube_id' => $videoId,
            ];
        }

        if (preg_match('#drive\.google\.com/file/d/([a-zA-Z0-9_-]+)#', $url, $m)
            || preg_match('#drive\.google\.com/(?:open|uc)\?(?:export=\w+&)?id=([a-zA-Z0-9_-]+)#', $url, $m)
        ) {
            return [
                'kind' => 'drive',
                'embed_url' => 'https://drive.google.com/file/d/'.$m[1].'/preview',
            ];
        }

        if (preg_match('#docs\.google\.com/(?:document|spreadsheets|presentation)/d/([a-zA-Z0-9_-]+)#', $url, $m)) {
            $parsed = parse_url($url);
            $embed = ($parsed['scheme'] ?? 'https').'://'.($parsed['host'] ?? 'docs.google.com').($parsed['path'] ?? '');
            $embed = preg_replace('#/(edit|view).*$#', '/preview', $embed);
            if (! str_ends_with($embed, '/preview')) {
                $embed = rtrim($embed, '/').'/preview';
            }

            return [
                'kind' => 'drive',
                'embed_url' => $embed,
            ];
        }

        if (preg_match('#drive\.google\.com/drive/(?:u/\d+/)?folders/([a-zA-Z0-9_-]+)#', $url, $m)) {
            return [
                'kind' => 'drive',
                'embed_url' => 'https://drive.google.com/embeddedfolderview?id='.$m[1].'#list',
            ];
        }

        // Zoom / generic external — open behind an authenticated gate only.
        abort_unless($resource->isExternalLink(), 404);

        return [
            'kind' => 'external',
            'open_url' => $url,
        ];
    }
}
