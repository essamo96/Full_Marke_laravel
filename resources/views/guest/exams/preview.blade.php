@extends('layouts.exam')

@section('title', 'معاينة الامتحان: ' . $exam->title)
@section('exam_title', $exam->title)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h3 class="text-white fw-bold m-0">{{ $exam->title }}</h3>
    <div class="d-flex gap-2">
        <a href="{{ route('guest.exam.enter', $exam) }}" class="btn btn-warning rounded-pill">ابدأ الامتحان</a>
        <a href="{{ route('guest.exam.blank-pdf', $exam) }}" class="btn btn-outline-light rounded-pill"><i class="bi bi-file-earmark-pdf me-1"></i> تحميل PDF</a>
    </div>
</div>
@include('exams._preview-body', ['showCorrect' => false])
@endsection
