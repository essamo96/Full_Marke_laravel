<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use App\Models\PermissionsGroup;

return new class extends Migration
{
    public function up(): void
    {
        $group = PermissionsGroup::where('name', 'groups')->first();
        if ($group) {
            Permission::create([
                'name' => 'admin.groups.generate_code',
                'name_ar' => 'إنشاء كود الانضمام للمجموعة',
                'name_en' => 'Generate Group Join Code',
                'group_id' => $group->id,
                'guard_name' => 'admin',
            ]);
            
            $role = \Spatie\Permission\Models\Role::where('name', 'super_admin')->first();
            if ($role) {
                $role->givePermissionTo('admin.groups.generate_code');
            }
        }
    }

    public function down(): void
    {
        Permission::where('name', 'admin.groups.generate_code')->delete();
    }
};
