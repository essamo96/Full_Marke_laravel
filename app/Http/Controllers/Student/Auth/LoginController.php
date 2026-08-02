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
