<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps the single-device lock enforced for the whole session, not just at
 * the login gate: if an admin clears a student's locked IP (the "delete IP"
 * action) and the account is then logged in from a new device, this signs
 * the old device's still-open session out the next time it makes a request
 * — otherwise the "only one device" rule would only ever apply to the login
 * form and a stale session could keep working forever.
 *
 * Also doubles as the heartbeat for the admin's live "who's online" panel,
 * throttled to one write per minute per student so it doesn't hit the
 * database on every single request.
 */
class EnforceStudentDeviceLock
{
    public function handle(Request $request, Closure $next): Response
    {
        $student = Auth::guard('student')->user();

        if ($student && $student->locked_ip && $student->locked_ip !== $request->ip()) {
            Auth::guard('student')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = 'تم تسجيل خروجك — تم فتح هذا الحساب من جهاز آخر.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => $message], 401);
            }

            return redirect()->route('student.login')->withErrors(['email' => $message]);
        }

        if ($student && (! $student->last_seen_at || $student->last_seen_at->lt(now()->subMinute()))) {
            $student->timestamps = false;
            $student->update(['last_seen_at' => now()]);
        }

        return $next($request);
    }
}
