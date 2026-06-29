<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('student.profile.edit', ['student' => Auth::guard('student')->user()]);
    }

    public function update(Request $request)
    {
        $student = Auth::guard('student')->user();

        $data = $request->validate([
            'full_name_en' => 'required|string|max:255',
            'full_name_ar' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'address' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('students', 'public');
        }

        $student->update($data);

        return back()->with('success', __('app.update_success'));
    }

    public function updatePassword(Request $request)
    {
        $student = Auth::guard('student')->user();

        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (! Hash::check($request->current_password, $student->password)) {
            return back()->withErrors(['current_password' => __('app.invalid_current_password')]);
        }

        $student->update(['password' => Hash::make($request->password)]);

        return back()->with('success', __('app.update_success'));
    }
}
