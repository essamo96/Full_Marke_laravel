<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Jobs\NotifyGroupOfNewNote;
use App\Models\Group;
use App\Models\Registration;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupNoteController extends Controller
{
    private const ACTIVE_STATUSES = ['pending', 'partially_paid', 'fully_paid'];

    public function store(Request $request, Group $group)
    {
        $teacher = Auth::guard('teacher')->user();
        abort_unless($group->teacher_id === $teacher->id, 403);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:5000',
        ]);

        $note = $group->notes()->create($data + ['teacher_id' => $teacher->id]);

        NotifyGroupOfNewNote::dispatch($note);

        return back()->with('success', 'تم نشر الملاحظة بنجاح.');
    }

    public function storeStudentNote(Request $request)
    {
        $teacher = Auth::guard('teacher')->user();

        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:5000',
            'is_alert' => 'nullable|boolean',
        ]);

        $teacherGroupIds = Group::where('teacher_id', $teacher->id)->pluck('id');

        $registration = Registration::where('student_id', $data['student_id'])
            ->whereIn('group_id', $teacherGroupIds)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->first();

        abort_unless($registration, 403);

        $note = \App\Models\GroupNote::create([
            'group_id' => $registration->group_id,
            'teacher_id' => $teacher->id,
            'student_id' => $data['student_id'],
            'title' => $data['title'],
            'content' => $data['content'],
            'is_alert' => $request->boolean('is_alert'),
        ]);

        NotifyGroupOfNewNote::dispatch($note);

        return back()->with('success', 'تم إرسال الملاحظة للطالب بنجاح.');
    }
}
