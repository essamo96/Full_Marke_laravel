<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('http://localhost:8000/admin/dashboard', 'GET');
Auth::guard('admin')->loginUsingId(1);
$response = $kernel->handle($request);
file_put_contents('dashboard.html', $response->getContent());
echo "Done";
