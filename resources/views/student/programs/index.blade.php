@extends('layouts.student')

@section('title', 'Academic Programs | FULL MARK ACADEMY')
@section('page_title_en', 'Programs')
@section('page_title_ar', 'البرامج الأكاديمية')

@section('content')
  <h1 class="h3 fw-bold mb-4" style="color: var(--text-primary);" data-en="Academic Programs" data-ar="البرامج الأكاديمية">Academic Programs</h1>

  @if($programs->isEmpty())
    <div class="glass-panel rounded-4 p-5 text-center opacity-75" data-en="No programs available at the moment." data-ar="لا توجد برامج متاحة حالياً.">No programs available at the moment.</div>
  @else
    <div class="row g-4">
      @foreach($programs as $program)
        <div class="col-lg-6">
          <div class="glass-panel rounded-4 p-4 h-100 d-flex flex-column">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: rgba(197,168,128,0.1); color: var(--accent-color);">
                <i class="bi bi-mortarboard-fill fs-5"></i>
              </div>
              <div>
                <h5 class="fw-bold mb-0" style="color: var(--text-primary);">{{ $program->title }}</h5>
                <span class="text-xs opacity-75">{{ $program->subjects->count() }} <span data-en="subjects" data-ar="مادة">subjects</span></span>
              </div>
            </div>

            @if($program->description)
              <p class="text-sm opacity-75 mb-3">{{ \Illuminate\Support\Str::limit($program->description, 140) }}</p>
            @endif

            @if($program->subjects->isNotEmpty())
              <ul class="list-unstyled d-flex flex-column gap-2 mb-3 flex-grow-1">
                @foreach($program->subjects->take(4) as $subject)
                  <li class="d-flex justify-content-between align-items-center text-sm">
                    <span style="color: var(--text-primary);"><i class="bi bi-book me-1" style="color: var(--accent-color);"></i>{{ $subject->name }}</span>
                    @if($registeredSubjectIds->contains($subject->id))
                      <span class="badge bg-success bg-opacity-25 text-success" data-en="Registered" data-ar="مسجل">Registered</span>
                    @endif
                  </li>
                @endforeach
                @if($program->subjects->count() > 4)
                  <li class="text-xs opacity-50">+ {{ $program->subjects->count() - 4 }} <span data-en="more" data-ar="أخرى">more</span></li>
                @endif
              </ul>
            @endif

            <a href="{{ route('programs.show', $program->slug) }}" class="btn btn-luxury mt-auto py-2" data-en="View Subjects & Register" data-ar="عرض المواد والتسجيل">View Subjects &amp; Register</a>
          </div>
        </div>
      @endforeach
    </div>
  @endif
@endsection
