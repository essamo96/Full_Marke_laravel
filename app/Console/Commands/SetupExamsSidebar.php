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

        // 1. Remove old "exams" parent if it exists to avoid conflicts
        $oldExams = PermissionsGroup::where('name', 'exams')->where('parent_id', 0)->first();
        if ($oldExams) {
            $oldExams->delete();
            $this->info('Old Exams root menu removed.');
        }

        // 2. Create Parent Menu (الامتحانات)
        $parent = PermissionsGroup::updateOrCreate(
            ['name' => 'exams_parent', 'parent_id' => 0],
            [
                'name_ar' => 'الامتحانات',
                'name_en' => 'Exams',
                'icon' => 'bi-journal-check',
                'sort' => 60,
                'status' => 1
            ]
        );

        // 3. Create Child Menu (إدارة الامتحانات)
        $child1 = PermissionsGroup::updateOrCreate(
            ['name' => 'exams', 'parent_id' => $parent->id],
            [
                'name_ar' => 'إدارة الامتحانات',
                'name_en' => 'Exam Management',
                'icon' => 'bi-list-ul',
                'sort' => 1,
                'status' => 1
            ]
        );

        // 4. Move/Create Child Menu (نتائج الامتحانات)
        $child2 = PermissionsGroup::updateOrCreate(
            ['name' => 'exams_results'],
            [
                'parent_id' => $parent->id,
                'name_ar' => 'نتائج الامتحانات',
                'name_en' => 'Exam Results',
                'icon' => 'bi-file-earmark-bar-graph',
                'sort' => 2,
                'status' => 1
            ]
        );

        // 5. Generate CRUD permissions
        $parent->generateCrudPermissions();
        $child1->generateCrudPermissions();
        $child2->generateCrudPermissions();

        // 6. Assign permissions to Super Admin (Role ID: 1)
        $superAdmin = Role::where('guard_name', 'admin')->where('name', 'Super Admin')->first();
        
        if ($superAdmin) {
            $permissions = Permission::whereIn('group_id', [$parent->id, $child1->id, $child2->id])->get();
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
