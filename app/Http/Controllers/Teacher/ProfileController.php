<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('teacher.profile.edit', ['teacher' => Auth::guard('teacher')->user()]);
    }

    public function update(Request $request)
    {
        $teacher = Auth::guard('teacher')->user();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('teachers', 'public');
        }

        $teacher->update($data);

        return back()->with('success', 'تم تحديث الملف الشخصي بنجاح.');
    }

    public function updatePassword(Request $request)
    {
        $teacher = Auth::guard('teacher')->user();

        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (! Hash::check($request->current_password, $teacher->password)) {
            return back()->withErrors(['current_password' => 'كلمة المرور الحالية غير صحيحة.']);
        }

        $teacher->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'تم تحديث كلمة المرور بنجاح.');
    }
}
