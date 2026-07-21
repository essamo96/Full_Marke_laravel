@extends('layouts.student')

@section('title', 'My Groups | FULL MARK ACADEMY')
@section('page_title_en', 'Study Groups & Sessions')
@section('page_title_ar', 'المجموعات والجلسات الدراسية')

@section('content')
  @php
    $dayLabels = [
      'sun' => ['ar' => 'الأحد', 'en' => 'Sun'],
      'mon' => ['ar' => 'الاثنين', 'en' => 'Mon'],
      'tue' => ['ar' => 'الثلاثاء', 'en' => 'Tue'],
      'wed' => ['ar' => 'الأربعاء', 'en' => 'Wed'],
      'thu' => ['ar' => 'الخميس', 'en' => 'Thu'],
      'fri' => ['ar' => 'الجمعة', 'en' => 'Fri'],
      'sat' => ['ar' => 'السبت', 'en' => 'Sat'],
    ];
    $lang = app()->getLocale();
  @endphp

  <h1 class="h3 fw-bold mb-4" style="color: var(--text-primary);" data-en="My Study Groups" data-ar="مجموعاتي الدراسية">My Study Groups</h1>

  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if ($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
  @endif

  <!-- Join Group By Code Section -->
  <div class="glass-panel bg-pattern-gold border border-white/10 rounded-4 p-4 mb-4">
    <div class="row align-items-center">
      <div class="col-md-6 mb-3 mb-md-0">
        <h4 class="fw-bold mb-1" style="color: var(--accent-color);" data-en="Join Group with Code" data-ar="الانضمام لمجموعة عبر الكود">الانضمام لمجموعة عبر الكود</h4>
        <p class="text-sm opacity-75 mb-0" style="color: var(--text-primary);" data-en="Enter the branching code provided by your teacher to join a study group directly." data-ar="أدخل كود التشعيب الخاص بالمجموعة للانضمام إليها مباشرة.">أدخل كود التشعيب الخاص بالمجموعة للانضمام إليها مباشرة.</p>
      </div>
      <div class="col-md-6">
        <form id="joinGroupForm" class="d-flex gap-2">
          <input type="text" id="groupJoinCodeInput" class="form-control form-control-solid bg-white/5 border-1 border-white/10 text-white" placeholder="أدخل الكود هنا (مثال: G-12345)" required>
          <button type="submit" class="btn btn-luxury px-4 flex-shrink-0" id="joinGroupBtn">
            <span class="indicator-label" data-en="Join" data-ar="انضمام">انضمام</span>
            <span class="indicator-progress d-none"><i class="fas fa-circle-notch fa-spin"></i></span>
          </button>
        </form>
        <div id="joinGroupFeedback" class="mt-2 text-sm d-none"></div>
        <a href="#" id="joinGroupRegisterBtn" class="btn btn-sm btn-outline-warning mt-2 d-none" data-en="Go to Registration" data-ar="الانتقال إلى حجز المقعد">الانتقال إلى حجز المقعد</a>
      </div>
    </div>
  </div>

  @if($withGroup->isEmpty() && $withoutGroup->isEmpty())
    <div class="glass-panel rounded-4 p-5 text-center">
      <i class="bi bi-people fs-1 mb-3 d-block" style="color: var(--accent-color);"></i>
      <p class="opacity-75 mb-3" data-en="You don't have any subject registrations yet." data-ar="لا يوجد لديك أي تسجيل في مواد بعد.">You don't have any subject registrations yet.</p>
      <a href="{{ route('student.programs') }}" class="btn btn-luxury px-4 py-2" data-en="Browse Programs" data-ar="تصفح البرامج">Browse Programs</a>
    </div>
  @else
    @if($withGroup->isNotEmpty())
      <div class="row g-4 mb-4">
        @foreach($withGroup as $registration)
          @php
            $group = $registration->group;
            $isSuspended = $registration->group_status === \App\Models\Registration::GROUP_STATUS_SUSPENDED;
          @endphp
          <div class="col-md-6 col-xl-4 group-card-animate" style="animation-delay: {{ $loop->index * 0.1 }}s">
            <a href="{{ $isSuspended ? '#' : route('student.groups.show', $group) }}"
               @if($isSuspended) onclick="event.preventDefault();" title="تم إيقاف حالتك في هذه المجموعة من قبل الإدارة" @endif
               class="text-decoration-none d-block h-100 group-card-link {{ $isSuspended ? 'opacity-50' : '' }}" style="{{ $isSuspended ? 'cursor: not-allowed;' : '' }}">
              <div class="glass-panel rounded-4 h-100 tilt-card overflow-hidden position-relative group-card d-flex flex-column" style="border: 1px solid var(--separator-color); box-shadow: 0 4px 20px rgba(0,0,0,0.15); background-color: var(--bg-primary);">
                
                <!-- Background Image & Gradient -->
                @if($registration->subject->image)
                  <div class="position-absolute w-100 top-0 start-0" style="height: 180px;">
                    <img src="{{ asset('storage/' . $registration->subject->image) }}" class="w-100 h-100" style="object-fit: cover;" alt="{{ $registration->subject->name }}">
                    <!-- Cloud / Fog Gradient -->
                    <div class="position-absolute bottom-0 start-0 w-100" style="height: 120px; background: linear-gradient(to top, var(--bg-primary) 10%, transparent 100%); backdrop-filter: blur(3px); -webkit-mask-image: linear-gradient(to top, black 30%, transparent 100%);"></div>
                    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(to bottom, rgba(14, 18, 22, 0.1) 0%, transparent 50%, var(--bg-primary) 100%); pointer-events: none;"></div>
                  </div>
                @else
                  <div class="position-absolute w-100 top-0 start-0 d-flex align-items-center justify-content-center" style="height: 180px; background: rgba(197, 168, 128, 0.05);">
                    <i class="bi bi-book fs-1" style="color: var(--accent-color); opacity: 0.3;"></i>
                    <div class="position-absolute bottom-0 start-0 w-100" style="height: 120px; background: linear-gradient(to top, var(--bg-primary) 10%, transparent 100%); backdrop-filter: blur(3px); -webkit-mask-image: linear-gradient(to top, black 30%, transparent 100%);"></div>
                    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(to bottom, transparent 0%, transparent 50%, var(--bg-primary) 100%); pointer-events: none;"></div>
                  </div>
                @endif
                
                <!-- Card Content -->
                <div class="position-relative p-4 pt-5 mt-5 d-flex flex-column h-100 z-1">
                  <div class="d-flex justify-content-between align-items-start mb-4">
                    <div class="p-2 rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 52px; height: 52px; background: var(--bg-secondary); color: var(--accent-color); border: 1px solid var(--separator-color);">
                      <i class="bi bi-people-fill fs-4"></i>
                    </div>
                    @if($isSuspended)
                      <span class="badge bg-danger bg-opacity-25 text-danger rounded-pill px-3 py-2 shadow-sm fs-6" style="backdrop-filter: blur(4px);" data-en="Suspended" data-ar="موقوف">موقوف</span>
                    @else
                      <span class="badge bg-success bg-opacity-25 text-success rounded-pill px-3 py-2 shadow-sm fs-6" style="backdrop-filter: blur(4px);" data-en="Joined" data-ar="منضم">منضم</span>
                    @endif
                  </div>
                  
                  <div class="mb-4">
                    <h5 class="fw-bold mb-2 fs-4" style="color: var(--text-primary); text-shadow: 0 1px 2px rgba(0,0,0,0.1); font-family: 'Tajawal', 'Almarai', sans-serif;">{{ $registration->subject->name }}</h5>
                    <p class="text-sm opacity-75 mb-0" style="color: var(--text-secondary);">{{ $group->name }}</p>
                  </div>

                  <div class="d-flex flex-column gap-3 text-sm mt-auto mb-4">
                    @if(!empty($group->days))
                      <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background: var(--bg-secondary); border: 1px solid var(--separator-color);">
                        <i class="bi bi-calendar-week fs-5" style="color: var(--accent-color);"></i>
                        <span class="fw-medium" style="color: var(--text-secondary);">{{ collect($group->days)->map(fn($d) => $dayLabels[$d][$lang] ?? $d)->implode(' · ') }}</span>
                      </div>
                    @endif
                    @if($group->start_time)
                      <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background: var(--bg-secondary); border: 1px solid var(--separator-color);">
                        <i class="bi bi-clock fs-5" style="color: var(--accent-color);"></i>
                        <span class="fw-medium" style="color: var(--text-secondary);">{{ \Carbon\Carbon::parse($group->start_time)->format('h:i A') }} — {{ \Carbon\Carbon::parse($group->end_time)->format('h:i A') }}</span>
                      </div>
                    @endif
                    @if($group->teacher)
                      <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background: var(--bg-secondary); border: 1px solid var(--separator-color);">
                        <i class="bi bi-person-badge fs-5" style="color: var(--accent-color);"></i>
                        <span class="fw-medium" style="color: var(--text-secondary);">{{ $group->teacher->name }}</span>
                      </div>
                    @endif
                  </div>

                  <!-- Animated Hover Button -->
                  <div class="group-card-action overflow-hidden mt-2">
                    <div class="btn btn-luxury w-100 d-flex justify-content-center align-items-center gap-2 py-3 fw-bold rounded-pill shadow-sm">
                      <span data-en="View Materials" data-ar="عرض المواد">عرض المواد</span> 
                      <i class="bi bi-arrow-left rtl:rotate-180"></i>
                    </div>
                  </div>
                </div>
              </div>
            </a>
          </div>
        @endforeach
      </div>
    @endif

    @if($withoutGroup->isNotEmpty())
      <h3 class="h5 fw-bold mb-3 border-start border-4 ps-3" style="border-color: var(--accent-color) !important;" data-en="Subjects Without a Group" data-ar="مواد بدون مجموعة">Subjects Without a Group</h3>
      <div class="glass-panel rounded-4 p-4 mb-4">
        <p class="text-sm opacity-75 mb-3" data-en="Choosing a group is optional — pick one whenever it suits your schedule." data-ar="اختيار المجموعة اختياري — يمكنك اختيار ما يناسب جدولك في أي وقت.">
          Choosing a group is optional — pick one whenever it suits your schedule.
        </p>
        <div class="d-flex flex-column gap-3">
          @foreach($withoutGroup as $registration)
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 p-3 rounded-3" style="background: var(--bg-secondary); border: 1px solid var(--separator-color);">
              <div>
                <div class="fw-bold" style="color: var(--text-primary);">{{ $registration->subject->name }}</div>
                <div class="text-xs opacity-75">{{ $registration->subject->groups->count() }} <span data-en="group(s) available" data-ar="مجموعة متاحة">group(s) available</span></div>
              </div>
              <form method="POST" action="{{ route('student.registrations.update-group', $registration) }}" class="d-flex gap-2">
                @csrf
                <select name="group_id" class="form-select form-select-sm" onchange="this.form.submit()">
                  <option value="" data-en="-- choose a group --" data-ar="-- اختر مجموعة --">-- choose a group --</option>
                  @foreach($registration->subject->groups as $group)
                    <option value="{{ $group->id }}" @disabled(! $group->hasAvailableCapacity())>
                      {{ $group->name }}@if(! $group->hasAvailableCapacity()) ({{ __('app.full') }}) @endif
                    </option>
                  @endforeach
                </select>
              </form>
            </div>
          @endforeach
        </div>
      </div>
    @endif
  @endif
@endsection

<!-- Join Group Register Modal -->
<div class="modal fade" id="joinGroupRegisterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-panel border-1 border-white/10" style="background: var(--bg-primary);">
            <div class="modal-header border-bottom border-white/10">
                <h5 class="modal-title fw-bold" style="color: var(--text-primary);" data-en="Join & Pay" data-ar="تسجيل ودفع">تسجيل ودفع</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="joinGroupRegisterForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning text-dark mb-4 fs-7">
                        <i class="bi bi-info-circle me-1"></i>
                        <span data-en="You are not registered in this subject. Please pay the required fees to join." data-ar="أنت غير مسجل في هذه المادة. يرجى سداد الرسوم المطلوبة لتتمكن من الانضمام للمجموعة.">أنت غير مسجل في هذه المادة. يرجى سداد الرسوم المطلوبة لتتمكن من الانضمام للمجموعة.</span>
                    </div>
                    
                    <input type="hidden" id="modalSubjectId" name="subject_id">
                    <input type="hidden" id="modalGroupId" name="group_id">
                    <input type="hidden" id="modalJoinCode" name="join_code">
                    
                    <div class="mb-3">
                        <label class="form-label fs-7" style="color: var(--text-secondary);" data-en="Subject & Group" data-ar="المادة والمجموعة">المادة والمجموعة</label>
                        <div class="form-control bg-white/5 border-white/10 text-white" readonly>
                            <span id="modalSubjectName" class="fw-bold text-gold"></span> 
                            - <span id="modalGroupName"></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7" style="color: var(--text-secondary);" data-en="Required Fee (JOD)" data-ar="الرسوم المطلوبة (دينار)">الرسوم المطلوبة (دينار)</label>
                        <input type="number" id="modalFee" name="amount" class="form-control bg-white/5 border-white/10 text-white" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7" style="color: var(--text-secondary);" data-en="Payment Method" data-ar="طريقة الدفع">طريقة الدفع</label>
                        <select name="payment_method_id" class="form-select bg-white/5 border-white/10 text-white" required>
                            <option value="">-- اختر طريقة الدفع --</option>
                            @foreach($paymentMethods ?? \App\Models\PaymentMethod::active()->orderBy('sort_order')->get() as $pm)
                                <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7" style="color: var(--text-secondary);" data-en="Payment Receipt" data-ar="صورة إيصال الدفع">صورة إيصال الدفع</label>
                        <input type="file" name="receipt" class="form-control bg-white/5 border-white/10 text-white" accept="image/*,.pdf" required>
                    </div>
                    
                    <div id="modalFeedback" class="mt-2 text-sm d-none"></div>
                </div>
                <div class="modal-footer border-top border-white/10 d-flex justify-content-between">
                    <a href="#" id="modalProgramUrl" class="btn btn-outline-secondary" data-en="View Details" data-ar="عرض التفاصيل">عرض التفاصيل</a>
                    <button type="submit" class="btn btn-luxury px-4" id="submitRegisterBtn">
                        <span class="indicator-label" data-en="Pay & Join" data-ar="تأكيد ودفع">تأكيد ودفع</span>
                        <span class="indicator-progress d-none"><i class="fas fa-circle-notch fa-spin"></i></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
  document.getElementById('joinGroupForm')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const btn = document.getElementById('joinGroupBtn');
    const input = document.getElementById('groupJoinCodeInput');
    const feedback = document.getElementById('joinGroupFeedback');
    const registerBtn = document.getElementById('joinGroupRegisterBtn');
    
    btn.disabled = true;
    btn.querySelector('.indicator-label').classList.add('d-none');
    btn.querySelector('.indicator-progress').classList.remove('d-none');
    feedback.classList.add('d-none');
    registerBtn.classList.add('d-none');

    fetch('{{ route("student.groups.join-by-code") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json'
      },
      body: JSON.stringify({ code: input.value })
    })
    .then(response => response.json().then(data => ({ status: response.status, body: data })))
    .then(({ status, body }) => {
      btn.disabled = false;
      btn.querySelector('.indicator-label').classList.remove('d-none');
      btn.querySelector('.indicator-progress').classList.add('d-none');

      feedback.classList.remove('d-none');
      if (status === 200 && body.success) {
        feedback.className = 'mt-2 text-sm text-success';
        feedback.textContent = body.message;
        setTimeout(() => window.location.reload(), 1500);
      } else {
        feedback.className = 'mt-2 text-sm text-danger';
        feedback.textContent = body.message || 'حدث خطأ غير متوقع.';
        
        if (body.needs_registration && body.program_url) {
          // Open Modal
          document.getElementById('modalProgramUrl').href = body.program_url;
          document.getElementById('modalSubjectId').value = body.subject_id;
          document.getElementById('modalGroupId').value = body.group_id;
          document.getElementById('modalJoinCode').value = body.join_code;
          document.getElementById('modalSubjectName').textContent = body.subject_name;
          document.getElementById('modalGroupName').textContent = body.group_name;
          document.getElementById('modalFee').value = body.fee;
          
          const modal = new bootstrap.Modal(document.getElementById('joinGroupRegisterModal'));
          modal.show();
        }
      }
    })
    .catch(error => {
      btn.disabled = false;
      btn.querySelector('.indicator-label').classList.remove('d-none');
      btn.querySelector('.indicator-progress').classList.add('d-none');
      
      feedback.classList.remove('d-none');
      feedback.className = 'mt-2 text-sm text-danger';
      feedback.textContent = 'حدث خطأ في الاتصال بالخادم.';
    });
  });

  document.getElementById('joinGroupRegisterForm')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const form = this;
    const btn = document.getElementById('submitRegisterBtn');
    const feedback = document.getElementById('modalFeedback');
    
    btn.disabled = true;
    btn.querySelector('.indicator-label').classList.add('d-none');
    btn.querySelector('.indicator-progress').classList.remove('d-none');
    feedback.classList.add('d-none');

    const formData = new FormData(form);

    fetch('{{ route("student.groups.register-and-join-by-code") }}', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json'
      },
      body: formData
    })
    .then(response => response.json().then(data => ({ status: response.status, body: data })))
    .then(({ status, body }) => {
      btn.disabled = false;
      btn.querySelector('.indicator-label').classList.remove('d-none');
      btn.querySelector('.indicator-progress').classList.add('d-none');

      feedback.classList.remove('d-none');
      if (status === 200 && body.success) {
        feedback.className = 'mt-3 text-sm text-success fw-bold p-2 bg-success bg-opacity-10 rounded';
        feedback.textContent = body.message;
        setTimeout(() => window.location.reload(), 2000);
      } else {
        feedback.className = 'mt-3 text-sm text-danger p-2 bg-danger bg-opacity-10 rounded';
        feedback.textContent = body.message || 'حدث خطأ غير متوقع.';
      }
    })
    .catch(error => {
      btn.disabled = false;
      btn.querySelector('.indicator-label').classList.remove('d-none');
      btn.querySelector('.indicator-progress').classList.add('d-none');
      
      feedback.classList.remove('d-none');
      feedback.className = 'mt-3 text-sm text-danger p-2 bg-danger bg-opacity-10 rounded';
      feedback.textContent = 'حدث خطأ في الاتصال بالخادم.';
    });
  });
</script>
@endpush

@push('styles')
<style>
/* Card Hover Animations & Styles */
.group-card-link:hover .group-card-action {
    height: auto !important;
    max-height: 80px !important;
    opacity: 1 !important;
    margin-top: 1rem !important;
}
.group-card-link:hover .group-card {
    border-color: var(--accent-color) !important;
    transform: translateY(-8px);
    box-shadow: 0 15px 35px rgba(197, 168, 128, 0.15) !important;
}
.group-card {
    transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
}
.group-card-action {
    height: auto;
    max-height: 0;
    opacity: 0;
    margin-top: 0;
    transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
}
/* Staggered Entrance Animation */
.group-card-animate {
    animation: fadeInUpCard 0.7s cubic-bezier(0.25, 0.8, 0.25, 1) forwards;
    opacity: 0;
    transform: translateY(30px);
}
@keyframes fadeInUpCard {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
@endpush
