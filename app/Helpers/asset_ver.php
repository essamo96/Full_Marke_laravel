<?php

/**
 * asset() with automatic cache-busting: appends the file's last-modified
 * timestamp instead of a manually-typed "?v=X.X". Static assets are served
 * with a 7-day Cache-Control, so a forgotten manual bump used to mean
 * browsers (mobile especially, since that's where people re-visit over
 * days) kept serving a stale/broken CSS or JS file for up to a week after
 * a fix was deployed. The mtime changes automatically on every edit, so
 * this can never go stale.
 */
if (!function_exists('asset_ver')) {
    function asset_ver(string $path): string
    {
        $fullPath = public_path($path);
        $version = is_file($fullPath) ? filemtime($fullPath) : time();

        return asset($path) . '?v=' . $version;
    }
}
