<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add the permission if it doesn't exist
        $permission = Permission::firstOrCreate([
            'name' => 'admin.teachers.change_password', 
            'guard_name' => 'admin'
        ]);

        // Assign the permission to all admin roles
        $roles = Role::where('guard_name', 'admin')->get();
        foreach($roles as $role) {
            $role->givePermissionTo($permission);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally remove the permission
        // Permission::where('name', 'admin.teachers.change_password')->where('guard_name', 'admin')->delete();
    }
};
