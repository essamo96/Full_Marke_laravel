@extends('layouts.student')

@section('title', 'Student Dashboard | FULL MARK ACADEMY')
@section('page_title_en', 'Overview')
@section('page_title_ar', 'الرئيسية')

@push('styles')
<style>
  .custom-pills .nav-link {
    color: var(--text-primary);
    border-radius: 50rem;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    margin-right: 0.5rem;
    background: transparent;
    border: 1px solid transparent;
    transition: all 0.3s ease;
  }
  .custom-pills .nav-link:hover {
    background: rgba(255,255,255,0.05);
  }
  .custom-pills .nav-link.active {
    background: var(--accent-gradient);
    color: var(--bg-primary);
    border-color: transparent;
    box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
  }
</style>
@endpush

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
          $ringOffset = 251.2 - (251.2 * min(100, max(0, $paidPercent)) / 100);
        @endphp

        <!-- Dashboard Tabs -->
        <div class="mb-4 fade-in-up delay-1">
            <ul class="nav nav-pills custom-pills" id="dashboardTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="overview-tab" data-bs-toggle="pill" data-bs-target="#overview" type="button" role="tab" aria-selected="true" data-en="Overview" data-ar="نظرة عامة">نظرة عامة</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="finance-tab" data-bs-toggle="pill" data-bs-target="#finance" type="button" role="tab" aria-selected="false" data-en="Financial Board" data-ar="اللوحة المالية">اللوحة المالية</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="schedule-tab" data-bs-toggle="pill" data-bs-target="#schedule" type="button" role="tab" aria-selected="false" data-en="Schedule & Calendar" data-ar="الجدول والتقويم">الجدول والتقويم</button>
                </li>
            </ul>
        </div>

        <div class="tab-content" id="dashboardTabsContent">
            
            <!-- ===================== OVERVIEW TAB ===================== -->
            <div class="tab-pane fade show active fade-in-up delay-2" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                <!-- Welcome Banner -->
                <section class="glass-panel bg-pattern-gold bg-pattern-animated rounded-4 p-5 mb-5 position-relative overflow-hidden d-flex flex-column flex-md-row align-items-center justify-content-between gap-5 border-1 border-white/10" style="box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
                  <div class="position-absolute top-0 end-0 w-50 h-100 bg-gold/10 blur-[80px] floating-orb"></div>
                  <div class="position-relative z-1 text-center text-md-start">
                    <h1 class="display-5 fw-bold mb-3" style="color: var(--text-primary); font-family: 'Tajawal', 'Almarai', sans-serif;">
                      <span data-en="Welcome back," data-ar="مرحباً بعودتك،">Welcome back,</span> <span style="background: var(--accent-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">{{ $student->name }}</span>
                    </h1>
                    @if($registrations->isEmpty())
                      <p class="fs-5 opacity-75 mb-0" data-en="You have no subject registrations yet — browse our programs to get started." data-ar="لا يوجد لديك أي تسجيل في مواد بعد — تصفح برامجنا لتبدأ رحلتك.">
                        You have no subject registrations yet — browse our programs to get started.
                      </p>
                    @elseif($totalRemaining <= 0)
                      <p class="fs-5 opacity-75 mb-0" data-en="All your registration fees are fully paid. You're all set!" data-ar="جميع رسوم تسجيلك مدفوعة بالكامل. أنت جاهز تماماً!">
                        All your registration fees are fully paid. You're all set!
                      </p>
                    @else
                      <p class="fs-5 opacity-75 mb-0">
                        <span data-en="You've paid" data-ar="لقد دفعت">لقد دفعت</span>
                        <strong style="color: var(--accent-color);">{{ $paidPercent }}%</strong>
                        <span data-en="of your total fees." data-ar="من إجمالي رسومك.">من إجمالي رسومك.</span>
                      </p>
                    @endif
                  </div>
                  <div class="position-relative z-1 d-flex align-items-center justify-content-center" style="width: 120px; height: 120px;">
                    <svg class="w-100 h-100 transform -rotate-90" viewBox="0 0 100 100">
                      <circle cx="50" cy="50" r="40" fill="none" stroke="var(--bg-tertiary)" stroke-width="8"></circle>
                      <circle class="progress-ring-circle" cx="50" cy="50" r="40" fill="none" stroke="var(--accent-color)" stroke-width="8" stroke-dasharray="251.2" stroke-dashoffset="{{ $ringOffset }}" stroke-linecap="round"></circle>
                    </svg>
                    <div class="position-absolute d-flex flex-column align-items-center justify-content-center">
                      <span class="fs-4 fw-bold" style="color: var(--accent-color);">{{ $paidPercent }}%</span>
                    </div>
                  </div>
                </section>

                <!-- Stats Grid -->
                <section class="row g-4 mb-4 fade-in-up delay-3">
                  <!-- Stat 1: Enrolled Subjects -->
                  <div class="col-md-6">
                    <div class="glass-panel rounded-4 p-4 h-100 tilt-card glow-card">
                      <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="p-2 rounded" style="background: rgba(197, 168, 128, 0.1); color: var(--accent-color);">
                          <i class="bi bi-journal-check fs-4"></i>
                        </div>
                        @if($pendingCount > 0)
                          <span class="badge bg-warning bg-opacity-25 text-warning" data-en="{{ $pendingCount }} pending" data-ar="{{ $pendingCount }} قيد الانتظار">{{ $pendingCount }} قيد الانتظار</span>
                        @endif
                      </div>
                      <p class="mb-1 text-sm opacity-75" data-en="Enrolled Subjects" data-ar="المواد المسجلة">المواد المسجلة</p>
                      <h3 class="fw-bold mb-0" style="color: var(--text-primary);">{{ $registrations->count() }}</h3>
                    </div>
                  </div>
                  <!-- Stat 2: Programs -->
                  <div class="col-md-6">
                    <div class="glass-panel rounded-4 p-4 h-100 tilt-card glow-card">
                      <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="p-2 rounded" style="background: rgba(197, 168, 128, 0.1); color: var(--accent-color);">
                          <i class="bi bi-mortarboard-fill fs-4"></i>
                        </div>
                      </div>
                      <p class="mb-1 text-sm opacity-75" data-en="Programs Joined" data-ar="البرامج الملتحق بها">البرامج الملتحق بها</p>
                      <h3 class="fw-bold mb-0" style="color: var(--text-primary);">{{ $programsCount }}</h3>
                    </div>
                  </div>
                </section>

                <!-- Join Group By Code Section -->
                <div class="glass-panel bg-pattern-gold border border-white/10 rounded-4 p-4 mb-4 fade-in-up delay-4">
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
                    </div>
                  </div>
                </div>

                <!-- My Registered Subjects -->
                <div class="d-flex align-items-center justify-content-between mb-4 mt-5 fade-in-up delay-5">
                  <h3 class="h4 fw-bold mb-0 border-start border-4 ps-3" style="border-color: var(--accent-color) !important;" data-en="My Registered Subjects" data-ar="موادي المسجلة">موادي المسجلة</h3>
                  <a href="{{ route('student.registrations') }}" class="text-decoration-none text-sm fw-medium d-flex align-items-center gap-1" style="color: var(--accent-color);">
                    <span data-en="View All" data-ar="عرض الكل">عرض الكل</span>
                    <i class="bi bi-arrow-right rtl:rotate-180"></i>
                  </a>
                </div>

                @if($registrations->isEmpty())
                  <div class="glass-panel rounded-4 p-5 text-center">
                    <i class="bi bi-journal-plus fs-1 mb-3 d-block" style="color: var(--accent-color);"></i>
                    <p class="opacity-75 mb-3" data-en="You haven't registered in any subject yet." data-ar="لم تسجل في أي مادة بعد.">لم تسجل في أي مادة بعد.</p>
                    <a href="{{ route('site.home') }}#programs" class="btn btn-luxury px-4 py-2" data-en="Browse Programs" data-ar="تصفح البرامج">تصفح البرامج</a>
                  </div>
                @else
                  <div class="row g-3">
                    @foreach($registrations as $registration)
                      @php
                        $fee = (float) $registration->fee_snapshot;
                        $paid = (float) $registration->amount_paid;
                        $pct = $fee > 0 ? min(100, round(($paid / $fee) * 100)) : 0;
                      @endphp
                      <div class="col-md-6">
                          <a href="{{ route('student.registrations.show', $registration) }}" class="glass-panel rounded-3 p-3 h-100 d-flex align-items-center gap-3 transition-all hover:bg-white/5 cursor-pointer text-decoration-none">
                            <div class="rounded p-3 d-flex align-items-center justify-content-center border" style="background: var(--bg-secondary); border-color: var(--separator-color); width: 64px; height: 64px;">
                              <i class="bi bi-book fs-2" style="color: var(--accent-color);"></i>
                            </div>
                            <div class="flex-1 w-100">
                              <div class="d-flex justify-content-between align-items-start mb-1 gap-2">
                                <h5 class="mb-0 fs-6 fw-bold" style="color: var(--text-primary);">{{ $registration->subject?->name ?? '-' }}</h5>
                                @if($registration->status === 'fully_paid')
                                  <span class="badge bg-success bg-opacity-25 text-success" data-en="Fully Paid" data-ar="مدفوع بالكامل">مدفوع بالكامل</span>
                                @elseif($registration->status === 'partially_paid')
                                  <span class="badge bg-warning bg-opacity-25 text-warning" data-en="Partially Paid" data-ar="مدفوع جزئياً">مدفوع جزئياً</span>
                                @else
                                  <span class="badge bg-secondary bg-opacity-25 text-secondary" data-en="Pending" data-ar="قيد الانتظار">قيد الانتظار</span>
                                @endif
                              </div>
                              <div class="text-muted fs-7 mb-2">{{ $registration->subject->program->title ?? '' }}</div>
                              <div class="progress mb-1" style="height: 6px; background: var(--bg-tertiary);">
                                <div class="progress-bar" role="progressbar" style="width: {{ $pct }}%; background: var(--accent-gradient);" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                              </div>
                              <span class="text-xs opacity-75">
                                @if($registration->group)
                                  <span class="me-2"><i class="bi bi-people me-1 text-gold"></i>{{ $registration->group->name }}</span>
                                @else
                                  <span class="me-2 opacity-50" data-en="No group selected" data-ar="بدون مجموعة">بدون مجموعة</span>
                                @endif
                              </span>
                            </div>
                          </a>
                      </div>
                    @endforeach
                  </div>
                @endif
            </div>

            <!-- ===================== FINANCIAL TAB ===================== -->
            <div class="tab-pane fade fade-in-up delay-2" id="finance" role="tabpanel" aria-labelledby="finance-tab">
                <div class="row g-4 mb-4">
                  <!-- Financial Snapshot -->
                  <div class="col-12">
                    <div class="glass-panel bg-pattern-gold rounded-4 p-5 h-100 tilt-card glow-card position-relative overflow-hidden d-flex flex-column justify-content-center">
                      <div class="position-absolute start-0 top-0 h-100 w-50 bg-gradient-to-r from-black/20 to-transparent z-0 pointer-events-none"></div>
                      <div class="position-relative z-1 d-flex flex-column flex-sm-row justify-content-between align-items-center h-100 gap-4">
                        <div class="text-center text-sm-start">
                          @if($totalRemaining > 0)
                            <span class="badge bg-danger bg-opacity-25 text-danger mb-3 d-inline-flex align-items-center gap-2 fs-6 px-3 py-2">
                              <i class="bi bi-exclamation-circle-fill"></i>
                              <span data-en="Balance Due" data-ar="رصيد مستحق">رصيد مستحق</span>
                            </span>
                            <h2 class="display-5 fw-bold mb-2" style="color: var(--text-primary);">{{ number_format($totalRemaining, 2) }} <span class="fs-4">JOD</span></h2>
                            <p class="fs-5 opacity-75 mb-0">
                              <i class="bi bi-cash-stack me-1 text-gold"></i>
                              <span data-en="Paid" data-ar="المدفوع">المدفوع</span>: <strong class="text-white">{{ number_format($totalPaid, 2) }}</strong> / {{ number_format($totalFee, 2) }} JOD
                            </p>
                          @else
                            <span class="badge bg-success bg-opacity-25 text-success mb-3 d-inline-flex align-items-center gap-2 fs-6 px-3 py-2">
                              <i class="bi bi-patch-check-fill"></i>
                              <span data-en="Fully Paid" data-ar="مدفوع بالكامل">مدفوع بالكامل</span>
                            </span>
                            <h2 class="display-5 fw-bold mb-2" style="color: var(--text-primary);">{{ number_format($totalFee, 2) }} <span class="fs-4">JOD</span></h2>
                            <p class="fs-5 opacity-75 mb-0" data-en="No outstanding balance" data-ar="لا يوجد رصيد مستحق">لا يوجد رصيد مستحق</p>
                          @endif
                        </div>
                        @if($totalRemaining > 0)
                          <a href="{{ route('student.checkout') }}" class="btn btn-luxury btn-lg px-5 py-3 align-self-sm-center" data-en="Pay Now" data-ar="ادفع الآن">ادفع الآن</a>
                        @endif
                      </div>
                    </div>
                  </div>
                </div>

                <h3 class="h4 fw-bold mb-4 border-start border-4 ps-3" style="border-color: var(--accent-color) !important;" data-en="Recent Payments" data-ar="آخر العمليات المالية">آخر العمليات المالية</h3>

                <div class="glass-panel rounded-4 p-4">
                  @if($recentPayments->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-receipt fs-1 text-muted mb-3 d-block"></i>
                        <p class="opacity-75 fs-5 mb-0" data-en="No payment activity yet." data-ar="لا يوجد عمليات دفع بعد.">لا يوجد عمليات دفع بعد.</p>
                    </div>
                  @else
                    <div class="table-responsive">
                        <table class="table table-borderless text-white align-middle mb-0">
                            <thead>
                                <tr class="opacity-50 border-bottom border-white/10">
                                    <th class="pb-3 fw-normal" data-en="Date" data-ar="التاريخ">التاريخ</th>
                                    <th class="pb-3 fw-normal" data-en="Amount" data-ar="المبلغ">المبلغ</th>
                                    <th class="pb-3 fw-normal" data-en="Method" data-ar="الطريقة">الطريقة</th>
                                    <th class="pb-3 fw-normal" data-en="Status" data-ar="الحالة">الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPayments as $payment)
                                    <tr class="border-bottom border-white/5">
                                        <td class="py-3">{{ $payment->created_at->format('Y-m-d') }}</td>
                                        <td class="py-3 fw-bold text-gold">{{ number_format($payment->amount, 2) }} JOD</td>
                                        <td class="py-3">{{ $payment->paymentMethod->name ?? 'Cash' }}</td>
                                        <td class="py-3">
                                            @if($payment->status === 'confirmed')
                                                <span class="badge bg-success bg-opacity-25 text-success"><i class="bi bi-check-circle me-1"></i> مؤكد</span>
                                            @elseif($payment->status === 'rejected')
                                                <span class="badge bg-danger bg-opacity-25 text-danger"><i class="bi bi-x-circle me-1"></i> مرفوض</span>
                                            @else
                                                <span class="badge bg-warning bg-opacity-25 text-warning"><i class="bi bi-clock me-1"></i> قيد المراجعة</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                  @endif
                </div>
            </div>

            <!-- ===================== SCHEDULE TAB ===================== -->
            <div class="tab-pane fade fade-in-up delay-2" id="schedule" role="tabpanel" aria-labelledby="schedule-tab">
                
                @if($needsGroupCount > 0)
                  <div class="glass-panel rounded-4 p-3 mb-4 d-flex align-items-center gap-3 border-warning border-opacity-50">
                    <i class="bi bi-exclamation-triangle-fill fs-2 text-warning"></i>
                    <div class="flex-grow-1">
                      <span class="text-white" data-en="You have {{ $needsGroupCount }} subject(s) without a study group yet. Please choose a group to see its schedule." data-ar="لديك {{ $needsGroupCount }} مادة بدون مجموعة دراسية بعد. يرجى اختيار مجموعة لتتمكن من رؤية جدولها.">
                        لديك {{ $needsGroupCount }} مادة بدون مجموعة دراسية بعد. يرجى اختيار مجموعة لتتمكن من رؤية جدولها.
                      </span>
                    </div>
                    <a href="{{ route('student.cart') }}" class="btn btn-warning" data-en="Choose Group" data-ar="اختر مجموعة">اختر مجموعة</a>
                  </div>
                @endif

                <h3 class="h4 fw-bold mb-4 border-start border-4 ps-3" style="border-color: var(--accent-color) !important;" data-en="Weekly Schedule" data-ar="الجدول الأسبوعي للمحاضرات">الجدول الأسبوعي للمحاضرات</h3>

                <div class="row g-4">
                    @if($upcomingSessions->isEmpty())
                        <div class="col-12">
                            <div class="glass-panel rounded-4 p-5 text-center">
                                <i class="bi bi-calendar-x fs-1 text-muted mb-3 d-block"></i>
                                <p class="opacity-75 fs-5 mb-0" data-en="No scheduled group sessions yet." data-ar="لا يوجد جلسات مجموعات مجدولة بعد.">لا يوجد جلسات مجموعات مجدولة بعد.</p>
                            </div>
                        </div>
                    @else
                        @foreach($upcomingSessions as $session)
                            <div class="col-md-6 col-lg-4">
                                <div class="glass-panel rounded-4 p-4 h-100 position-relative border-top border-4" style="border-top-color: var(--accent-color) !important;">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="fw-bold mb-0 text-white">{{ $session['subject']->name }}</h5>
                                        <span class="badge bg-white/10 text-gold fs-7 py-1 px-2 border border-white/20">
                                            {{ $session['group']->name }}
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 mb-2 text-white/80">
                                        <i class="bi bi-calendar-event text-gold"></i>
                                        <span>{{ $session['days']->map(fn($d) => $dayLabels[$d][$lang] ?? $d)->implode(' · ') }}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 text-white/80">
                                        <i class="bi bi-clock text-gold"></i>
                                        <span>
                                            @if($session['group']->start_time)
                                                {{ \Carbon\Carbon::parse($session['group']->start_time)->format('h:i A') }}
                                            @else
                                                يحدد لاحقاً
                                            @endif
                                        </span>
                                    </div>
                                    @if($session['group']->teacher)
                                        <div class="mt-3 pt-3 border-top border-white/10 d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-white/10 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                <i class="bi bi-person-fill text-gold"></i>
                                            </div>
                                            <span class="text-sm text-white">{{ $session['group']->teacher->name }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
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

    btn.disabled = true;
    btn.querySelector('.indicator-label').classList.add('d-none');
    btn.querySelector('.indicator-progress').classList.remove('d-none');
    feedback.classList.add('d-none');

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

</script>
@endpush
@endsection
