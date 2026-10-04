@extends('admin.layout.mainLayouts.master')

@section('title', 'معاينة الامتحان: ' . $exam->title)

@section('breadcrumb')
    <li class="breadcrumb-item text-muted"><a href="{{ route('exams.view') }}" class="text-muted text-hover-primary">الامتحانات</a></li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">معاينة</li>
@endsection

@section('page-content')
<div class="card card-flush mt-5">
    <div class="card-header align-items-center py-5 gap-2">
        <div class="card-title flex-column">
            <h3 class="fw-bold mb-1">{{ $exam->title }}</h3>
            <div class="fs-6 text-muted">{{ $exam->subject->name ?? '' }} · {{ $exam->group->name ?? '' }} · {{ $exam->questions->count() }} سؤال</div>
        </div>
        <div class="card-toolbar">
            <a href="{{ $pdfRoute }}" class="btn btn-danger"><i class="bi bi-file-earmark-pdf me-1"></i> تحميل نموذج فارغ PDF</a>
        </div>
    </div>
    <div class="card-body pt-0">
        <div class="alert alert-info">الإجابة الصحيحة باللون الأخضر، والأرقام بجانب كل خيار تمثل عدد مرات اختيار الطلاب/الضيوف له.</div>
        @include('exams._preview-body', ['showCorrect' => true])
    </div>
</div>
@endsection
