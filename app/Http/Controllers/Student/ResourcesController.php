<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SubjectResource;
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

        $resourcesBySubject = SubjectResource::active()
            ->whereIn('subject_id', $subjectIds)
            ->with('subject')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('subject_id');

        return view('student.resources.index', compact('resourcesBySubject'));
    }
}
