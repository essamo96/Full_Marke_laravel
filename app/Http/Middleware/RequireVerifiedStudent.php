<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequireVerifiedStudent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('student')->check()) {
            return redirect()->route('student.login');
        }

        if (Auth::guard('student')->user()->email_verified_at === null) {
            return redirect()->route('student.verification.notice')
                ->with('warning', 'يرجى تأكيد بريدك الإلكتروني للوصول إلى هذه الصفحة.');
        }

        return $next($request);
    }
}
