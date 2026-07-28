<?php

namespace App\Http\Controllers\Student\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\EmailVerificationCode;
use App\Actions\SendVerificationCodeAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:students,email',
            'code' => 'required|string|size:6',
        ]);

        $student = Student::where('email', $request->email)->firstOrFail();

        // Get the latest active verification code
        $verification = EmailVerificationCode::active()
            ->where('student_id', $student->id)
            ->latest()
            ->first();

        if (!$verification) {
            return response()->json([
                'status' => 'error',
                'message' => 'انتهت صلاحية الكود أو غير موجود، اطلب رمزاً جديداً.' // Code expired or invalid
            ], 422);
        }

        if ($verification->attempts >= 5) {
            return response()->json([
                'status' => 'error',
                'message' => 'تجاوزت الحد الأقصى للمحاولات، يرجى طلب كود جديد.'
            ], 422);
        }

        if (!Hash::check($request->code, $verification->code)) {
            $verification->increment('attempts');
            return response()->json([
                'status' => 'error',
                'message' => 'رمز التحقق غير صحيح.'
            ], 422);
        }

        // Success — verifying the email also activates the account immediately
        $student->update(['email_verified_at' => now(), 'status' => true]);
        EmailVerificationCode::where('student_id', $student->id)->delete(); // clear old codes

        $request->session()->forget('otp.student.email');

        Auth::guard('student')->login($student);

        return response()->json([
            'status' => 'success',
            'message' => 'تم التحقق بنجاح!',
            'redirect' => route('student.dashboard') // Assuming student.dashboard exists
        ]);
    }

    public function resend(Request $request, SendVerificationCodeAction $sendCodeAction)
    {
        $request->validate([
            'email' => 'required|email|exists:students,email',
        ]);

        $student = Student::where('email', $request->email)->firstOrFail();

        if ($student->isEmailVerified()) {
            return response()->json(['status' => 'error', 'message' => 'الحساب مفعل مسبقاً.'], 400);
        }

        // Throttle logic could also be handled by route middleware 'throttle:1,1'
        $sendCodeAction->execute($student);

        return response()->json([
            'status' => 'success',
            'message' => 'تم إرسال كود التحقق الجديد بنجاح.'
        ]);
    }
}
