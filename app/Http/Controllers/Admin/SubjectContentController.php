<?php

namespace App\Http\Controllers\Admin;

use App\Http\Middleware\EnsureLibraryEditAccess;
use App\Models\Subject;
use App\Services\ResourceLibrary\LibraryActor;
use App\Services\ResourceLibrary\ResourceCatalog;
use App\Support\Library\UploadLimits;
use App\Support\RouteKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

/**
 * The admin's content screen of one subject. It renders the same shared workspace the teacher
 * uses; everything that changes data goes through the shared library API
 * (Admin\LibraryApiController, routes/library-api.php).
 */
class SubjectContentController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'subject_content';
    }

    public function index()
    {
        $subjects = Subject::with('program')->get();

        return view('admin.subject_content.index', self::$data + compact('subjects'));
    }

    public function manage(Request $request, $id, ResourceCatalog $catalog)
    {
        $subject = $this->subjectFrom((string) $id);

        if (! $subject) {
            return redirect()->route('subject_content.view')->with('danger', __('app.not_found'));
        }

        $actor = LibraryActor::admin(Auth::guard('admin')->user());
        $groupId = $request->integer('group') ?: null;
        $tree = $catalog->manage($actor, $subject, $groupId);

        $config = [
            'role' => 'admin',
            'isAdmin' => true,
            'canEdit' => (bool) Auth::guard('admin')->user()?->can(EnsureLibraryEditAccess::PERMISSION),
            'api' => url('admin/library'),
            'csrf' => csrf_token(),
            'actorName' => $actor->name(),
            'types' => config('resource_library.types'),
            'upload' => UploadLimits::forClient(),
            'subject' => $tree['subject'],
            'group' => $tree['selected_group'],
            'focusId' => $request->integer('focus') ?: null,
            'tree' => $tree,
        ];

        return view('admin.subject_content.manage', self::$data + compact('subject', 'config'));
    }

    /**
     * Subject links come in two spellings: the route key used everywhere now, and the serialized
     * Crypt::encrypt() id older pages and bookmarks still carry. Both keep working.
     */
    private function subjectFrom(string $id): ?Subject
    {
        $subjectId = RouteKey::decrypt($id);

        if (! $subjectId) {
            try {
                $subjectId = (int) Crypt::decrypt($id);
            } catch (\Throwable) {
                return null;
            }
        }

        return Subject::find($subjectId);
    }
}
