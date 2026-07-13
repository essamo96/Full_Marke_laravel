<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/admin/programs/list', 'GET', [
    'draw' => 1,
    'columns' => [
        ['data' => 'image', 'name' => 'image', 'searchable' => true, 'orderable' => false],
        ['data' => 'name', 'name' => 'name', 'searchable' => true, 'orderable' => true]
    ]
]);
$request->headers->set('X-Requested-With', 'XMLHttpRequest');
auth('admin')->loginUsingId(1);
$response = $kernel->handle($request);

echo $response->getContent();
