<?php

namespace App\Http\Controllers\Student\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerificationCode;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use App\Models\Admin;
use App\Notifications\NewStudentRegisteredNotification;

class RegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:student');
    }

    public function showRegisterForm(Request $request)
    {
        $regions = \App\Models\Region::where('status', 1)->get();
        $branches = \App\Models\Branch::where('status', 1)->get();

        // If this session left an unverified registration behind (e.g. the user
        // refreshed the page before entering the OTP), surface it again so the
        // verification modal reopens instead of silently disappearing.
        $pendingEmail = $request->session()->get('otp.student.email');
        $pendingStudent = null;

        if ($pendingEmail) {
            $pendingStudent = Student::where('email', $pendingEmail)->whereNull('email_verified_at')->first();

            if (!$pendingStudent) {
                $request->session()->forget('otp.student.email');
                $pendingEmail = null;
            }
        }

        $activeCode = $pendingStudent
            ? $pendingStudent->emailVerificationCodes()->active()->latest()->first()
            : null;

        $hasActiveCode = (bool) $activeCode;
        $codeExpirySeconds = $activeCode ? max(0, now()->diffInSeconds($activeCode->expires_at, false)) : 0;

        return view('student.auth.register', compact('regions', 'branches', 'pendingEmail', 'hasActiveCode', 'codeExpirySeconds'));
    }

    public function register(Request $request, \App\Actions\SendVerificationCodeAction $sendCodeAction)
    {
        $data = $request->validate([
            'fullnameAr' => 'required|string|max:255',
            'fullnameEn' => 'required|string|max:255',
            // A row only blocks re-registration once its owner has verified the
            // email — an abandoned, unverified signup must not lock the address.
            'nationalId' => ['nullable', 'string', 'max:20', Rule::unique('students', 'national_id')->where(fn ($q) => $q->whereNotNull('email_verified_at'))],
            'email' => ['required', 'email', Rule::unique('students', 'email')->where(fn ($q) => $q->whereNotNull('email_verified_at'))],
            'phone' => 'required|string|max:30',
            'dob' => 'required|date',
            'gender' => 'required|string',
            'region_id' => 'required|string',
            'branch_id' => 'required|string',
            'program' => 'required|string',
            'address' => 'nullable|string',
            'health' => 'nullable|string',
            'profession' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'terms' => 'required|accepted',
        ], [
            'email.unique' => 'An account with this email already exists. Please sign in.',
        ]);

        $attributes = [
            'full_name_ar' => $data['fullnameAr'],
            'full_name_en' => $data['fullnameEn'],
            'national_id' => $data['nationalId'] ?? null,
            'email' => $data['email'],
            'phone' => $data['phone'],
            'date_of_birth' => $data['dob'],
            'gender' => $data['gender'],
            'region_id' => $data['region_id'],
            'branch_id' => $data['branch_id'],
            'address' => $data['address'] ?? null,
            'health_information' => $data['health'] ?? null,
            'major_profession' => $data['profession'] ?? null,
            'password' => Hash::make($data['password']),
            'status' => 0,
        ];

        // Resume an abandoned, unverified registration instead of creating a
        // duplicate row for the same email address.
        $student = Student::where('email', $data['email'])->whereNull('email_verified_at')->first();

        if ($student) {
            $student->update($attributes);
            EmailVerificationCode::where('student_id', $student->id)->delete();
        } else {
            $student = Student::create($attributes);
        }

        $sendCodeAction->execute($student);

        // Notify admins
        Notification::send(Admin::all(), new NewStudentRegisteredNotification($student));

        // Store email in session (not flashed) so a page refresh can still
        // detect the pending verification and reopen the OTP modal.
        $request->session()->put('otp.student.email', $student->email);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Account created successfully. Please verify your email.',
                'email' => $student->email
            ]);
        }

        return redirect()->back();
    }
}
