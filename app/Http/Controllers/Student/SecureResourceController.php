<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Student\Concerns\AuthorizesStudentResourceAccess;
use App\Models\SubjectResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SecureResourceController extends Controller
{
    use AuthorizesStudentResourceAccess;

    /**
     * Resolves a resource into a protected media payload for the unified player.
     * Does not expose raw YouTube watch URLs — only the video id when needed.
     */
    public function resolve(Request $request, SubjectResource $resource)
    {
        $this->authorizeStudentResource($resource);

        return response()->json([
            'success' => true,
            'title' => $resource->title,
            'resource_id' => $resource->getRouteKey(),
            ...$this->resolveMediaPayload($resource),
        ]);
    }

    /**
     * Legacy nested embed page (kept as fallback). Prefer in-page unified player.
     */
    public function embed(Request $request, SubjectResource $resource)
    {
        $this->authorizeStudentResource($resource);

        $student = Auth::guard('student')->user();
        $payload = $this->resolveMediaPayload($resource);

        return view('student.resources.embed', compact('resource', 'student', 'payload'));
    }
}
