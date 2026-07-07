<?php

function appendTranslations($file) {
    if (!file_exists($file)) return;
    $content = file_get_contents($file);
    
    // Check if 'teams' exists
    if (strpos($content, "'teams'") === false && strpos($content, '"teams"') === false) {
        // add 'teams' => 'فريق العمل',
        $content = preg_replace('/];\s*$/', "    'teams' => 'فريق العمل',\n    'faqs' => 'الأسئلة الشائعة',\n];", $content);
    }
    
    file_put_contents($file, $content);
}

appendTranslations(__DIR__ . '/lang/ar/app.php');
appendTranslations(__DIR__ . '/lang/en/app.php');

echo "Done adding translations.\n";
