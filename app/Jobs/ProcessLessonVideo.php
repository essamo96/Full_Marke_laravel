<?php

namespace App\Jobs;

use App\Models\SubjectResource;
use FFMpeg\Format\Video\X264;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class ProcessLessonVideo implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 3600;

    /**
     * Resolution ladder for adaptive-bitrate HLS. Academic screen-recording +
     * talking-head content never benefits from more than 720p, so we cap
     * there to keep storage and encoding time down.
     */
    private const RENDITIONS = [
        ['name' => '360p', 'width' => 640, 'height' => 360, 'kbps' => 800],
        ['name' => '480p', 'width' => 854, 'height' => 480, 'kbps' => 1400],
        ['name' => '720p', 'width' => 1280, 'height' => 720, 'kbps' => 2800],
    ];

    public function __construct(public int $resourceId)
    {
    }

    public function handle(): void
    {
        $resource = SubjectResource::find($this->resourceId);

        if (! $resource || ! $resource->isVideo()) {
            return;
        }

        $sourcePath = $resource->url; // e.g. "incoming/{uuid}.mp4"
        $outputDir = "resources/{$resource->id}";

        try {
            $exporter = FFMpeg::fromDisk('protected_videos')
                ->open($sourcePath)
                ->exportForHLS()
                ->onProgress(function ($percentage) use ($resource) {
                    // Send progress event every few percent or at least send it
                    // Laravel FFMpeg's onProgress closure gets called continuously
                    \App\Events\VideoProcessingProgress::dispatch($resource->id, $percentage);
                })
                // One key per 6 segments (~60s of video at the default 10s segment length) instead of
                // per-segment: a leaked key only ever exposes a short window, but the player no longer
                // needs a separate key-fetch round trip for every single segment — each extra request
                // costs real time on a dev server, so this cuts a meaningful chunk of startup latency.
                ->withRotatingEncryptionKey(function ($keyFilename, $encryptionKey) use ($outputDir) {
                    Storage::disk('protected_videos')->put("{$outputDir}/{$keyFilename}", $encryptionKey);
                }, 6);

            foreach (self::RENDITIONS as $rendition) {
                $exporter->addFormat(
                    (new X264('aac'))->setKiloBitrate($rendition['kbps']),
                    function ($filters) use ($rendition) {
                        $filters->resize($rendition['width'], $rendition['height']);
                    }
                );
            }

            $exporter->toDisk('protected_videos')->save("{$outputDir}/master.m3u8");

            $resource->update([
                'hls_path' => "{$outputDir}/master.m3u8",
                'processing_status' => 'ready',
                'processing_error' => null,
            ]);

            Storage::disk('protected_videos')->delete($sourcePath);
        } catch (\Throwable $e) {
            Log::error('ProcessLessonVideo failed', [
                'resource_id' => $resource->id,
                'error' => $e->getMessage(),
            ]);

            $resource->update([
                'processing_status' => 'failed',
                'processing_error' => $e->getMessage(),
            ]);
        }
    }
}
