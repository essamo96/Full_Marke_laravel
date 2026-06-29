@extends('layouts.student-register')

@section('title', 'Student Registration | FULL MARK ACADEMY')

@section('content')
          <!-- Header -->
          <div class="form-section-head">
            <h1 class="form-title" data-en="Join Our Academy" data-ar="انضم لأكاديميتنا">Join Our Academy</h1>
            <div class="gold-divider"></div>
            <p class="form-subtitle" data-en="Complete your details to begin your academic excellence journey with globally recognized standards." data-ar="أكمل بياناتك لبدء رحلة التميز الأكاديمي والحصول على تعليم بمعايير عالمية.">
              Complete your details to begin your academic excellence journey with globally recognized standards.
            </p>
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
                <!-- Academy Branch -->
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <select class="fi-select" id="branch" name="branch" required>
                      <option value="" disabled {{ old('branch') ? '' : 'selected' }}></option>
                      <option value="amman" {{ old('branch') === 'amman' ? 'selected' : '' }} data-en="Amman Branch" data-ar="فرع عمّان">Amman Branch</option>
                      <option value="zarqa" {{ old('branch') === 'zarqa' ? 'selected' : '' }} data-en="Zarqa Branch" data-ar="فرع الزرقاء">Zarqa Branch</option>
                      <option value="irbid" {{ old('branch') === 'irbid' ? 'selected' : '' }} data-en="Irbid Branch" data-ar="فرع إربد">Irbid Branch</option>
                      <option value="online" {{ old('branch') === 'online' ? 'selected' : '' }} data-en="Online Learning" data-ar="التعلم عن بُعد">Online Learning</option>
                    </select>
                    <label class="fi-label" for="branch" data-en="Academy Branch" data-ar="فرع الأكاديمية">Academy Branch</label>
                    <i class="bi bi-geo-alt fi-icon"></i>
                  </div>
                </div>
                <!-- Program -->
                <div class="col-12">
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
                <!-- Health Notes -->
                <div class="col-12">
                  <div class="floating-input-group">
                    <input class="fi-input" type="text" id="health" name="health" value="{{ old('health') }}" placeholder=" ">
                    <label class="fi-label" for="health" data-en="Health Status (any important notes)" data-ar="الحالة الصحية (أي ملاحظات هامة)">Health Status (any important notes)</label>
                    <i class="bi bi-heart-pulse fi-icon"></i>
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
