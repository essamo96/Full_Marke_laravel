<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Grade;

class ResultsController extends Controller
{
    public function index()
    {
        $student = auth('student')->user();
        
        $results = Grade::where('student_id', $student->id)
            ->latest()
            ->paginate(10);
            
        return view('student.exams.results', compact('results'));
    }
}
