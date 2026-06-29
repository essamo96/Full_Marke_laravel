<?php

namespace App\Http\Controllers\Guardian\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:guardian')->except('logout');
        $this->middleware('auth:guardian')->only('logout');
    }

    public function showLoginForm()
    {
        return view('guardian.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::guard('guardian')->attempt($credentials, $request->boolean('remember'))) {
            $guardian = Auth::guard('guardian')->user();

            if (! $guardian->is_active) {
                Auth::guard('guardian')->logout();
                $request->session()->put('otp.guardian.email', $guardian->email);

                return redirect()->route('guardian.verify')->withErrors([
                    'email' => __('app.account_not_verified'),
                ]);
            }

            $request->session()->regenerate();

            return redirect()->intended(route('site.home'));
        }

        return back()->withErrors([
            'email' => __('auth.failed'),
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('guardian')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('guardian.login');
    }
}
