<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    App\Models\SubjectResource::create([
        'subject_id' => 9,
        'educational_lesson_id' => 1,
        'title' => 'Test',
        'type' => 'link',
        'category' => 'link',
        'url' => 'https://drive.google.com/test',
        'processing_status' => 'ready',
        'is_active' => true
    ]);
    echo 'SUCCESS';
} catch (\Exception $e) {
    echo 'ERROR: ' . $e->getMessage();
}
