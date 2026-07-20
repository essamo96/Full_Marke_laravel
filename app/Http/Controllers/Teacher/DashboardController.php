<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Exam;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private const ACTIVE_STATUSES = ['pending', 'partially_paid', 'fully_paid'];

    public function index()
    {
        $teacher = Auth::guard('teacher')->user();

        $groups = Group::where('teacher_id', $teacher->id)
            ->with(['subject.program'])
            ->withCount(['registrations as students_count' => function ($q) {
                $q->whereIn('status', self::ACTIVE_STATUSES);
            }])
            ->get();

        $groupIds = $groups->pluck('id');

        // Program -> Subject -> Group student-count breakdown
        $programBreakdown = $groups
            ->filter(fn ($g) => $g->subject)
            ->groupBy(fn ($g) => $g->subject->program->title ?? 'بدون برنامج')
            ->map(function ($programGroups) {
                return $programGroups->groupBy(fn ($g) => $g->subject->name)
                    ->map(function ($subjectGroups) {
                        return [
                            'groups' => $subjectGroups,
                            'total_students' => $subjectGroups->sum('students_count'),
                        ];
                    });
            });

        $totalStudents = $groups->sum('students_count');

        $upcomingExams = Exam::whereIn('group_id', $groupIds)
            ->where('status', 'published')
            ->with(['group', 'subject'])
            ->orderBy('start_time')
            ->limit(10)
            ->get();

        $mostAbsentStudents = Attendance::whereIn('group_id', $groupIds)
            ->where('status', 'absent')
            ->selectRaw('student_id, count(*) as absences')
            ->groupBy('student_id')
            ->orderByDesc('absences')
            ->with('student')
            ->limit(10)
            ->get();

        return view('teacher.dashboard.index', compact(
            'teacher', 'groups', 'programBreakdown', 'totalStudents', 'upcomingExams', 'mostAbsentStudents'
        ));
    }
}
