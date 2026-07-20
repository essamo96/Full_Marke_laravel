<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    private const ACTIVE_STATUSES = ['pending', 'partially_paid', 'fully_paid'];
    private const VALID_STATUSES = ['present', 'absent', 'late', 'excused'];

    public function show(Request $request, Group $group)
    {
        $teacher = Auth::guard('teacher')->user();
        abort_unless($group->teacher_id === $teacher->id, 403);

        $date = $request->input('date', now()->format('Y-m-d'));

        $roster = $group->registrations()
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->with('student')
            ->get();

        $existing = Attendance::where('group_id', $group->id)
            ->where('date', $date)
            ->get()
            ->keyBy('student_id');

        return view('teacher.attendance.group', compact('group', 'roster', 'existing', 'date'));
    }

    public function store(Request $request, Group $group)
    {
        $teacher = Auth::guard('teacher')->user();
        abort_unless($group->teacher_id === $teacher->id, 403);

        $data = $request->validate([
            'date' => 'required|date',
            'records' => 'required|array',
            'records.*' => 'required|in:' . implode(',', self::VALID_STATUSES),
        ]);

        foreach ($data['records'] as $studentId => $status) {
            Attendance::updateOrCreate(
                ['student_id' => $studentId, 'group_id' => $group->id, 'date' => $data['date']],
                ['status' => $status, 'teacher_id' => $teacher->id]
            );
        }

        return back()->with('success', 'تم حفظ الحضور بنجاح.');
    }
}
