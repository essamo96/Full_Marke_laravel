<?php

namespace App\Support\Library;

/**
 * Sizes of the chunked uploader, reconciled with what this PHP really accepts.
 *
 * A chunk travels as one multipart request, so it must fit both `upload_max_filesize` (the file
 * part) and `post_max_size` (the whole body, with the form fields and multipart boundaries). The
 * browser is handed the smaller of the configured chunk size and those limits minus a margin, so a
 * server with `upload_max_filesize = 2M` keeps working instead of failing every chunk.
 */
final class UploadLimits
{
    /** Room left in a request for the form fields and the multipart framing. */
    public const MARGIN_BYTES = 512 * 1024;

    public const MIN_CHUNK_BYTES = 256 * 1024;

    /** Chunk size the browser must use. */
    public static function chunkBytes(): int
    {
        $configured = max(self::MIN_CHUNK_BYTES, (int) config('resource_library.upload.chunk_bytes'));
        $server = self::serverRequestBytes();

        if ($server === null) {
            return $configured;
        }

        return max(self::MIN_CHUNK_BYTES, min($configured, $server - self::MARGIN_BYTES));
    }

    /** Largest file (sum of every chunk) the uploader accepts. */
    public static function maxFileBytes(): int
    {
        return max(1, (int) config('resource_library.upload.max_video_bytes'));
    }

    /** The smallest of upload_max_filesize / post_max_size, null when both are unlimited. */
    public static function serverRequestBytes(): ?int
    {
        $limits = array_filter([
            self::iniBytes('upload_max_filesize'),
            self::iniBytes('post_max_size'),
        ]);

        return $limits ? min($limits) : null;
    }

    /** What the resource form needs (RL_CONFIG.upload). */
    public static function forClient(): array
    {
        return [
            'chunk_bytes' => self::chunkBytes(),
            'max_video_bytes' => self::maxFileBytes(),
            'keepalive_seconds' => max(30, (int) config('resource_library.upload.keepalive_seconds')),
        ];
    }

    /** An ini size ("8M", "2G", "512K", "1048576") in bytes; null for 0 / -1 / unset (no limit). */
    public static function iniBytes(string $key): ?int
    {
        return self::parseBytes((string) ini_get($key));
    }

    public static function parseBytes(string $value): ?int
    {
        $value = trim($value);
        if ($value === '' || ! preg_match('/^(-?\d+)\s*([kmg]?)b?$/i', $value, $m)) {
            return null;
        }

        $number = (int) $m[1];
        if ($number <= 0) {
            return null;
        }

        return $number * match (strtolower($m[2])) {
            'g' => 1024 ** 3,
            'm' => 1024 ** 2,
            'k' => 1024,
            default => 1,
        };
    }

    public static function human(?int $bytes): string
    {
        if ($bytes === null) {
            return 'غير محدود';
        }
        if ($bytes >= 1024 ** 3) {
            return round($bytes / 1024 ** 3, 1).' GB';
        }

        return round($bytes / 1024 ** 2, 1).' MB';
    }
}
