@extends('layouts.teacher')

@section('title', 'New Exam | FULL MARK ACADEMY')
@section('page_title_en', 'New Exam')
@section('page_title_ar', 'امتحان جديد')

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
@endpush

{{-- The exam builder form (admin.exams.form) is shared with the admin panel
     and was built against Metronic's own light theme — it has no awareness
     of this panel's dark/light/gold theme system, so without this it always
     renders as a plain white Bootstrap card no matter which theme is active. --}}
@push('styles')
<style>
    #examForm .card {
        background: var(--bg-secondary);
        color: var(--text-primary);
        border: 1px solid var(--separator-color);
    }
    #examForm .card-header {
        border-bottom: 1px solid var(--separator-color);
    }
    #examForm .card-title,
    #examForm .card-title h2,
    #examForm label,
    #examForm .form-label {
        color: var(--text-primary);
    }
    #examForm .form-control,
    #examForm .form-select {
        background: var(--input-bg);
        color: var(--text-primary);
        border: 1px solid var(--separator-color);
    }
    #examForm .form-control:focus,
    #examForm .form-select:focus {
        background: var(--input-bg);
        color: var(--text-primary);
        border-color: var(--accent-color);
        box-shadow: none;
    }
    #examForm .form-control::placeholder {
        color: var(--text-muted);
    }
    #examForm .text-muted {
        color: var(--text-muted) !important;
    }
    #examForm .question-card {
        background: var(--bg-tertiary);
        border-color: var(--separator-color) !important;
    }
    #examForm .card-header.bg-light-secondary {
        background: var(--bg-secondary) !important;
    }
    /* select2 (auto-initialized by Metronic's own JS elsewhere, but degrades
       gracefully to a native <select> here since that JS isn't loaded on this
       panel) still needs its native rendering to match the theme. */
    #examForm select.form-select option {
        background: var(--input-bg);
        color: var(--text-primary);
    }
</style>
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
        'preselectedGroupId' => $preselectedGroupId ?? null,
        'preselectedSubjectId' => $preselectedSubjectId ?? null,
    ])
</form>
@endsection
