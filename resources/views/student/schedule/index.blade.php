@extends('layouts.student')

@section('title', 'My Schedule | FULL MARK ACADEMY')
@section('page_title_en', 'My Schedule')
@section('page_title_ar', 'جدولي الدراسي')

@php
  use App\Services\WeeklySchedule;

  $statusMeta = [
    'active'   => ['label' => 'جارية',  'class' => 'sched-pill--active'],
    'upcoming' => ['label' => 'قادمة',  'class' => 'sched-pill--upcoming'],
    'ended'    => ['label' => 'منتهية', 'class' => 'sched-pill--ended'],
  ];
@endphp

@section('content')

  <div class="sched-header">
    <div>
      <h1 class="sched-title" data-en="My Schedule" data-ar="جدولي الدراسي">جدولي الدراسي</h1>
      <p class="sched-subtitle" dir="rtl">{{ now()->translatedFormat('l، j F Y') }}</p>
    </div>

    <div class="sched-summary">
      <div class="sched-summary__item">
        <span class="sched-summary__value">{{ $todayCount }}</span>
        <span class="sched-summary__label" data-en="Today" data-ar="حصص اليوم">حصص اليوم</span>
      </div>
      <div class="sched-summary__item">
        <span class="sched-summary__value">{{ $weeklyCount }}</span>
        <span class="sched-summary__label" data-en="This week" data-ar="حصص الأسبوع">حصص الأسبوع</span>
      </div>
      <div class="sched-summary__item">
        <span class="sched-summary__value">{{ $groups->count() }}</span>
        <span class="sched-summary__label" data-en="Groups" data-ar="المجموعات">المجموعات</span>
      </div>
    </div>
  </div>

  @include('partials.schedule.week', [
    'byDay' => $groupsByDay,
    'todayKey' => $todayKey,
    'linkRoute' => 'student.groups.show',
    'showTeacher' => true,
  ])

  @if($awaitingGroup->isNotEmpty())
    <div class="sched-notice">
      <i class="bi bi-info-circle"></i>
      <div>
        <strong data-en="Not scheduled yet" data-ar="بانتظار تحديد المجموعة">بانتظار تحديد المجموعة</strong>
        <p>
          <span data-en="These subjects have no group assigned yet, so they do not appear on the calendar:"
                data-ar="لم يتم تحديد مجموعة لهذه المواد بعد، لذلك لا تظهر في الجدول:">لم يتم تحديد مجموعة لهذه المواد بعد، لذلك لا تظهر في الجدول:</span>
          {{ $awaitingGroup->map(fn($r) => $r->subject->name_ar ?? $r->subject->name ?? '—')->implode(' · ') }}
        </p>
      </div>
    </div>
  @endif

  <h2 class="sched-section-title" data-en="My Groups" data-ar="مجموعاتي">مجموعاتي</h2>

  <div class="sched-cards">
    @forelse($groups as $group)
      @php $meta = $statusMeta[$group->schedule_status] ?? $statusMeta['active']; @endphp
      <a href="{{ route('student.groups.show', $group) }}" class="sched-card">
        <div class="sched-card__top">
          <h3 class="sched-card__title">{{ $group->name }}</h3>
          <span class="sched-pill {{ $meta['class'] }}">{{ $meta['label'] }}</span>
        </div>

        <p class="sched-card__subject">{{ $group->subject->name_ar ?? $group->subject->name ?? '' }}</p>

        <div class="sched-card__days">
          @foreach($group->days ?? [] as $d)
            <span class="sched-daychip {{ $d === $todayKey ? 'is-today' : '' }}">{{ WeeklySchedule::DAY_SHORT_AR[$d] ?? $d }}</span>
          @endforeach
        </div>

        <div class="sched-card__meta">
          <span dir="ltr"><i class="bi bi-clock"></i> {{ WeeklySchedule::formatTime($group->start_time) }} – {{ WeeklySchedule::formatTime($group->end_time) }}</span>
        </div>

        @if($group->teacher)
          <div class="sched-card__meta">
            <span><i class="bi bi-person"></i> {{ $group->teacher->name }}</span>
          </div>
        @endif

        @if($group->remaining_lectures !== null)
          <div class="sched-card__meta">
            <span><i class="bi bi-journal-check"></i> {{ $group->remaining_lectures }} محاضرة متبقية</span>
          </div>
        @endif
      </a>
    @empty
      <div class="sched-empty" data-en="You have no scheduled groups yet." data-ar="لا توجد لديك مجموعات مجدولة بعد.">لا توجد لديك مجموعات مجدولة بعد.</div>
    @endforelse
  </div>

@endsection

@push('styles')
  @include('partials.schedule.styles')
  @include('partials.schedule.page-styles')
<style>
  .sched-notice {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    margin-top: 22px;
    padding: 14px 16px;
    border-radius: 14px;
    background: var(--input-bg);
    border: 1px solid var(--separator-color);
    border-inline-start: 3px solid #f59e0b;
  }
  .sched-notice i { color: #f59e0b; font-size: 1.05rem; margin-top: 2px; }
  .sched-notice strong { display: block; font-size: 0.84rem; color: var(--text-primary); margin-bottom: 3px; }
  .sched-notice p { margin: 0; font-size: 0.76rem; color: var(--text-muted); line-height: 1.6; }
</style>
@endpush

@push('scripts')
  @include('partials.schedule.scripts')
@endpush
