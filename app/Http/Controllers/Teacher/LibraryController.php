<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Services\ResourceLibrary\LibraryActor;
use App\Support\Library\UploadLimits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The teacher's resource library page: every resource of every subject they teach in one table,
 * with the same distribution matrix and controls the admin has (the data comes from the shared API).
 */
class LibraryController extends Controller
{
    public function index(Request $request)
    {
        $actor = LibraryActor::teacher(Auth::guard('teacher')->user());

        $config = [
            'role' => 'teacher',
            'isAdmin' => false,
            'api' => url('teacher/library'),
            'csrf' => csrf_token(),
            'actorName' => $actor->name(),
            'types' => config('resource_library.types'),
            'upload' => UploadLimits::forClient(),
            'subject' => $request->integer('subject') ?: null,
            // "__SUBJECT__" is replaced by the encrypted key of the subject in the browser
            'manageUrl' => url('teacher/content').'/__SUBJECT__',
        ];

        return view('teacher.library.index', compact('config'));
    }
}
