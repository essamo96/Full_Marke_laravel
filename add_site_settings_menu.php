<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$group = \App\Models\PermissionsGroup::updateOrCreate(
    ['name' => 'site_settings'],
    [
        'name_ar' => 'إعدادات المنصة',
        'name_en' => 'Site Settings',
        'color' => 'dark',
        'icon' => 'bi-cogs',
        'sort' => 50,
        'status' => 1,
        'parent_id' => 21 // Under dashboard_settings "اعدادات الموقع"
    ]
);

$group->generateCrudPermissions();

$role = \Spatie\Permission\Models\Role::first();
if ($role) {
    $permissions = \Spatie\Permission\Models\Permission::where('name', 'like', 'admin.site_settings.%')->get();
    $role->givePermissionTo($permissions);
}

app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
echo "Done.\n";
