<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ResourceLibrarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. إضافة الرابط في القائمة الجانبية للأدمن (جدول permissions_groups)
        DB::table('permissions_groups')->updateOrInsert(
            ['name' => 'resource-library'],
            [
                'name_en' => 'Resource Library',
                'name_ar' => 'مكتبة المرفقات',
                'icon' => 'ki-duotone ki-briefcase',
                'color' => '#d4af37'
            ]
        );

        // 2. إنشاء صلاحية العرض في نظام Spatie
        $perm = Permission::firstOrCreate(['name' => 'admin.resource-library.view', 'guard_name' => 'admin']);

        // 3. إعطاء الصلاحية للأدمن الرئيسي
        $role = Role::findByName('super-admin', 'admin');
        if ($role) {
            $role->givePermissionTo($perm);
        }
    }
}
