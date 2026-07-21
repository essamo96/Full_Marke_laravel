@extends('layouts.teacher')

@section('title', 'My Subjects | FULL MARK ACADEMY')
@section('page_title_en', 'Programs & Subjects')
@section('page_title_ar', 'البرامج والمواد')

@section('content')

  <h1 class="h3 fw-bold mb-4" style="color: var(--text-primary);" data-en="Programs & Subjects" data-ar="البرامج والمواد">البرامج والمواد</h1>

  @forelse($programs as $programGroup)
    @php($program = $programGroup['program'])
    @php($subjects = $programGroup['subjects'])
    @php($programGroupsCount = $subjects->sum(fn($s) => $subjectStats[$s->id]['groups_count'] ?? 0))
    @php($programStudentsCount = $subjects->sum(fn($s) => $subjectStats[$s->id]['students_count'] ?? 0))

    <div class="glass-panel rounded-4 p-4 mb-4">
      <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom" style="border-color: var(--separator-color) !important;">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 56px; height: 56px; background: rgba(197,168,128,0.1); color: var(--accent-color);">
          <i class="bi bi-mortarboard-fill fs-4"></i>
        </div>
        <div class="flex-grow-1">
          <h4 class="fw-bold mb-1" style="color: var(--text-primary);">{{ $program->title ?? 'بدون برنامج' }}</h4>
          <div class="d-flex gap-3 fs-7 text-muted">
            <span><i class="bi bi-journal-bookmark me-1"></i>{{ $subjects->count() }} مادة</span>
            <span><i class="bi bi-people-fill me-1"></i>{{ $programGroupsCount }} مجموعة</span>
            <span><i class="bi bi-person-fill me-1"></i>{{ $programStudentsCount }} طالب</span>
          </div>
        </div>
      </div>

      <div class="row g-3">
        @foreach($subjects as $subject)
          @php($stats = $subjectStats[$subject->id] ?? ['groups_count' => 0, 'students_count' => 0])
          <div class="col-md-6 col-xl-4">
            <a href="{{ route('teacher.subjects.show', $subject) }}" class="text-decoration-none">
              <div class="rounded-4 p-4 h-100 transition-all hover-glow" style="background: var(--bg-secondary); border: 1px solid var(--separator-color);">
                <div class="d-flex align-items-center gap-2 mb-3">
                  <i class="bi bi-book-fill" style="color: var(--accent-color);"></i>
                  <h6 class="fw-bold mb-0" style="color: var(--text-primary);">{{ $subject->name }}</h6>
                </div>
                <div class="d-flex gap-3 fs-7 text-muted">
                  <span><i class="bi bi-people-fill me-1"></i>{{ $stats['groups_count'] }} مجموعة</span>
                  <span><i class="bi bi-person-fill me-1"></i>{{ $stats['students_count'] }} طالب</span>
                </div>
              </div>
            </a>
          </div>
        @endforeach
      </div>
    </div>
  @empty
    <div class="glass-panel rounded-4 p-5 text-center text-muted">
      <i class="bi bi-journal-x fs-1 d-block mb-3 opacity-50"></i>
      <span data-en="No subjects assigned yet." data-ar="لا توجد مواد مسندة إليك بعد.">لا توجد مواد مسندة إليك بعد.</span>
    </div>
  @endforelse

@endsection

@push('styles')
<style>
.hover-glow:hover { box-shadow: 0 10px 25px rgba(197,168,128,0.12); border-color: var(--accent-color) !important; transform: translateY(-2px); }
.hover-glow { transition: all 0.25s ease; }
</style>
@endpush
