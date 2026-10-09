@extends('layouts.teacher')

@section('title', 'Exam Preview | FULL MARK ACADEMY')
@section('page_title_en', 'Exam Preview')
@section('page_title_ar', 'معاينة الامتحان')

@section('content')
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <h1 class="h3 fw-bold mb-0" style="color: var(--text-primary);">{{ $exam->title }}</h1>
      <div class="text-muted">{{ $exam->subject->name ?? '' }} · {{ $exam->groupNames() }}</div>
    </div>
    <div class="tbtn-group">
      <a href="{{ route('teacher.exams.index') }}" class="tbtn tbtn--neutral"><i class="bi bi-arrow-right"></i><span data-en="Back" data-ar="رجوع">رجوع</span></a>
      <a href="{{ route('teacher.exams.blank-pdf', $exam) }}" class="tbtn tbtn--solid tbtn--pdf"><i class="bi bi-file-earmark-pdf"></i><span data-en="Download blank PDF" data-ar="تحميل نموذج فارغ PDF">تحميل نموذج فارغ PDF</span></a>
    </div>
  </div>
  @include('exams._preview-body', ['showCorrect' => true, 'stats' => $stats])
@endsection
