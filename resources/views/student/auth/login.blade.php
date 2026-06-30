@extends('layouts.student-auth')

@section('title', 'Student Login | FULL MARK ACADEMY')

@section('content')
          @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
          @endif

          <!-- Login Form -->
          <form id="studentLoginForm" method="POST" action="{{ route('student.login.submit') }}">
            @csrf
            <div class="d-flex flex-column gap-4">

              <!-- Username / Student ID -->
              <div class="input-group-luxe">
                <label for="studentId" data-en="Email or Student ID" data-ar="البريد الإلكتروني أو رقم الطالب">Email or Student ID</label>
                <div class="position-relative">
                  <i class="bi bi-person-fill input-icon"></i>
                  <input type="email" id="studentId" name="email" value="{{ old('email') }}" class="lux-input" placeholder="student@fullmark.jo" autocomplete="username" autofocus required>
                </div>
              </div>

              <!-- Password -->
              <div class="input-group-luxe">
                <label for="studentPassword" data-en="Password" data-ar="كلمة المرور">Password</label>
                <div class="position-relative pw-input-wrap">
                  <i class="bi bi-lock-fill input-icon"></i>
                  <input type="password" id="studentPassword" name="password" class="lux-input" placeholder="••••••••" autocomplete="current-password" required>
                  <button type="button" class="pw-toggle-btn" id="pwToggleStudent" aria-label="Toggle password visibility">
                    <i class="bi bi-eye" id="pwToggleIconStudent"></i>
                  </button>
                </div>
              </div>

              <!-- Remember & Forgot -->
              <div class="form-meta">
                <label>
                  <input type="checkbox" id="rememberMe" name="remember">
                  <span data-en="Remember me" data-ar="تذكرني">Remember me</span>
                </label>
                <a href="#" data-en="Forgot password?" data-ar="نسيت كلمة المرور؟">Forgot password?</a>
              </div>

              <!-- Submit -->
              <button type="submit" id="studentLoginBtn" class="btn btn-luxury w-100 py-3 rounded-lg d-flex align-items-center justify-content-center gap-2" style="font-size:1rem; font-weight:700; margin-top:0.5rem;">
                <i class="bi bi-box-arrow-in-right"></i>
                <span data-en="Access My Platform" data-ar="دخول المنصة">Access My Platform</span>
              </button>

              <!-- Divider -->
              <div class="or-divider">
                <span data-en="or" data-ar="أو">or</span>
              </div>

              <!-- Quick access chips -->
              <div>
                <p class="text-center mb-2" style="font-size:0.8rem; color:var(--text-muted);" data-en="Quick Access" data-ar="دخول سريع">Quick Access</p>
                <div class="quick-access">
                  <span class="quick-chip"><i class="bi bi-mortarboard-fill"></i> <span data-en="Tawjihi" data-ar="التوجيهي">Tawjihi</span></span>
                  <span class="quick-chip"><i class="bi bi-person-hearts"></i> <span data-en="Children" data-ar="الأطفال">Children</span></span>
                  <span class="quick-chip"><i class="bi bi-chat-dots-fill"></i> <span data-en="Speech" data-ar="النطق">Speech</span></span>
                  <span class="quick-chip"><i class="bi bi-stars"></i> <span data-en="Rehab" data-ar="التأهيلي">Rehab</span></span>
                </div>
              </div>

              <!-- Teacher portal link -->
              <div class="signup-link" style="padding-top:0.5rem; border-top: 1px solid rgba(197,168,128,0.1); margin-top:0.25rem;">
                <i class="bi bi-shield-lock-fill" style="color:var(--text-muted); font-size:0.8rem;"></i>
                <a href="{{ route('teacher.login') }}" style="font-size:0.82rem; font-weight:500;" data-en="Teacher / Staff Portal →" data-ar="← بوابة المعلمين والموظفين">Teacher / Staff Portal →</a>
              </div>
            </div>
          </form>
@endsection

@push('scripts')
<script>
  document.getElementById('pwToggleStudent').addEventListener('click', function() {
    const inp = document.getElementById('studentPassword');
    const icon = document.getElementById('pwToggleIconStudent');
    if (inp.type === 'password') { inp.type = 'text'; icon.className = 'bi bi-eye-slash'; }
    else { inp.type = 'password'; icon.className = 'bi bi-eye'; }
  });
</script>
@endpush
