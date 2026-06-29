<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\PermissionsGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminPermissionsSeeder extends Seeder
{
    protected array $modules = ['settings', 'users', 'permissions', 'sidebar'];

    protected array $moduleNamesAr = [
        'settings' => 'الإعدادات',
        'users' => 'المستخدمين',
        'permissions' => 'الصلاحيات',
        'sidebar' => 'القائمة الجانبية',
        'programs' => 'البرامج',
        'subjects' => 'المواد',
        'groups' => 'المجموعات',
        'teachers' => 'المدرسون',
        'payment_methods' => 'طرق الدفع',
        'approvals' => 'الطلبات المعلقة',
        'payments' => 'المدفوعات',
        'financial_reports' => 'التقارير المالية',
    ];

    protected array $academyModules = [
        'programs', 'subjects', 'groups', 'teachers',
        'payment_methods', 'approvals', 'payments', 'financial_reports',
    ];

    public function run(): void
    {
        $group = PermissionsGroup::updateOrCreate(
            ['name' => 'site_management'],
            ['name_ar' => 'إدارة الأكاديمية', 'name_en' => 'Academy Management', 'icon' => 'ki-duotone ki-setting-2', 'sort' => 90, 'status' => 1, 'parent_id' => 0]
        );

        foreach ($this->modules as $sort => $module) {
            $permissionGroup = PermissionsGroup::updateOrCreate(
                ['name' => $module],
                ['name_ar' => $this->moduleNamesAr[$module] ?? $module, 'name_en' => ucfirst($module), 'sort' => $sort, 'status' => 1, 'parent_id' => $group->id]
            );

            $permissionGroup->generateCrudPermissions();
        }

        $academyGroup = PermissionsGroup::updateOrCreate(
            ['name' => 'academy'],
            ['name_ar' => 'النظام الأكاديمي', 'name_en' => 'Academic System', 'icon' => 'ki-duotone ki-teacher', 'sort' => 10, 'status' => 1, 'parent_id' => 0]
        );

        foreach ($this->academyModules as $sort => $module) {
            $permissionGroup = PermissionsGroup::updateOrCreate(
                ['name' => $module],
                ['name_ar' => $this->moduleNamesAr[$module] ?? $module, 'name_en' => ucfirst(str_replace('_', ' ', $module)), 'sort' => $sort, 'status' => 1, 'parent_id' => $academyGroup->id]
            );

            $permissionGroup->generateCrudPermissions();
        }

        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'admin']);
        $superAdmin->syncPermissions(Permission::where('guard_name', 'admin')->get());

        Role::firstOrCreate(['name' => 'Data Entry', 'guard_name' => 'admin']);
        Role::firstOrCreate(['name' => 'Accountant', 'guard_name' => 'admin']);

        $admin = Admin::firstOrCreate(
            ['email' => 'admin@fullmarkacademy.test'],
            ['name' => 'Super Admin', 'password' => Hash::make('password'), 'status' => 1]
        );
        $admin->syncRoles([$superAdmin->name]);

        Cache::forget('spatie.permission.cache');
    }
}
