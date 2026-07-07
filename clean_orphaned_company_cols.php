<?php

$controllers = glob(__DIR__ . '/app/Http/Controllers/Admin/*.php');

foreach ($controllers as $file) {
    $c = file_get_contents($file);
    
    // 1. Remove `->addColumn(function ($row) { ... $row->company ... })` blocks
    // This matches `->addColumn(function` followed by anything that doesn't contain `->addColumn`, 
    // and ends with `})` IF it contains `$row->company`
    $c = preg_replace_callback('/->addColumn\(\s*function\s*\(\$row\)\s*\{(.*?)\}\)/is', function ($m) {
        if (strpos($m[1], '$row->company') !== false) {
            return ''; // delete the entire addColumn block
        }
        return $m[0]; // keep it
    }, $c);

    // 2. Also remove `->editColumn(function ($row) { ... $row->company ... })` just in case
    $c = preg_replace_callback('/->editColumn\(\s*function\s*\(\$row\)\s*\{(.*?)\}\)/is', function ($m) {
        if (strpos($m[1], '$row->company') !== false) {
            return ''; 
        }
        return $m[0];
    }, $c);

    // 3. Remove `use App\Models\Company;`
    $c = preg_replace('/use\s+App\\\\Models\\\\Company;\s*/', '', $c);

    // 4. Remove `->with('company')` or `->with(['company'])`
    $c = preg_replace('/->with\([\'"]company[\'"]\)/', '', $c);
    $c = preg_replace('/->with\(\[[\'"]company[\'"]\]\)/', '', $c);
    // Replace `->with(['translation', 'company.translation'])` with `->with(['translation'])`
    $c = str_replace('\'company.translation\'', '', $c);
    $c = str_replace('"company.translation"', '', $c);
    // clean up empty array items `->with(['translation', ])`
    $c = preg_replace('/,\s*\]/', ']', $c);

    file_put_contents($file, $c);
}

echo "Done.\n";
