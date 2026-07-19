<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ExamResultsPermissionsSeeder extends Seeder
{
    public function run()
    {
        // 1. إضافة القوائم الجانبية
        // الحصول على معرفات الأقسام الأب (الامتحانات والطلاب) إن وجدت، وإلا استخدام القيم الافتراضية
        $examsGroup = DB::table('permissions_groups')->where('name', 'exams')->first();
        $examsParentId = $examsGroup ? $examsGroup->id : 44;

        $studentsGroup = DB::table('permissions_groups')->where('name', 'students')->first();
        $studentsParentId = $studentsGroup ? $studentsGroup->id : 26;

        DB::table('permissions_groups')->updateOrInsert(
            ['name' => 'exams_results'],
            ['name_ar' => 'نتائج الامتحانات', 'name_en' => 'Exam Results', 'parent_id' => $examsParentId, 'sort' => 1, 'status' => 1]
        );

        DB::table('permissions_groups')->updateOrInsert(
            ['name' => 'students_results'],
            ['name_ar' => 'نتائج الطلاب', 'name_en' => 'Student Results', 'parent_id' => $studentsParentId, 'sort' => 1, 'status' => 1]
        );

        $examsResultsGroupId = DB::table('permissions_groups')->where('name', 'exams_results')->first()->id;
        $studentsResultsGroupId = DB::table('permissions_groups')->where('name', 'students_results')->first()->id;

        // 2. إنشاء أو تحديث الصلاحيات
        $p1 = Permission::firstOrCreate(['name' => 'admin.exams_results.view', 'guard_name' => 'admin']);
        $p1->update(['group_id' => $examsResultsGroupId]);

        $p2 = Permission::firstOrCreate(['name' => 'admin.students_results.view', 'guard_name' => 'admin']);
        $p2->update(['group_id' => $studentsResultsGroupId]);

        // 3. إعطاء الصلاحيات لمدير النظام (Super Admin)
        $role = Role::findByName('Super Admin', 'admin');
        if ($role) {
            $role->givePermissionTo([$p1, $p2]);
        }

        // 4. ربط النتائج القديمة بمعرف الامتحان لتظهر كـ "تم التسليم"
        DB::table('grades')->whereNull('exam_id')->update(['exam_id' => 1]);

        // 5. مسح كاش الصلاحيات
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
