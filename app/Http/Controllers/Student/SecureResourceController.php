<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SubjectResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;

class SecureResourceController extends Controller
{
    /**
     * Embed a secure link or resource within an iframe.
     * Prevents direct access to the URL by wrapping it in a protected view.
     */
    public function embed(Request $request, SubjectResource $resource)
    {

        $student = Auth::guard('student')->user();
        if (!$student) {
            abort(403);
        }

        return view('student.resources.embed', compact('resource', 'student'));
    }
}
