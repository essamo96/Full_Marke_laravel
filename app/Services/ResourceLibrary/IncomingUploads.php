<?php

namespace App\Services\ResourceLibrary;

use App\Models\Admin;
use App\Models\SubjectResource;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Files parked in `incoming/` by the chunked uploader and never attached to a resource (the form
 * was closed, the tab crashed, the save failed...). Each upload gets a JSON sidecar
 * (`incoming/<uuid>.mp4.json`: original name, size, who uploaded it, when) so an orphan can be
 * recognised and given back: linked to a resource whose file is missing, turned into a new
 * resource, or deleted. `library:cleanup-incoming` removes what nobody claimed in time.
 *
 * Admins see every orphan; a teacher sees and handles only the uploads they made.
 */
final class IncomingUploads
{
    public const SIDECAR = '.json';

    /** @var array<int, Collection<int, SubjectResource>> */
    private array $missingCache = [];

    public function __construct(
        private readonly ResourceFileStore $files,
        private readonly LibraryJournal $journal,
    ) {}

    public function directory(): string
    {
        return rtrim((string) config('resource_library.incoming_directory'), '/');
    }

    /** Remember where a finished upload came from (called once the last chunk is merged). */
    public function register(string $path, ?string $originalName, ?LibraryActor $uploader): void
    {
        $this->assertIncoming($path);

        $this->writeMeta($path, [
            'original_filename' => $originalName,
            'size_bytes' => $this->files->size($path),
            'uploaded_by_type' => $uploader?->type,
            'uploaded_by_id' => $uploader?->id(),
            'uploaded_at' => now()->toIso8601String(),
        ]);
    }

    /** @return array<string, mixed> */
    public function meta(string $path): array
    {
        try {
            $raw = $this->files->disk()->get($path.self::SIDECAR);
        } catch (Throwable) {
            return [];
        }

        return is_string($raw) ? (json_decode($raw, true) ?: []) : [];
    }

    /**
     * When the clock of an upload started: its upload time, or - for files parked before sidecars
     * existed - the first time the cleanup saw it, which gives those files a full grace period.
     */
    public function startedAt(string $path): Carbon
    {
        $meta = $this->meta($path);
        $stamp = $meta['uploaded_at'] ?? $meta['first_seen_at'] ?? null;

        if (! $stamp) {
            $stamp = now()->toIso8601String();
            $this->writeMeta($path, ['first_seen_at' => $stamp, 'size_bytes' => $this->files->size($path)]);
        }

        $modified = Carbon::createFromTimestamp($this->files->disk()->lastModified($path));

        return Carbon::parse($stamp)->max($modified);
    }

    /** Upload paths a resource row (even a trashed one) still points at - never orphans. */
    public function inUse(): Collection
    {
        return SubjectResource::withTrashed()
            ->where('url', 'like', $this->directory().'/%')
            ->pluck('url')
            ->flip();
    }

    /** Every parked file that is not a sidecar and that no resource uses. */
    public function orphanPaths(): Collection
    {
        $inUse = $this->inUse();

        return collect($this->files->disk()->files($this->directory()))
            ->reject(fn ($path) => str_ends_with($path, self::SIDECAR) || isset($inUse[$path]))
            ->values();
    }

    /**
     * The orphans the actor may handle, newest first, each with the resources whose file is missing
     * and that look like the same file (same size or same original name).
     *
     * @return array<int, array<string, mixed>>
     */
    public function orphans(LibraryActor $actor): array
    {
        $paths = $this->orphanPaths()->filter(fn ($path) => $this->canHandle($actor, $path));
        if ($paths->isEmpty()) {
            return [];
        }

        // one scan per actor: the CLI audit asks again for every subject
        $missing = $this->missingCache[spl_object_id($actor)] ??= $this->missingResources($actor);

        return $paths->map(function ($path) use ($missing) {
            $meta = $this->meta($path);
            $size = $meta['size_bytes'] ?? $this->files->size($path);
            $name = $meta['original_filename'] ?? null;

            $suggestions = $missing->filter(fn (SubjectResource $r) => ($size && (int) $r->size_bytes === (int) $size)
                || ($name && $r->original_filename && mb_strtolower($r->original_filename) === mb_strtolower($name)));

            return [
                'path' => $path,
                'original_filename' => $name,
                'size_bytes' => $size,
                'uploaded_at' => $meta['uploaded_at'] ?? null,
                'modified_at' => Carbon::createFromTimestamp($this->files->disk()->lastModified($path))->format('Y-m-d H:i'),
                'uploaded_by' => $this->uploaderName($meta),
                'is_video' => in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['mp4', 'mov', 'avi', 'webm', 'mkv'], true),
                'suggestions' => $suggestions->map(fn (SubjectResource $r) => $this->resourceOption($r))->values()->all(),
            ];
        })->sortByDesc('modified_at')->values()->all();
    }

    /** Resources the actor may relink: their file is missing and the actor can edit them. */
    public function missingResources(LibraryActor $actor): Collection
    {
        $subjectIds = $actor->subjectIds();

        return SubjectResource::query()
            ->with('subject')
            ->whereNotNull('url')
            ->where('url', 'not like', 'http%')
            ->when($subjectIds !== null, fn ($q) => $q->whereIn('subject_id', $subjectIds ?: [0]))
            ->orderByDesc('id')
            ->get()
            ->filter(fn (SubjectResource $r) => $this->files->isMissing($r) && $actor->canManageResource($r))
            ->values();
    }

    /** @return array<string, mixed> */
    public function resourceOption(SubjectResource $resource): array
    {
        return [
            'key' => $resource->getRouteKey(),
            'id' => (int) $resource->id,
            'title' => $resource->title,
            'type' => $resource->type,
            'subject' => $resource->subject?->name_ar ?? $resource->subject?->name_en,
            'original_filename' => $resource->original_filename,
            'is_active' => (bool) $resource->is_active,
        ];
    }

    /** Absolute path of an orphan, for the preview stream. */
    public function absolutePath(LibraryActor $actor, string $path): string
    {
        $this->assertOrphan($actor, $path);

        return $this->files->disk()->path($path);
    }

    /**
     * Give a resource whose file is missing the bytes of an orphan upload. Final, like replacing
     * a file from the edit form: the upload moves into the library and is journaled (not undoable).
     */
    public function relink(LibraryActor $actor, SubjectResource $resource, string $path): LibraryResult
    {
        $this->assertOrphan($actor, $path);

        if (! $actor->canManageResource($resource)) {
            throw LibraryException::forbidden('لا يمكنك تعديل هذا المورد.');
        }
        if (! $this->files->isMissing($resource)) {
            throw LibraryException::invalid('ملف هذا المورد موجود؛ استعمل «تعديل» لاستبداله.', 'file_present');
        }

        $meta = $this->meta($path);
        $source = $this->files->ingest($resource->type, $path, null, null, $meta['original_filename'] ?? $resource->original_filename);

        $resource->forceFill($source + ['processing_status' => 'ready'])->save();
        $this->missingCache = [];

        $this->journal->note($actor, 'resource.relink', "ربط ملف مرفوع سابقاً بالمورد «{$resource->title}»", (int) $resource->subject_id, $resource, [
            'from' => $path,
        ]);

        return new LibraryResult(
            "عاد ملف «{$resource->title}»."
                .($resource->is_active ? '' : ' المورد ما زال موقوفاً — أعد تشغيله ليراه الطلاب.'),
            null,
            ['resource' => $resource->fresh()],
        );
    }

    /** Delete an upload nobody needs (the form was cancelled, or an orphan is discarded). */
    public function discard(LibraryActor $actor, string $path): LibraryResult
    {
        $this->assertOrphan($actor, $path);

        $this->files->disk()->delete([$path, $path.self::SIDECAR]);

        return new LibraryResult('تم حذف الملف المرفوع.');
    }

    /** Remove the sidecar of an upload that became a resource. */
    public function forget(string $path): void
    {
        $this->files->disk()->delete($path.self::SIDECAR);
    }

    /** Sidecars whose upload is gone (moved into the library, or deleted by hand). */
    public function strayMetaPaths(): Collection
    {
        $disk = $this->files->disk();

        return collect($disk->files($this->directory()))
            ->filter(fn ($path) => str_ends_with($path, self::SIDECAR))
            ->reject(fn ($path) => $disk->exists(substr($path, 0, -strlen(self::SIDECAR))))
            ->values();
    }

    // ----------------------------------------------------------------- helpers

    private function canHandle(LibraryActor $actor, string $path): bool
    {
        if ($actor->isAdmin()) {
            return true;
        }

        $meta = $this->meta($path);

        return $actor->is($meta['uploaded_by_type'] ?? null, $meta['uploaded_by_id'] ?? null);
    }

    private function assertOrphan(LibraryActor $actor, string $path): void
    {
        $this->assertIncoming($path);

        if (str_ends_with($path, self::SIDECAR) || ! $this->files->disk()->exists($path)) {
            throw LibraryException::notFound('الملف المرفوع غير موجود.');
        }
        if (isset($this->inUse()[$path])) {
            throw LibraryException::invalid('هذا الملف مستعمل في مورد ولا يمكن تغييره من هنا.', 'upload_in_use');
        }
        if (! $this->canHandle($actor, $path)) {
            throw LibraryException::forbidden('هذا الملف رفعه مستخدم آخر.');
        }
    }

    private function assertIncoming(string $path): void
    {
        $prefix = $this->directory().'/';
        $rest = substr($path, strlen($prefix));

        if (! str_starts_with($path, $prefix) || $rest === '' || str_contains($rest, '/') || str_contains($path, '..') || str_contains($path, "\0")) {
            throw LibraryException::invalid('مسار الملف المرفوع غير صالح.', 'bad_upload_path');
        }
    }

    /** @param  array<string, mixed>  $meta */
    private function writeMeta(string $path, array $meta): void
    {
        $this->files->disk()->put($path.self::SIDECAR, json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    /** @param  array<string, mixed>  $meta */
    private function uploaderName(array $meta): ?string
    {
        return match ($meta['uploaded_by_type'] ?? null) {
            LibraryActor::ADMIN => Admin::find($meta['uploaded_by_id'] ?? 0)?->name,
            LibraryActor::TEACHER => Teacher::find($meta['uploaded_by_id'] ?? 0)?->name,
            default => null,
        };
    }
}
