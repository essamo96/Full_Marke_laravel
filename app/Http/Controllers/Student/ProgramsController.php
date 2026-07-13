<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Support\Facades\Auth;

class ProgramsController extends Controller
{
    public function index()
    {
        $student = Auth::guard('student')->user();

        $registeredSubjectIds = $student->registrations()
            ->whereIn('status', ['pending', 'partially_paid', 'fully_paid'])
            ->pluck('subject_id');

        $programs = Program::where('is_active', true)
            ->with(['subjects' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->get();

        return view('student.programs.index', compact('programs', 'registeredSubjectIds'));
    }
}
