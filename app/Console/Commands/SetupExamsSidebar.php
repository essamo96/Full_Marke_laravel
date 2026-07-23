<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PermissionsGroup;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Cache;

class SetupExamsSidebar extends Command
{
    protected $signature = 'setup:exams-sidebar';
    protected $description = 'Setup Exams sidebar menu and permissions for Super Admin';

    public function handle()
    {
        $this->info('Setting up Exams sidebar and permissions...');

        // 1. Create Parent Menu (الامتحانات)
        $parent = PermissionsGroup::updateOrCreate(
            ['name' => 'exams_parent', 'parent_id' => 0],
            [
                'name_ar' => 'الامتحانات',
                'name_en' => 'Exams',
                'icon' => 'bi-journal-check',
                'sort' => 60, // Adjust sort order as needed
                'status' => 1
            ]
        );

        // 2. Create Child Menu (إدارة الامتحانات)
        $child = PermissionsGroup::updateOrCreate(
            ['name' => 'exams', 'parent_id' => $parent->id],
            [
                'name_ar' => 'إدارة الامتحانات',
                'name_en' => 'Exam Management',
                'icon' => 'bi-list-ul',
                'sort' => 1,
                'status' => 1
            ]
        );

        // 3. Generate CRUD permissions for the parent and child menu
        $parent->generateCrudPermissions();
        $child->generateCrudPermissions();

        // 4. Assign permissions to Super Admin (Role ID: 1)
        $superAdmin = Role::where('guard_name', 'admin')->where('name', 'Super Admin')->first();
        
        if ($superAdmin) {
            // Give all newly created permissions for "exams_parent" and "exams" to the Super Admin
            $permissions = Permission::whereIn('group_id', [$parent->id, $child->id])->get();
            $superAdmin->givePermissionTo($permissions);
            $this->info('Permissions assigned to Super Admin successfully.');
        } else {
            $this->warn('Super Admin role not found. Please assign permissions manually.');
        }

        // Clear Spatie Permission Cache
        Cache::forget('spatie.permission.cache');
        
        $this->info('Exams sidebar setup completed successfully!');
    }
}
