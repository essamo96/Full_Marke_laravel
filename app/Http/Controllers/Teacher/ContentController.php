<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Subject;
use App\Services\ResourceLibrary\LibraryActor;
use App\Services\ResourceLibrary\ResourceCatalog;
use App\Support\Library\UploadLimits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The teacher's content screens. Everything that changes data (units, lessons, resources,
 * sharing, exclusions, the kill switch, undo...) goes through the shared library API
 * (Teacher\LibraryApiController, routes/library-api.php); these actions only render the pages.
 */
class ContentController extends Controller
{
    private function actor(): LibraryActor
    {
        return LibraryActor::teacher(Auth::guard('teacher')->user());
    }

    /** Every subject the teacher may manage, however they are linked to it (pivot or own group). */
    private function accessibleSubjects()
    {
        return Subject::whereIn('id', $this->actor()->subjectIds() ?? [])
            ->with('program')
            ->orderBy('name_ar')
            ->get();
    }

    public function index()
    {
        $subjects = $this->accessibleSubjects();

        return view('teacher.content.index', compact('subjects'));
    }

    public function hub()
    {
        $teacher = Auth::guard('teacher')->user();

        $subjects = $this->accessibleSubjects();
        $groups = Group::where('teacher_id', $teacher->id)
            ->with(['subject.program'])
            ->withCount(['registrations as students_count' => function ($q) {
                $q->whereIn('status', ['pending', 'partially_paid', 'fully_paid']);
            }])
            ->orderByDesc('created_at')
            ->get();

        return view('teacher.content.hub', compact('subjects', 'groups'));
    }

    public function manage(Request $request, Subject $subject, ResourceCatalog $catalog)
    {
        $actor = $this->actor();
        abort_unless($actor->canAccessSubject($subject->id), 403);

        $groupId = $request->integer('group') ?: null;
        $tree = $catalog->manage($actor, $subject, $groupId);

        $config = [
            'role' => 'teacher',
            'isAdmin' => false,
            'api' => url('teacher/library'),
            'csrf' => csrf_token(),
            'actorName' => $actor->name(),
            'types' => config('resource_library.types'),
            'upload' => UploadLimits::forClient(),
            'subject' => $tree['subject'],
            'group' => $tree['selected_group'],
            'focusId' => $request->integer('focus') ?: null,
            'tree' => $tree,
        ];

        return view('teacher.content.manage', compact('subject', 'config'));
    }
}
