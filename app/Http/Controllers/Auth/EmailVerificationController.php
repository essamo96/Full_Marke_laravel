<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\Student;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Shared OTP verification flow for both `student` and `guardian` registrations
 * (P4 in academy_system_analysis.md — single 6-digit code screen, 10-minute
 * expiry, 60s resend countdown). The $userType route param selects the guard.
 */
class EmailVerificationController extends Controller
{
    public function showForm(string $userType)
    {
        $email = session("otp.{$userType}.email");

        if (! $email) {
            return redirect()->route("{$userType}.register");
        }

        return view('auth.verify-otp', ['userType' => $userType, 'email' => $email]);
    }

    public function verify(Request $request, string $userType, OtpService $otp)
    {
        $email = session("otp.{$userType}.email");

        if (! $email) {
            return redirect()->route("{$userType}.register");
        }

        $request->validate(['code' => 'required|digits:6']);

        if (! $otp->verify($email, $userType, $request->code)) {
            return back()->withErrors(['code' => __('app.invalid_or_expired_code')]);
        }

        $model = $userType === 'guardian'
            ? Guardian::where('email', $email)->firstOrFail()
            : Student::where('email', $email)->firstOrFail();

        $model->update(array_merge(
            ['email_verified_at' => now()],
            $userType === 'guardian' ? ['is_active' => true] : ['status' => 1]
        ));

        session()->forget("otp.{$userType}.email");

        Auth::guard($userType)->login($model);
        $request->session()->regenerate();

        return redirect()->route('site.home');
    }

    public function resend(Request $request, string $userType, OtpService $otp)
    {
        $email = session("otp.{$userType}.email");

        if (! $email) {
            return redirect()->route("{$userType}.register");
        }

        $otp->resend($email, $userType);

        return back()->with('success', __('app.code_resent'));
    }
}
