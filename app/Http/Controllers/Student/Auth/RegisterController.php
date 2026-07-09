<?php

namespace App\Http\Controllers\Student\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Models\Admin;
use App\Notifications\NewStudentRegisteredNotification;

class RegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:student');
    }

    public function showRegisterForm()
    {
        $regions = \App\Models\Region::where('status', 1)->get();
        $branches = \App\Models\Branch::where('status', 1)->get();
        
        return view('student.auth.register', compact('regions', 'branches'));
    }

    public function register(Request $request, \App\Actions\SendVerificationCodeAction $sendCodeAction)
    {
        $data = $request->validate([
            'fullnameAr' => 'required|string|max:255',
            'fullnameEn' => 'required|string|max:255',
            'nationalId' => 'nullable|string|max:20|unique:students,national_id',
            'email' => 'required|email|unique:students,email',
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
        ]);

        $student = Student::create([
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
        ]);

        $sendCodeAction->execute($student);

        // Notify admins
        Notification::send(Admin::all(), new NewStudentRegisteredNotification($student));

        // Store email in session in case page is reloaded or for traditional submit
        $request->session()->put('otp.student.email', $student->email);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Account created successfully. Please verify your email.',
                'email' => $student->email
            ]);
        }

        return redirect()->back()->with('show_otp_modal', true);
    }
}
