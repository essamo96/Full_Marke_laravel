<?php

$dir = __DIR__ . '/resources/views/admin';

function processDirectory($dir) {
    $files = glob($dir . '/*/view.blade.php');
    
    foreach ($files as $file) {
        $content = file_get_contents($file);
        
        $originalContent = $content;
        
        // Find the filter block between card-title and card-body
        if (preg_match('/(<div class="card-title w-100 mb-0 row">.*?)(<\/div>\s*<\/div>\s*<div class="card-body py-4">)/is', $content, $matches)) {
            $filterBlock = $matches[1];
            
            // 1. Remove all form-label labels
            $filterBlock = preg_replace('/<label[^>]*class="[^"]*form-label[^"]*"[^>]*>.*?<\/label>\s*/is', '', $filterBlock);
            
            // 2. Adjust col-lg-* col-md-* sizes to col-lg-4 col-md-4
            // Some classes have blade conditions like {{ $program ? 'col-lg-5' : 'col-lg-5' }}
            // We match class="... col-sm-12 mb-3"
            // Let's do a more generic replacement:
            // Match class="..." that contains col-sm-12 and mb-3
            $filterBlock = preg_replace_callback('/class="([^"]*?col-sm-12[^"]*?)"/is', function($m) {
                $cls = $m[1];
                // Remove existing col-lg-*, col-md-* and blade conditionals
                $cls = preg_replace('/\{\{.*?\}\}/', '', $cls);
                $cls = preg_replace('/col-lg-\d+/', '', $cls);
                $cls = preg_replace('/col-md-\d+/', '', $cls);
                $cls = preg_replace('/col-2/', '', $cls); // some use col-2
                
                // cleanup spaces
                $cls = trim(preg_replace('/\s+/', ' ', $cls));
                
                // Add col-lg-4 col-md-4
                return 'class="col-lg-4 col-md-4 ' . $cls . '"';
            }, $filterBlock);
            
            $content = str_replace($matches[1], $filterBlock, $content);
            
            if ($content !== $originalContent) {
                file_put_contents($file, $content);
                echo "Updated: $file\n";
            }
        }
    }
}

processDirectory($dir);
echo "Done!\n";
