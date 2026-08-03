<?php

namespace App\Http\Middleware;

use App\Support\StudentDeviceLock;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps the single-device lock enforced for the whole session, not just at
 * the login gate: if an admin clears a student's locked device (the "delete
 * device" action) and the account is then logged in from a new device, this
 * signs the old device's still-open session out the next time it makes a
 * request — otherwise the "only one device" rule would only ever apply to
 * the login form and a stale session could keep working forever.
 *
 * The lock itself is keyed off the long-lived cookie set at login (see
 * StudentDeviceLock / LoginController), not the IP address — so a student
 * switching WiFi/mobile networks on the same phone stays logged in, while a
 * different phone/browser (no matching cookie) gets rejected.
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

        $deviceId = $request->cookie(StudentDeviceLock::COOKIE);

        // An admin clearing the device lock (force_logout_after) must sign
        // out this session even though locked_device_id is now null — the
        // plain mismatch check below would otherwise let an already-open
        // session on the old device keep working indefinitely.
        $sessionStartedAt = $request->session()->get('student_logged_in_at');
        $forceLoggedOut = $student
            && $student->force_logout_after
            && (! $sessionStartedAt || $student->force_logout_after->timestamp > $sessionStartedAt);

        $lockedDeviceIds = $student ? $student->locked_device_ids : [];
        $deviceMismatch = ! empty($lockedDeviceIds) && ! in_array($deviceId, $lockedDeviceIds, true);

        if ($student && ($deviceMismatch || $forceLoggedOut)) {
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
