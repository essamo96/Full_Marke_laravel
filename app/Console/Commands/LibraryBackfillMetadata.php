<?php

namespace App\Console\Commands;

use App\Models\SubjectResource;
use App\Services\ResourceLibrary\ResourceFileStore;
use Illuminate\Console\Command;

/**
 * Fill the central repository's file metadata (size, mime type, content fingerprint) for resources
 * uploaded before those columns existed. The fingerprint is what lets the quality check spot the
 * same video uploaded twice.
 *
 *   php artisan library:backfill-metadata [--dry-run]
 */
class LibraryBackfillMetadata extends Command
{
    protected $signature = 'library:backfill-metadata {--dry-run : only report what would be filled}';

    protected $description = 'Record size / mime type / fingerprint of stored resource files (enables duplicate detection)';

    public function handle(ResourceFileStore $files): int
    {
        $filled = $missing = 0;

        SubjectResource::withTrashed()
            ->whereNotNull('url')
            ->where(fn ($q) => $q->whereNull('size_bytes')->orWhereNull('content_hash'))
            ->orderBy('id')
            ->each(function (SubjectResource $resource) use ($files, &$filled, &$missing) {
                if ($resource->isExternalLink()) {
                    return;
                }

                if (! $files->exists($resource)) {
                    $missing++;
                    $this->warn("#{$resource->id} «{$resource->title}»: file not found ({$resource->url})");

                    return;
                }

                $filled++;
                $this->line("#{$resource->id} «{$resource->title}»");

                if ($this->option('dry-run')) {
                    return;
                }

                $resource->forceFill([
                    'size_bytes' => $files->size($resource->url),
                    'mime_type' => $files->mime($resource->url),
                    'content_hash' => $files->fingerprint($resource->url),
                ])->saveQuietly();
            });

        $this->info(($this->option('dry-run') ? 'Would fill ' : 'Filled ')."{$filled} resource(s); {$missing} file(s) missing on disk.");

        return self::SUCCESS;
    }
}
