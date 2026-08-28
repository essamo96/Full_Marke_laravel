<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PermissionsGroup;
use Illuminate\Support\Facades\DB;

class ResourceArchiveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define route base name to easily manage it
        $routeName = 'resource-archive';

        // 1. Check if "Resource Archive" menu item already exists
        $existingMenu = PermissionsGroup::where('name', $routeName)->first();

        if (!$existingMenu) {
            // Find a good parent or create it as a root menu
            // Let's create it as a root menu for visibility, next to "مكتبة المرفقات"
            
            // Get max sort
            $maxSort = PermissionsGroup::where('parent_id', 0)->max('sort') ?? 0;

            $menu = PermissionsGroup::create([
                'name' => $routeName,
                'name_ar' => 'أرشيف المرفقات',
                'name_en' => 'Resource Archive',
                'parent_id' => 0,
                'icon' => 'ki-trash',
                'color' => 'danger',
                'sort' => $maxSort + 1,
            ]);

            // Give permission to super admin
            // Check for the super admin role
            $superAdminRole = DB::table('roles')->where('name', 'super-admin')->first() 
                            ?? DB::table('roles')->where('name', 'Super Admin')->first()
                            ?? DB::table('roles')->first();

            if ($superAdminRole) {
                // Add view permission
                DB::table('permission_role')->insertOrIgnore([
                    'permission_id' => $menu->id,
                    'role_id' => $superAdminRole->id,
                    'permission_type' => 'view' // as used by this project
                ]);
            }
        }
    }
}
