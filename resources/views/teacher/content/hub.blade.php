@extends('layouts.teacher')

@section('title', 'Content & Groups | FULL MARK ACADEMY')
@section('page_title_en', 'Content & Groups')
@section('page_title_ar', 'المحتوى والمجموعات')

@section('content')
  <div class="row g-4 g-lg-5">
    <div class="col-xl-7">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 fw-bold mb-0" style="color: var(--text-primary);" data-en="Your Subjects" data-ar="المواد التابعة لك">Your Subjects</h2>
        <a href="{{ route('teacher.content.index') }}" class="btn btn-sm btn-outline-light" data-en="Browse all" data-ar="استعراض الكل">Browse all</a>
      </div>

      <div class="row g-4">
        @forelse($subjects as $subject)
          <div class="col-md-6">
            <a href="{{ route('teacher.content.manage', $subject) }}" class="text-decoration-none">
              <div class="glass-panel rounded-4 p-4 h-100 transition-all hover-glow" style="border: 1px solid var(--separator-color);">
                <div class="d-flex align-items-center gap-3 mb-3">
                  <div class="p-3 rounded-circle shadow-sm" style="background: rgba(197, 168, 128, 0.1); color: var(--accent-color);">
                    <i class="bi bi-journal-richtext fs-4"></i>
                  </div>
                  <div>
                    <h5 class="fw-bold mb-1" style="color: var(--text-primary);">{{ $subject->name }}</h5>
                    <p class="text-muted fs-7 mb-0">{{ $subject->program->title ?? '' }}</p>
                  </div>
                </div>
                <p class="text-muted mb-0" data-en="Open the content builder for this subject and add lessons, units, and resources." data-ar="افتح منشئ المحتوى لهذه المادة وأضف الوحدات والدروس والمرفقات.">Open the content builder for this subject and add lessons, units, and resources.</p>
              </div>
            </a>
          </div>
        @empty
          <div class="col-12">
            <div class="glass-panel rounded-4 p-5 text-center text-muted" data-en="No subjects assigned yet." data-ar="لا توجد مواد مسندة إليك بعد.">No subjects assigned yet.</div>
          </div>
        @endforelse
      </div>
    </div>

    <div class="col-xl-5">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 fw-bold mb-0" style="color: var(--text-primary);" data-en="Your Groups" data-ar="مجموعاتك">Your Groups</h2>
        <a href="{{ route('teacher.groups.index') }}" class="btn btn-sm btn-outline-light" data-en="View all" data-ar="عرض الكل">View all</a>
      </div>

      <div class="d-flex flex-column gap-3">
        @forelse($groups as $group)
          <a href="{{ route('teacher.groups.show',
            Illuminate\Support\Facades\Crypt::encryptString($group->id)) }}" class="text-decoration-none">
            <div class="glass-panel rounded-4 p-4 transition-all hover-glow" style="border: 1px solid var(--separator-color);">
              <div class="d-flex justify-content-between align-items-start gap-3">
                <div>
                  <h5 class="fw-bold mb-2" style="color: var(--text-primary);">{{ $group->name }}</h5>
                  <p class="text-muted mb-1 fs-7">{{ $group->subject->name ?? '-' }} · {{ $group->subject->program->title ?? '-' }}</p>
                  <p class="text-muted mb-0 fs-7">
                    <i class="bi bi-people me-1"></i> {{ $group->students_count }} students
                  </p>
                </div>
                <span class="badge bg-gold text-dark">{{ $group->is_active ? 'Active' : 'Inactive' }}</span>
              </div>
            </div>
          </a>
        @empty
          <div class="glass-panel rounded-4 p-5 text-center text-muted" data-en="No groups assigned yet." data-ar="لا توجد مجموعات مسندة إليك بعد.">No groups assigned yet.</div>
        @endforelse
      </div>
    </div>
  </div>
@endsection
