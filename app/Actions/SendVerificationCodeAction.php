<?php

namespace App\Actions;

use App\Models\EmailVerificationCode;
use App\Models\Student;
use App\Mail\StudentVerificationMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendVerificationCodeAction
{
    public function execute(Student $student)
    {
        // 1. Generate 6-digit random code
        $code = (string) random_int(100000, 999999);

        // 2. Hash and store it with a 5-minute expiration
        EmailVerificationCode::create([
            'student_id' => $student->id,
            'code' => Hash::make($code),
            'expires_at' => now()->addMinutes(5),
            'attempts' => 0,
        ]);

        // 3. Dispatch the email job (queued)
        try {
            Mail::to($student->email)->send(new StudentVerificationMail($code));
        } catch (\Throwable $e) {
            Log::error('Student Verification OTP mail send failed', [
                'student_id' => $student->id, 
                'email' => $student->email, 
                'error' => $e->getMessage()
            ]);
        }
    }
}
