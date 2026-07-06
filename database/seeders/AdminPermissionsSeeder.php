<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\PermissionsGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class AdminPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Dashboard permission (Always needed)
        Permission::firstOrCreate(['name' => 'admin.dashboard.view', 'guard_name' => 'admin']);

        // 2. Fetch all groups and assign permissions dynamically
        $groups = PermissionsGroup::all();

        foreach ($groups as $group) {
            if ($group->parent_id == 0 || $group->parent_id === null) {
                // Parent group -> only 'view'
                $group->generateCrudPermissions(['view']);
            } else {
                // Child group -> 7 permissions
                $group->generateCrudPermissions(['view', 'add', 'edit', 'delete', 'status', 'import', 'export']);
            }
        }

        // 3. Sync all permissions to Super Admin role
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'admin']);
        $superAdmin->syncPermissions(Permission::where('guard_name', 'admin')->get());

        // Assign Super Admin role to the main admin user
        $admin = Admin::firstOrCreate(
            ['email' => 'admin@fullmarkacademy.test'],
            ['name' => 'Super Admin', 'password' => Hash::make('password'), 'status' => 1]
        );
        
        if (!$admin->hasRole('Super Admin')) {
            $admin->syncRoles([$superAdmin->name]);
        }

        Cache::forget('spatie.permission.cache');
    }
}
