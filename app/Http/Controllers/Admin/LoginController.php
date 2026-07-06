<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App;
use Illuminate\Http\RedirectResponse;

class LoginController extends AdminController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getIndex()
    {
        return view('admin.auth.login');
    }

    public function postIndex(Request $request)
    {
        // التحقق من الحقول
        $validator = Validator::make(
            $request->all(),
            [
                'username' => 'required|string',
                'password' => 'required|string|min:6',
            ],
            [
                'username.required' => 'يرجى إدخال البريد الإلكتروني أو اسم المستخدم.',
                'password.required' => 'يرجى إدخال كلمة المرور.',
                'password.min' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل.',
            ]
        );

        if ($validator->fails()) {
            return redirect()
                ->route('login')
                ->withErrors($validator)
                ->withInput();
        }

        // تحديد ما إذا كان الحقل بريد إلكتروني أو اسم مستخدم
        $field = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // بيانات تسجيل الدخول
        $credentials = [
            $field     => $request->username,
            'password' => $request->password,
            'status'   => 1,
        ];

        // محاولة تسجيل الدخول
        if (Auth::guard('admin')->attempt($credentials)) {
            return redirect()->intended('/admin/dashboard');
        }

        // في حال الفشل
        return redirect()
            ->route('login')
            ->withInput()
            ->with('danger', 'اسم المستخدم أو كلمة المرور غير صحيحة');
    }



    public function getLogout(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();

        $request->session()->regenerateToken();
        return redirect('/admin/login');
    }
}
