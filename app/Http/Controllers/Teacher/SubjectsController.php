<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;

class SubjectsController extends Controller
{
    private const ACTIVE_STATUSES = ['pending', 'partially_paid', 'fully_paid'];

    public function index()
    {
        $teacher = Auth::guard('teacher')->user();

        $subjects = $teacher->subjects()->with('program')->get();

        $subjectStats = [];
        foreach ($subjects as $subject) {
            $groups = Group::where('subject_id', $subject->id)->where('teacher_id', $teacher->id)
                ->withCount(['registrations as students_count' => function ($q) {
                    $q->whereIn('status', self::ACTIVE_STATUSES);
                }])
                ->get();

            $subjectStats[$subject->id] = [
                'groups_count' => $groups->count(),
                'students_count' => $groups->sum('students_count'),
            ];
        }

        $programs = $subjects->groupBy(fn ($s) => $s->program->id ?? 0)
            ->map(function ($programSubjects) {
                return [
                    'program' => $programSubjects->first()->program,
                    'subjects' => $programSubjects,
                ];
            });

        return view('teacher.subjects.index', compact('programs', 'subjectStats'));
    }

    public function show(Subject $subject)
    {
        $teacher = Auth::guard('teacher')->user();
        abort_unless($teacher->subjects->contains($subject->id), 403);

        $subject->load([
            'stages' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
            'stages.units' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
            'stages.units.lessons' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
            'stages.units.lessons.resources' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
        ]);

        $groups = Group::where('subject_id', $subject->id)
            ->where('teacher_id', $teacher->id)
            ->withCount(['registrations as students_count' => function ($q) {
                $q->whereIn('status', self::ACTIVE_STATUSES);
            }])
            ->get();

        return view('teacher.subjects.show', compact('subject', 'groups'));
    }
}
