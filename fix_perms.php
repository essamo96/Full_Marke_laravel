<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$group = \App\Models\PermissionsGroup::where('name', 'general_settings')->first();
if ($group) {
    $group->generateCrudPermissions();
    echo "Permissions generated for general_settings.\n";
} else {
    echo "Group general_settings not found.\n";
}

// Assign these permissions to the super admin role (usually ID 1)
$role = \Spatie\Permission\Models\Role::first();
if ($role) {
    $permissions = \Spatie\Permission\Models\Permission::where('name', 'like', 'admin.general_settings.%')->get();
    $role->givePermissionTo($permissions);
    echo "Permissions assigned to admin role.\n";
}

app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
echo "Cache cleared.\n";
