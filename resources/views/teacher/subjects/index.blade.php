@extends('layouts.teacher')

@section('title', 'My Subjects | FULL MARK ACADEMY')
@section('page_title_en', 'Programs & Subjects')
@section('page_title_ar', 'البرامج والمواد')

@section('content')

  <h1 class="h3 fw-bold mb-4" style="color: var(--text-primary);" data-en="Programs & Subjects" data-ar="البرامج والمواد">البرامج والمواد</h1>

  @forelse($subjects as $programName => $programSubjects)
    <div class="mb-5">
      <h3 class="h5 fw-bold mb-3 border-start border-4 ps-3" style="border-color: var(--accent-color) !important; color: var(--text-primary);">{{ $programName }}</h3>
      <div class="row g-4">
        @foreach($programSubjects as $subject)
          <div class="col-md-6 col-xl-4">
            <a href="{{ route('teacher.subjects.show', $subject) }}" class="text-decoration-none">
              <div class="glass-panel rounded-4 p-4 h-100 transition-all hover-glow" style="border: 1px solid var(--separator-color);">
                <div class="p-3 rounded-circle shadow-sm mb-3 d-inline-flex" style="background: rgba(197, 168, 128, 0.1); color: var(--accent-color);">
                  <i class="bi bi-journal-bookmark-fill fs-4"></i>
                </div>
                <h5 class="fw-bold mb-1" style="color: var(--text-primary);">{{ $subject->name }}</h5>
                <p class="text-muted fs-7 mb-0">{{ $subject->description }}</p>
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
