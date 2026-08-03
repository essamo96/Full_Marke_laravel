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

            // Single-device lock: keyed off a random token in a long-lived
            // cookie (survives IP/network changes, unique per browser), not
            // the IP address — the first device/browser to log in claims the
            // account; any other device is refused until an admin clears it
            // (the "delete device" action), which is how a student
            // legitimately switches to a new phone/laptop/browser.
            $deviceId = $request->cookie(StudentDeviceLock::COOKIE);
            if (! $deviceId) {
                $deviceId = (string) Str::uuid();
                Cookie::queue(StudentDeviceLock::COOKIE, $deviceId, StudentDeviceLock::COOKIE_MINUTES);
            }

            if ($student->locked_device_id && $student->locked_device_id !== $deviceId) {
                Auth::guard('student')->logout();

                return back()->withErrors([
                    'email' => 'حسابك مسجّل الدخول حالياً من جهاز آخر. يرجى التواصل مع الإدارة لإلغاء ربط الجهاز إذا كنت تريد الدخول من جهاز جديد.',
                ])->onlyInput('email');
            }

            $currentIp = $request->ip();
            $updates = ['last_seen_at' => now(), 'locked_ip' => $currentIp];
            if (! $student->locked_device_id) {
                $updates['locked_device_id'] = $deviceId;
                $updates['locked_device_id_set_at'] = now();
            }
            if (! $student->locked_ip_set_at) {
                $updates['locked_ip_set_at'] = now();
            }
            $student->update($updates);

            $request->session()->regenerate();

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
