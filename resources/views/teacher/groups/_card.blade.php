@php
  $dayLabels = [
    'sun' => 'الأحد', 'mon' => 'الاثنين', 'tue' => 'الثلاثاء', 'wed' => 'الأربعاء',
    'thu' => 'الخميس', 'fri' => 'الجمعة', 'sat' => 'السبت',
  ];
@endphp
<a href="{{ route('teacher.groups.show', $group) }}" class="text-decoration-none d-block h-100 teacher-group-card-link">
  <div class="glass-panel rounded-4 h-100 overflow-hidden position-relative teacher-group-card" style="border: 1px solid var(--separator-color);">
    <div class="position-relative" style="height: 130px;">
      @if($group->image)
        <img src="{{ asset('storage/'.$group->image) }}" class="w-100 h-100" style="object-fit: cover;" alt="{{ $group->name }}">
        <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(to top, var(--bg-primary) 0%, transparent 60%);"></div>
      @else
        <div class="w-100 h-100 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, rgba(197,168,128,0.15), rgba(197,168,128,0.03));">
          <i class="bi bi-people-fill" style="font-size: 2.5rem; color: var(--accent-color); opacity: 0.6;"></i>
        </div>
      @endif
      @if($group->is_active)
        <div class="position-absolute top-0 end-0 m-3 px-3 py-1 rounded-pill d-flex align-items-center gap-2 shadow-sm" style="background: rgba(32, 201, 151, 0.9); backdrop-filter: blur(4px); font-size: 0.75rem; font-weight: 700; color: #fff; z-index: 2; white-space: nowrap; max-width: calc(100% - 1.5rem);">
          <span class="spinner-grow spinner-grow-sm text-white flex-shrink-0" style="width: 10px; height: 10px;" role="status" aria-hidden="true"></span>
          <span data-en="Active" data-ar="مجموعة نشطة">مجموعة نشطة</span>
        </div>
      @else
        <div class="position-absolute top-0 end-0 m-3 px-3 py-1 rounded-pill d-flex align-items-center gap-2 shadow-sm" style="background: rgba(108, 117, 125, 0.9); backdrop-filter: blur(4px); font-size: 0.75rem; font-weight: 700; color: #fff; z-index: 2; white-space: nowrap; max-width: calc(100% - 1.5rem);">
          <i class="bi bi-pause-circle-fill flex-shrink-0"></i>
          <span data-en="Inactive" data-ar="غير نشطة">غير نشطة</span>
        </div>
      @endif
    </div>
    <div class="p-4">
      <h5 class="fw-bold mb-1" style="color: var(--text-primary);">{{ $group->name }}</h5>
      @if($group->subject ?? null)
        <div class="text-muted fs-7 mb-3">{{ $group->subject->name }}</div>
      @endif

      <div class="d-flex flex-column gap-2 mb-3">
        <div class="d-flex align-items-center gap-2 fs-7 text-muted">
          <i class="bi bi-calendar-week" style="color: var(--accent-color);"></i>
          <span>{{ $group->days ? collect($group->days)->map(fn($d) => $dayLabels[$d] ?? $d)->implode(' · ') : '-' }}</span>
        </div>
        @if($group->start_time)
          <div class="d-flex align-items-center gap-2 fs-7 text-muted">
            <i class="bi bi-clock" style="color: var(--accent-color);"></i>
            <span>{{ $group->start_time }} - {{ $group->end_time }}</span>
          </div>
        @endif
        <div class="d-flex align-items-center gap-2 fs-7 text-muted">
          <i class="bi bi-person-fill" style="color: var(--accent-color);"></i>
          <span>{{ $group->students_count ?? $group->registrations()->whereIn('status', ['pending','partially_paid','fully_paid'])->count() }} طالب</span>
        </div>
      </div>

      <div class="btn btn-luxury w-100 py-2 fw-bold rounded-pill" data-en="Open Group" data-ar="دخول المجموعة">دخول المجموعة</div>
    </div>
  </div>
</a>
