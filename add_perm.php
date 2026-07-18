<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

$p = Permission::firstOrCreate(['name' => 'admin.teachers.change_password', 'guard_name' => 'admin']);
$role = Role::where('name', 'Super Admin')->first();
if ($role) {
    $role->givePermissionTo($p);
    echo "Permission created and assigned to Super Admin.\n";
} else {
    echo "Super Admin role not found.\n";
}
