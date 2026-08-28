<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PermissionsGroup;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ResourceArchiveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $routeName = 'resource-archive';

        $maxSort = PermissionsGroup::where('parent_id', 0)->max('sort') ?? 0;

        // 1. إضافة الرابط في القائمة الجانبية للأدمن (جدول permissions_groups)
        DB::table('permissions_groups')->updateOrInsert(
            ['name' => $routeName],
            [
                'name_en' => 'Resource Archive',
                'name_ar' => 'أرشيف المرفقات',
                'icon' => 'ki-trash',
                'color' => 'danger',
                'parent_id' => 0,
                'sort' => $maxSort + 1,
            ]
        );

        // 2. إنشاء صلاحية العرض في نظام Spatie
        $perm = Permission::firstOrCreate(['name' => 'admin.resource-archive.view', 'guard_name' => 'admin']);

        // 3. إعطاء الصلاحية للأدمن الرئيسي
        try {
            $role = Role::findByName('Super Admin', 'admin');
            $role->givePermissionTo($perm);
        } catch (\Exception $e) {
            // Ignore if role doesn't exist
            // Try assigning directly to admin ID 1
            $admin = \App\Models\Admin::find(1);
            if ($admin) {
                $admin->givePermissionTo($perm);
            }
        }
    }
}
