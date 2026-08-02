<?php

namespace App\Http\Controllers\Student\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

            // Single-device lock: the first device to log in claims the
            // account's IP; any other device is refused until an admin
            // clears it (the "delete IP" action), which is how a student
            // legitimately switches to a new device/laptop.
            $currentIp = $request->ip();
            if ($student->locked_ip && $student->locked_ip !== $currentIp) {
                Auth::guard('student')->logout();

                return back()->withErrors([
                    'email' => 'حسابك مسجّل الدخول حالياً من جهاز آخر. يرجى التواصل مع الإدارة لإلغاء ربط الجهاز إذا كنت تريد الدخول من جهاز جديد.',
                ])->onlyInput('email');
            }

            if (! $student->locked_ip) {
                $student->update(['locked_ip' => $currentIp, 'locked_ip_set_at' => now()]);
            }
            $student->update(['last_seen_at' => now()]);

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
