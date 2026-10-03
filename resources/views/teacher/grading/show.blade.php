@extends('layouts.teacher')

@section('title', 'Review Submission | FULL MARK ACADEMY')
@section('page_title_en', 'Review Submission')
@section('page_title_ar', 'مراجعة الإجابات')

@section('content')

  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
      <h1 class="h3 fw-bold mb-1" style="color: var(--text-primary);">{{ $grade->student?->full_name_ar ?? $grade->student?->full_name_en }}</h1>
      <p class="text-muted mb-0">{{ $grade->exam?->title }}</p>
    </div>
    <div class="d-flex gap-3 flex-wrap">
      <div class="glass-panel rounded-4 px-4 py-2">
        <span class="fw-bold" style="color: var(--accent-color);">{{ $grade->score }} / {{ $grade->max_score }}</span>
      </div>
      @if($grade->tab_switch_count > 0 || $grade->fullscreen_exit_count > 0)
        <div class="glass-panel rounded-4 px-4 py-2" title="عدد مرات الخروج من الصفحة / وضع ملء الشاشة">
          <i class="bi bi-eye-fill text-warning me-1"></i>
          <span class="fw-bold text-warning">{{ $grade->tab_switch_count + $grade->fullscreen_exit_count }}</span>
          <span class="fs-7 text-muted">مرات مغادرة</span>
        </div>
      @endif
      @if($grade->auto_submitted)
        <div class="glass-panel rounded-4 px-4 py-2">
          <span class="badge bg-danger">تسليم تلقائي (مخالفة)</span>
        </div>
      @endif
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="glass-panel rounded-4 p-3 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
    @if($grade->admin_approved_at)
      <span class="badge bg-success px-3 py-2"><i class="bi bi-check2-all me-1"></i> معتمدة من الإدارة — يمكنك دائماً معاينة الإجابات والأخطاء</span>
    @elseif($grade->teacher_reviewed_at)
      <span class="badge bg-warning text-dark px-3 py-2"><i class="bi bi-hourglass-split me-1"></i> بانتظار اعتماد الإدارة</span>
    @else
      <span class="badge bg-secondary px-3 py-2">لم تتم المراجعة بعد</span>
    @endif

    <div class="d-flex gap-2">
      <a href="{{ route('teacher.grading.exam', $grade->exam_id) }}" class="btn btn-sm btn-outline-secondary">رجوع</a>
      @if(!$grade->teacher_reviewed_at)
        <form method="POST" action="{{ route('teacher.grading.approve', $grade) }}">
          @csrf
          <button type="submit" class="btn btn-luxury btn-sm"><i class="bi bi-send-check-fill me-1"></i> اعتماد وإرسال للإدارة</button>
        </form>
      @endif
    </div>
  </div>

  <div class="mb-3 text-muted fs-7">
    <i class="bi bi-info-circle me-1"></i>
    الأخضر = إجابة صحيحة · الأحمر = إجابة الطالب الخاطئة
  </div>

  @include('partials.exam-answers-review', ['grade' => $grade, 'showCorrect' => true, 'compact' => true])

  {{-- Essay grading controls kept separate for interactive marking --}}
  @foreach($grade->answers as $answer)
    @if($answer->question?->type === 'essay' && ! $grade->admin_approved_at)
      <div class="glass-panel rounded-4 p-3 mb-3">
        <div class="fw-bold mb-2" style="color: var(--text-primary);">تصحيح سؤال مقالي</div>
        <div class="mb-2 p-2 rounded" style="background: var(--bg-secondary); color: var(--text-secondary);">{!! $answer->question?->content !!}</div>
        <div class="mb-2 small text-muted">إجابة الطالب: {{ $answer->essay_answer ?: '—' }}</div>
        <form method="POST" action="{{ route('teacher.grading.grade-essay', $answer) }}" class="d-flex align-items-center gap-2">
          @csrf
          <label class="fs-7 text-muted">النقاط</label>
          <input type="number" name="points_earned" step="0.01" min="0" max="{{ $answer->question->points }}" value="{{ $answer->points_earned }}" class="form-control form-control-sm" style="max-width: 120px;">
          <button type="submit" class="btn btn-luxury btn-sm">حفظ</button>
        </form>
      </div>
    @endif
  @endforeach

@endsection

@push('styles')
<style>
.content-area img { max-width: 100%; height: auto; border-radius: 0.5rem; }
</style>
@endpush
