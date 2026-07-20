<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;

class GroupsController extends Controller
{
    private const ACTIVE_STATUSES = ['pending', 'partially_paid', 'fully_paid'];

    public function index()
    {
        $teacher = Auth::guard('teacher')->user();

        $groups = Group::where('teacher_id', $teacher->id)
            ->with('subject.program')
            ->withCount(['registrations as students_count' => function ($q) {
                $q->whereIn('status', self::ACTIVE_STATUSES);
            }])
            ->get();

        return view('teacher.groups.index', compact('groups'));
    }

    public function show(Group $group)
    {
        $teacher = Auth::guard('teacher')->user();
        abort_unless($group->teacher_id === $teacher->id, 403);

        $group->load('subject.program');

        $roster = $group->registrations()
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->with('student')
            ->get();

        $notes = $group->notes()->latest()->get();

        return view('teacher.groups.show', compact('group', 'roster', 'notes'));
    }
}
