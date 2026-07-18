<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $group1 = \App\Models\PermissionsGroup::create([
            'name' => 'student_inquiry',
            'name_ar' => 'استعلامات الطلاب',
            'name_en' => 'Student Inquiries',
            'color' => 'primary',
            'icon' => 'ki-user-square',
            'sort' => 100,
            'status' => 1,
            'parent_id' => 0,
        ]);

        \Spatie\Permission\Models\Permission::create([
            'name' => 'admin.student_inquiry.view',
            'name_ar' => 'عرض استعلامات الطلاب',
            'name_en' => 'View Student Inquiries',
            'group_id' => $group1->id,
            'guard_name' => 'admin',
        ]);

        $group2 = \App\Models\PermissionsGroup::create([
            'name' => 'subject_content',
            'name_ar' => 'المحتوى التعليمي',
            'name_en' => 'Subject Content',
            'color' => 'success',
            'icon' => 'ki-folder',
            'sort' => 101,
            'status' => 1,
            'parent_id' => 0,
        ]);

        \Spatie\Permission\Models\Permission::create([
            'name' => 'admin.subject_content.view',
            'name_ar' => 'عرض المحتوى التعليمي',
            'name_en' => 'View Subject Content',
            'group_id' => $group2->id,
            'guard_name' => 'admin',
        ]);
        
        \Spatie\Permission\Models\Permission::create([
            'name' => 'admin.subject_content.edit',
            'name_ar' => 'تعديل المحتوى التعليمي',
            'name_en' => 'Edit Subject Content',
            'group_id' => $group2->id,
            'guard_name' => 'admin',
        ]);

        // Assign to Super Admin role
        $role = \Spatie\Permission\Models\Role::where('name', 'super_admin')->first();
        if ($role) {
            $role->givePermissionTo(['admin.student_inquiry.view', 'admin.subject_content.view', 'admin.subject_content.edit']);
        }
    }

    public function down(): void
    {
        \Spatie\Permission\Models\Permission::whereIn('name', [
            'admin.student_inquiry.view', 
            'admin.subject_content.view', 
            'admin.subject_content.edit'
        ])->delete();
        \App\Models\PermissionsGroup::whereIn('name', ['student_inquiry', 'subject_content'])->delete();
    }
};
