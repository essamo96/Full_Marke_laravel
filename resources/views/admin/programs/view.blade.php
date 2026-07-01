@extends('layouts.admin')

@section('title', __('app.programs'))

@php($pageTitle = __('app.programs'))

@section('content')
    @include('admin.components.search-filter', ['route' => 'programs.view', 'placeholder' => __('app.program_name_ar'), 'datatable' => true])

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title"><h2>{{ __('app.programs') }}</h2></div>
            <div class="card-toolbar">
                <a href="{{ route('programs.add') }}" class="btn btn-primary">
                    <i class="ki-duotone ki-plus fs-2"></i> {{ __('app.add_new') }}
                </a>
            </div>
        </div>
        <div class="card-body py-4">
            <table id="programs_table" class="table align-middle table-row-dashed gy-5 admin-datatable">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold text-uppercase gs-0">
                        <th>{{ __('app.image') }}</th>
                        <th>{{ __('app.name') }}</th>
                        <th>{{ __('app.program_type') }}</th>
                        <th>{{ __('app.subjects') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600"></tbody>
            </table>
        </div>
    </div>

    @include('admin.components.datatable-init', [
        'tableId' => 'programs_table',
        'ajaxUrl' => route('programs.list'),
        'columns' => [
            ['data' => 'image', 'name' => 'image', 'title' => __('app.image')],
            ['data' => 'name', 'name' => 'title_ar', 'title' => __('app.name')],
            ['data' => 'type', 'name' => 'type', 'title' => __('app.program_type')],
            ['data' => 'subjects_count', 'name' => 'subjects_count', 'title' => __('app.subjects')],
            ['data' => 'status', 'name' => 'is_active', 'title' => __('app.status')],
            ['data' => 'actions', 'name' => 'actions', 'title' => __('app.actions'), 'className' => 'text-end'],
        ],
    ])
@endsection
