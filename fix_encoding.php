<?php
$files = glob(__DIR__ . '/app/Http/Controllers/Admin/*.php');
foreach($files as $f){
    $c = file_get_contents($f);
    $n = str_replace("'name'=>'???????'", "'name'=>'العربية'", $c);
    if($c !== $n){
        file_put_contents($f, $n);
        echo "Updated " . basename($f) . "\n";
    }
}
