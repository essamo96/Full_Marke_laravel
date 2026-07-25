<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $parent = \App\Models\PermissionsGroup::where('name', 'outside_settings')->first();

        $group = \App\Models\PermissionsGroup::create([
            'name' => 'pos_points',
            'name_ar' => 'نقاط بيع الكتب',
            'name_en' => 'Book Sale Points',
            'color' => 'dark',
            'icon' => 'bi-shop',
            'sort' => 9,
            'status' => 1,
            'parent_id' => $parent->id ?? 0,
        ]);

        $group->generateCrudPermissions(['view', 'add', 'edit', 'delete', 'status']);

        $role = \Spatie\Permission\Models\Role::where('name', 'Super Admin')->where('guard_name', 'admin')->first();
        if ($role) {
            $role->givePermissionTo(\Spatie\Permission\Models\Permission::where('group_id', $group->id)->pluck('name'));
        }
    }

    public function down(): void
    {
        $group = \App\Models\PermissionsGroup::where('name', 'pos_points')->first();
        if ($group) {
            \Spatie\Permission\Models\Permission::where('group_id', $group->id)->delete();
            $group->delete();
        }
    }
};
