<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Models\Branch;
use App\Models\PermissionsGroup;
use App\Models\Student;
use App\Models\Teacher;
use Spatie\Permission\Models\Role;

class DashboardController extends AdminController
{
    public function index()
    {
        $stats = [
            'students' => Student::count(),
            'students_active' => Student::active()->count(),
            'teachers' => Teacher::count(),
            'teachers_active' => Teacher::active()->count(),
            'branches' => Branch::active()->count(),
            'admins' => Admin::active()->count(),
            'roles' => Role::where('guard_name', 'admin')->count(),
            'permission_groups' => PermissionsGroup::active()->where('parent_id', '!=', 0)->count(),
        ];

        return view('admin.dashboard.index', self::$data + ['stats' => $stats]);
    }
}
