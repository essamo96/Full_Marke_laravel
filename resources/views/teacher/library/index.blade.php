@extends('layouts.teacher')

@section('title', 'مكتبة الموارد | FULL MARK ACADEMY')
@section('page_title_en', 'Resource Library')
@section('page_title_ar', 'مكتبة الموارد')

@section('content')
  @include('resource-library.workspace', ['role' => 'teacher', 'mode' => 'library', 'config' => $config])
@endsection
