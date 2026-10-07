<?php

namespace App\Services\ResourceLibrary;

use App\Models\SubjectResource;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Where the bytes of a resource live. A resource is either
 *   - a file on the private disk (document, image, video) - uploaded in one request or
 *     through the chunked uploader that parks it in `incoming/`, or
 *   - an external link (YouTube, Drive, Zoom...) stored as its URL.
 *
 * For files it also records size, mime type and a cheap content fingerprint, which is what
 * lets the library spot the same video uploaded twice.
 */
final class ResourceFileStore
{
    public function disk(): FilesystemAdapter
    {
        return Storage::disk(config('resource_library.disk'));
    }

    /**
     * Turn the upload form into the columns of a resource.
     *
     * @return array{url: string, original_filename: ?string, size_bytes: ?int, mime_type: ?string, content_hash: ?string}
     */
    public function ingest(string $type, ?string $uploadedPath, ?UploadedFile $file, ?string $url, ?string $originalFilename = null): array
    {
        $directory = config('resource_library.directory');

        if ($uploadedPath) {
            $this->assertIncoming($uploadedPath);

            if (! $this->disk()->exists($uploadedPath)) {
                throw LibraryException::invalid('الملف المرفوع غير موجود، يرجى إعادة الرفع.', 'upload_missing');
            }

            $extension = pathinfo($uploadedPath, PATHINFO_EXTENSION) ?: ($type === 'video' ? 'mp4' : 'bin');
            $stored = $directory.'/'.Str::uuid().'.'.strtolower($extension);
            $this->disk()->move($uploadedPath, $stored);
            $this->disk()->delete($uploadedPath.IncomingUploads::SIDECAR);

            return $this->describe($stored, $originalFilename);
        }

        if ($file) {
            $stored = $file->store($directory, config('resource_library.disk'));

            return $this->describe($stored, $file->getClientOriginalName());
        }

        if ($url) {
            return [
                'url' => $url,
                'original_filename' => null,
                'size_bytes' => null,
                'mime_type' => null,
                'content_hash' => null,
            ];
        }

        throw LibraryException::invalid('يرجى إرفاق ملف أو رابط صحيح.', 'no_source');
    }

    /** @return array{url: string, original_filename: ?string, size_bytes: ?int, mime_type: ?string, content_hash: ?string} */
    private function describe(string $path, ?string $originalFilename): array
    {
        return [
            'url' => $path,
            'original_filename' => $originalFilename,
            'size_bytes' => $this->size($path),
            'mime_type' => $this->mime($path),
            'content_hash' => $this->fingerprint($path),
        ];
    }

    public function exists(SubjectResource $resource): bool
    {
        return $resource->url && ! $resource->isExternalLink() && $this->disk()->exists($resource->url);
    }

    /** A stored-file resource whose bytes are gone from the disk (links are never "missing"). */
    public function isMissing(SubjectResource $resource): bool
    {
        return $resource->url && ! $resource->isExternalLink() && ! $this->disk()->exists($resource->url);
    }

    /** Remove the stored file of a resource (links have nothing to remove). */
    public function delete(SubjectResource $resource): void
    {
        if (! $resource->url || $resource->isExternalLink()) {
            return;
        }

        // never pull a file out from under another row (even a trashed one) that still points at it
        if (SubjectResource::withTrashed()->where('url', $resource->url)->whereKeyNot($resource->id)->exists()) {
            return;
        }

        $this->disk()->delete($resource->url);
        $this->disk()->deleteDirectory(config('resource_library.directory')."/{$resource->id}");
    }

    public function size(string $path): ?int
    {
        try {
            return $this->disk()->size($path);
        } catch (Throwable) {
            return null;
        }
    }

    public function mime(string $path): ?string
    {
        try {
            return $this->disk()->mimeType($path) ?: null;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Cheap fingerprint: size + first and last chunk. Identical files always match; a false
     * positive only ever produces a "possible duplicate" a human has to confirm.
     */
    public function fingerprint(string $path): ?string
    {
        try {
            $disk = $this->disk();
            if (! method_exists($disk, 'path')) {
                return null;
            }

            $absolute = $disk->path($path);
            if (! is_file($absolute)) {
                return null;
            }

            $size = filesize($absolute);
            $chunk = (int) config('resource_library.fingerprint_bytes', 1048576);
            $handle = fopen($absolute, 'rb');
            if (! $handle) {
                return null;
            }

            $context = hash_init('sha1');
            hash_update($context, (string) $size);
            hash_update($context, (string) fread($handle, $chunk));

            if ($size > $chunk) {
                fseek($handle, max(0, $size - $chunk));
                hash_update($context, (string) fread($handle, $chunk));
            }
            fclose($handle);

            return hash_final($context);
        } catch (Throwable) {
            return null;
        }
    }

    /** `incoming/...` only, and never a path that climbs out of it. */
    private function assertIncoming(string $path): void
    {
        $prefix = rtrim(config('resource_library.incoming_directory'), '/').'/';

        if (! str_starts_with($path, $prefix) || str_contains($path, '..') || str_contains($path, "\0")
            || str_ends_with($path, IncomingUploads::SIDECAR)) {
            throw LibraryException::invalid('مسار الملف المرفوع غير صالح.', 'bad_upload_path');
        }
    }
}
