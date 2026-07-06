<?php

namespace App\Helpers;

if (!function_exists('App\Helpers\translate')) {
    function translate($key)
    {
        return __('app.' . $key);
    }
}
