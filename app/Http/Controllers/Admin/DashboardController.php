<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Models\Application;
use App\Models\Branch;
use App\Models\Group;
use App\Models\PermissionsGroup;
use App\Models\Program;
use App\Models\Registration;
use App\Models\Student;
use App\Models\StudyBranch;
use App\Models\Subject;
use App\Models\Teacher;
use Spatie\Permission\Models\Role;



class DashboardController extends AdminController
{

    public function __construct() {
        parent::__construct();
        parent::$data['active_menu'] = 'dashboard';
    }
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

            'programs' => Program::where('is_active', true)->count(),
            'subjects' => Subject::active()->count(),
            'groups' => Group::active()->count(),
            'study_branches' => StudyBranch::active()->count(),
            'applications_pending' => Application::where('status', 'new')->count(),
            'registrations_active' => Registration::where('registration_status', 'active')->count(),
        ];

        $topPrograms = Program::query()
            ->withCount('subjects')
            ->where('is_active', true)
            ->orderByDesc('subjects_count')
            ->take(5)
            ->get();

        // Groups with the highest fill ratio (capacity utilisation), used as the
        // "active lessons / progress" style list widget.
        $topGroups = Group::query()
            ->active()
            ->with(['subject', 'teacher'])
            ->whereColumn('current_count', '<=', 'max_capacity')
            ->orderByDesc('current_count')
            ->take(5)
            ->get();

        // Recent applications for the "recommended / recent activity" timeline-style list.
        $recentApplications = Application::query()
            ->with(['program', 'subject'])
            ->latest()
            ->take(6)
            ->get();

        // Last 7 days of new student registrations, for the trend chart.
        $registrationsTrend = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo);

            return [
                'label' => $date->translatedFormat('D'),
                'count' => Student::whereDate('created_at', $date->toDateString())->count(),
            ];
        });

        return view('admin.dashboard.view', self::$data + [
            'stats' => $stats,
            'topPrograms' => $topPrograms,
            'topGroups' => $topGroups,
            'recentApplications' => $recentApplications,
            'registrationsTrend' => $registrationsTrend,
        ]);
    }
}
