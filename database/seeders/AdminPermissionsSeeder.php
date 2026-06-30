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
    protected array $modules = ['settings', 'users', 'permissions', 'sidebar', 'site_settings'];

    protected array $moduleNamesAr = [
        'settings' => 'الإعدادات',
        'users' => 'المستخدمين',
        'permissions' => 'الصلاحيات',
        'sidebar' => 'القائمة الجانبية',
        'site_settings' => 'إعدادات الموقع',
        'programs' => 'البرامج',
        'subjects' => 'المواد',
        'groups' => 'المجموعات',
        'teachers' => 'المدرسون',
        'students' => 'الطلاب',
        'payment_methods' => 'طرق الدفع',
        'approvals' => 'الطلبات المعلقة',
        'payments' => 'المدفوعات',
        'financial_reports' => 'التقارير المالية',
    ];

    // ki- icon (Keenicons duotone, verified present in plugins.bundle.css) per sidebar module.
    protected array $moduleIcons = [
        'settings' => 'ki-duotone ki-setting-2',
        'users' => 'ki-duotone ki-user',
        'permissions' => 'ki-duotone ki-shield-tick',
        'sidebar' => 'ki-duotone ki-burger-menu-2',
        'site_settings' => 'ki-duotone ki-setting-3',
        'programs' => 'ki-duotone ki-graph-up',
        'subjects' => 'ki-duotone ki-book-open',
        'groups' => 'ki-duotone ki-people',
        'teachers' => 'ki-duotone ki-teacher',
        'students' => 'ki-duotone ki-profile-user',
        'payment_methods' => 'ki-duotone ki-credit-cart',
        'approvals' => 'ki-duotone ki-double-check-circle',
        'payments' => 'ki-duotone ki-dollar',
        'financial_reports' => 'ki-duotone ki-chart-line-up',
    ];

    protected array $academyModules = [
        'programs', 'subjects', 'groups', 'teachers', 'students',
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
                [
                    'name_ar' => $this->moduleNamesAr[$module] ?? $module,
                    'name_en' => ucfirst($module),
                    'icon' => $this->moduleIcons[$module] ?? 'ki-duotone ki-element-11',
                    'sort' => $sort,
                    'status' => 1,
                    'parent_id' => $group->id,
                ]
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
                [
                    'name_ar' => $this->moduleNamesAr[$module] ?? $module,
                    'name_en' => ucfirst(str_replace('_', ' ', $module)),
                    'icon' => $this->moduleIcons[$module] ?? 'ki-duotone ki-element-11',
                    'sort' => $sort,
                    'status' => 1,
                    'parent_id' => $academyGroup->id,
                ]
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
