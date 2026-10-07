<?php

namespace App\Console\Commands;

use App\Services\ResourceLibrary\IncomingUploads;
use App\Services\ResourceLibrary\ResourceFileStore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * A video uploaded in chunks is parked in `incoming/` until the form that owns it is saved. When the
 * form is abandoned the file stays there forever (hundreds of MB each). This lists - and, without
 * --dry-run, deletes - the parked files older than N hours that no resource uses, with their sidecars.
 *
 * Age counts from the upload time recorded in the sidecar; a file parked before sidecars existed is
 * stamped "first seen" on its first run and gets the full grace period from then on, so nobody loses
 * an upload they could still link from the "orphan uploads" panel. Scheduled daily (routes/console.php).
 *
 *   php artisan library:cleanup-incoming --dry-run
 *   php artisan library:cleanup-incoming --hours=48
 */
class LibraryCleanupIncoming extends Command
{
    /** When the cleanup last ran for real - library:doctor reads it to tell whether the scheduler runs. */
    public const LAST_RUN_KEY = 'library:cleanup-incoming:last-run';

    protected $signature = 'library:cleanup-incoming {--hours= : only files older than this (default: resource_library.upload.orphan_grace_hours)} {--dry-run : only list what would be deleted}';

    protected $description = 'Delete abandoned chunk uploads from the incoming/ area';

    public function handle(ResourceFileStore $files, IncomingUploads $uploads): int
    {
        $disk = $files->disk();
        $hours = (int) ($this->option('hours') ?? config('resource_library.upload.orphan_grace_hours', 168));
        $cutoff = now()->subHours($hours);
        $dry = (bool) $this->option('dry-run');

        $count = 0;
        $bytes = 0;

        foreach ($uploads->orphanPaths() as $path) {
            $startedAt = $uploads->startedAt($path);
            if ($startedAt->greaterThan($cutoff)) {
                $this->line($path.'  kept until '.$startedAt->copy()->addHours($hours)->format('Y-m-d H:i'), null, 'v');

                continue;
            }

            $size = (int) $disk->size($path);
            $this->line($path.'  ('.round($size / 1048576, 1).' MB)');

            if (! $dry) {
                $disk->delete([$path, $path.IncomingUploads::SIDECAR]);
            }

            $count++;
            $bytes += $size;
        }

        if (! $dry) {
            $disk->delete($uploads->strayMetaPaths()->all());
            Cache::forever(self::LAST_RUN_KEY, now()->toIso8601String());
        }

        $this->info(($dry ? 'Would delete ' : 'Deleted ').$count.' file(s), '.round($bytes / 1048576, 1)." MB (older than {$hours}h).");

        return self::SUCCESS;
    }
}
