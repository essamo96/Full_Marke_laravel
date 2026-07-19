<?php

namespace App\Http\Controllers\Student\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Notifications\StudentNewPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:student');
    }

    public function showLinkRequestForm()
    {
        return view('student.auth.forgot-password');
    }

    public function sendNewPassword(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
        ]);

        $student = Student::where('email', $data['email'])->first();

        if (! $student) {
            return back()->withErrors([
                'email' => __('app.email_not_found'),
            ])->onlyInput('email');
        }

        $newPassword = Str::password(10);

        $student->update([
            'password' => Hash::make($newPassword),
        ]);

        $student->notify(new StudentNewPasswordNotification($newPassword));

        return redirect()->route('student.login')->with('status', __('app.new_password_sent'));
    }
}
