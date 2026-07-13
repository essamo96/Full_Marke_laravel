@extends('layouts.student')

@section('title', 'Student Dashboard | FULL MARK ACADEMY')
@section('page_title_en', 'Overview')
@section('page_title_ar', 'الرئيسية')

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

        <!-- Welcome Banner -->
        <section class="glass-panel rounded-4 p-4 p-md-5 mb-4 position-relative overflow-hidden d-flex flex-column flex-md-row align-items-center justify-content-between gap-4">
          <div class="position-absolute top-0 end-0 w-50 h-100 bg-gold/10 blur-[80px]"></div>
          <div class="position-relative z-1 text-center text-md-start">
            <h1 class="display-6 fw-bold mb-2" style="color: var(--text-primary);">
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
                <span data-en="You've paid" data-ar="لقد دفعت">You've paid</span>
                {{ $paidPercent }}%
                <span data-en="of your total fees. Remaining balance:" data-ar="من إجمالي رسومك. المبلغ المتبقي:">of your total fees. Remaining balance:</span>
                <strong style="color: var(--accent-color);">{{ number_format($totalRemaining, 2) }} JOD</strong>
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
              <span class="text-xs opacity-75" data-en="Paid" data-ar="مدفوع">Paid</span>
            </div>
          </div>
        </section>

        <!-- Stats Grid -->
        <section class="row g-4 mb-4">
          <!-- Stat 1: Enrolled Subjects -->
          <div class="col-md-6 col-lg-3">
            <div class="glass-panel rounded-4 p-4 h-100 tilt-card glow-card">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="p-2 rounded" style="background: rgba(197, 168, 128, 0.1); color: var(--accent-color);">
                  <i class="bi bi-journal-check fs-4"></i>
                </div>
                @if($pendingCount > 0)
                  <span class="badge bg-warning bg-opacity-25 text-warning" data-en="{{ $pendingCount }} pending" data-ar="{{ $pendingCount }} قيد الانتظار">{{ $pendingCount }} pending</span>
                @endif
              </div>
              <p class="mb-1 text-sm opacity-75" data-en="Enrolled Subjects" data-ar="المواد المسجلة">Enrolled Subjects</p>
              <h3 class="fw-bold mb-0" style="color: var(--text-primary);">{{ $registrations->count() }}</h3>
            </div>
          </div>
          <!-- Stat 2: Programs -->
          <div class="col-md-6 col-lg-3">
            <div class="glass-panel rounded-4 p-4 h-100 tilt-card glow-card">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="p-2 rounded" style="background: rgba(197, 168, 128, 0.1); color: var(--accent-color);">
                  <i class="bi bi-mortarboard-fill fs-4"></i>
                </div>
              </div>
              <p class="mb-1 text-sm opacity-75" data-en="Programs Joined" data-ar="البرامج الملتحق بها">Programs Joined</p>
              <h3 class="fw-bold mb-0" style="color: var(--text-primary);">{{ $programsCount }}</h3>
            </div>
          </div>
          <!-- Financial Snapshot (Spans 2 cols) -->
          <div class="col-lg-6">
            <div class="glass-panel rounded-4 p-4 h-100 tilt-card glow-card position-relative overflow-hidden d-flex flex-column justify-content-center">
              <div class="position-absolute start-0 top-0 h-100 w-50 bg-gradient-to-r from-black/20 to-transparent z-0 pointer-events-none"></div>
              <div class="position-relative z-1 d-flex flex-column flex-sm-row justify-content-between align-items-start h-100 gap-3">
                <div>
                  @if($totalRemaining > 0)
                    <span class="badge bg-danger bg-opacity-25 text-danger mb-2 d-inline-flex align-items-center gap-2">
                      <i class="bi bi-exclamation-circle-fill"></i>
                      <span data-en="Balance Due" data-ar="رصيد مستحق">Balance Due</span>
                    </span>
                    <h4 class="fw-bold mb-1" style="color: var(--text-primary);">{{ number_format($totalRemaining, 2) }} JOD</h4>
                    <p class="text-sm opacity-75 mb-0">
                      <i class="bi bi-cash-stack me-1"></i>
                      <span data-en="Paid" data-ar="مدفوع">Paid</span> {{ number_format($totalPaid, 2) }} / {{ number_format($totalFee, 2) }} JOD
                    </p>
                  @else
                    <span class="badge bg-success bg-opacity-25 text-success mb-2 d-inline-flex align-items-center gap-2">
                      <i class="bi bi-patch-check-fill"></i>
                      <span data-en="Fully Paid" data-ar="مدفوع بالكامل">Fully Paid</span>
                    </span>
                    <h4 class="fw-bold mb-1" style="color: var(--text-primary);">{{ number_format($totalFee, 2) }} JOD</h4>
                    <p class="text-sm opacity-75 mb-0" data-en="No outstanding balance" data-ar="لا يوجد رصيد مستحق">No outstanding balance</p>
                  @endif
                </div>
                @if($totalRemaining > 0)
                  <a href="{{ route('student.checkout') }}" class="btn btn-luxury px-4 py-2 mt-auto mt-sm-0 align-self-sm-center" data-en="Pay Now" data-ar="ادفع الآن">Pay Now</a>
                @else
                  <a href="{{ route('site.home') }}#programs" class="btn btn-glass px-4 py-2 mt-auto mt-sm-0 align-self-sm-center" data-en="Browse More" data-ar="تصفح المزيد">Browse More</a>
                @endif
              </div>
            </div>
          </div>
        </section>

        @if($needsGroupCount > 0)
          <div class="glass-panel rounded-4 p-3 mb-4 d-flex align-items-center gap-3" style="border-color: rgba(197,168,128,0.3) !important;">
            <i class="bi bi-people-fill fs-3" style="color: var(--accent-color);"></i>
            <div class="flex-grow-1">
              <span data-en="You have {{ $needsGroupCount }} subject(s) without a study group yet. Choosing a group is optional, but recommended." data-ar="لديك {{ $needsGroupCount }} مادة بدون مجموعة دراسية بعد. اختيار المجموعة اختياري لكنه موصى به.">
                You have {{ $needsGroupCount }} subject(s) without a study group yet. Choosing a group is optional, but recommended.
              </span>
            </div>
            <a href="{{ route('student.cart') }}" class="btn btn-sm btn-glass" data-en="Choose Group" data-ar="اختر مجموعة">Choose Group</a>
          </div>
        @endif

        <div class="row g-4">
          <!-- My Registered Subjects -->
          <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-4">
              <h3 class="h4 fw-bold mb-0 border-start border-4 ps-3" style="border-color: var(--accent-color) !important;" data-en="My Registered Subjects" data-ar="موادي المسجلة">My Registered Subjects</h3>
              <a href="{{ route('student.registrations') }}" class="text-decoration-none text-sm fw-medium d-flex align-items-center gap-1" style="color: var(--accent-color);">
                <span data-en="View All" data-ar="عرض الكل">View All</span>
                <i class="bi bi-arrow-right rtl:rotate-180"></i>
              </a>
            </div>

            @if($registrations->isEmpty())
              <div class="glass-panel rounded-4 p-5 text-center">
                <i class="bi bi-journal-plus fs-1 mb-3 d-block" style="color: var(--accent-color);"></i>
                <p class="opacity-75 mb-3" data-en="You haven't registered in any subject yet." data-ar="لم تسجل في أي مادة بعد.">You haven't registered in any subject yet.</p>
                <a href="{{ route('site.home') }}#programs" class="btn btn-luxury px-4 py-2" data-en="Browse Programs" data-ar="تصفح البرامج">Browse Programs</a>
              </div>
            @else
              <div class="d-flex flex-column gap-3">
                @foreach($registrations as $registration)
                  @php
                    $fee = (float) $registration->fee_snapshot;
                    $paid = (float) $registration->amount_paid;
                    $pct = $fee > 0 ? min(100, round(($paid / $fee) * 100)) : 0;
                  @endphp
                  <a href="{{ route('student.registrations.show', $registration) }}" class="glass-panel rounded-3 p-3 d-flex align-items-center gap-3 transition-all hover:bg-white/5 cursor-pointer text-decoration-none">
                    <div class="rounded p-3 d-flex align-items-center justify-content-center border" style="background: var(--bg-secondary); border-color: var(--separator-color); width: 64px; height: 64px;">
                      <i class="bi bi-book fs-2" style="color: var(--accent-color);"></i>
                    </div>
                    <div class="flex-1 w-100">
                      <div class="d-flex justify-content-between align-items-start mb-1 gap-2">
                        <h5 class="mb-0 fs-6 fw-bold" style="color: var(--text-primary);">{{ $registration->subject->name }}</h5>
                        @if($registration->status === 'fully_paid')
                          <span class="badge bg-success bg-opacity-25 text-success" data-en="Fully Paid" data-ar="مدفوع بالكامل">Fully Paid</span>
                        @elseif($registration->status === 'partially_paid')
                          <span class="badge bg-warning bg-opacity-25 text-warning" data-en="Partially Paid" data-ar="مدفوع جزئياً">Partially Paid</span>
                        @else
                          <span class="badge bg-secondary bg-opacity-25 text-secondary" data-en="Pending" data-ar="قيد الانتظار">Pending</span>
                        @endif
                      </div>
                      <div class="text-muted fs-7 mb-2">{{ $registration->subject->program->title ?? '' }}</div>
                      <div class="progress mb-1" style="height: 6px; background: var(--bg-tertiary);">
                        <div class="progress-bar" role="progressbar" style="width: {{ $pct }}%; background: var(--accent-gradient);" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                      <span class="text-xs opacity-75">
                        <i class="bi bi-cash me-1"></i>{{ number_format($paid, 2) }} / {{ number_format($fee, 2) }} JOD
                        @if($registration->group)
                          <span class="ms-2"><i class="bi bi-people me-1"></i>{{ $registration->group->name }}</span>
                        @else
                          <span class="ms-2 opacity-50" data-en="No group selected" data-ar="بدون مجموعة">No group selected</span>
                        @endif
                      </span>
                    </div>
                  </a>
                @endforeach
              </div>
            @endif
          </div>

          <!-- Weekly Schedule + Recent Financial Activity -->
          <div class="col-lg-4">
            <h3 class="h4 fw-bold mb-4 border-start border-4 ps-3" style="border-color: var(--accent-color) !important;" data-en="Weekly Schedule" data-ar="الجدول الأسبوعي">Weekly Schedule</h3>

            <div class="glass-panel rounded-4 p-4 mb-4">
              @if($upcomingSessions->isEmpty())
                <p class="opacity-75 text-sm mb-0 text-center py-3" data-en="No scheduled group sessions yet." data-ar="لا يوجد جلسات مجموعات مجدولة بعد.">No scheduled group sessions yet.</p>
              @else
                <div class="d-flex flex-column gap-3">
                  @foreach($upcomingSessions as $session)
                    <div class="d-flex gap-3 align-items-start">
                      <div class="rounded-circle border border-4 mt-1 flex-shrink-0" style="width: 14px; height: 14px; background: var(--accent-color); border-color: var(--bg-primary);"></div>
                      <div>
                        <span class="text-sm fw-bold d-block mb-1" style="color: var(--accent-color);">
                          {{ $session['days']->map(fn($d) => $dayLabels[$d][$lang] ?? $d)->implode(' · ') }}
                          @if($session['group']->start_time)
                            — {{ \Carbon\Carbon::parse($session['group']->start_time)->format('h:i A') }}
                          @endif
                        </span>
                        <div class="rounded p-3 border" style="background: var(--bg-secondary); border-color: var(--separator-color);">
                          <h6 class="fw-bold mb-1" style="color: var(--text-primary);">{{ $session['subject']->name }}</h6>
                          <span class="text-xs opacity-75">{{ $session['group']->name }}@if($session['group']->teacher) — {{ $session['group']->teacher->name }} @endif</span>
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
              @endif
            </div>

            <h3 class="h4 fw-bold mb-4 border-start border-4 ps-3" style="border-color: var(--accent-color) !important;" data-en="Recent Payments" data-ar="آخر العمليات المالية">Recent Payments</h3>

            <div class="glass-panel rounded-4 p-4">
              @if($recentPayments->isEmpty())
                <p class="opacity-75 text-sm mb-0 text-center py-3" data-en="No payment activity yet." data-ar="لا يوجد عمليات دفع بعد.">No payment activity yet.</p>
              @else
                <div class="d-flex flex-column gap-3">
                  @foreach($recentPayments as $payment)
                    <div class="d-flex justify-content-between align-items-center gap-2 pb-3" style="border-bottom: 1px solid var(--separator-color);">
                      <div>
                        <h6 class="fw-bold mb-1" style="color: var(--text-primary);">{{ number_format($payment->amount, 2) }} JOD</h6>
                        <span class="text-xs opacity-75">{{ $payment->created_at->format('Y-m-d') }}</span>
                      </div>
                      @if($payment->status === 'confirmed')
                        <span class="badge bg-success bg-opacity-25 text-success" data-en="Confirmed" data-ar="مؤكد">Confirmed</span>
                      @elseif($payment->status === 'rejected')
                        <span class="badge bg-danger bg-opacity-25 text-danger" data-en="Rejected" data-ar="مرفوض">Rejected</span>
                      @else
                        <span class="badge bg-warning bg-opacity-25 text-warning" data-en="Pending" data-ar="قيد المراجعة">Pending</span>
                      @endif
                    </div>
                  @endforeach
                </div>
              @endif
            </div>
          </div>
        </div>

@endsection
