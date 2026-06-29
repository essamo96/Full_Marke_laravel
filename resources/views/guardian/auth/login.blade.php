@extends('layouts.student-auth')

@section('title', 'Guardian Login | FULL MARK ACADEMY')

@section('content')
          @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
          @endif

          <form id="guardianLoginForm" method="POST" action="{{ route('guardian.login.submit') }}">
            @csrf
            <div class="d-flex flex-column gap-4">
              <div class="input-group-luxe">
                <label for="guardianEmail" data-en="Email Address" data-ar="البريد الإلكتروني">Email Address</label>
                <div class="position-relative">
                  <i class="bi bi-envelope-fill input-icon"></i>
                  <input type="email" id="guardianEmail" name="email" value="{{ old('email') }}" class="lux-input" autocomplete="username" autofocus required>
                </div>
              </div>

              <div class="input-group-luxe pw-input-wrap">
                <label for="guardianPassword" data-en="Password" data-ar="كلمة المرور">Password</label>
                <div class="position-relative">
                  <i class="bi bi-lock-fill input-icon"></i>
                  <input type="password" id="guardianPassword" name="password" class="lux-input" autocomplete="current-password" required>
                </div>
              </div>

              <button type="submit" class="btn btn-luxury w-100 py-3" data-en="Sign In" data-ar="تسجيل الدخول">Sign In</button>

              <div class="signup-link">
                <span data-en="No account yet?" data-ar="ليس لديك حساب؟">No account yet?</span>
                <a href="{{ route('guardian.register') }}" data-en="Register" data-ar="سجّل الآن">Register</a>
              </div>
            </div>
          </form>
@endsection
