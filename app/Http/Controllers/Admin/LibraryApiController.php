<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Library\LibraryApiController as BaseLibraryApiController;
use App\Services\ResourceLibrary\LibraryActor;
use Illuminate\Support\Facades\Auth;

/**
 * The library API as the admin uses it: every subject, every group, every student.
 *
 * Who may read and who may change is decided by the route group (see routes/admin.php and
 * App\Http\Middleware\EnsureLibraryEditAccess).
 */
class LibraryApiController extends BaseLibraryApiController
{
    protected function actor(): LibraryActor
    {
        return LibraryActor::admin(Auth::guard('admin')->user());
    }
}
