<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $group = \App\Models\PermissionsGroup::create([
            'name' => 'file_manager',
            'name_ar' => 'مدير الملفات',
            'name_en' => 'File Manager',
            'color' => 'primary',
            'icon' => 'ki-duotone ki-folder',
            'sort' => 15,
            'status' => 1,
            'parent_id' => 0,
        ]);

        $group->generateCrudPermissions(['view', 'add', 'edit', 'delete']);

        $role = \Spatie\Permission\Models\Role::where('name', 'Super Admin')->where('guard_name', 'admin')->first();
        if ($role) {
            $role->givePermissionTo(\Spatie\Permission\Models\Permission::where('group_id', $group->id)->pluck('name'));
        }
    }

    public function down(): void
    {
        $group = \App\Models\PermissionsGroup::where('name', 'file_manager')->first();
        if ($group) {
            \Spatie\Permission\Models\Permission::where('group_id', $group->id)->delete();
            $group->delete();
        }
    }
};
