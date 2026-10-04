@extends('layouts.teacher')

@section('title', 'Exam Preview | FULL MARK ACADEMY')
@section('page_title_en', 'Exam Preview')
@section('page_title_ar', 'معاينة الامتحان')

@section('content')
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <h1 class="h3 fw-bold mb-0" style="color: var(--text-primary);">{{ $exam->title }}</h1>
      <div class="text-muted">{{ $exam->subject->name ?? '' }} · {{ $exam->group->name ?? '' }}</div>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('teacher.exams.index') }}" class="btn btn-outline-secondary" data-en="Back" data-ar="رجوع">رجوع</a>
      <a href="{{ route('teacher.exams.blank-pdf', $exam) }}" class="btn btn-luxury"><i class="bi bi-file-earmark-pdf me-1"></i> <span data-en="Download blank PDF" data-ar="تحميل نموذج فارغ PDF">تحميل نموذج فارغ PDF</span></a>
    </div>
  </div>
  @include('exams._preview-body', ['showCorrect' => true, 'stats' => $stats])
@endsection
