<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Remove general_settings permissions and group
$group = \App\Models\PermissionsGroup::where('name', 'general_settings')->first();
if ($group) {
    \Spatie\Permission\Models\Permission::where('name', 'like', 'admin.general_settings.%')->delete();
    $group->delete();
    echo "Removed general_settings group and its permissions.\n";
}

// Generate permissions for site_settings just in case
$group2 = \App\Models\PermissionsGroup::where('name', 'site_settings')->first();
if ($group2) {
    $group2->generateCrudPermissions();
    $role = \Spatie\Permission\Models\Role::first();
    if ($role) {
        $permissions = \Spatie\Permission\Models\Permission::where('name', 'like', 'admin.site_settings.%')->get();
        $role->givePermissionTo($permissions);
    }
    echo "Ensured permissions exist for site_settings.\n";
}

app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
echo "Done.\n";
