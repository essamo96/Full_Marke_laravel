<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\EducationalStage;
use App\Models\Group;
use App\Services\StudentContentGate;
use Illuminate\Support\Facades\Auth;

class GroupsController extends Controller
{
    private const ACTIVE_STATUSES = ['pending', 'partially_paid', 'fully_paid'];

    private const WEEKDAY_ORDER = ['sun' => 0, 'mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6];

    public function index()
    {
        $teacher = Auth::guard('teacher')->user();

        $groups = Group::where('teacher_id', $teacher->id)
            ->with('subject.program')
            ->withCount(['registrations as students_count' => function ($q) {
                $q->whereIn('status', self::ACTIVE_STATUSES);
            }])
            ->get()
            ->sortBy([
                [fn ($g) => collect($g->days ?? [])->map(fn ($d) => self::WEEKDAY_ORDER[$d] ?? 99)->min() ?? 99, 'asc'],
                [fn ($g) => $g->start_time ?? '99:99', 'asc'],
            ])
            ->values();

        return view('teacher.groups.index', compact('groups'));
    }

    public function show(Group $group)
    {
        $teacher = Auth::guard('teacher')->user();
        abort_unless($group->teacher_id === $teacher->id, 403);

        $group->load('subject.program');
        $groupId = $group->id;
        // What the group's students actually see: the same gate as the student pages, so this
        // list can never promise content that is hidden, switched off or only shared elsewhere.
        $tree = app(StudentContentGate::class)->tree(0, $group->subject, [$groupId]);
        $stages = EducationalStage::where('subject_id', $group->subject_id)->where('is_active', true)->orderBy('sort_order')->get()
            ->each(fn ($stage) => $stage->setRelation('units', $tree['units']->where('educational_stage_id', $stage->id)->values()))
            ->filter(fn ($stage) => $stage->units->isNotEmpty())
            ->values();
        $group->subject->setRelation('stages', $stages);
        $generalResources = $tree['general'];

        $roster = $group->registrations()
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->with('student')
            ->get();

        $notes = $group->notes()->whereNull('student_id')->latest()->get();

        $exams = \App\Models\Exam::where('group_id', $group->id)->latest()->get();

        $topStudents = \App\Models\Grade::where('group_id', $group->id)
            ->with('student')
            ->orderByDesc('score')
            ->limit(5)
            ->get();

        $mostAbsentStudents = \App\Models\Attendance::where('group_id', $group->id)
            ->where('status', 'absent')
            ->selectRaw('student_id, count(*) as absences')
            ->groupBy('student_id')
            ->orderByDesc('absences')
            ->with('student')
            ->limit(5)
            ->get();

        return view('teacher.groups.show', compact('group', 'generalResources', 'roster', 'notes', 'exams', 'topStudents', 'mostAbsentStudents'));
    }
}
