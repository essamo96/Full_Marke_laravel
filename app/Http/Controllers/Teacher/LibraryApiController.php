<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Library\LibraryApiController as BaseLibraryApiController;
use App\Services\ResourceLibrary\LibraryActor;
use Illuminate\Support\Facades\Auth;

/**
 * The library API as the teacher uses it: identical to the admin's, fenced into the teacher's own
 * groups and students by LibraryActor.
 */
class LibraryApiController extends BaseLibraryApiController
{
    protected function actor(): LibraryActor
    {
        return LibraryActor::teacher(Auth::guard('teacher')->user());
    }
}
