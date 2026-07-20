@extends('layouts.teacher')

@section('title', $subject->name . ' | FULL MARK ACADEMY')
@section('page_title_en', $subject->name)
@section('page_title_ar', $subject->name)

@push('styles')
<style>
.teacher-accordion .accordion-collapse { visibility: visible !important; }
.teacher-accordion .accordion-item { background: transparent; border: none; border-bottom: 1px solid rgba(255,255,255,0.05); }
.teacher-accordion .accordion-button { background: transparent; color: var(--text-primary); box-shadow: none; }
.teacher-accordion .accordion-button:not(.collapsed) { color: var(--accent-color); background: rgba(255,255,255,0.02); }
</style>
@endpush

@section('content')

  <h1 class="h3 fw-bold mb-4" style="color: var(--text-primary);">{{ $subject->name }}</h1>

  <div class="row g-4 mb-4">
    @forelse($groups as $group)
      <div class="col-md-6 col-xl-4">
        <div class="glass-panel rounded-4 p-4 h-100" style="border: 1px solid var(--separator-color);">
          <h5 class="fw-bold mb-2" style="color: var(--text-primary);">{{ $group->name }}</h5>
          <div class="text-muted fs-7 mb-2">
            <i class="bi bi-people-fill me-1"></i>{{ $group->students_count }} <span data-en="students" data-ar="طالب">طالب</span>
          </div>
          <div class="text-muted fs-7 mb-3">
            <i class="bi bi-clock me-1"></i>
            {{ $group->days ? implode(', ', (array) $group->days) : '-' }}
            @if($group->start_time) — {{ $group->start_time }} - {{ $group->end_time }} @endif
          </div>
          <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('teacher.groups.show', $group) }}" class="btn btn-luxury btn-sm" data-en="Open Group" data-ar="فتح المجموعة">فتح المجموعة</a>
            <a href="{{ route('teacher.attendance.show', $group) }}" class="btn btn-glass btn-sm" data-en="Attendance" data-ar="الحضور">الحضور</a>
          </div>
        </div>
      </div>
    @empty
      <div class="col-12">
        <div class="glass-panel rounded-4 p-4 text-center text-muted" data-en="No groups assigned to you for this subject." data-ar="لا توجد مجموعات مسندة إليك لهذه المادة.">لا توجد مجموعات مسندة إليك لهذه المادة.</div>
      </div>
    @endforelse
  </div>

  <h3 class="h5 fw-bold mb-3" style="color: var(--text-primary);" data-en="Educational Resources" data-ar="الموارد التعليمية">الموارد التعليمية</h3>
  <div class="glass-panel rounded-4 p-2">
    <div class="accordion teacher-accordion" id="stagesAccordion">
      @forelse($subject->stages as $stageIndex => $stage)
        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button {{ $stageIndex === 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseStage{{ $stage->id }}">
              {{ $stage->name_ar ?? $stage->name_en }}
            </button>
          </h2>
          <div id="collapseStage{{ $stage->id }}" class="accordion-collapse collapse {{ $stageIndex === 0 ? 'show' : '' }}">
            <div class="accordion-body p-3">
              @foreach($stage->units as $unit)
                <div class="fw-bold fs-7 text-uppercase opacity-50 mb-2" style="color: var(--text-primary);">{{ $unit->name_ar ?? $unit->name_en }}</div>
                @foreach($unit->lessons as $lesson)
                  <div class="mb-2 ps-3">
                    <div class="fw-medium fs-7 mb-1" style="color: var(--text-primary);">{{ $lesson->name_ar ?? $lesson->name_en }}</div>
                    @forelse($lesson->resources as $resource)
                      <div class="text-muted fs-7 d-flex align-items-center gap-2 ps-3">
                        <i class="bi bi-{{ $resource->type === 'video' ? 'play-circle-fill text-danger' : 'file-earmark-text-fill text-info' }}"></i>
                        <span>{{ $resource->title }}</span>
                      </div>
                    @empty
                      <div class="text-muted fs-7 ps-3" data-en="No resources yet." data-ar="لا توجد موارد بعد.">لا توجد موارد بعد.</div>
                    @endforelse
                  </div>
                @endforeach
              @endforeach
            </div>
          </div>
        </div>
      @empty
        <div class="p-4 text-center text-muted" data-en="No curriculum structure yet." data-ar="لا يوجد هيكل مناهج بعد.">لا يوجد هيكل مناهج بعد.</div>
      @endforelse
    </div>
  </div>

  <div class="mt-4">
    <a href="{{ route('teacher.content.manage', $subject) }}" class="btn btn-luxury" data-en="Manage Resources" data-ar="إدارة الموارد">إدارة الموارد</a>
  </div>

@endsection
