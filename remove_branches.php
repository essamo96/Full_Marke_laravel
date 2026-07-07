<?php

$addFiles = glob(__DIR__ . '/resources/views/admin/*/add.blade.php');
$viewFiles = glob(__DIR__ . '/resources/views/admin/*/view.blade.php');

foreach($addFiles as $f) {
    $c = file_get_contents($f);
    
    // Pattern 1: @if ($company_id == 0) ... @else ... @endif
    $c = preg_replace('/@if\s*\(\s*\$company_id\s*==\s*0\s*\).*?@else\s*(.*?)\s*@endif/is', '$1', $c);
    
    // Pattern 2: @if (isset($company_id) && $company_id == 0) ... @else ... @endif
    $c = preg_replace('/@if\s*\(\s*isset\(\$company_id\)\s*&&\s*\$company_id\s*==\s*0\s*\).*?@else\s*(.*?)\s*@endif/is', '$1', $c);

    // Pattern 3: general_settings/add.blade.php has similar without @else if commented out? Wait, let's just make sure.
    file_put_contents($f, $c);
    echo "Updated Add: " . basename(dirname($f)) . "\n";
}

foreach($viewFiles as $f) {
    $c = file_get_contents($f);
    
    // Remove TH
    $c = preg_replace('/<th[^>]*>\s*\{\{\s*\\\App\\\Helpers\\\translate\(\'company_id\'\)\s*\}\}\s*<\/th>/is', '', $c);
    
    // Remove JS column (e.g. { data: "company_id" }, or data: "company_id")
    $c = preg_replace('/\{\s*data\s*:\s*["\']company_id["\']\s*\}?,?\s*/is', '', $c);
    
    // Remove Filter dropdown block
    $c = preg_replace('/<div[^>]*>\s*<label for="companies" class="form-label">.*?<\/select>\s*<\/div>/is', '', $c);
    
    file_put_contents($f, $c);
    echo "Updated View: " . basename(dirname($f)) . "\n";
}

echo "Done.\n";
