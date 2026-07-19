@extends('layouts.student-auth')

@section('title', 'Forgot Password | FULL MARK ACADEMY')

@section('content')
          @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
          @endif
          @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
          @endif

          <p class="card-subtitle text-center mb-4" data-en="Enter your registered email address and we will send a new password to it." data-ar="أدخل بريدك الإلكتروني المسجل وسيتم إرسال كلمة مرور جديدة إليه.">
            Enter your registered email address and we will send a new password to it.
          </p>

          <form method="POST" action="{{ route('student.password.email') }}">
            @csrf
            <div class="d-flex flex-column gap-4">

              <div class="input-group-luxe">
                <label for="forgotEmail" data-en="Email" data-ar="البريد الإلكتروني">Email</label>
                <div class="position-relative">
                  <i class="bi bi-envelope-fill input-icon"></i>
                  <input type="email" id="forgotEmail" name="email" value="{{ old('email') }}" class="lux-input" placeholder="student@fullmark.jo" autocomplete="email" autofocus required>
                </div>
              </div>

              <button type="submit" class="btn btn-luxury w-100 py-3 rounded-lg d-flex align-items-center justify-content-center gap-2" style="font-size:1rem; font-weight:700; margin-top:0.5rem;">
                <i class="bi bi-send-fill"></i>
                <span data-en="Send New Password" data-ar="إرسال كلمة المرور الجديدة">Send New Password</span>
              </button>

              <div class="signup-link" style="padding-top:0.5rem; border-top: 1px solid rgba(197,168,128,0.1); margin-top:0.25rem;">
                <a href="{{ route('student.login') }}" data-en="← Back to login" data-ar="← العودة لتسجيل الدخول">← Back to login</a>
              </div>
            </div>
          </form>
@endsection
