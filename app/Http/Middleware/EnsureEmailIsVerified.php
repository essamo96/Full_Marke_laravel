<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $student = Auth::guard('student')->user();

        if ($student && !$student->isEmailVerified()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Your email address is not verified.'], 403);
            }

            // Redirect back to apply form with intent to show OTP modal
            return redirect()->route('apply.create')->with([
                'show_otp_modal' => true,
                'email' => $student->email
            ]);
        }

        return $next($request);
    }
}
