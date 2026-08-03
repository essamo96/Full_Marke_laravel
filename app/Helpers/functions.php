<?php

namespace App\Helpers;

if (!function_exists('App\Helpers\translate')) {
    function translate($key)
    {
        return __('app.' . $key);
    }
}

// Loaded via require here (not a separate composer.json "files" entry) so it
// works immediately on deploy without depending on `composer dump-autoload`
// having already run on the server — this file is already registered.
require_once __DIR__ . '/asset_ver.php';
