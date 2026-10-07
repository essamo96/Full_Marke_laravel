<?php

namespace App\Http\Middleware;

use App\Services\ResourceLibrary\LibraryException;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin side of the resource library API: reading is open to anyone the route group lets in
 * (resource library / subject content "view"), but changing anything needs the subject content
 * "edit" permission. A view-only role therefore gets a read-only library instead of a working
 * kill switch.
 */
class EnsureLibraryEditAccess
{
    public const PERMISSION = 'admin.subject_content.edit';

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethodSafe() && ! Auth::guard('admin')->user()?->can(self::PERMISSION)) {
            throw LibraryException::forbidden('ليس لديك صلاحية تعديل محتوى المواد — الشاشة للعرض فقط.');
        }

        return $next($request);
    }
}
