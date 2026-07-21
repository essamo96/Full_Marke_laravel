<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Registration;
use Illuminate\Support\Facades\Auth;

class StudentsController extends Controller
{
    private const ACTIVE_STATUSES = ['pending', 'partially_paid', 'fully_paid'];

    public function index()
    {
        $teacher = Auth::guard('teacher')->user();
        $teacherGroupIds = Group::where('teacher_id', $teacher->id)->pluck('id');

        $registrations = Registration::whereIn('group_id', $teacherGroupIds)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->with(['student', 'group', 'subject'])
            ->get()
            ->unique('student_id');

        return view('teacher.students.index', compact('registrations'));
    }
}
