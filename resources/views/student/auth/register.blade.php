@extends('layouts.student-register')

@section('title', 'Student Registration | FULL MARK ACADEMY')

@section('content')
          <!-- Header -->
          <div class="form-section-head">
            <h1 class="form-title" data-en="Join Our Academy" data-ar="انضم لأكاديميتنا">Join Our Academy</h1>
            <div class="gold-divider"></div>
            <div class="motivation-rotator" id="motivationRotator" role="status" aria-live="polite">
              <span class="motivation-line is-active"
                    data-en="Your first step toward the full mark starts right here."
                    data-ar="خطوتك الأولى نحو العلامة الكاملة تبدأ من هنا.">Your first step toward the full mark starts right here.</span>
              <span class="motivation-line"
                    data-en="An elite team of top educators is ready to guide you to excellence."
                    data-ar="نخبة من أفضل المعلمين بانتظارك لتحقيق التفوق.">An elite team of top educators is ready to guide you to excellence.</span>
              <span class="motivation-line"
                    data-en="Excellence isn't an accident — it's a decision you make today."
                    data-ar="التميز ليس صدفة... إنه قرار تتخذه اليوم.">Excellence isn't an accident — it's a decision you make today.</span>
            </div>
          </div>

          <!-- Step dots -->
          <div class="step-indicator" aria-hidden="true">
            <div class="step-dot active"></div>
            <div class="step-dot"></div>
            <div class="step-dot"></div>
          </div>

          @if ($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <!-- Form -->
          <div class="form-body">
            <form id="registrationForm" method="POST" action="{{ route('student.register.submit') }}">
              @csrf

              <!-- Personal Info -->
              <p class="form-section-label" data-en="Personal Information" data-ar="المعلومات الشخصية">Personal Information</p>
              <div class="row g-4 mb-4">
                <!-- Full Name Arabic -->
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <input class="fi-input" type="text" id="fullnameAr" name="fullnameAr" value="{{ old('fullnameAr') }}" placeholder=" " required autocomplete="name">
                    <label class="fi-label" for="fullnameAr" data-en="Full Name (Arabic)" data-ar="الاسم الرباعي بالعربية">Full Name (Arabic)</label>
                    <i class="bi bi-person fi-icon"></i>
                  </div>
                </div>
                <!-- Full Name English -->
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <input class="fi-input" type="text" id="fullnameEn" name="fullnameEn" value="{{ old('fullnameEn') }}" placeholder=" " required autocomplete="name">
                    <label class="fi-label" for="fullnameEn" data-en="Full Name (English)" data-ar="الاسم الكامل بالإنجليزية">Full Name (English)</label>
                    <i class="bi bi-person fi-icon"></i>
                  </div>
                </div>
                <!-- National ID -->
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <input class="fi-input" type="text" id="nationalId" name="nationalId" value="{{ old('nationalId') }}" placeholder=" ">
                    <label class="fi-label" for="nationalId" data-en="National ID" data-ar="رقم الهوية">National ID</label>
                    <i class="bi bi-credit-card-2-front fi-icon"></i>
                  </div>
                </div>
                <!-- Email -->
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <input class="fi-input" type="email" id="email" name="email" value="{{ old('email') }}" placeholder=" " required autocomplete="email">
                    <label class="fi-label" for="email" data-en="Email Address" data-ar="البريد الإلكتروني">Email Address</label>
                    <i class="bi bi-envelope fi-icon"></i>
                  </div>
                </div>
                <!-- Phone -->
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <input class="fi-input" type="tel" id="phone" name="phone" value="{{ old('phone') }}" placeholder=" " required autocomplete="tel">
                    <label class="fi-label" for="phone" data-en="Mobile Number" data-ar="رقم الجوال">Mobile Number</label>
                    <i class="bi bi-telephone fi-icon"></i>
                  </div>
                </div>
                <!-- Date of Birth -->
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <input class="fi-input" type="date" id="dob" name="dob" value="{{ old('dob') }}" required>
                    <label class="fi-label" for="dob" data-en="Date of Birth" data-ar="تاريخ الميلاد">Date of Birth</label>
                  </div>
                </div>
                <!-- Gender -->
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <select class="fi-select" id="gender" name="gender" required>
                      <option value="" disabled {{ old('gender') ? '' : 'selected' }}></option>
                      <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }} data-en="Male" data-ar="ذكر">Male</option>
                      <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }} data-en="Female" data-ar="أنثى">Female</option>
                    </select>
                    <label class="fi-label" for="gender" data-en="Gender" data-ar="الجنس">Gender</label>
                    <i class="bi bi-chevron-down fi-icon"></i>
                  </div>
                </div>
                <!-- Health Notes -->
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <input class="fi-input" type="text" id="health" name="health" value="{{ old('health') }}" placeholder=" ">
                    <label class="fi-label" for="health" data-en="Health Status (any important notes)" data-ar="الحالة الصحية (أي ملاحظات هامة)">Health Status (any important notes)</label>
                    <i class="bi bi-heart-pulse fi-icon"></i>
                  </div>
                </div>
              </div>

              <!-- Academic Info -->
              <p class="form-section-label" data-en="Academic Details" data-ar="التفاصيل الأكاديمية">Academic Details</p>
              <div class="row g-4 mb-4">
                <!-- Profession -->
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <input class="fi-input" type="text" id="profession" name="profession" value="{{ old('profession') }}" placeholder=" ">
                    <label class="fi-label" for="profession" data-en="Profession / Specialization" data-ar="المهنة / التخصص">Profession / Specialization</label>
                    <i class="bi bi-briefcase fi-icon"></i>
                  </div>
                </div>
                <!-- Region -->
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <select class="fi-select" id="region_id" name="region_id" required>
                      <option value="" disabled {{ old('region_id') ? '' : 'selected' }}></option>
                      @foreach($regions as $region)
                      <option value="{{ $region->id }}" {{ old('region_id') == $region->id ? 'selected' : '' }} data-en="{{ $region->name_en }}" data-ar="{{ $region->name_ar }}">{{ $region->name }}</option>
                      @endforeach
                    </select>
                    <label class="fi-label" for="region_id" data-en="Region" data-ar="المنطقة">Region</label>
                    <i class="bi bi-geo-alt fi-icon"></i>
                  </div>
                </div>
                <!-- Study Branch -->
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <select class="fi-select" id="branch_id" name="branch_id" required>
                      <option value="" disabled {{ old('branch_id') ? '' : 'selected' }}></option>
                      @foreach($branches as $branch)
                      <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }} data-en="{{ $branch->name_en }}" data-ar="{{ $branch->name_ar }}">{{ $branch->name }}</option>
                      @endforeach
                    </select>
                    <label class="fi-label" for="branch_id" data-en="Study Branch" data-ar="الفرع الدراسي">Study Branch</label>
                    <i class="bi bi-diagram-3 fi-icon"></i>
                  </div>
                </div>
                <!-- Program -->
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <select class="fi-select" id="program" name="program" required>
                      <option value="" disabled {{ old('program') ? '' : 'selected' }}></option>
                      <option value="tawjihi" {{ old('program') === 'tawjihi' ? 'selected' : '' }} data-en="Tawjihi Program" data-ar="برنامج التوجيهي">Tawjihi Program</option>
                      <option value="children" {{ old('program') === 'children' ? 'selected' : '' }} data-en="Children Program" data-ar="برنامج الأطفال">Children Program</option>
                      <option value="speech" {{ old('program') === 'speech' ? 'selected' : '' }} data-en="Speech Therapy Program" data-ar="برنامج النطق">Speech Therapy Program</option>
                      <option value="rehab" {{ old('program') === 'rehab' ? 'selected' : '' }} data-en="Rehabilitation Program" data-ar="البرنامج التأهيلي">Rehabilitation Program</option>
                    </select>
                    <label class="fi-label" for="program" data-en="Program of Interest" data-ar="البرنامج المطلوب">Program of Interest</label>
                    <i class="bi bi-book fi-icon"></i>
                  </div>
                </div>
              </div>

              <!-- Security -->
              <p class="form-section-label" data-en="Account Security" data-ar="أمان الحساب">Account Security</p>
              <div class="row g-4 mb-4">
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <input class="fi-input" type="password" id="password" name="password" placeholder=" " required autocomplete="new-password">
                    <label class="fi-label" for="password" data-en="Password" data-ar="كلمة المرور">Password</label>
                    <i class="bi bi-lock fi-icon"></i>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <input class="fi-input" type="password" id="password_confirmation" name="password_confirmation" placeholder=" " required autocomplete="new-password">
                    <label class="fi-label" for="password_confirmation" data-en="Confirm Password" data-ar="تأكيد كلمة المرور">Confirm Password</label>
                    <i class="bi bi-lock fi-icon"></i>
                  </div>
                </div>
              </div>

              <!-- Additional Info -->
              <p class="form-section-label" data-en="Additional Information" data-ar="معلومات إضافية">Additional Information</p>
              <div class="row g-4 mb-4">
                <!-- Address -->
                <div class="col-12">
                  <div class="floating-input-group">
                    <textarea class="fi-input fi-textarea" id="address" name="address" placeholder=" " rows="3" style="min-height:90px; resize:none; padding-top:1.25rem;">{{ old('address') }}</textarea>
                    <label class="fi-label" for="address" style="top:1.25rem;" data-en="Address Details" data-ar="تفاصيل العنوان">Address Details</label>
                  </div>
                </div>
              </div>

              <!-- Terms -->
              <div class="terms-block mb-4">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">
                  <span data-en="I confirm that the information provided is accurate and agree to the " data-ar="أقر بصحة البيانات المدخلة وأوافق على ">I confirm that the information provided is accurate and agree to the </span>
                  <a href="#" data-en="Terms & Conditions" data-ar="شروط وأحكام الانتساب">Terms &amp; Conditions</a>
                  <span data-en=" and privacy policy of the Academy." data-ar=" وسياسة الخصوصية الخاصة بالأكاديمية."> and privacy policy of the Academy.</span>
                </label>
              </div>

              <!-- Submit Button -->
              <button type="submit" class="btn btn-luxury w-100 py-3 rounded-lg d-flex align-items-center justify-content-center gap-2" id="submitBtn" style="font-size: 1.1rem; font-weight: 700;">
                <i class="bi bi-person-plus-fill"></i>
                <span data-en="Create My Account" data-ar="إنشاء حساب جديد">Create My Account</span>
              </button>

              <!-- Login link -->
              <div class="login-link-block mt-4">
                <span data-en="Already have an account?" data-ar="هل لديك حساب مسبق؟">Already have an account?</span>
                <a href="{{ route('student.login') }}" data-en="Sign In" data-ar="تسجيل الدخول">Sign In</a>
              </div>

            </form>
          </div><!-- /form-body -->

@endsection

@push('modals')
          <!-- OTP Verification Modal -->
          <div class="modal fade" id="otpVerificationModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="otpModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content glass-panel">
                <div class="modal-header border-0 pb-0">
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="display:none;"></button>
                </div>
                <div class="modal-body text-center pt-0 px-4 pb-5">
                  <div class="mb-4">
                    <i class="bi bi-envelope-check text-success" style="font-size: 3rem;"></i>
                  </div>
                  <h4 class="mb-3" id="otpModalLabel" data-en="Verify Your Email" data-ar="تحقق من بريدك الإلكتروني">Verify Your Email</h4>
                  <p class="mb-4" style="color: var(--text-secondary);">
                    <span data-en="A verification code has been sent to:" data-ar="تم إرسال رمز التحقق إلى:">A verification code has been sent to:</span><br>
                    <strong id="sentEmailAddress" style="color: var(--text-primary);"></strong>
                  </p>

                  <div class="alert alert-warning d-flex align-items-start gap-2 text-start mb-3" style="font-size: 0.85rem;">
                    <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                    <span data-en="Important: Do not close this page until your email has been verified. If you leave, just return and enter the same email to continue." data-ar="هام: لا تغلق هذه الصفحة حتى يتم التحقق من بريدك الإلكتروني. إذا غادرت، عد وأدخل نفس البريد الإلكتروني للمتابعة.">Important: Do not close this page until your email has been verified. If you leave, just return and enter the same email to continue.</span>
                  </div>

                  <p class="mb-3" style="font-size: 0.9rem; color: var(--text-secondary);">
                    <span data-en="Code expires in" data-ar="ينتهي الكود خلال">Code expires in</span>
                    <strong id="expiryTimer" style="color: var(--text-primary);">5:00</strong>
                  </p>

                  <div id="otpErrorMsg" class="alert alert-danger d-none" style="font-size: 0.9rem;"></div>

                  <form id="verifyOtpForm">
                    @csrf
                    <input type="hidden" id="verifyEmailInput" name="email">

                    <div class="d-flex justify-content-center gap-2 mb-4" dir="ltr">
                      <input type="text" class="form-control otp-input text-center fs-4 fw-bold p-0 rounded" maxlength="1" style="width: 45px; height: 50px;" required>
                      <input type="text" class="form-control otp-input text-center fs-4 fw-bold p-0 rounded" maxlength="1" style="width: 45px; height: 50px;" required>
                      <input type="text" class="form-control otp-input text-center fs-4 fw-bold p-0 rounded" maxlength="1" style="width: 45px; height: 50px;" required>
                      <input type="text" class="form-control otp-input text-center fs-4 fw-bold p-0 rounded" maxlength="1" style="width: 45px; height: 50px;" required>
                      <input type="text" class="form-control otp-input text-center fs-4 fw-bold p-0 rounded" maxlength="1" style="width: 45px; height: 50px;" required>
                      <input type="text" class="form-control otp-input text-center fs-4 fw-bold p-0 rounded" maxlength="1" style="width: 45px; height: 50px;" required>
                    </div>

                    <button type="submit" class="btn btn-luxury w-100 py-3 rounded-lg fw-bold mb-3" id="verifyBtn">
                      <span data-en="Verify My Account" data-ar="تحقق من حسابي">Verify My Account</span>
                    </button>
                  </form>

                  <div class="mt-3">
                    <span class="text-muted" data-en="Didn't receive the code?" data-ar="لم تستلم الرمز؟">Didn't receive the code?</span>
                    <button type="button" class="btn btn-link p-0 text-decoration-none fw-bold ms-1" id="resendCodeBtn" disabled>
                      <span data-en="Resend" data-ar="إعادة الإرسال">Resend</span> <span id="resendTimer">(00:59)</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cycle the motivational sentences under the page title
    const motivationLines = document.querySelectorAll('#motivationRotator .motivation-line');
    if (motivationLines.length > 1 && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        let activeIndex = 0;
        setInterval(function() {
            motivationLines[activeIndex].classList.remove('is-active');
            activeIndex = (activeIndex + 1) % motivationLines.length;
            motivationLines[activeIndex].classList.add('is-active');
        }, 4200);
    }

    const regForm = document.getElementById('registrationForm');
    const submitBtn = document.getElementById('submitBtn');
    const verifyForm = document.getElementById('verifyOtpForm');
    const otpInputs = document.querySelectorAll('.otp-input');
    let resendInterval;
    let expiryInterval;

    function startExpiryTimer(seconds = 300) {
        const expiryEl = document.getElementById('expiryTimer');
        clearInterval(expiryInterval);

        const render = (s) => {
            const m = Math.floor(s / 60);
            const sec = s % 60;
            expiryEl.textContent = `${m}:${sec < 10 ? '0' + sec : sec}`;
        };
        render(seconds);

        expiryInterval = setInterval(() => {
            seconds--;
            if (seconds <= 0) {
                clearInterval(expiryInterval);
                render(0);
                showOtpError(document.documentElement.lang === 'ar'
                    ? 'انتهت صلاحية الكود. يرجى طلب كود جديد.'
                    : 'This code has expired. Please request a new one.');
                // Let the student request a fresh code immediately, regardless
                // of the 60s anti-abuse throttle, since the code is now dead.
                const resendBtn = document.getElementById('resendCodeBtn');
                resendBtn.disabled = false;
                resendBtn.innerHTML = '<span data-en="Resend" data-ar="إعادة الإرسال">Resend</span>';
                clearInterval(resendInterval);
            } else {
                render(seconds);
            }
        }, 1000);
    }
    const csrfToken = document.querySelector('input[name="_token"]').value;

    let otpModal = null;
    try {
        const otpModalEl = document.getElementById('otpVerificationModal');
        if (otpModalEl && typeof bootstrap !== 'undefined') {
            otpModal = new bootstrap.Modal(otpModalEl);
        }
    } catch (err) {
        console.error('Could not initialize the OTP modal:', err);
    }

    // Auto-focus OTP inputs logic
    otpInputs.forEach((input, index) => {
        input.addEventListener('keyup', function(e) {
            if (e.key >= 0 && e.key <= 9) {
                this.value = e.key;
                if (index < otpInputs.length - 1) otpInputs[index + 1].focus();
            } else if (e.key === 'Backspace') {
                this.value = '';
                if (index > 0) otpInputs[index - 1].focus();
            } else {
                this.value = ''; // ignore non-numeric
            }
        });

        // Handle paste event
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedData = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
            if(pastedData.length > 0) {
                pastedData.split('').forEach((char, i) => {
                    if (otpInputs[i]) {
                        otpInputs[i].value = char;
                        if (i < 5) otpInputs[i+1].focus();
                    }
                });
            }
        });
    });

    // Handle Registration Form via AJAX
    regForm.addEventListener('submit', function(e) {
        e.preventDefault();
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> <span data-en="Processing..." data-ar="جاري المعالجة...">Processing...</span>';

        const formData = new FormData(regForm);
        fetch('{{ route("student.register.submit") }}', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => {
            if (response.status === 419) {
                // Session/CSRF token expired while filling the form — reload
                // to get a fresh token rather than surfacing a raw error.
                window.location.reload();
                return Promise.reject(new Error('CSRF token expired'));
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                // Show modal
                document.getElementById('sentEmailAddress').textContent = data.email;
                document.getElementById('verifyEmailInput').value = data.email;
                if (otpModal) otpModal.show();
                startResendTimer();
                startExpiryTimer();
            } else if (data.errors) {
                Swal.fire({
                    icon: 'error',
                    title: document.documentElement.lang === 'ar' ? 'خطأ' : 'Error',
                    text: Object.values(data.errors).flat().join('\n'),
                });
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-person-plus-fill"></i> <span data-en="Create My Account" data-ar="إنشاء حساب جديد">Create My Account</span>';
            } else {
                Swal.fire({
                    icon: 'error',
                    title: document.documentElement.lang === 'ar' ? 'خطأ' : 'Error',
                    text: data.message || 'An error occurred.',
                });
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-person-plus-fill"></i> <span data-en="Create My Account" data-ar="إنشاء حساب جديد">Create My Account</span>';
            }
        })
        .catch(err => {
            if (err && err.message === 'CSRF token expired') return; // already reloading
            console.error(err);
            Swal.fire({
                icon: 'error',
                title: document.documentElement.lang === 'ar' ? 'خطأ' : 'Error',
                text: document.documentElement.lang === 'ar' ? 'حدث خطأ ما. يرجى التحقق من المدخلات والمحاولة مرة أخرى.' : 'Something went wrong. Please check your inputs and try again.',
            });
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-person-plus-fill"></i> <span data-en="Create My Account" data-ar="إنشاء حساب جديد">Create My Account</span>';
        });
    });

    // Handle Verify Form via AJAX
    verifyForm.addEventListener('submit', function(e) {
        e.preventDefault();

        let code = Array.from(otpInputs).map(i => i.value).join('');
        if (code.length !== 6) return;

        const verifyBtn = document.getElementById('verifyBtn');
        const originalText = verifyBtn.innerHTML;
        verifyBtn.disabled = true;
        verifyBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        const formData = new FormData(verifyForm);
        formData.append('code', code);

        fetch('{{ route("student.verify.submit") }}', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => {
            if (response.status === 419) {
                window.location.reload();
                return Promise.reject(new Error('CSRF token expired'));
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                window.location.href = data.redirect;
            } else {
                showOtpError(data.message || 'Invalid code.');
                verifyBtn.disabled = false;
                verifyBtn.innerHTML = originalText;
                otpInputs.forEach(i => i.value = '');
                otpInputs[0].focus();
            }
        })
        .catch(err => {
            if (err && err.message === 'CSRF token expired') return; // already reloading
            showOtpError('An error occurred during verification.');
            verifyBtn.disabled = false;
            verifyBtn.innerHTML = originalText;
        });
    });

    // Handle Resend Request
    function requestResendCode() {
        const btn = document.getElementById('resendCodeBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        const email = document.getElementById('verifyEmailInput').value;
        const formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('email', email);

        fetch('{{ route("student.verify.resend") }}', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => {
            if(response.status === 429) {
                showOtpError('يرجى الانتظار دقيقة قبل طلب كود جديد.');
                startResendTimer(60);
                return;
            }
            if (response.status === 419) {
                window.location.reload();
                return;
            }
            return response.json();
        })
        .then(data => {
            if(data && data.status === 'success') {
                showOtpError('تم إرسال الكود بنجاح!', 'success');
                startResendTimer();
                startExpiryTimer();
            } else if (data) {
                showOtpError(data.message || 'فشل في إعادة الإرسال.');
            }
        });
    }

    document.getElementById('resendCodeBtn').addEventListener('click', function(e) {
        e.preventDefault();
        requestResendCode();
    });

    function showOtpError(msg, type = 'danger') {
        const errDiv = document.getElementById('otpErrorMsg');
        errDiv.className = `alert alert-${type} d-block`;
        errDiv.textContent = msg;
    }

    function startResendTimer(seconds = 59) {
        const resendBtn = document.getElementById('resendCodeBtn');
        const timerSpan = document.getElementById('resendTimer');
        resendBtn.disabled = true;

        clearInterval(resendInterval);

        resendInterval = setInterval(() => {
            if (seconds <= 0) {
                clearInterval(resendInterval);
                resendBtn.disabled = false;
                resendBtn.innerHTML = '<span data-en="Resend" data-ar="إعادة الإرسال">Resend</span>';
            } else {
                let secStr = seconds < 10 ? '0' + seconds : seconds;
                resendBtn.innerHTML = `<span data-en="Resend" data-ar="إعادة الإرسال">Resend</span> <span class="text-muted">(00:${secStr})</span>`;
                seconds--;
            }
        }, 1000);
    }

    // Reopen the OTP modal if this session left an unverified registration
    // behind (e.g. the page was refreshed before the code was entered).
    @if($pendingEmail ?? false)
        document.getElementById('sentEmailAddress').textContent = '{{ $pendingEmail }}';
        document.getElementById('verifyEmailInput').value = '{{ $pendingEmail }}';
        if (otpModal) otpModal.show();

        @if($hasActiveCode ?? false)
            startResendTimer();
            startExpiryTimer({{ (int) ($codeExpirySeconds ?? 300) }});
        @else
            // The previous code already expired — send a fresh one automatically
            // so the user isn't stuck staring at a modal with a dead code.
            requestResendCode();
        @endif
    @endif
});
</script>
@endpush
