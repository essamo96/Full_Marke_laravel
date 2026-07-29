{{--
  Responsive weekly timetable, shared by the teacher and student schedules.

  Layout adapts rather than just shrinking:
    ≥1200px  seven columns side by side — the whole week at a glance
    768–1199 a wrapping grid, so columns stay readable instead of squeezing
    <768px   a stacked day list with today pinned open, since a 7-column grid
             is unusable on a phone and horizontal scrolling hides days

  Expects:
    $byDay      array<string dayKey, Collection<Group>>
    $todayKey   string
    $linkRoute    route name taking a Group, or null for non-clickable cards
    $showTeacher  include the instructor's name (student view); off by default
                  so the teacher's own schedule does not repeat their name and
                  does not lazy-load the relation
--}}
@php
  use App\Services\WeeklySchedule;

  $linkRoute = $linkRoute ?? null;
  $showTeacher = $showTeacher ?? false;
  $statusMeta = [
    'active'   => ['label_ar' => 'جارية',  'label_en' => 'Active',   'class' => 'is-active'],
    'upcoming' => ['label_ar' => 'قادمة',  'label_en' => 'Upcoming', 'class' => 'is-upcoming'],
    'ended'    => ['label_ar' => 'منتهية', 'label_en' => 'Ended',    'class' => 'is-ended'],
  ];
  // Start the week at today so the most relevant days lead on small screens.
  $orderedKeys = array_keys(WeeklySchedule::WEEKDAY_NUM);
  $todayIndex = array_search($todayKey, $orderedKeys, true) ?: 0;
@endphp

<div class="cal" data-today="{{ $todayKey }}">
  <div class="cal__grid">
    @foreach($orderedKeys as $key)
      @php
        $sessions = $byDay[$key] ?? collect();
        $isToday = $key === $todayKey;
      @endphp

      <section class="cal__day {{ $isToday ? 'is-today' : '' }} {{ $sessions->isEmpty() ? 'is-empty' : '' }}"
               data-day="{{ $key }}" style="--cal-order: {{ ($loop->index - $todayIndex + 7) % 7 }};">

        {{-- On phones the header doubles as the accordion toggle. --}}
        <button type="button" class="cal__head" aria-expanded="{{ $isToday ? 'true' : 'false' }}"
                aria-controls="cal-body-{{ $key }}">
          <span class="cal__head-main">
            <span class="cal__dayname">
              <span class="d-none d-sm-inline">{{ WeeklySchedule::DAY_LABELS_AR[$key] }}</span>
              <span class="d-sm-none">{{ WeeklySchedule::DAY_SHORT_AR[$key] }}</span>
            </span>
            @if($isToday)
              <span class="cal__today-chip" data-en="Today" data-ar="اليوم">اليوم</span>
            @endif
          </span>
          <span class="cal__head-meta">
            <span class="cal__count">{{ $sessions->count() }}</span>
            <i class="bi bi-chevron-down cal__chev" aria-hidden="true"></i>
          </span>
        </button>

        <div class="cal__body" id="cal-body-{{ $key }}">
          @forelse($sessions as $group)
            @php
              $meta = $statusMeta[$group->schedule_status] ?? $statusMeta['active'];
              $live = WeeklySchedule::isLiveNow($group);
              $href = $linkRoute ? route($linkRoute, $group) : null;
            @endphp

            <{{ $href ? 'a' : 'div' }} @if($href) href="{{ $href }}" @endif
               class="cal__session {{ $meta['class'] }} {{ $live ? 'is-live' : '' }}">

              @if($live)
                <span class="cal__live">
                  <span class="cal__live-dot"></span>
                  <span data-en="Live now" data-ar="جارية الآن">جارية الآن</span>
                </span>
              @endif

              <div class="cal__session-title">{{ $group->name }}</div>

              @if($group->subject)
                <div class="cal__session-sub">{{ $group->subject->name_ar ?? $group->subject->name ?? '' }}</div>
              @endif

              <div class="cal__session-time">
                <i class="bi bi-clock"></i>
                <span dir="ltr">{{ WeeklySchedule::formatTime($group->start_time) }} – {{ WeeklySchedule::formatTime($group->end_time) }}</span>
              </div>

              @if($showTeacher && $group->teacher)
                <div class="cal__session-sub">
                  <i class="bi bi-person"></i> {{ $group->teacher->name }}
                </div>
              @endif

              <span class="cal__status">{{ $meta['label_ar'] }}</span>
            </{{ $href ? 'a' : 'div' }}>
          @empty
            <p class="cal__none" data-en="No sessions" data-ar="لا توجد حصص">لا توجد حصص</p>
          @endforelse
        </div>
      </section>
    @endforeach
  </div>
</div>
