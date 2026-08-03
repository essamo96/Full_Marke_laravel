<?php

namespace App\Http\Controllers\Student\Auth;

use App\Http\Controllers\Controller;
use App\Support\StudentDeviceLock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:student')->except('logout');
        $this->middleware('auth:student')->only('logout');
    }

    public function showLoginForm()
    {
        return view('student.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::guard('student')->attempt($credentials, $request->boolean('remember'))) {
            $student = Auth::guard('student')->user();

            if (! $student->status) {
                Auth::guard('student')->logout();

                // A never-verified account still needs the OTP flow; a previously-verified
                // account with status=0 was suspended by the admin (fees) — block it outright.
                if (! $student->isEmailVerified()) {
                    $request->session()->put('otp.student.email', $student->email);

                    return redirect()->route('student.verify')->withErrors([
                        'email' => __('app.account_not_verified'),
                    ]);
                }

                return back()->withErrors([
                    'email' => __('app.account_suspended_fees', ['amount' => number_format($student->total_due, 2)]),
                ])->onlyInput('email');
            }

            // Device lock: keyed off a random token in a long-lived cookie
            // (survives IP/network changes, unique per browser), not the IP
            // address. A student may hold up to $student->max_devices
            // distinct devices at once (default 1; an admin can raise it to
            // e.g. 2 for "phone + laptop") — the first max_devices
            // browsers to log in claim those slots; anything beyond that is
            // refused until an admin clears the account's devices (the
            // "delete device" action).
            $deviceId = $request->cookie(StudentDeviceLock::COOKIE);
            if (! $deviceId) {
                $deviceId = (string) Str::uuid();
                Cookie::queue(StudentDeviceLock::COOKIE, $deviceId, StudentDeviceLock::COOKIE_MINUTES);
            }

            $lockedDeviceIds = $student->locked_device_ids;
            $maxDevices = max(1, (int) $student->max_devices);
            $isKnownDevice = in_array($deviceId, $lockedDeviceIds, true);

            if (! $isKnownDevice && count($lockedDeviceIds) >= $maxDevices) {
                Auth::guard('student')->logout();

                return back()->withErrors([
                    'email' => 'حسابك مسجّل الدخول حالياً من جهاز آخر. يرجى التواصل مع الإدارة لإلغاء ربط الجهاز إذا كنت تريد الدخول من جهاز جديد.',
                ])->onlyInput('email');
            }

            $currentIp = $request->ip();
            $updates = ['last_seen_at' => now(), 'locked_ip' => $currentIp];
            if (! $isKnownDevice) {
                $lockedDeviceIds[] = $deviceId;
                $updates['locked_device_id'] = json_encode($lockedDeviceIds);
                $updates['locked_device_id_set_at'] = now();
            }
            if (! $student->locked_ip_set_at) {
                $updates['locked_ip_set_at'] = now();
            }
            $student->update($updates);

            $request->session()->regenerate();
            // Stamped so EnforceStudentDeviceLock can tell this session apart
            // from one that existed before an admin's later "clear device"
            // action (force_logout_after) — see that middleware for why.
            $request->session()->put('student_logged_in_at', now()->timestamp);

            return redirect()->intended(route('student.dashboard'))->with('show_welcome', true);
        }

        return back()->withErrors([
            'email' => __('auth.failed'),
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('student')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('student.login');
    }
}
