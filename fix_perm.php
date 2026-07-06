<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$admin = \App\Models\Admin::find(1);
if ($admin) {
    $admin->givePermissionTo('admin.role.permissions');
    echo "Permission added directly to admin 1.\n";
} else {
    echo "Admin not found.\n";
}
