@extends('layouts.teacher')

@section('title', 'إدارة المحتوى - ' . $subject->name)
@section('page_title_en', 'Manage Content')
@section('page_title_ar', 'إدارة المحتوى')

{{--
    The whole screen (units, lessons, resources, sharing, exclusions, kill switch, undo) is the
    shared resource-library workspace - the same one the admin uses.
--}}
@section('content')
  @include('resource-library.workspace', ['role' => 'teacher', 'mode' => 'manager', 'config' => $config])
@endsection
