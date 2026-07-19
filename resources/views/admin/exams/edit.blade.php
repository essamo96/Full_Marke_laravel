@extends('admin.layout.mainLayouts.master')

@section('title', 'تعديل الامتحان')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('exams.view') }}" class="text-muted text-hover-primary">@lang('app.exams')</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">تعديل</li>
@endsection

@section('page-content')
<form action="{{ route('exams.update', $exam) }}" method="POST" id="examForm" class="mt-5">
    @csrf
    @method('PUT')
    
    <div class="d-flex justify-content-end align-items-center mb-5">
        <div>
            <a href="{{ route('exams.view') }}" class="btn btn-light me-2">إلغاء</a>
            <button type="submit" class="btn btn-primary" id="saveExamBtn">
                <i class="ki-duotone ki-save-2 fs-2"></i>حفظ التعديلات
            </button>
        </div>
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

    @include('admin.exams.form')
</form>
@endsection
