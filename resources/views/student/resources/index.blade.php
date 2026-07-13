@extends('layouts.student')

@section('title', 'Learning Resources | FULL MARK ACADEMY')
@section('page_title_en', 'Resources')
@section('page_title_ar', 'الموارد التعليمية')

@section('content')
  <h1 class="h3 fw-bold mb-4" style="color: var(--text-primary);" data-en="Learning Resources" data-ar="الموارد التعليمية">Learning Resources</h1>

  @if($resourcesBySubject->isEmpty())
    <div class="glass-panel rounded-4 p-5 text-center">
      <i class="bi bi-collection-play fs-1 mb-3 d-block" style="color: var(--accent-color);"></i>
      <p class="opacity-75 mb-3" data-en="No learning resources have been published for your subjects yet." data-ar="لا توجد موارد تعليمية منشورة لموادك حتى الآن.">No learning resources have been published for your subjects yet.</p>
      <a href="{{ route('student.registrations') }}" class="btn btn-glass px-4 py-2" data-en="View My Subjects" data-ar="عرض موادي">View My Subjects</a>
    </div>
  @else
    @foreach($resourcesBySubject as $subjectId => $resources)
      @php $subject = $resources->first()->subject; @endphp
      <div class="glass-panel rounded-4 p-4 mb-4">
        <h5 class="fw-bold mb-3" style="color: var(--text-primary);">{{ $subject->name }}</h5>
        <div class="row g-3">
          @foreach($resources as $resource)
            @php
              $icon = match($resource->type) {
                'video' => 'play-circle-fill',
                'document' => 'file-earmark-text-fill',
                'zoom' => 'camera-video-fill',
                default => 'link-45deg',
              };
            @endphp
            <div class="col-md-6">
              <a href="{{ $resource->url }}" target="_blank" rel="noopener" class="d-flex align-items-center gap-3 p-3 rounded-3 text-decoration-none h-100" style="background: var(--bg-secondary); border: 1px solid var(--separator-color);">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; background: rgba(197,168,128,0.1); color: var(--accent-color);">
                  <i class="bi bi-{{ $icon }} fs-5"></i>
                </div>
                <div class="flex-grow-1">
                  <div class="fw-bold" style="color: var(--text-primary);">{{ $resource->title }}</div>
                  @if($resource->description)
                    <div class="text-xs opacity-75">{{ $resource->description }}</div>
                  @endif
                </div>
                <i class="bi bi-box-arrow-up-right opacity-50"></i>
              </a>
            </div>
          @endforeach
        </div>
      </div>
    @endforeach
  @endif
@endsection
