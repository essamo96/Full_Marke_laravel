<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\EducationalUnit;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;

class ResourcesController extends Controller
{
    public function index()
    {
        $student = Auth::guard('student')->user();
        $studentId = (int) $student->id;

        $registrations = $student->registrations()
            ->whereIn('status', ['pending', 'partially_paid', 'fully_paid'])
            ->get();

        $subjectIds = $registrations->pluck('subject_id')->unique();
        $groupIdBySubject = $registrations->pluck('group_id', 'subject_id');

        $subjects = Subject::whereIn('id', $subjectIds)
            ->with(['stages' => function ($q) {
                $q->orderBy('sort_order');
            }])
            ->get()
            ->map(function ($subject) use ($groupIdBySubject, $studentId) {
                $groupId = $groupIdBySubject->get($subject->id) ? (int) $groupIdBySubject->get($subject->id) : null;

                $units = EducationalUnit::query()
                    ->whereIn('educational_stage_id', $subject->stages->pluck('id'))
                    ->visibleToStudent($studentId, $groupId)
                    ->where('is_active', true)
                    ->with(['lessons' => function ($lq) use ($groupId, $studentId) {
                        $lq->visibleToStudent($studentId, $groupId)
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->with(['resources' => function ($rq) use ($groupId, $studentId) {
                                $rq->active()->visibleToStudent($studentId, $groupId)->orderBy('sort_order');
                            }]);
                    }])
                    ->orderBy('sort_order')
                    ->get()
                    ->filter(function ($unit) {
                        return $unit->lessons->contains(fn ($lesson) => $lesson->resources->isNotEmpty());
                    })
                    ->values();

                $generalResources = \App\Models\SubjectResource::query()
                    ->where('subject_id', $subject->id)
                    ->whereNull('educational_lesson_id')
                    ->active()
                    ->visibleToStudent($studentId, $groupId)
                    ->orderBy('sort_order')
                    ->get();

                $subject->units = $units;
                $subject->general_resources = $generalResources;

                return $subject;
            })
            ->filter(fn ($subject) => $subject->units->isNotEmpty() || $subject->general_resources->isNotEmpty())
            ->values();

        return view('student.resources.index', compact('subjects'));
    }
}
