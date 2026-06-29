<?php

namespace App\Http\Controllers\Student\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:student');
    }

    public function showRegisterForm()
    {
        return view('student.auth.register');
    }

    public function register(Request $request, OtpService $otp)
    {
        $data = $request->validate([
            'fullnameAr' => 'required|string|max:255',
            'fullnameEn' => 'required|string|max:255',
            'nationalId' => 'nullable|string|max:20|unique:students,national_id',
            'email' => 'required|email|unique:students,email',
            'phone' => 'required|string|max:30',
            'dob' => 'required|date',
            'gender' => 'required|string',
            'branch' => 'required|string',
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
            'address' => $data['address'] ?? null,
            'health_information' => $data['health'] ?? null,
            'major_profession' => $data['profession'] ?? null,
            'password' => Hash::make($data['password']),
            'status' => 0,
        ]);

        $otp->generateAndSend($student->email, 'student');

        $request->session()->put('otp.student.email', $student->email);

        return redirect()->route('student.verify');
    }
}
