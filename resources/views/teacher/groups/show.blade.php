@extends('layouts.teacher')

@section('title', $group->name . ' | FULL MARK ACADEMY')
@section('page_title_en', $group->name)
@section('page_title_ar', $group->name)

@push('styles')
<style>
.teacher-accordion .accordion-collapse { visibility: visible !important; }
.teacher-accordion .accordion-item { background: transparent; border: none; border-bottom: 1px solid rgba(255,255,255,0.05); }
.teacher-accordion .accordion-button { background: transparent; color: var(--text-primary); box-shadow: none; }
.teacher-accordion .accordion-button:not(.collapsed) { color: var(--accent-color); background: rgba(255,255,255,0.02); }
</style>
@endpush

@section('content')

  <!-- Group Banner -->
  <div class="glass-panel rounded-4 overflow-hidden mb-4 position-relative">
    <div style="height: 140px; {{ $group->image ? "background-image: url('".asset('storage/'.$group->image)."'); background-size: cover; background-position: center;" : 'background: linear-gradient(135deg, rgba(197,168,128,0.18), rgba(197,168,128,0.03));' }}"></div>
    <div class="p-4 d-flex justify-content-between align-items-start flex-wrap gap-2">
      <div>
        <h1 class="h3 fw-bold mb-1" style="color: var(--text-primary);">{{ $group->name }}</h1>
        <div class="text-muted fs-7">
          {{ $group->subject->name ?? '' }} — {{ $group->subject->program->title ?? '' }}
          &middot; {{ $group->days ? implode(', ', (array) $group->days) : '-' }}
          @if($group->start_time) — {{ $group->start_time }} - {{ $group->end_time }} @endif
        </div>
      </div>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <!-- Quick Actions -->
  <div class="glass-panel rounded-4 p-3 mb-4 d-flex flex-wrap gap-2">
    <a href="{{ route('teacher.attendance.show', $group) }}" class="btn btn-luxury btn-sm"><i class="bi bi-clipboard-check me-1"></i> <span data-en="Take Attendance" data-ar="أخذ الحضور">أخذ الحضور</span></a>
    <a href="{{ route('teacher.grading.index') }}" class="btn btn-glass btn-sm"><i class="bi bi-check2-square me-1"></i> <span data-en="Grading" data-ar="رصد العلامات">رصد العلامات</span></a>
    <a href="{{ route('teacher.content.manage', $group->subject) }}" class="btn btn-glass btn-sm"><i class="bi bi-cloud-upload me-1"></i> <span data-en="Add Content" data-ar="إضافة محتوى تعليمي">إضافة محتوى تعليمي</span></a>
    <a href="{{ route('teacher.exams.create', ['group_id' => $group->id]) }}" class="btn btn-glass btn-sm"><i class="bi bi-calendar-plus me-1"></i> <span data-en="Schedule Exam" data-ar="جدولة امتحان">جدولة امتحان</span></a>
  </div>

  <div class="row g-4 mb-4">
    <!-- Roster -->
    <div class="col-xl-7">
      <h3 class="h5 fw-bold mb-3" style="color: var(--text-primary);" data-en="Students" data-ar="الطلاب">الطلاب</h3>
      <div class="glass-panel rounded-4 p-0 overflow-hidden">
        <div class="table-responsive">
          <table class="table table-borderless text-white align-middle mb-0">
            <thead>
              <tr class="text-muted text-uppercase fs-7">
                <th data-en="Student" data-ar="الطالب">Student</th>
                <th data-en="Phone" data-ar="الهاتف">Phone</th>
                <th data-en="Status" data-ar="الحالة">Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse($roster as $reg)
                <tr>
                  <td>{{ $reg->student?->full_name_ar ?? $reg->student?->full_name_en }}</td>
                  <td>{{ $reg->student?->phone }}</td>
                  <td><span class="badge bg-gold text-dark">{{ \App\Models\Registration::groupStatusLabel($reg->group_status) }}</span></td>
                </tr>
              @empty
                <tr><td colspan="3" class="text-center text-muted py-4" data-en="No students yet." data-ar="لا يوجد طلاب بعد.">لا يوجد طلاب بعد.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Group Notes -->
    <div class="col-xl-5">
      <h3 class="h5 fw-bold mb-3" style="color: var(--text-primary);" data-en="Group Notes" data-ar="ملاحظات المجموعة">ملاحظات المجموعة</h3>
      <div class="glass-panel rounded-4 p-4 mb-3">
        <form method="POST" action="{{ route('teacher.group-notes.store', $group) }}">
          @csrf
          <div class="mb-3">
            <input type="text" name="title" class="form-control" placeholder="العنوان" required maxlength="255">
          </div>
          <div class="mb-3">
            <textarea name="content" class="form-control" rows="3" placeholder="نص الملاحظة" required></textarea>
          </div>
          <button type="submit" class="btn btn-luxury btn-sm" data-en="Post Note" data-ar="نشر الملاحظة">نشر الملاحظة</button>
        </form>
      </div>

      <div class="d-flex flex-column gap-3">
        @forelse($notes as $note)
          <div class="glass-panel rounded-4 p-3" style="border: 1px solid var(--separator-color);">
            <div class="fw-bold fs-7 mb-1" style="color: var(--accent-color);">{{ $note->title }}</div>
            <div class="text-sm" style="color: var(--text-primary);">{{ $note->content }}</div>
            <div class="text-muted fs-7 mt-2">{{ $note->created_at->diffForHumans() }}</div>
          </div>
        @empty
          <div class="text-muted text-center py-4" data-en="No notes yet." data-ar="لا توجد ملاحظات بعد.">لا توجد ملاحظات بعد.</div>
        @endforelse
      </div>
    </div>
  </div>

  <div class="row g-4 mb-4">
    <!-- Learning Resources -->
    <div class="col-xl-6">
      <h3 class="h5 fw-bold mb-3" style="color: var(--text-primary);" data-en="Learning Resources" data-ar="الموارد التعليمية">الموارد التعليمية</h3>
      <div class="glass-panel rounded-4 p-2" style="max-height: 360px; overflow-y: auto;">
        <div class="accordion teacher-accordion" id="stagesAccordion">
          @forelse($group->subject->stages as $stageIndex => $stage)
            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseStage{{ $stage->id }}">
                  {{ $stage->name_ar ?? $stage->name_en }}
                </button>
              </h2>
              <div id="collapseStage{{ $stage->id }}" class="accordion-collapse collapse">
                <div class="accordion-body p-3">
                  @foreach($stage->units as $unit)
                    @foreach($unit->lessons as $lesson)
                      @foreach($lesson->resources as $resource)
                        <div class="text-muted fs-7 d-flex align-items-center gap-2 mb-1">
                          <i class="bi bi-{{ $resource->type === 'video' ? 'play-circle-fill text-danger' : 'file-earmark-text-fill text-info' }}"></i>
                          <span>{{ $resource->title }}</span>
                        </div>
                      @endforeach
                    @endforeach
                  @endforeach
                </div>
              </div>
            </div>
          @empty
            <div class="p-4 text-center text-muted" data-en="No resources yet." data-ar="لا توجد موارد بعد.">لا توجد موارد بعد.</div>
          @endforelse
        </div>
      </div>
    </div>

    <!-- Exams -->
    <div class="col-xl-6">
      <h3 class="h5 fw-bold mb-3" style="color: var(--text-primary);" data-en="Exams" data-ar="امتحانات المجموعة">امتحانات المجموعة</h3>
      <div class="glass-panel rounded-4 p-0 overflow-hidden">
        <div class="table-responsive">
          <table class="table table-borderless text-white align-middle mb-0">
            <thead>
              <tr class="text-muted text-uppercase fs-7">
                <th data-en="Title" data-ar="العنوان">Title</th>
                <th data-en="Status" data-ar="الحالة">Status</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              @forelse($exams as $exam)
                <tr>
                  <td>{{ $exam->title }}</td>
                  <td><span class="badge bg-gold text-dark">{{ $exam->status }}</span></td>
                  <td><a href="{{ route('teacher.grading.exam', $exam) }}" class="btn btn-sm btn-outline-primary" data-en="Grades" data-ar="العلامات">العلامات</a></td>
                </tr>
              @empty
                <tr><td colspan="3" class="text-center text-muted py-4" data-en="No exams for this group yet." data-ar="لا توجد امتحانات لهذه المجموعة بعد.">لا توجد امتحانات لهذه المجموعة بعد.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <!-- Top students -->
    <div class="col-xl-6">
      <h3 class="h5 fw-bold mb-3" style="color: var(--text-primary);" data-en="Top Students" data-ar="أفضل الطلاب">أفضل الطلاب</h3>
      <div class="glass-panel rounded-4 p-4">
        @forelse($topStudents as $grade)
          <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="border-color: var(--separator-color) !important;">
            <span class="fw-medium fs-7" style="color: var(--text-primary);"><i class="bi bi-trophy-fill text-warning me-1"></i>{{ $grade->student?->full_name_ar ?? $grade->student?->full_name_en }}</span>
            <span class="badge bg-success">{{ $grade->score }} / {{ $grade->max_score }}</span>
          </div>
        @empty
          <div class="text-muted text-center py-4" data-en="No grades recorded yet." data-ar="لا توجد علامات مسجلة بعد.">لا توجد علامات مسجلة بعد.</div>
        @endforelse
      </div>
    </div>

    <!-- Most absent -->
    <div class="col-xl-6">
      <h3 class="h5 fw-bold mb-3" style="color: var(--text-primary);" data-en="Most Absent" data-ar="الأكثر غياباً">الأكثر غياباً</h3>
      <div class="glass-panel rounded-4 p-4">
        @forelse($mostAbsentStudents as $row)
          <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="border-color: var(--separator-color) !important;">
            <span class="fw-medium fs-7" style="color: var(--text-primary);"><i class="bi bi-exclamation-triangle-fill text-danger me-1"></i>{{ $row->student?->full_name_ar ?? $row->student?->full_name_en }}</span>
            <span class="badge bg-danger">{{ $row->absences }}</span>
          </div>
        @empty
          <div class="text-muted text-center py-4" data-en="No absences recorded." data-ar="لا يوجد غياب مسجل.">لا يوجد غياب مسجل.</div>
        @endforelse
      </div>
    </div>
  </div>

@endsection
