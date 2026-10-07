<?php

namespace App\Console\Commands;

use App\Services\ResourceLibrary\IncomingUploads;
use App\Services\ResourceLibrary\ResourceFileStore;
use App\Support\Library\UploadLimits;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Checks that this server can take what the resource library asks of it: chunk size vs the PHP
 * request limits, time limits for merging a big video, the storage directories, free disk space,
 * parked uploads and whether the scheduler (daily incoming/ cleanup) actually runs.
 *
 *   php artisan library:doctor
 *
 * Note: the CLI may read another php.ini than the web server. The values shown are the CLI ones;
 * the page itself always uses the web server's limits (UploadLimits is evaluated per request).
 */
class LibraryDoctor extends Command
{
    protected $signature = 'library:doctor';

    protected $description = 'Check the server settings the resource library depends on (uploads, storage, scheduler)';

    private int $errors = 0;

    private int $warnings = 0;

    public function handle(ResourceFileStore $files, IncomingUploads $uploads): int
    {
        $this->info('Resource library — server check ('.php_ini_loaded_file().')');

        $this->uploads();
        $this->storage($files, $uploads);
        $this->scheduler();

        $this->newLine();
        $summary = "{$this->errors} error(s), {$this->warnings} warning(s).";
        $this->errors ? $this->error($summary) : $this->info($summary);

        return $this->errors ? self::FAILURE : self::SUCCESS;
    }

    private function uploads(): void
    {
        $this->section('Chunked uploads');

        $configured = (int) config('resource_library.upload.chunk_bytes');
        $chunk = UploadLimits::chunkBytes();
        $uploadMax = UploadLimits::iniBytes('upload_max_filesize');
        $postMax = UploadLimits::iniBytes('post_max_size');

        $this->row('upload_max_filesize', UploadLimits::human($uploadMax));
        $this->row('post_max_size', UploadLimits::human($postMax));
        $this->row('configured chunk', UploadLimits::human($configured));
        $this->row('chunk used by the browser', UploadLimits::human($chunk));
        $this->row('largest file accepted', UploadLimits::human(UploadLimits::maxFileBytes()));

        if ($uploadMax && $postMax && $postMax < $uploadMax) {
            $this->warn('  ! post_max_size is smaller than upload_max_filesize: raise post_max_size to at least upload_max_filesize + 1M.');
            $this->warnings++;
        }
        if ($chunk < $configured) {
            $this->warn('  ! PHP limits force smaller chunks than configured (more requests per video). Raise upload_max_filesize/post_max_size to at least '
                .UploadLimits::human($configured + UploadLimits::MARGIN_BYTES).' for full speed.');
            $this->warnings++;
        }
        $this->line('  · the web server must accept a body of '.UploadLimits::human($chunk + UploadLimits::MARGIN_BYTES).' (nginx: client_max_body_size 16M or more).');

        $execution = (int) ini_get('max_execution_time');
        $input = (int) ini_get('max_input_time');
        $this->row('max_execution_time', $execution === 0 ? 'unlimited' : $execution.'s');
        $this->row('max_input_time', $input <= 0 ? 'unlimited' : $input.'s');
        if ($execution > 0 && $execution < 120) {
            $this->warn('  ! max_execution_time < 120s: merging the last chunk of a multi-GB video may time out (300 recommended).');
            $this->warnings++;
        }
        if ($input > 0 && $input < 120) {
            $this->warn('  ! max_input_time < 120s: one chunk on a slow connection may be cut off.');
            $this->warnings++;
        }

        $this->row('session lifetime', config('session.lifetime').' min (kept alive every '.UploadLimits::forClient()['keepalive_seconds'].'s while uploading)');
    }

    private function storage(ResourceFileStore $files, IncomingUploads $uploads): void
    {
        $this->section('Storage (disk "'.config('resource_library.disk').'")');

        $disk = $files->disk();
        foreach ([config('resource_library.directory'), $uploads->directory()] as $directory) {
            $path = $disk->path($directory);
            if (! is_dir($path)) {
                @mkdir($path, 0775, true);
            }

            if (is_dir($path) && is_writable($path)) {
                $this->row($directory.'/', 'writable  '.$path);
            } else {
                $this->error("  ✗ {$directory}/ is not writable: {$path}");
                $this->errors++;
            }
        }

        $chunks = storage_path('app/'.trim((string) config('chunk-upload.storage.chunks', 'chunks'), '/'));
        $this->row('chunk parts', is_dir($chunks) ? $chunks : $chunks.' (created on first upload)');

        $free = @disk_free_space($disk->path(''));
        if ($free !== false) {
            $this->row('free space', UploadLimits::human((int) $free));
            // a finished upload exists twice for a moment: the parts and the merged file
            $needed = 2 * UploadLimits::maxFileBytes();
            if ($free < $needed) {
                $this->warn('  ! less free space than two of the largest accepted video ('.UploadLimits::human($needed).').');
                $this->warnings++;
            }
        }

        $orphans = $uploads->orphanPaths();
        $bytes = $orphans->sum(fn ($path) => (int) $disk->size($path));
        $this->row('parked uploads (incoming/)', $orphans->count().' file(s), '.UploadLimits::human((int) $bytes)
            .' — kept '.config('resource_library.upload.orphan_grace_hours').'h, then deleted');
    }

    private function scheduler(): void
    {
        $this->section('Scheduler');

        $last = Cache::get(LibraryCleanupIncoming::LAST_RUN_KEY);
        if ($last && Carbon::parse($last)->greaterThan(now()->subHours(48))) {
            $this->row('library:cleanup-incoming', 'last ran '.Carbon::parse($last)->diffForHumans());

            return;
        }

        $this->warn('  ! library:cleanup-incoming has not run in the last 48h'.($last ? ' (last: '.$last.')' : '').'.');
        $this->line('    The scheduler must be triggered every minute:');
        $this->line('      Linux cron:      * * * * * cd '.base_path().' && php artisan schedule:run >> /dev/null 2>&1');
        $this->line('      Windows:         schtasks /create /sc minute /tn "laravel-schedule" /tr "php '.base_path('artisan').' schedule:run"');
        $this->warnings++;
    }

    private function section(string $title): void
    {
        $this->newLine();
        $this->line("<comment>{$title}</comment>");
    }

    private function row(string $label, string $value): void
    {
        $this->line('  ✓ '.str_pad($label, 28).$value);
    }
}
