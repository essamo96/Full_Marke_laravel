@extends('layouts.teacher-auth')

@section('title', 'Teacher Login | FULL MARK ACADEMY')

@section('content')
          <!-- Security badge -->
          <div class="security-badge">
            <i class="bi bi-shield-check-fill"></i>
            <span data-en="Secure Staff Access" data-ar="وصول آمن للكادر">Secure Staff Access</span>
          </div>

          <h1 class="teacher-form-title" data-en="Teaching Staff Portal" data-ar="بوابة أعضاء هيئة التدريس">Teaching Staff Portal</h1>
          <p class="teacher-form-subtitle" data-en="Welcome back — please sign in with your staff credentials to access the management dashboard." data-ar="مرحباً بك — يرجى تسجيل الدخول ببيانات الكادر للوصول إلى لوحة التحكم.">
            Welcome back — please sign in with your staff credentials to access the management dashboard.
          </p>

          @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
          @endif

          <!-- Teacher Login Form -->
          <form id="teacherLoginForm" method="POST" action="{{ route('teacher.login.submit') }}">
            @csrf
            <div class="input-row mb-4">

              <!-- Staff ID / Email -->
              <div class="lux-field">
                <label for="teacherId" data-en="Staff ID or Professional Email" data-ar="معرّف المعلم أو البريد المهني">Staff ID or Professional Email</label>
                <div class="lux-input-wrap">
                  <i class="bi bi-badge-fill field-icon"></i>
                  <input type="email" id="teacherId" name="email" value="{{ old('email') }}" class="lux-input" placeholder="teacher@fullmark.jo" autocomplete="username" autofocus required>
                </div>
              </div>

              <!-- Password -->
              <div class="lux-field">
                <label for="teacherPassword" data-en="Password" data-ar="كلمة المرور">Password</label>
                <div class="lux-input-wrap pw-input">
                  <i class="bi bi-lock-fill field-icon"></i>
                  <input type="password" id="teacherPassword" name="password" class="lux-input" placeholder="••••••••" autocomplete="current-password" required>
                  <button type="button" class="pw-reveal-btn" id="teacherPwToggle" aria-label="Toggle password">
                    <i class="bi bi-eye" id="teacherPwIcon"></i>
                  </button>
                </div>
              </div>

            </div>

            <!-- Remember -->
            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" id="rememberMe" name="remember">
              <label class="form-check-label" for="rememberMe" data-en="Remember me" data-ar="تذكرني">Remember me</label>
            </div>

            <!-- Submit -->
            <button type="submit" id="teacherLoginBtn" class="btn btn-luxury w-100 py-3 rounded-lg d-flex align-items-center justify-content-center gap-2 mb-3" style="font-size:1rem; font-weight:700;">
              <span data-en="Access Dashboard" data-ar="دخول لوحة التحكم">Access Dashboard</span>
              <i class="bi bi-arrow-right-circle-fill" id="teacherLoginIcon"></i>
            </button>

            <!-- Support link -->
            <a href="#" class="support-link">
              <i class="bi bi-headset"></i>
              <span data-en="Request credentials or contact technical support" data-ar="طلب بيانات الدخول أو التواصل مع الدعم الفني">Request credentials or contact technical support</span>
            </a>

            <!-- Student portal link -->
            <div class="student-portal-link">
              <span data-en="Looking for Student Portal?" data-ar="تبحث عن بوابة الطالب؟">Looking for Student Portal?</span>
              <a href="{{ route('student.login') }}" data-en="Login here" data-ar="ادخل من هنا">Login here</a>
            </div>

          </form>
@endsection

@push('scripts')
<script>
  document.getElementById('teacherPwToggle').addEventListener('click', function() {
    const inp = document.getElementById('teacherPassword');
    const icon = document.getElementById('teacherPwIcon');
    if (inp.type === 'password') { inp.type = 'text'; icon.className = 'bi bi-eye-slash'; }
    else { inp.type = 'password'; icon.className = 'bi bi-eye'; }
  });
</script>
@endpush
