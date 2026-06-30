@extends('layouts.admin')

@section('title', __('app.study_branches'))

@php($pageTitle = __('app.study_branches'))

@section('content')
    @include('admin.components.search-filter', ['route' => 'study_branches.view', 'placeholder' => __('app.study_branch_name_ar')])

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title"><h2>{{ __('app.study_branches') }}</h2></div>
            <div class="card-toolbar">
                <a href="{{ route('study_branches.add') }}" class="btn btn-primary">
                    <i class="ki-duotone ki-plus fs-2"></i> {{ __('app.add_new') }}
                </a>
            </div>
        </div>
        <div class="card-body py-4">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th>{{ __('app.name') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @foreach ($studyBranches as $studyBranch)
                        @include('admin.study_branches.parts.row', ['studyBranch' => $studyBranch])
                    @endforeach
                </tbody>
            </table>
            {{ $studyBranches->links() }}
        </div>
    </div>
@endsection
