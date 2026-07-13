<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

\Illuminate\Support\Facades\Auth::guard('admin')->loginUsingId(1);
$request = Illuminate\Http\Request::create('http://localhost:8000/admin/dashboard', 'GET');
$app->instance('request', $request);

$permission_group = new \App\Models\PermissionsGroup();
$sidebar = $permission_group->getAllParentPermissionGroup();
$html = view('admin.layout.mainLayouts.sidebar', ['sidebar' => $sidebar, 'current_route' => new \stdClass()])->render();

file_put_contents('sidebar.html', $html);
echo "Done";
