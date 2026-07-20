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

        $subjects = $teacher->subjects()->with('program')->get()->groupBy(fn ($s) => $s->program->title ?? 'بدون برنامج');

        return view('teacher.subjects.index', compact('subjects'));
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
