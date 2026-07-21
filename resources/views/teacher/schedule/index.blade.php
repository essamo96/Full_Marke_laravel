@extends('layouts.teacher')

@section('title', 'Academic Schedule | FULL MARK ACADEMY')
@section('page_title_en', 'Academic Schedule')
@section('page_title_ar', 'الجدول الأكاديمي')

@php
  $dayLabels = ['sun' => 'الأحد', 'mon' => 'الاثنين', 'tue' => 'الثلاثاء', 'wed' => 'الأربعاء', 'thu' => 'الخميس', 'fri' => 'الجمعة', 'sat' => 'السبت'];
  $todayKey = ['sunday' => 'sun', 'monday' => 'mon', 'tuesday' => 'tue', 'wednesday' => 'wed', 'thursday' => 'thu', 'friday' => 'fri', 'saturday' => 'sat'][strtolower(now()->format('l'))];
  $statusMeta = [
    'active' => ['label' => 'جارية', 'class' => 'bg-success'],
    'upcoming' => ['label' => 'قادمة', 'class' => 'bg-warning text-dark'],
    'ended' => ['label' => 'منتهية', 'class' => 'bg-secondary'],
  ];
@endphp

@section('content')

  <h1 class="h3 fw-bold mb-4" style="color: var(--text-primary);" data-en="Academic Schedule" data-ar="الجدول الأكاديمي">الجدول الأكاديمي</h1>

  <div class="schedule-strip d-flex gap-3 overflow-auto pb-3 mb-4">
    @foreach($dayLabels as $key => $label)
      <div class="schedule-day-col flex-shrink-0 {{ $key === $todayKey ? 'schedule-day-today' : '' }}" style="width: 260px;">
        <div class="glass-panel rounded-4 p-3 h-100" style="border: 1px solid {{ $key === $todayKey ? 'var(--accent-color)' : 'var(--separator-color)' }};">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0" style="color: {{ $key === $todayKey ? 'var(--accent-color)' : 'var(--text-primary)' }};">{{ $label }}</h6>
            @if($key === $todayKey)
              <span class="badge bg-gold text-dark" data-en="Today" data-ar="اليوم">اليوم</span>
            @endif
          </div>

          <div class="d-flex flex-column gap-2">
            @forelse($groupsByDay[$key] as $group)
              @php($meta = $statusMeta[$group->schedule_status])
              <a href="{{ route('teacher.groups.show', $group) }}" class="text-decoration-none">
                <div class="rounded-3 p-3" style="background: var(--bg-secondary); border-inline-start: 3px solid var(--accent-color);">
                  <div class="fw-bold fs-7 mb-1" style="color: var(--text-primary);">{{ $group->name }}</div>
                  <div class="text-muted fs-8 mb-2">{{ $group->subject->name ?? '' }}</div>
                  <div class="d-flex align-items-center gap-1 fs-8 text-muted mb-1">
                    <i class="bi bi-clock"></i> {{ $group->start_time }} - {{ $group->end_time }}
                  </div>
                  <span class="badge {{ $meta['class'] }} fs-8">{{ $meta['label'] }}</span>
                </div>
              </a>
            @empty
              <div class="text-muted fs-8 text-center py-3" data-en="No groups" data-ar="لا توجد مجموعات">لا توجد مجموعات</div>
            @endforelse
          </div>
        </div>
      </div>
    @endforeach
  </div>

  <h3 class="h5 fw-bold mb-3" style="color: var(--text-primary);" data-en="All Groups" data-ar="كل المجموعات">كل المجموعات</h3>
  <div class="row g-3">
    @forelse($groups as $group)
      @php($meta = $statusMeta[$group->schedule_status])
      <div class="col-md-6 col-xl-4">
        <div class="glass-panel rounded-4 p-4 h-100" style="border: 1px solid var(--separator-color);">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <h6 class="fw-bold mb-0" style="color: var(--text-primary);">{{ $group->name }}</h6>
            <span class="badge {{ $meta['class'] }}">{{ $meta['label'] }}</span>
          </div>
          <div class="text-muted fs-7 mb-2">{{ $group->subject->name ?? '' }}</div>
          <div class="text-muted fs-7 mb-1"><i class="bi bi-calendar-week me-1"></i>{{ collect($group->days ?? [])->map(fn($d) => $dayLabels[$d] ?? $d)->implode(' · ') }}</div>
          @if($group->start_date || $group->end_date)
            <div class="text-muted fs-7 mb-1">
              <i class="bi bi-calendar-range me-1"></i>
              {{ $group->start_date?->format('Y-m-d') ?? '?' }} — {{ $group->end_date?->format('Y-m-d') ?? '?' }}
            </div>
          @endif
          @if($group->remaining_lectures !== null)
            <div class="text-muted fs-7"><i class="bi bi-journal-check me-1"></i>{{ $group->remaining_lectures }} محاضرة متبقية</div>
          @endif
        </div>
      </div>
    @empty
      <div class="col-12">
        <div class="glass-panel rounded-4 p-5 text-center text-muted" data-en="No groups yet." data-ar="لا توجد مجموعات بعد.">لا توجد مجموعات بعد.</div>
      </div>
    @endforelse
  </div>

@endsection

@push('styles')
<style>
.schedule-strip::-webkit-scrollbar { height: 6px; }
.schedule-strip::-webkit-scrollbar-thumb { background: var(--accent-color); border-radius: 3px; }
</style>
@endpush
