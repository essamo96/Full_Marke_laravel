@extends('admin.layout.mainLayouts.master')

@section('title', 'مكتبة المرفقات')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('resource-library.view') }}" class="text-muted text-hover-primary">مكتبة المرفقات</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-dark">كل الموارد والتوزيع على المجموعات</li>
@endsection

{{--
    The library is the shared workspace in "library" mode: one table of every resource with the
    distribution matrix (resource x group), the kill switch, sharing, exclusions, quality check
    and undo - the same controls the teacher has, over all subjects and groups.
--}}
@section('page-content')
    @include('resource-library.workspace', ['role' => 'admin', 'mode' => 'library', 'config' => $config])
@endsection
