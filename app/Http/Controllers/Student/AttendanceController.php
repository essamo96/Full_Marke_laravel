<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    public function index()
    {
        $student = auth('student')->user();
        
        $attendances = Attendance::where('student_id', $student->id)
            ->with('group')
            ->latest('date')
            ->paginate(15);
            
        return view('student.attendance.index', compact('attendances'));
    }
}
