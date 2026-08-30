@extends('layouts.teacher')

@section('title', $group->name . ' | FULL MARK ACADEMY')
@section('page_title_en', $group->name)
@section('page_title_ar', $group->name)

@push('styles')
<style>
.teacher-accordion .accordion-collapse { visibility: visible !important; }
.teacher-accordion .accordion-item { background: transparent; border: none; border-bottom: 1px solid var(--separator-color); }
.teacher-accordion .accordion-button { background: transparent; color: var(--text-primary); box-shadow: none; }
.teacher-accordion .accordion-button:not(.collapsed) { color: var(--accent-color); background: var(--bg-tertiary); }
</style>
@endpush

@section('content')

  <!-- Group Banner -->
  <div class="glass-panel rounded-4 overflow-hidden mb-4 position-relative d-flex flex-column flex-md-row align-items-stretch" style="border: 1px solid var(--separator-color); box-shadow: 0 8px 32px rgba(0,0,0,0.2);">
    <!-- Image Section -->
    <div class="col-md-4 col-lg-3 position-relative" style="min-height: 200px;">
      @if($group->image)
        <img src="{{ asset('storage/'.$group->image) }}" class="w-100 h-100 object-fit-cover position-absolute top-0 start-0" alt="{{ $group->name }}">
      @else
        <div class="w-100 h-100 position-absolute top-0 start-0 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, rgba(197,168,128,0.2), rgba(197,168,128,0.05));">
          <i class="bi bi-people-fill text-gold opacity-50" style="font-size: 4rem;"></i>
        </div>
      @endif
      <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(90deg, transparent 0%, var(--bg-secondary) 100%);"></div>
    </div>
    
    <!-- Details Section -->
    <div class="col-md-8 col-lg-9 p-4 p-md-5 position-relative z-1 d-flex flex-column justify-content-center">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <h1 class="display-6 fw-bold mb-0" style="color: var(--text-primary); font-family: 'Tajawal', sans-serif;">{{ $group->name }}</h1>
        <span class="badge {{ $group->is_active ? 'bg-success' : 'bg-secondary' }} px-3 py-2 rounded-pill fs-7 shadow-sm">
          {{ $group->is_active ? 'مجموعة نشطة' : 'غير نشطة' }}
        </span>
      </div>
      
      <div class="text-gold fs-5 fw-medium mb-4">{{ $group->subject->name ?? '' }} <span class="text-muted mx-2">|</span> {{ $group->subject->program->title ?? '' }}</div>
      
      <div class="d-flex flex-wrap gap-4 text-muted">
        <div class="d-flex align-items-center gap-2">
          <div class="p-2 rounded-circle" style="background: rgba(197, 168, 128, 0.1);"><i class="bi bi-calendar3 text-gold"></i></div>
          <span>{{ $group->days ? implode(', ', (array) $group->days) : '-' }}</span>
        </div>
        @if($group->start_time)
        <div class="d-flex align-items-center gap-2">
          <div class="p-2 rounded-circle" style="background: rgba(197, 168, 128, 0.1);"><i class="bi bi-clock text-gold"></i></div>
          <span dir="ltr">{{ \Carbon\Carbon::parse($group->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($group->end_time)->format('h:i A') }}</span>
        </div>
        @endif
        <div class="d-flex align-items-center gap-2">
          <div class="p-2 rounded-circle" style="background: rgba(197, 168, 128, 0.1);"><i class="bi bi-people text-gold"></i></div>
          <span>{{ $roster->count() }} / {{ $group->max_capacity }} طالب</span>
        </div>
      </div>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success rounded-3 shadow-sm border-0">{{ session('success') }}</div>
  @endif

  <div class="row g-4">
    
    <!-- Main Content (Left in LTR / Right in RTL) -->
    <div class="col-xl-8">
      
      <!-- Educational Resources -->
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="h4 fw-bold mb-0 border-start border-4 ps-3" style="border-color: var(--accent-color) !important; color: var(--text-primary);" data-en="Educational Resources" data-ar="الموارد التعليمية">الموارد التعليمية</h3>
        <a href="{{ route('teacher.content.manage', $group->subject) }}?group={{ $group->id }}" class="btn btn-sm btn-outline-gold rounded-pill px-3" data-en="Manage Resources" data-ar="إدارة الموارد">إدارة الموارد</a>
      </div>
      
      <div class="glass-panel rounded-4 p-3 mb-5 shadow-sm" style="border: 1px solid var(--separator-color);">
        <div class="accordion teacher-accordion" id="stagesAccordion">
          @forelse($group->subject->stages as $stageIndex => $stage)
            <div class="accordion-item mb-2 rounded-3 overflow-hidden" style="background: rgba(0,0,0,0.2);">
              <h2 class="accordion-header">
                <button class="accordion-button fw-bold {{ $stageIndex === 0 ? '' : 'collapsed' }} px-4 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseStage{{ $stage->id }}">
                  <i class="bi bi-journal-bookmark-fill me-2 text-gold"></i> {{ $stage->name_ar ?? $stage->name_en }}
                </button>
              </h2>
              <div id="collapseStage{{ $stage->id }}" class="accordion-collapse collapse {{ $stageIndex === 0 ? 'show' : '' }}">
                <div class="accordion-body p-4 pt-2">
                  @foreach($stage->units as $unit)
                    <div class="mb-4">
                      <h6 class="fw-bold fs-7 text-uppercase mb-3 px-3 py-2 rounded" style="background: rgba(197, 168, 128, 0.1); color: var(--text-primary); border-right: 3px solid var(--accent-color);">{{ $unit->name_ar ?? $unit->name_en }}</h6>
                      <div class="row g-3 ps-3">
                      @foreach($unit->lessons as $lesson)
                        <div class="col-md-6">
                          <div class="p-3 rounded-3 h-100 transition-all hover-glow" style="background: var(--bg-tertiary); border: 1px solid var(--separator-color);">
                            <div class="fw-bold fs-6 mb-2" style="color: var(--text-primary);">{{ $lesson->name_ar ?? $lesson->name_en }}</div>
                            <div class="d-flex flex-column gap-2">
                            @forelse($lesson->resources as $resource)
                              @php
                                $rIcon = match($resource->type) {
                                  'video' => 'play-circle-fill',
                                  'document' => 'file-earmark-text-fill',
                                  'image' => 'image-fill',
                                  'zoom' => 'camera-video-fill',
                                  default => 'link-45deg',
                                };
                                $rIsPending = $resource->isVideo() && ! $resource->isReady();
                              @endphp
                              @if($rIsPending)
                                <span class="d-flex align-items-center gap-2 fs-7 text-muted opacity-75">
                                  <i class="bi bi-hourglass-split text-gold"></i>
                                  <span class="text-truncate">{{ $resource->title }}</span>
                                  <span class="badge ms-auto" style="background: rgba(197,168,128,0.15); color: var(--accent-color);" data-en="Processing" data-ar="قيد المعالجة">قيد المعالجة</span>
                                </span>
                              @else
                                <a href="{{ $resource->isExternalLink() ? $resource->url : route('teacher.content.view-file', $resource) }}"
                                   target="_blank" rel="noopener"
                                   class="text-decoration-none text-muted d-flex align-items-center gap-2 fs-7 hover-text-gold transition-all">
                                  <i class="bi bi-{{ $rIcon }} text-gold"></i>
                                  <span class="text-truncate">{{ $resource->title }}</span>
                                  <i class="bi bi-box-arrow-up-right ms-auto opacity-50 flex-shrink-0" style="font-size: .7rem;"></i>
                                </a>
                              @endif
                            @empty
                              <div class="text-muted fs-7" data-en="No resources yet." data-ar="لا توجد موارد بعد.">لا توجد موارد بعد.</div>
                            @endforelse
                            </div>
                          </div>
                        </div>
                      @endforeach
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>
          @empty
            <div class="p-5 text-center text-muted" data-en="No curriculum structure yet." data-ar="لا يوجد هيكل مناهج بعد.">
              <i class="bi bi-folder-x display-1 mb-3 d-block opacity-25"></i>
              لا يوجد هيكل مناهج بعد.
            </div>
          @endforelse
        </div>
      </div>

      <!-- Students Roster -->
      <h3 class="h4 fw-bold mb-3 border-start border-4 ps-3 mt-4" style="border-color: var(--accent-color) !important; color: var(--text-primary);" data-en="Students" data-ar="طلاب المجموعة">طلاب المجموعة</h3>
      <div class="glass-panel rounded-4 p-0 overflow-hidden shadow-sm mb-5" style="border: 1px solid var(--separator-color);">
        <div class="table-responsive">
          <table class="table table-borderless align-middle mb-0">
            <thead>
              <tr class="text-muted text-uppercase fs-7">
                <th class="py-3 px-4" data-en="Student" data-ar="الطالب">Student</th>
                <th class="py-3" data-en="Phone" data-ar="الهاتف">Phone</th>
                <th class="py-3" data-en="Status" data-ar="الحالة">Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse($roster as $reg)
                <tr style="border-bottom: 1px solid var(--separator-color);">
                  <td class="px-4 py-3">
                    <div class="d-flex align-items-center gap-3">
                      <div class="rounded-circle d-flex align-items-center justify-content-center text-gold fw-bold" style="width: 40px; height: 40px; background: var(--bg-tertiary);">
                        {{ mb_substr($reg->student?->full_name_ar ?? $reg->student?->full_name_en ?? 'U', 0, 1) }}
                      </div>
                      <span class="fw-medium">{{ $reg->student?->full_name_ar ?? $reg->student?->full_name_en }}</span>
                    </div>
                  </td>
                  <td class="text-muted"><bdi>{{ $reg->student?->phone }}</bdi></td>
                  <td><span class="badge bg-success bg-opacity-25 text-success rounded-pill px-3">{{ \App\Models\Registration::groupStatusLabel($reg->group_status) }}</span></td>
                </tr>
              @empty
                <tr><td colspan="3" class="text-center text-muted py-5" data-en="No students yet." data-ar="لا يوجد طلاب بعد.">لا يوجد طلاب بعد.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
      
    </div>

    <!-- Sidebar (Right in LTR / Left in RTL) -->
    <div class="col-xl-4 d-flex flex-column gap-4">
      
      <!-- Quick Actions -->
      <div class="glass-panel rounded-4 p-4 shadow-sm" style="border: 1px solid var(--separator-color);">
        <h4 class="fw-bold mb-4" style="color: var(--text-primary); font-size: 1.1rem;"><i class="bi bi-lightning-charge text-gold me-2"></i>الإجراءات السريعة</h4>
        <div class="d-flex flex-column gap-2">
          <a href="{{ route('teacher.attendance.show', $group) }}" class="btn btn-luxury w-100 py-3 text-start fw-bold d-flex justify-content-between align-items-center">
            <span><i class="bi bi-clipboard-check me-2"></i> تسجيل الحضور</span>
            <i class="bi bi-chevron-left"></i>
          </a>
          <a href="{{ route('teacher.exams.create', ['group_id' => \Illuminate\Support\Facades\Crypt::encryptString($group->id)]) }}" class="btn btn-glass w-100 py-3 text-start d-flex justify-content-between align-items-center">
            <span><i class="bi bi-journal-plus me-2 text-gold"></i> إعداد امتحان جديد</span>
            <i class="bi bi-chevron-left text-muted"></i>
          </a>
          <a href="{{ route('teacher.grading.index') }}" class="btn btn-glass w-100 py-3 text-start d-flex justify-content-between align-items-center">
            <span><i class="bi bi-check2-square me-2 text-gold"></i> رصد علامات الطلاب</span>
            <i class="bi bi-chevron-left text-muted"></i>
          </a>
        </div>
      </div>

      <!-- Exams -->
      <div class="glass-panel rounded-4 p-4 shadow-sm" style="border: 1px solid var(--separator-color);">
        <h4 class="fw-bold mb-4 d-flex justify-content-between align-items-center" style="color: var(--text-primary); font-size: 1.1rem;">
          <span><i class="bi bi-card-checklist text-gold me-2"></i>الامتحانات</span>
          <span class="badge bg-gold text-dark rounded-pill">{{ $exams->count() }}</span>
        </h4>
        <div class="d-flex flex-column gap-3">
          @forelse($exams->take(4) as $exam)
            <div class="d-flex justify-content-between align-items-center p-3 rounded-3" style="background: rgba(0,0,0,0.2);">
              <div>
                <div class="fw-bold text-primary mb-1 fs-7">{{ $exam->title }}</div>
                <div class="text-muted fs-8"><i class="bi bi-clock me-1"></i> {{ $exam->created_at->format('Y-m-d') }}</div>
              </div>
              <a href="{{ route('teacher.grading.exam', $exam) }}" class="btn btn-sm btn-outline-gold rounded-pill px-3 fs-8">العلامات</a>
            </div>
          @empty
            <div class="text-muted text-center py-3 fs-7">لا توجد امتحانات مسجلة.</div>
          @endforelse
        </div>
      </div>

      <!-- Group Notes -->
      <div class="glass-panel rounded-4 p-4 shadow-sm" style="border: 1px solid var(--separator-color);">
        <h4 class="fw-bold mb-4" style="color: var(--text-primary); font-size: 1.1rem;"><i class="bi bi-sticky text-gold me-2"></i>ملاحظات المجموعة</h4>
        
        <form method="POST" action="{{ route('teacher.group-notes.store', $group) }}" class="mb-4">
          @csrf
          <div class="input-group">
            <input type="text" name="title" class="form-control" style="background: var(--input-bg); border-color: var(--separator-color); color: var(--text-primary);" placeholder="عنوان الملاحظة..." required>
            <input type="hidden" name="content" value="ملاحظة سريعة">
            <button class="btn btn-luxury" type="submit"><i class="bi bi-send-fill"></i></button>
          </div>
        </form>

        <div class="d-flex flex-column gap-3" style="max-height: 300px; overflow-y: auto;">
          @forelse($notes as $note)
            <div class="p-3 rounded-3 position-relative" style="background: rgba(197, 168, 128, 0.05); border-right: 3px solid var(--accent-color);">
              <div class="fw-bold fs-7 mb-1 text-primary">{{ $note->title }}</div>
              @if($note->content !== 'ملاحظة سريعة')
                <div class="text-muted fs-8 mb-2">{{ $note->content }}</div>
              @endif
              <div class="text-muted fs-8">{{ $note->created_at->diffForHumans() }}</div>
            </div>
          @empty
            <div class="text-muted text-center py-3 fs-7">لا توجد ملاحظات.</div>
          @endforelse
        </div>
      </div>
      
      <!-- Top / Absent Students Grid -->
      <div class="row g-3">
        <div class="col-6">
          <div class="glass-panel rounded-4 p-3 shadow-sm h-100 text-center text-md-start" style="border: 1px solid var(--separator-color);">
            <div class="text-warning mb-2"><i class="bi bi-trophy-fill fs-4"></i></div>
            <div class="fw-bold text-primary fs-7 mb-1">أفضل الطلاب</div>
            <div class="text-muted fs-8">{{ $topStudents->count() }} طالب</div>
          </div>
        </div>
        <div class="col-6">
          <div class="glass-panel rounded-4 p-3 shadow-sm h-100 text-center text-md-start" style="border: 1px solid var(--separator-color);">
            <div class="text-danger mb-2"><i class="bi bi-exclamation-triangle-fill fs-4"></i></div>
            <div class="fw-bold text-primary fs-7 mb-1">الأكثر غياباً</div>
            <div class="text-muted fs-8">{{ $mostAbsentStudents->count() }} طالب</div>
          </div>
        </div>
      </div>

    </div>
  </div>

@endsection
