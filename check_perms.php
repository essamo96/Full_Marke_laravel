<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$admin = \App\Models\Admin::find(1);
if ($admin) {
    echo "Admin 1 Permissions:\n";
    print_r($admin->getAllPermissions()->pluck('name')->toArray());
}

$role = \Spatie\Permission\Models\Role::find(1);
if ($role) {
    echo "\nRole 1 Permissions:\n";
    print_r($role->permissions->pluck('name')->toArray());
}
