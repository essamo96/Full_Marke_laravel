<?php

namespace App\Http\Controllers\Admin;

use App\Http\Middleware\EnsureLibraryEditAccess;
use App\Services\ResourceLibrary\LibraryActor;
use App\Support\Library\UploadLimits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

/**
 * The admin's resource library: every resource of every subject in one table, with the
 * distribution matrix (resource x group), the kill switch, sharing, exclusions and undo.
 *
 * It used to filter resources by a `group_ids` column that no longer exists, so every group
 * "had" every video, general resources never appeared, and nothing could be changed from there.
 * It now reads the same catalog the teacher's screens read, so both show the same videos.
 */
class ResourceLibraryController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'resource-library';
    }

    public function index(Request $request)
    {
        $actor = LibraryActor::admin(Auth::guard('admin')->user());

        $config = [
            'role' => 'admin',
            'isAdmin' => true,
            'canEdit' => (bool) Auth::guard('admin')->user()?->can(EnsureLibraryEditAccess::PERMISSION),
            'api' => url('admin/library'),
            'csrf' => csrf_token(),
            'actorName' => $actor->name(),
            'types' => config('resource_library.types'),
            'upload' => UploadLimits::forClient(),
            'subject' => $this->requestedSubject($request),
            // "__SUBJECT__" is replaced by the encrypted key of the subject in the browser
            'manageUrl' => url('admin/subject-content').'/__SUBJECT__',
        ];

        return view('admin.resource_library.index', self::$data + compact('config'));
    }

    /** ?subject=<id>, or the legacy ?subject_id=<Crypt::encrypt(id)> older links carry. */
    private function requestedSubject(Request $request): ?int
    {
        if ($request->filled('subject')) {
            return $request->integer('subject') ?: null;
        }

        if ($request->filled('subject_id')) {
            try {
                return (int) Crypt::decrypt($request->query('subject_id'));
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }
}
