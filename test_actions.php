<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::create('/', 'GET'));

$subject = App\Models\Subject::first();
echo view('admin.subjects.parts.actions', ['subject' => $subject, 'program' => null])->render();
