<?php

$controllers = glob(__DIR__ . '/app/Http/Controllers/Admin/*.php');

foreach ($controllers as $file) {
    $c = file_get_contents($file);
    
    // Replace Company::all() and Company::where('status', 1)->get() with []
    $c = preg_replace('/Company::(all\(\)|where\([^\)]+\)->get\(\))/i', '[]', $c);
    
    file_put_contents($file, $c);
}

echo "Done.\n";
