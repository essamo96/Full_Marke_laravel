<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PermissionsGroup;
use Spatie\Permission\Models\Role;

class CMSPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Create parent group for CMS
        $cmsGroup = PermissionsGroup::firstOrCreate(
            ['name' => 'cms_management'],
            [
                'name_ar' => 'إدارة المحتوى والأخبار',
                'name_en' => 'CMS Management',
                'icon' => 'ki-duotone ki-book-open',
                'color' => 'primary',
                'bg_color' => 'light-primary',
                'sort' => 10,
                'parent_id' => 0,
            ]
        );

        // Parent group only needs its own "view" permission (same convention as AdminPermissionsSeeder)
        $cmsGroup->generateCrudPermissions(['view']);

        $groups = [
            [
                'name' => 'news_categories',
                'name_ar' => 'أقسام الأخبار',
                'name_en' => 'News Categories',
                'icon' => 'ki-duotone ki-category',
            ],
            [
                'name' => 'news_tags',
                'name_ar' => 'التصنيفات (Tags)',
                'name_en' => 'News Tags',
                'icon' => 'ki-duotone ki-tag',
            ],
            [
                'name' => 'news_articles',
                'name_ar' => 'الأخبار والمقالات',
                'name_en' => 'News Articles',
                'icon' => 'ki-duotone ki-document',
            ],
            [
                'name' => 'static_pages',
                'name_ar' => 'الصفحات الثابتة',
                'name_en' => 'Static Pages',
                'icon' => 'ki-duotone ki-tablet-text-down',
            ],
            [
                'name' => 'paper_editions',
                'name_ar' => 'الإصدارات الورقية',
                'name_en' => 'Paper Editions',
                'icon' => 'ki-duotone ki-book',
            ],
            [
                'name' => 'partners',
                'name_ar' => 'شركاء النجاح',
                'name_en' => 'Partners',
                'icon' => 'ki-duotone ki-briefcase',
            ],
            [
                'name' => 'contact_messages',
                'name_ar' => 'رسائل اتصل بنا',
                'name_en' => 'Contact Messages',
                'icon' => 'ki-duotone ki-sms',
            ],
            [
                'name' => 'sidebar_manager',
                'name_ar' => 'مدير القائمة الجانبية',
                'name_en' => 'Sidebar Manager',
                'icon' => 'ki-duotone ki-setting-2',
            ]
        ];

        foreach ($groups as $index => $groupData) {
            $group = PermissionsGroup::firstOrCreate(
                ['name' => $groupData['name']],
                [
                    'name_ar' => $groupData['name_ar'],
                    'name_en' => $groupData['name_en'],
                    'icon' => $groupData['icon'],
                    'sort' => $index + 1,
                    'parent_id' => $cmsGroup->id,
                ]
            );

            // Generate standard CRUD permissions for each subgroup
            $group->generateCrudPermissions();
        }

        // Create Roles for Editor and Author
        $editorRole = Role::firstOrCreate(['name' => 'Editor', 'guard_name' => 'admin']);
        $authorRole = Role::firstOrCreate(['name' => 'Author', 'guard_name' => 'admin']);

        // We can assign permissions to these roles if needed, 
        // e.g. Editor can edit everything, Author can only add/edit news_articles
        $authorPermissions = [
            'admin.news_articles.view',
            'admin.news_articles.add',
            'admin.news_articles.edit',
        ];
        
        foreach ($authorPermissions as $p) {
            $authorRole->givePermissionTo($p);
        }
        
        // Editor gets everything under CMS (except maybe sidebar manager)
        $editorPermissions = \Spatie\Permission\Models\Permission::where('name', 'like', 'admin.%')
            ->whereNotIn('name', [
                'admin.sidebar_manager.view',
                'admin.sidebar_manager.add',
                'admin.sidebar_manager.edit',
                'admin.sidebar_manager.delete',
                'admin.sidebar_manager.status'
            ])
            ->get();
            
        foreach ($editorPermissions as $p) {
            $editorRole->givePermissionTo($p);
        }

        // Super Admin must always have every permission, including newly generated CMS ones
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'admin']);
        $superAdmin->syncPermissions(\Spatie\Permission\Models\Permission::where('guard_name', 'admin')->get());

        \Illuminate\Support\Facades\Cache::forget('spatie.permission.cache');
    }
}
