<?php

namespace App\Http\Controllers\Student\Concerns;

use App\Models\SubjectResource;
use App\Services\ResourceLibrary\ResourceFileStore;
use App\Services\StudentContentGate;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait AuthorizesStudentResourceAccess
{
    /**
     * A stored file whose bytes are gone (typically a database restored without its files folder):
     * the student gets a clear message instead of a bare 404, and the staff get one log line per
     * resource per day - the quality check ("missing_file") lists and switches these off.
     */
    protected function ensureResourceFileExists(SubjectResource $resource): void
    {
        if (! app(ResourceFileStore::class)->isMissing($resource)) {
            return;
        }

        if (Cache::add('library:missing-file-reported:'.$resource->id, true, now()->addDay())) {
            Log::warning('Student opened a resource whose file is missing', [
                'resource_id' => $resource->id,
                'subject_id' => $resource->subject_id,
                'path' => $resource->url,
            ]);
        }

        $message = 'هذا الملف غير متاح حالياً، تواصل مع المدرس.';

        // a download link opened in the tab gets the normal error page; the in-page players fetch() and read JSON
        abort_if(request()->header('Sec-Fetch-Mode') === 'navigate', 410, $message);

        throw new HttpResponseException(response()->json(['message' => $message, 'code' => 'file_missing'], 410));
    }

    /**
     * One rule for the listing pages and for every direct URL (stream, file, link, download,
     * embed): the resource must pass StudentContentGate - kill switch, audience, exclusions on
     * the resource AND on its lesson / unit, and an open placement. A link copied before the
     * student was excluded stops working the moment the exclusion is saved.
     */
    protected function authorizeStudentResource(SubjectResource $resource): void
    {
        $student = Auth::guard('student')->user();
        abort_unless($student, 403);

        app(StudentContentGate::class)->authorize($resource, (int) $student->id);
    }

    /**
     * @return array{kind: string, youtube_id?: string, embed_url?: string, open_url?: string}
     */
    protected function resolveMediaPayload(SubjectResource $resource): array
    {
        $this->ensureResourceFileExists($resource);

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
