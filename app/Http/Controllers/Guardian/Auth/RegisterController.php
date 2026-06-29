<?php

namespace App\Http\Controllers\Guardian\Auth;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\Student;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:guardian');
    }

    public function showRegisterForm()
    {
        return view('guardian.auth.register');
    }

    /**
     * Children-program registration (Flow 2): the guardian is the real
     * account holder; the child is stored as a Student with is_child=true
     * and no login of their own (random unusable password).
     */
    public function register(Request $request, OtpService $otp)
    {
        $data = $request->validate([
            'childName' => 'required|string|max:255',
            'childDob' => 'required|date',
            'childGender' => 'required|in:male,female',
            'fullName' => 'required|string|max:255',
            'nationalId' => 'nullable|string|max:20|unique:guardians,national_id',
            'relationship' => 'required|in:father,mother,brother,sister,other',
            'phone' => 'required|string|max:30',
            'email' => 'required|email|unique:guardians,email',
            'address' => 'nullable|string',
            'password' => 'required|string|min:8|confirmed',
            'terms' => 'required|accepted',
        ]);

        $guardian = Guardian::create([
            'full_name' => $data['fullName'],
            'national_id' => $data['nationalId'] ?? null,
            'phone' => $data['phone'],
            'email' => $data['email'],
            'address' => $data['address'] ?? null,
            'relationship' => $data['relationship'],
            'password' => Hash::make($data['password']),
            'is_active' => false,
        ]);

        Student::create([
            'full_name_ar' => $data['childName'],
            'full_name_en' => $data['childName'],
            'is_child' => true,
            'guardian_id' => $guardian->id,
            'email' => $guardian->email.'.child'.$guardian->id.'@placeholder.local',
            'phone' => $data['phone'],
            'date_of_birth' => $data['childDob'],
            'gender' => $data['childGender'],
            'password' => Hash::make(Str::random(40)),
            'status' => 0,
        ]);

        $otp->generateAndSend($guardian->email, 'guardian');

        $request->session()->put('otp.guardian.email', $guardian->email);

        return redirect()->route('guardian.verify');
    }
}
