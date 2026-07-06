<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$permission = \App\Models\Permission::firstOrCreate([
    'name' => 'admin.role.permissions',
    'guard_name' => 'web',
    'group_id' => \App\Models\PermissionsGroup::where('name_en', 'role')->first()->id ?? 0
]);

$role = \Spatie\Permission\Models\Role::find(1);
if ($role) {
    $role->givePermissionTo($permission);
}

echo "Permission added to role.";
