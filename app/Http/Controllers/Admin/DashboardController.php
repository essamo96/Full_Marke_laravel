<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Models\Application;
use App\Models\Branch;
use App\Models\Group;
use App\Models\PermissionsGroup;
use App\Models\Program;
use App\Models\Registration;
use App\Models\Region;
use App\Models\Student;
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
            'branches' => Region::active()->count(),
            'admins' => Admin::active()->count(),
            'roles' => Role::where('guard_name', 'admin')->count(),
            'permission_groups' => PermissionsGroup::active()->where('parent_id', '!=', 0)->count(),

            'programs' => Program::where('is_active', true)->count(),
            'subjects' => Subject::active()->count(),
            'groups' => Group::active()->count(),
            'study_branches' => Branch::active()->count(),
            'applications_pending' => Application::where('status', 'new')->count(),
            'registrations_active' => Registration::whereNotNull('activated_at')->count(),
            'attendance_rate' => \App\Models\Attendance::count() > 0 ? round((\App\Models\Attendance::where('status', 'present')->count() / \App\Models\Attendance::count()) * 100, 2) : 0,
            'average_grade' => \App\Models\Grade::count() > 0 ? round(\App\Models\Grade::avg('score'), 2) : 0,
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
                'count' => Student::whereDate('created_at', $date->toDateString())->count()];
        });

        // Distribution by Region
        $regionDistribution = Student::selectRaw('region_id, count(*) as count')
            ->groupBy('region_id')
            ->with('region')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->region ? $item->region->name_ar : 'غير محدد',
                    'count' => $item->count,
                ];
            });

        // Distribution by Study Branch
        $studyBranchDistribution = Student::selectRaw('branch_id, count(*) as count')
            ->groupBy('branch_id')
            ->with('branch')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->branch ? $item->branch->name_ar : 'غير محدد',
                    'count' => $item->count,
                ];
            });

        // Outstanding Fees
        $outstandingFeesStudents = Registration::with('student', 'subject')
            ->whereColumn('amount_paid', '<', 'fee_snapshot')
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        // Calendar Events
        $allGroups = Group::active()->with('subject', 'teacher')->get();
        $calendarEvents = [];
        foreach ($allGroups as $group) {
            $days = is_string($group->days) ? json_decode($group->days, true) : $group->days;
            if (!$days) continue;
            
            $daysMap = [
                'sun' => 0, 'sunday' => 0,
                'mon' => 1, 'monday' => 1,
                'tue' => 2, 'tuesday' => 2,
                'wed' => 3, 'wednesday' => 3,
                'thu' => 4, 'thursday' => 4,
                'fri' => 5, 'friday' => 5,
                'sat' => 6, 'saturday' => 6,
            ];
            
            $intDays = [];
            foreach ($days as $d) {
                $dLower = strtolower($d);
                if (isset($daysMap[$dLower])) {
                    $intDays[] = $daysMap[$dLower];
                } elseif (is_numeric($d)) {
                    $intDays[] = intval($d);
                }
            }
            
            $calendarEvents[] = [
                'title' => $group->name . ($group->subject ? ' - ' . $group->subject->name : ''),
                'startTime' => $group->start_time,
                'endTime' => $group->end_time,
                'daysOfWeek' => $intDays,
                'groupId' => $group->id,
            ];
        }

        // Ordered list of groups by lecture time/day
        $orderedGroups = Group::active()
            ->with('subject', 'teacher')
            ->orderBy('start_time')
            ->get();

        return view('admin.dashboard.view', self::$data + [
            'stats' => $stats,
            'topPrograms' => $topPrograms,
            'topGroups' => $topGroups,
            'recentApplications' => $recentApplications,
            'registrationsTrend' => $registrationsTrend,
            'regionDistribution' => $regionDistribution,
            'studyBranchDistribution' => $studyBranchDistribution,
            'outstandingFeesStudents' => $outstandingFeesStudents,
            'calendarEvents' => $calendarEvents,
            'orderedGroups' => $orderedGroups
        ]);
    }
}
