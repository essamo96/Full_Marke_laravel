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

          @if (session('applied'))
            <!-- OTP Verification Modal -->
            <div class="modal fade" id="otpModal" tabindex="-1" aria-labelledby="otpModalLabel" aria-hidden="true" data-bs-backdrop="static">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content glass-panel p-6 md:p-8" style="border: 1px solid var(--accent-color); background: var(--bg-secondary);">
                  <div class="modal-header border-0 justify-content-between p-0 mb-4">
                    <h5 class="modal-title font-bold text-2xl text-gold" id="otpModalLabel" data-en="Verify Your Email" data-ar="تحقق من بريدك الإلكتروني">Verify Your Email</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body border-0 p-0">
                    <p style="color: var(--text-secondary);" class="text-sm mb-6 leading-relaxed"
                       data-en="We have sent a 6-digit verification code to your email. Please enter it below to complete your registration."
                       data-ar="لقد أرسلنا رمز تحقق مكون من 6 أرقام إلى بريدك الإلكتروني. يرجى إدخاله أدناه لإكمال عملية التسجيل.">
                      We have sent a 6-digit verification code to your email. Please enter it below to complete your registration.
                    </p>
                    
                    <form method="POST" action="{{ route('apply.verify') }}">
                      @csrf
                      <div class="floating-input-group mb-4">
                        <input type="text" name="otp" id="otpCode" placeholder=" " required maxlength="6" class="text-center font-bold tracking-widest fs-4" style="letter-spacing: 0.5em !important;">
                        <label for="otpCode" data-en="Verification Code (Try 123456)" data-ar="رمز التحقق (جرب 123456)">Verification Code (Try 123456)</label>
                      </div>
                      @error('otp')
                        <div class="text-danger text-sm mb-4 font-medium">{{ $message }}</div>
                      @enderror
                      
                      <button type="submit" class="btn btn-luxury w-100 py-3 rounded-lg text-lg font-bold">
                        <span data-en="Confirm & Login" data-ar="تأكيد ودخول">Confirm & Login</span>
                      </button>
                    </form>
                  </div>
                </div>
              </div>
            </div>

            <script>
              document.addEventListener('DOMContentLoaded', () => {
                const otpModalEl = document.getElementById('otpModal');
                if (otpModalEl) {
                  const otpModal = new bootstrap.Modal(otpModalEl);
                  otpModal.show();
                }
              });
            </script>
          @endif

          <div class="glass-panel p-6 md:p-12">
            <form method="POST" action="{{ route('apply.store') }}" enctype="multipart/form-data">
              @csrf

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
                    <input type="date" name="date_of_birth" id="applyDob" placeholder=" " value="{{ old('date_of_birth') }}">
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
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <select name="branch_id" id="applyBranch">
                      <option value="" selected data-en="No preference" data-ar="بدون تفضيل">No preference</option>
                      @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>{{ $branch->name }}</option>
                      @endforeach
                    </select>
                    <label for="applyBranch" data-en="Preferred Branch" data-ar="الفرع المفضل">Preferred Branch</label>
                  </div>
                  @error('branch_id') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <select name="study_branch_id" id="applyStudyBranch">
                      <option value="" selected data-en="No preference" data-ar="بدون تفضيل">No preference</option>
                      @foreach ($studyBranches as $studyBranch)
                        <option value="{{ $studyBranch->id }}" @selected(old('study_branch_id') == $studyBranch->id) data-en="{{ $studyBranch->name_en }}" data-ar="{{ $studyBranch->name_ar }}">{{ $studyBranch->name }}</option>
                      @endforeach
                    </select>
                    <label for="applyStudyBranch" data-en="Study Branch" data-ar="الفرع الدراسي">Study Branch</label>
                  </div>
                  @error('study_branch_id') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <input type="text" name="major_profession" id="applyProfession" placeholder=" " value="{{ old('major_profession') }}">
                    <label for="applyProfession" data-en="Major / Profession" data-ar="التخصص / المهنة">Major / Profession</label>
                  </div>
                  @error('major_profession') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
              </div>

              <div class="floating-input-group">
                <textarea name="health_information" id="applyHealth" rows="2" placeholder=" ">{{ old('health_information') }}</textarea>
                <label for="applyHealth" data-en="Health Information (optional)" data-ar="معلومات صحية (اختياري)">Health Information (optional)</label>
              </div>

              <div class="mb-4">
                <label for="applyImage" class="d-block text-sm font-bold mb-2" style="color: var(--text-primary);" data-en="Personal Photo (optional)" data-ar="صورة شخصية (اختياري)">Personal Photo (optional)</label>
                <input type="file" name="image" id="applyImage" accept="image/*" class="form-control">
                @error('image') <small class="text-danger">{{ $message }}</small> @enderror
              </div>

              <h5 class="text-gold font-bold tracking-widest text-xs uppercase mb-4 mt-6" data-en="Program Selection" data-ar="اختيار البرنامج">Program Selection</h5>

              <div class="row">
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <select name="program_id" id="applyProgram">
                      <option value="" disabled {{ old('program_id') ? '' : 'selected' }} hidden></option>
                      @foreach ($programs as $p)
                        <option value="{{ $p->id }}"
                                data-en="{{ $p->title_en }}" data-ar="{{ $p->title_ar }}"
                                @selected(old('program_id', $selectedProgram) == $p->id || old('program_id', $selectedProgram) === $p->slug)>
                          {{ $p->title }}
                        </option>
                      @endforeach
                    </select>
                    <label for="applyProgram" data-en="Program" data-ar="البرنامج">Program</label>
                  </div>
                  @error('program_id') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <select name="subject_id" id="applySubject">
                      <option value="" selected data-en="No specific subject" data-ar="بدون مادة محددة">No specific subject</option>
                      @foreach ($programs as $p)
                        @foreach ($p->subjects as $s)
                          <option value="{{ $s->id }}" data-program="{{ $p->id }}"
                                  data-en="{{ $s->name_en }}" data-ar="{{ $s->name_ar }}"
                                  @selected(request('subject') == $s->id)>
                            {{ $p->title }} — {{ $s->name }}
                          </option>
                        @endforeach
                      @endforeach
                    </select>
                    <label for="applySubject" data-en="Subject (optional)" data-ar="المادة (اختياري)">Subject (optional)</label>
                  </div>
                  @error('subject_id') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
              </div>

              <div class="floating-input-group">
                <textarea name="message" id="applyMessage" rows="4" placeholder=" ">{{ old('message') }}</textarea>
                <label for="applyMessage" data-en="Additional Notes" data-ar="ملاحظات إضافية">Additional Notes</label>
              </div>

              <button type="submit" class="btn btn-luxury w-100 py-3 rounded-lg text-lg d-flex align-items-center justify-content-center">
                <span data-en="Submit Application" data-ar="إرسال الطلب">Submit Application</span>
                <i class="bi bi-arrow-right ms-2 rtl:rotate-180"></i>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
