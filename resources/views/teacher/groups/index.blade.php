@extends('layouts.teacher')

@section('title', 'My Groups | FULL MARK ACADEMY')
@section('page_title_en', 'Study Groups')
@section('page_title_ar', 'المجموعات الدراسية')

@section('content')

  <h1 class="h3 fw-bold mb-4" style="color: var(--text-primary);" data-en="Study Groups" data-ar="المجموعات الدراسية">المجموعات الدراسية</h1>

  <div class="row g-4">
    @forelse($groups as $group)
      <div class="col-md-6 col-xl-4">
        <div class="glass-panel rounded-4 p-4 h-100" style="border: 1px solid var(--separator-color);">
          <h5 class="fw-bold mb-1" style="color: var(--text-primary);">{{ $group->name }}</h5>
          <div class="text-muted fs-7 mb-2">{{ $group->subject->name ?? '' }} — {{ $group->subject->program->title ?? '' }}</div>
          <div class="text-muted fs-7 mb-3"><i class="bi bi-people-fill me-1"></i>{{ $group->students_count }} <span data-en="students" data-ar="طالب">طالب</span></div>
          <a href="{{ route('teacher.groups.show', $group) }}" class="btn btn-luxury btn-sm" data-en="Open" data-ar="فتح">فتح</a>
        </div>
      </div>
    @empty
      <div class="col-12">
        <div class="glass-panel rounded-4 p-5 text-center text-muted" data-en="No groups assigned to you yet." data-ar="لا توجد مجموعات مسندة إليك بعد.">لا توجد مجموعات مسندة إليك بعد.</div>
      </div>
    @endforelse
  </div>

@endsection
