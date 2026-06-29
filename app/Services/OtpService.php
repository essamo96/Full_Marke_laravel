<?php

namespace App\Services;

use App\Mail\VerificationCodeMail;
use App\Models\EmailVerification;
use Illuminate\Support\Facades\Mail;

/**
 * Generates, sends and verifies the 6-digit email OTP codes used by both
 * the student and guardian registration flows (P4 in academy_system_analysis.md).
 * Codes are valid for 10 minutes and single-use.
 */
class OtpService
{
    public function generateAndSend(string $email, string $userType): EmailVerification
    {
        $code = (string) random_int(100000, 999999);

        $verification = EmailVerification::create([
            'email' => $email,
            'code' => $code,
            'user_type' => $userType,
            'expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($email)->send(new VerificationCodeMail($code));

        return $verification;
    }

    public function verify(string $email, string $userType, string $code): bool
    {
        $verification = EmailVerification::where('email', $email)
            ->where('user_type', $userType)
            ->where('code', $code)
            ->whereNull('used_at')
            ->latest('id')
            ->first();

        if (! $verification || $verification->isExpired()) {
            return false;
        }

        $verification->update(['used_at' => now()]);

        return true;
    }

    public function resend(string $email, string $userType): EmailVerification
    {
        return $this->generateAndSend($email, $userType);
    }
}
