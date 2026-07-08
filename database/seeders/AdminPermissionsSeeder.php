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
        // 1. Create or ensure Dashboard permission
        Permission::firstOrCreate(['name' => 'admin.dashboard.view', 'guard_name' => 'admin']);

        // 2. Explicitly define and insert Permissions Groups
        $groups = [
            [
                'name' => 'dashboard',
                'name_ar' => 'الرئيسية',
                'name_en' => 'Dashboard',
                'color' => 'dark',
                'icon' => 'bi-house-fill',
                'sort' => 1,
                'status' => 1,
                'children' => []
            ],
            [
                'name' => 'site_settings',
                'name_ar' => 'إعدادات الموقع',
                'name_en' => 'Site Settings',
                'color' => 'dark',
                'icon' => 'bi-gear-fill',
                'sort' => 1,
                'status' => 1,
                'children' => []
            ],
            [
                'name' => 'outside_settings',
                'name_ar' => 'التحكم بالموقع الخارجي',
                'name_en' => 'Control Out Wibsite',
                'color' => 'dark',
                'icon' => 'bi-wrench',
                'sort' => 29,
                'status' => 1,
                'children' => [
                    [
                        'name' => 'socials',
                        'name_ar' => 'منصات التواصل',
                        'name_en' => 'Socials',
                        'color' => 'dark',
                        'icon' => 'bi-pie-chart',
                        'sort' => 1,
                        'status' => 1,
                    ],
                    [
                        'name' => 'sliders',
                        'name_ar' => 'السلايدر',
                        'name_en' => 'Sliders',
                        'color' => 'dark',
                        'icon' => 'bi-record-fill',
                        'sort' => 2,
                        'status' => 1,
                    ],
                    [
                        'name' => 'testimonials',
                        'name_ar' => 'قالوا عنا',
                        'name_en' => 'Testimonials',
                        'color' => 'dark',
                        'icon' => 'bi-chat-quote-fill',
                        'sort' => 3,
                        'status' => 1,
                    ]
                ]
            ],
            [
                'name' => 'dashboard_settings',
                'name_ar' => 'اعدادات الموقع',
                'name_en' => 'Dashboard Settings',
                'color' => 'dark',
                'icon' => 'bi-tools',
                'sort' => 30,
                'status' => 1,
                'children' => [
                    [
                        'name' => 'users',
                        'name_ar' => 'المستخدمين',
                        'name_en' => 'Users',
                        'color' => null,
                        'icon' => 'ki-duotone ki-user',
                        'sort' => 1,
                        'status' => 1,
                    ],
                    [
                        'name' => 'permissions_group',
                        'name_ar' => 'القائمة الجانبية',
                        'name_en' => 'Permissions group',
                        'color' => null,
                        'icon' => 'ki-duotone ki-burger-menu-2',
                        'sort' => 2,
                        'status' => 1,
                    ],
                    [
                        'name' => 'permissions',
                        'name_ar' => 'الصلاحيات',
                        'name_en' => 'Permissions',
                        'color' => null,
                        'icon' => 'ki-duotone ki-shield-tick',
                        'sort' => 3,
                        'status' => 1,
                    ],
                    [
                        'name' => 'role',
                        'name_ar' => 'الادوار',
                        'name_en' => 'Role',
                        'color' => null,
                        'icon' => 'ki-duotone ki-key',
                        'sort' => 4,
                        'status' => 1,
                    ],
                ]
            ]
        ];

        foreach ($groups as $groupData) {
            $children = $groupData['children'] ?? [];
            unset($groupData['children']);
            $groupData['parent_id'] = 0;

            $parentGroup = PermissionsGroup::updateOrCreate(
                ['name' => $groupData['name']],
                $groupData
            );

            // Generate view permission for parent
            $parentGroup->generateCrudPermissions(['view']);

            foreach ($children as $childData) {
                $childData['parent_id'] = $parentGroup->id;
                $childGroup = PermissionsGroup::updateOrCreate(
                    ['name' => $childData['name']],
                    $childData
                );

                // Generate full CRUD permissions for child
                $childGroup->generateCrudPermissions(['view', 'add', 'edit', 'delete', 'status', 'import', 'export']);
            }
        }

        // 3. Sync all permissions to Super Admin role
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'admin']);
        $superAdmin->syncPermissions(Permission::where('guard_name', 'admin')->get());

        // 4. Assign Super Admin role to the main admin user (essam@hotmail.com)
        $admin = Admin::updateOrCreate(
            ['email' => 'essam@hotmail.com'],
            ['name' => 'Essam', 'password' => Hash::make('123456789'), 'status' => 1]
        );
        
        if (!$admin->hasRole('Super Admin')) {
            $admin->syncRoles([$superAdmin->name]);
        }

        Cache::forget('spatie.permission.cache');
    }
}
