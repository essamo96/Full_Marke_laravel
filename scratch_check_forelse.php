<?php
$dir = new RecursiveDirectoryIterator('resources/views');
$ite = new RecursiveIteratorIterator($dir);
foreach($ite as $file) {
    if ($file->isFile() && strpos($file->getFilename(), '.blade.php') !== false) {
        $c = file_get_contents($file->getRealPath());
        $f = substr_count($c, '@forelse');
        $e = substr_count($c, '@endforelse');
        $em = substr_count($c, '@empty');
        if ($f !== $e || $f !== $em) {
            echo "Mismatch: " . $file->getRealPath() . " ($f forelse, $em empty, $e endforelse)\n";
        }
    }
}
