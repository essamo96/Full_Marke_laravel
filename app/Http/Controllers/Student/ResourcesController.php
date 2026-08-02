<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;

class ResourcesController extends Controller
{
    public function index()
    {
        $student = Auth::guard('student')->user();

        $subjectIds = $student->registrations()
            ->whereIn('status', ['pending', 'partially_paid', 'fully_paid'])
            ->pluck('subject_id')
            ->unique();

        // Same unit -> lesson -> resource tree the admin/teacher content
        // manager is built around (Subject -> Stages -> Units -> Lessons ->
        // Resources), filtered to only what's actually published (active
        // units/lessons/resources) and ordered by sort_order at every level,
        // so a subject's resources read in the order they're meant to be
        // studied instead of a flat, unordered list.
        $subjects = Subject::whereIn('id', $subjectIds)
            ->with(['stages' => function ($q) {
                $q->with(['units' => function ($uq) {
                    $uq->where('is_active', true)
                        ->orderBy('sort_order')
                        ->with(['lessons' => function ($lq) {
                            $lq->where('is_active', true)
                                ->orderBy('sort_order')
                                ->with(['resources' => function ($rq) {
                                    $rq->active()->orderBy('sort_order');
                                }]);
                        }]);
                }]);
            }])
            ->get()
            ->map(function ($subject) {
                // Flatten stages away — the student view only ever needs
                // Subject -> Units -> Lessons, stages are an admin-side detail.
                $subject->units = $subject->stages->flatMap->units->sortBy('sort_order')->values();
                return $subject;
            })
            ->filter(function ($subject) {
                return $subject->units->contains(function ($unit) {
                    return $unit->lessons->contains(fn ($lesson) => $lesson->resources->isNotEmpty());
                });
            })
            ->values();

        return view('student.resources.index', compact('subjects'));
    }
}
