<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Jobs\NotifyGroupOfNewNote;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupNoteController extends Controller
{
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
}
