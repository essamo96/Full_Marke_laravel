@extends('layouts.teacher')

@section('title', 'Educational Content | FULL MARK ACADEMY')
@section('page_title_en', 'Content')
@section('page_title_ar', 'المحتوى التعليمي')

@section('content')

  <h1 class="h3 fw-bold mb-4" style="color: var(--text-primary);" data-en="Manage Resources" data-ar="إدارة الموارد التعليمية">إدارة الموارد التعليمية</h1>

  <div class="row g-4">
    @forelse($subjects as $subject)
      <div class="col-md-6 col-xl-4">
        <a href="{{ route('teacher.content.manage', $subject) }}" class="text-decoration-none">
          <div class="glass-panel rounded-4 p-4 h-100 transition-all hover-glow" style="border: 1px solid var(--separator-color);">
            <div class="p-3 rounded-circle shadow-sm mb-3 d-inline-flex" style="background: rgba(197, 168, 128, 0.1); color: var(--accent-color);">
              <i class="bi bi-collection-play-fill fs-4"></i>
            </div>
            <h5 class="fw-bold mb-1" style="color: var(--text-primary);">{{ $subject->name }}</h5>
            <p class="text-muted fs-7 mb-0">{{ $subject->program->title ?? '' }}</p>
          </div>
        </a>
      </div>
    @empty
      <div class="col-12">
        <div class="glass-panel rounded-4 p-5 text-center text-muted" data-en="No subjects assigned yet." data-ar="لا توجد مواد مسندة إليك بعد.">لا توجد مواد مسندة إليك بعد.</div>
      </div>
    @endforelse
  </div>

@endsection
