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
            ->map(function ($subject) use ($groupIdBySubject) {
                $groupId = $groupIdBySubject->get($subject->id) ? (int) $groupIdBySubject->get($subject->id) : null;

                $units = EducationalUnit::query()
                    ->whereIn('educational_stage_id', $subject->stages->pluck('id'))
                    ->forGroup($groupId)
                    ->where('is_active', true)
                    ->with(['lessons' => function ($lq) use ($groupId) {
                        $lq->forGroup($groupId)
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->with(['resources' => function ($rq) use ($groupId) {
                                $rq->active()->forGroup($groupId)->orderBy('sort_order');
                            }]);
                    }])
                    ->orderBy('sort_order')
                    ->get()
                    ->filter(function ($unit) {
                        return $unit->lessons->contains(fn ($lesson) => $lesson->resources->isNotEmpty());
                    })
                    ->values();

                $subject->units = $units;

                return $subject;
            })
            ->filter(fn ($subject) => $subject->units->isNotEmpty())
            ->values();

        return view('student.resources.index', compact('subjects'));
    }
}
