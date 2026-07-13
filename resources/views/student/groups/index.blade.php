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
          @php $group = $registration->group; @endphp
          <div class="col-md-6 col-xl-4">
            <div class="glass-panel rounded-4 p-4 h-100 tilt-card glow-card">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="p-2 rounded" style="background: rgba(197, 168, 128, 0.1); color: var(--accent-color);">
                  <i class="bi bi-people-fill fs-4"></i>
                </div>
                <span class="badge bg-success bg-opacity-25 text-success" data-en="Joined" data-ar="منضم">Joined</span>
              </div>
              <h5 class="fw-bold mb-1" style="color: var(--text-primary);">{{ $registration->subject->name }}</h5>
              <p class="text-sm opacity-75 mb-3">{{ $group->name }}</p>

              <div class="d-flex flex-column gap-2 text-sm">
                @if(!empty($group->days))
                  <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-calendar-week" style="color: var(--accent-color);"></i>
                    <span>{{ collect($group->days)->map(fn($d) => $dayLabels[$d][$lang] ?? $d)->implode(' · ') }}</span>
                  </div>
                @endif
                @if($group->start_time)
                  <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-clock" style="color: var(--accent-color);"></i>
                    <span>{{ \Carbon\Carbon::parse($group->start_time)->format('h:i A') }} — {{ \Carbon\Carbon::parse($group->end_time)->format('h:i A') }}</span>
                  </div>
                @endif
                @if($group->teacher)
                  <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-person-badge" style="color: var(--accent-color);"></i>
                    <span>{{ $group->teacher->name }}</span>
                  </div>
                @endif
              </div>

              @if($group->zoom_link)
                <a href="{{ $group->zoom_link }}" target="_blank" rel="noopener" class="btn btn-luxury w-100 mt-3 py-2">
                  <i class="bi bi-camera-video-fill me-1"></i>
                  <span data-en="Join Session" data-ar="انضم للجلسة">Join Session</span>
                </a>
              @endif
            </div>
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
