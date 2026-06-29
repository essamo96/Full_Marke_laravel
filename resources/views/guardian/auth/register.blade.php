@extends('layouts.student-register')

@section('title', 'Guardian Registration | FULL MARK ACADEMY')

@section('content')
          <div class="form-section-head">
            <h1 class="form-title" data-en="Register Your Child" data-ar="تسجيل طفلك">Register Your Child</h1>
            <div class="gold-divider"></div>
            <p class="form-subtitle" data-en="Create a guardian account to manage your child's enrollment, payments and resources." data-ar="أنشئ حساب ولي أمر لإدارة تسجيل طفلك ومدفوعاته وموارده التعليمية.">
              Create a guardian account to manage your child's enrollment, payments and resources.
            </p>
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

          <div class="form-body">
            <form method="POST" action="{{ route('guardian.register.submit') }}">
              @csrf

              <p class="form-section-label" data-en="Child Information" data-ar="بيانات الطفل">Child Information</p>
              <div class="row g-4 mb-4">
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <input class="fi-input" type="text" id="childName" name="childName" value="{{ old('childName') }}" placeholder=" " required>
                    <label class="fi-label" for="childName" data-en="Child's Full Name" data-ar="اسم الطفل الكامل">Child's Full Name</label>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="floating-input-group">
                    <input class="fi-input" type="date" id="childDob" name="childDob" value="{{ old('childDob') }}" placeholder=" " required>
                    <label class="fi-label" for="childDob" data-en="Date of Birth" data-ar="تاريخ الميلاد">Date of Birth</label>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="floating-input-group">
                    <select class="fi-select" id="childGender" name="childGender" required>
                      <option value="" disabled {{ old('childGender') ? '' : 'selected' }}></option>
                      <option value="male" {{ old('childGender') === 'male' ? 'selected' : '' }} data-en="Male" data-ar="ذكر">Male</option>
                      <option value="female" {{ old('childGender') === 'female' ? 'selected' : '' }} data-en="Female" data-ar="أنثى">Female</option>
                    </select>
                    <label class="fi-label" for="childGender" data-en="Gender" data-ar="الجنس">Gender</label>
                  </div>
                </div>
              </div>

              <p class="form-section-label" data-en="Guardian (Account) Information" data-ar="بيانات ولي الأمر (الحساب)">Guardian (Account) Information</p>
              <div class="row g-4 mb-4">
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <input class="fi-input" type="text" id="fullName" name="fullName" value="{{ old('fullName') }}" placeholder=" " required>
                    <label class="fi-label" for="fullName" data-en="Full Name" data-ar="الاسم الكامل">Full Name</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <input class="fi-input" type="text" id="nationalId" name="nationalId" value="{{ old('nationalId') }}" placeholder=" ">
                    <label class="fi-label" for="nationalId" data-en="National ID" data-ar="رقم الهوية">National ID</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <select class="fi-select" id="relationship" name="relationship" required>
                      <option value="" disabled {{ old('relationship') ? '' : 'selected' }}></option>
                      @foreach (['father' => 'Father / الأب', 'mother' => 'Mother / الأم', 'brother' => 'Brother / الأخ', 'sister' => 'Sister / الأخت', 'other' => 'Other / أخرى'] as $key => $label)
                        <option value="{{ $key }}" {{ old('relationship') === $key ? 'selected' : '' }}>{{ $label }}</option>
                      @endforeach
                    </select>
                    <label class="fi-label" for="relationship" data-en="Relationship to Student" data-ar="العلاقة بالطالب">Relationship to Student</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <input class="fi-input" type="tel" id="phone" name="phone" value="{{ old('phone') }}" placeholder=" " required>
                    <label class="fi-label" for="phone" data-en="Phone Number" data-ar="رقم الهاتف">Phone Number</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <input class="fi-input" type="email" id="email" name="email" value="{{ old('email') }}" placeholder=" " required>
                    <label class="fi-label" for="email" data-en="Email Address" data-ar="البريد الإلكتروني">Email Address</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <input class="fi-input" type="text" id="address" name="address" value="{{ old('address') }}" placeholder=" ">
                    <label class="fi-label" for="address" data-en="Address" data-ar="العنوان">Address</label>
                  </div>
                </div>
              </div>

              <p class="form-section-label" data-en="Account Security" data-ar="أمان الحساب">Account Security</p>
              <div class="row g-4 mb-4">
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <input class="fi-input" type="password" id="password" name="password" placeholder=" " required>
                    <label class="fi-label" for="password" data-en="Password" data-ar="كلمة المرور">Password</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <input class="fi-input" type="password" id="password_confirmation" name="password_confirmation" placeholder=" " required>
                    <label class="fi-label" for="password_confirmation" data-en="Confirm Password" data-ar="تأكيد كلمة المرور">Confirm Password</label>
                  </div>
                </div>
              </div>

              <div class="terms-block mb-4">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">
                  <span data-en="I confirm that the information provided is accurate and agree to the " data-ar="أقر بصحة البيانات المدخلة وأوافق على ">I confirm that the information provided is accurate and agree to the </span>
                  <a href="#" data-en="Terms & Conditions" data-ar="شروط وأحكام الانتساب">Terms &amp; Conditions</a>.
                </label>
              </div>

              <button type="submit" class="btn btn-luxury w-100 py-3 rounded-lg" data-en="Create Guardian Account" data-ar="إنشاء حساب ولي الأمر">Create Guardian Account</button>

              <div class="login-link-block mt-4">
                <span data-en="Already have an account?" data-ar="هل لديك حساب مسبق؟">Already have an account?</span>
                <a href="{{ route('guardian.login') }}" data-en="Sign In" data-ar="تسجيل الدخول">Sign In</a>
              </div>
            </form>
          </div>
@endsection
