@extends('layouts.site')

@section('title', 'Apply Now | FULL MARKS ACADEMY')

@section('content')
  <section style="background: var(--bg-secondary); padding-top: 160px !important;">
    <div class="container px-4">
      <div class="text-center max-w-2xl mx-auto mb-12 reveal">
        <h5 class="section-subtitle" data-en="JOIN FULL MARK" data-ar="انضم إلى العلامة الكاملة">JOIN FULL MARK</h5>
        <h2 class="section-title" data-en="Apply Now" data-ar="تقديم طلب الانضمام">Apply Now</h2>
        <div class="section-divider mx-auto"></div>
      </div>

      <div class="row justify-content-center">
        <div class="col-lg-8">


          <div class="glass-panel p-6 md:p-12">
            <form id="registrationForm" method="POST" action="{{ route('apply.store') }}" enctype="multipart/form-data">
              @csrf
              @if(isset($selectedProgram))
                <input type="hidden" name="program_id" value="{{ $selectedProgram->id }}">
              @endif
              @if(isset($selectedSubject))
                <input type="hidden" name="subject_id" value="{{ $selectedSubject->id }}">
              @endif

              <h5 class="text-gold font-bold tracking-widest text-xs uppercase mb-4" data-en="Personal Information" data-ar="البيانات الشخصية">Personal Information</h5>

              <div class="row">
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <input type="text" name="full_name_en" id="applyNameEn" placeholder=" " value="{{ old('full_name_en') }}" required>
                    <label for="applyNameEn" data-en="Full Name (English)" data-ar="الاسم الكامل (إنجليزي)">Full Name (English)</label>
                  </div>
                  @error('full_name_en') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <input type="text" name="full_name_ar" id="applyNameAr" placeholder=" " value="{{ old('full_name_ar') }}" required>
                    <label for="applyNameAr" data-en="Full Name (Arabic)" data-ar="الاسم الكامل (عربي)">Full Name (Arabic)</label>
                  </div>
                  @error('full_name_ar') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <input type="email" name="email" id="applyEmail" placeholder=" " value="{{ old('email') }}" required>
                    <label for="applyEmail" data-en="Email Address" data-ar="البريد الإلكتروني">Email Address</label>
                  </div>
                  @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <input type="text" name="phone" id="applyPhone" placeholder=" " value="{{ old('phone') }}" required>
                    <label for="applyPhone" data-en="Phone Number" data-ar="رقم الهاتف">Phone Number</label>
                  </div>
                  @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <input type="date" name="date_of_birth" id="applyDob" placeholder=" " value="{{ old('date_of_birth') }}" onclick="this.showPicker()">
                    <label for="applyDob" data-en="Date of Birth" data-ar="تاريخ الميلاد">Date of Birth</label>
                  </div>
                  @error('date_of_birth') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <select name="gender" id="applyGender">
                      <option value="" disabled {{ old('gender') ? '' : 'selected' }} hidden></option>
                      <option value="male" data-en="Male" data-ar="ذكر" @selected(old('gender') === 'male')>Male</option>
                      <option value="female" data-en="Female" data-ar="أنثى" @selected(old('gender') === 'female')>Female</option>
                    </select>
                    <label for="applyGender" data-en="Gender" data-ar="الجنس">Gender</label>
                  </div>
                  @error('gender') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
              </div>

              <div class="floating-input-group">
                <textarea name="address" id="applyAddress" rows="2" placeholder=" ">{{ old('address') }}</textarea>
                <label for="applyAddress" data-en="Address" data-ar="العنوان">Address</label>
              </div>

              <div class="row">
                <div class="col-md-4">
                  <div class="floating-input-group">
                    <select name="branch_id" id="applyBranch">
                      <option value="" selected data-en="No preference" data-ar="بدون تفضيل">No preference</option>
                      @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id) data-en="{{ $branch->name_en }}" data-ar="{{ $branch->name_ar }}">{{ $branch->name }}</option>
                      @endforeach
                    </select>
                    <label for="applyBranch" data-en="Study Branch" data-ar="الفرع الدراسي">Study Branch</label>
                  </div>
                  @error('branch_id') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-md-4">
                  <div class="floating-input-group">
                    <select name="region_id" id="applyRegion">
                      <option value="" selected data-en="No preference" data-ar="بدون تفضيل">No preference</option>
                      @foreach ($regions as $region)
                        <option value="{{ $region->id }}" @selected(old('region_id') == $region->id) data-en="{{ $region->name_en }}" data-ar="{{ $region->name_ar }}">{{ $region->name }}</option>
                      @endforeach
                    </select>
                    <label for="applyRegion" data-en="Select Region" data-ar="حدد المنطقة">Select Region</label>
                  </div>
                  @error('region_id') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-md-4">
                  <div class="floating-input-group">
                    <input type="text" name="major_profession" id="applyProfession" placeholder=" " value="{{ old('major_profession') }}">
                    <label for="applyProfession" data-en="Major / Profession" data-ar="التخصص / المهنة">Major / Profession</label>
                  </div>
                  @error('major_profession') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
              </div>

              <div class="mb-4">
                <label for="applyImage" class="d-block text-sm font-bold mb-2" style="color: var(--text-primary);" data-en="Personal Photo (optional)" data-ar="صورة شخصية (اختياري)">Personal Photo (optional)</label>
                <input type="file" name="image" id="applyImage" accept="image/*" class="form-control">
                @error('image') <small class="text-danger">{{ $message }}</small> @enderror
              </div>

              <div class="floating-input-group">
                <textarea name="message" id="applyMessage" rows="4" placeholder=" ">{{ old('message') }}</textarea>
                <label for="applyMessage" data-en="Additional Notes" data-ar="ملاحظات إضافية">Additional Notes</label>
              </div>

              <div class="floating-input-group">
                <textarea name="health_information" id="applyHealth" rows="2" placeholder=" ">{{ old('health_information') }}</textarea>
                <label for="applyHealth" data-en="Health Information (optional)" data-ar="معلومات صحية (اختياري)">Health Information (optional)</label>
              </div>

              <button type="submit" id="submitBtn" class="btn btn-luxury w-100 py-3 rounded-lg text-lg d-flex align-items-center justify-content-center">
                <span data-en="Submit Application" data-ar="إرسال الطلب">Submit Application</span>
                <i class="bi bi-arrow-right ms-2 rtl:rotate-180"></i>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- OTP Verification Modal -->
  <div class="modal fade" id="otpVerificationModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="otpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content glass-panel p-6 md:p-8" style="border: 1px solid var(--accent-color); background: var(--bg-secondary);">
        <div class="modal-header border-0 justify-content-between p-0 mb-4">
          <h5 class="modal-title font-bold text-2xl text-gold" id="otpModalLabel" data-en="Verify Your Email" data-ar="تحقق من بريدك الإلكتروني">Verify Your Email</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="display:none;"></button>
        </div>
        <div class="modal-body text-center pt-0 px-2 pb-2">
          <div class="mb-4">
            <i class="bi bi-envelope-check text-success" style="font-size: 3rem;"></i>
          </div>
          <p style="color: var(--text-secondary);" class="text-sm mb-4 leading-relaxed">
            <span data-en="A verification code has been sent to:" data-ar="تم إرسال رمز التحقق إلى:">A verification code has been sent to:</span><br>
            <strong id="sentEmailAddress" style="color: var(--text-primary);"></strong>
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

          <div class="mt-3 text-center">
            <span style="color: var(--text-secondary);" data-en="Didn't receive the code?" data-ar="لم تستلم الرمز؟">Didn't receive the code?</span>
            <button type="button" class="btn btn-link p-0 text-decoration-none fw-bold ms-1 text-gold" id="resendCodeBtn" disabled>
              <span data-en="Resend" data-ar="إعادة الإرسال">Resend</span> <span id="resendTimer">(00:59)</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const regForm = document.getElementById('registrationForm');
    const submitBtn = document.getElementById('submitBtn');
    const verifyForm = document.getElementById('verifyOtpForm');
    const otpInputs = document.querySelectorAll('.otp-input');
    let resendInterval;
    const csrfToken = document.querySelector('input[name="_token"]').value;

    // Building the Bootstrap modal must never be able to take down the rest
    // of this script — a slow/blocked CDN would otherwise throw here and
    // silently skip attaching the form's submit handler below, leaving the
    // browser to fall back to a plain (non-AJAX) POST that surfaces Laravel's
    // raw error page on any failure (e.g. a stale CSRF token) instead of the
    // friendly in-page message this form is built to show.
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
    if (regForm) {
        regForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const originalContent = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> <span data-en="Processing..." data-ar="جاري المعالجة...">Processing...</span>';
            
            const formData = new FormData(regForm);
            fetch('{{ route("apply.store") }}', {
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
                    // Session/CSRF token expired while filling the form —
                    // reload to get a fresh token instead of throwing a raw error.
                    window.location.reload();
                    return Promise.reject(new Error('CSRF token expired'));
                }
                if (!response.ok && response.status !== 422) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    // Show modal
                    document.getElementById('sentEmailAddress').textContent = data.email;
                    document.getElementById('verifyEmailInput').value = data.email;
                    if(otpModal) otpModal.show();
                    startResendTimer();
                } else if (data.errors) {
                    Swal.fire({
                        icon: 'error',
                        title: document.documentElement.lang === 'ar' ? 'خطأ' : 'Error',
                        text: Object.values(data.errors).flat().join('\n'),
                    });
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalContent;
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: document.documentElement.lang === 'ar' ? 'خطأ' : 'Error',
                        text: data.message || 'An error occurred.',
                    });
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalContent;
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
                submitBtn.innerHTML = originalContent;
            });
        });
    }

    // Handle Verify Form via AJAX
    if (verifyForm) {
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
    }

    // Handle Resend Request
    const resendBtn = document.getElementById('resendCodeBtn');
    if (resendBtn) {
        resendBtn.addEventListener('click', function(e) {
            e.preventDefault();
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

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
                } else if (data) {
                    showOtpError(data.message || 'فشل في إعادة الإرسال.');
                }
            });
        });
    }

    function showOtpError(msg, type = 'danger') {
        const errDiv = document.getElementById('otpErrorMsg');
        errDiv.className = `alert alert-${type} d-block`;
        errDiv.textContent = msg;
    }

    function startResendTimer(seconds = 59) {
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
    
    @if(session('show_otp_modal'))
        const sessionEmail = '{{ session("email") }}';
        if(sessionEmail) {
            document.getElementById('sentEmailAddress').textContent = sessionEmail;
            document.getElementById('verifyEmailInput').value = sessionEmail;
            if (otpModal) otpModal.show();
            startResendTimer();
        }
    @endif
});
</script>
@endpush
