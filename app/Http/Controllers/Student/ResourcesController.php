<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Services\StudentContentGate;
use Illuminate\Support\Facades\Auth;

class ResourcesController extends Controller
{
    /**
     * Everything the student may open, per enrolled subject.
     *
     * The tree comes from StudentContentGate - the same rules that guard the stream / file /
     * link URLs - so a unit or video the teacher hid, switched off or excluded this student from
     * is gone here AND cannot be reached by a copied link.
     */
    public function index(StudentContentGate $gate)
    {
        $student = Auth::guard('student')->user();
        $studentId = (int) $student->id;

        $subjectIds = $student->registrations()
            ->whereIn('status', StudentContentGate::ACTIVE_STATUSES)
            ->pluck('subject_id')
            ->unique();

        $subjects = Subject::whereIn('id', $subjectIds)
            ->get()
            ->map(function (Subject $subject) use ($gate, $studentId) {
                $tree = $gate->tree($studentId, $subject);

                $subject->units = $tree['units'];
                $subject->general_resources = $tree['general'];

                return $subject;
            })
            ->filter(fn ($subject) => $subject->units->isNotEmpty() || $subject->general_resources->isNotEmpty())
            ->values();

        return view('student.resources.index', compact('subjects'));
    }
}
