@extends('admin.layout.mainLayouts.master')
@section('title', 'إدارة المحتوى التعليمي - ' . $subject->name_ar)

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('subject_content.view') }}" class="text-muted text-hover-primary">المحتوى التعليمي</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-dark">{{ $subject->name_ar }}</li>
@endsection

{{--
    The whole screen (units, lessons, resources, sharing, exclusions, kill switch, undo) is the
    shared resource-library workspace - the same one every teacher uses.
--}}
@section('page-content')
    @include('resource-library.workspace', ['role' => 'admin', 'mode' => 'manager', 'config' => $config])
@endsection
