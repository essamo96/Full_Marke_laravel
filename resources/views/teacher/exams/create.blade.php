@extends('layouts.teacher')

@section('title', 'New Exam | FULL MARK ACADEMY')
@section('page_title_en', 'New Exam')
@section('page_title_ar', 'امتحان جديد')

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
@endpush

@section('content')
<form action="{{ route('teacher.exams.store') }}" method="POST" id="examForm">
    @csrf

    <div class="d-flex justify-content-end align-items-center mb-4 gap-2">
        <a href="{{ route('teacher.exams.index') }}" class="btn btn-glass" data-en="Cancel" data-ar="إلغاء">إلغاء</a>
        <button type="submit" class="btn btn-luxury" data-en="Save Exam" data-ar="حفظ الامتحان">حفظ الامتحان</button>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @include('admin.exams.form', [
        'subjectGroupsAjaxBase' => '/teacher/exams/ajax/subject',
        'groupStudentsAjaxBase' => '/teacher/exams/ajax/group',
        'examReorderRouteName' => 'teacher.exams.reorder-questions',
    ])
</form>
@endsection
