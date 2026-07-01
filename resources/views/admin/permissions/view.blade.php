@extends('layouts.admin')

@section('title', __('app.permissions'))

@php($pageTitle = __('app.permissions'))

@section('content')
    @include('admin.components.search-filter', ['route' => 'permissions.view', 'placeholder' => __('app.name'), 'datatable' => true])

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title"><h2>{{ __('app.permissions') }}</h2></div>
            <div class="card-toolbar">
                <a href="{{ route('permissions.add') }}" class="btn btn-primary">
                    <i class="ki-duotone ki-plus fs-2"></i> {{ __('app.add_new') }}
                </a>
            </div>
        </div>
        <div class="card-body py-4">
            <table id="permissions_table" class="table align-middle table-row-dashed gy-5 admin-datatable">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold text-uppercase gs-0">
                        <th>{{ __('app.name') }}</th>
                        <th>{{ __('app.permissions') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600"></tbody>
            </table>
        </div>
    </div>

    @include('admin.components.datatable-init', [
        'tableId' => 'permissions_table',
        'ajaxUrl' => route('permissions.list'),
        'columns' => [
            ['data' => 'name', 'name' => 'name', 'title' => __('app.name')],
            ['data' => 'permissions', 'name' => 'permissions', 'title' => __('app.permissions')],
            ['data' => 'actions', 'name' => 'actions', 'title' => __('app.actions'), 'className' => 'text-end'],
        ],
    ])
@endsection
