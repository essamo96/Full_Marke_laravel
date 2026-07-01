@extends('layouts.admin')

@section('title', __('app.users'))

@php($pageTitle = __('app.users'))

@section('content')
    @include('admin.components.search-filter', ['route' => 'users.view', 'placeholder' => __('app.email'), 'datatable' => true])

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title"><h2>{{ __('app.users') }}</h2></div>
            <div class="card-toolbar">
                <a href="{{ route('users.add') }}" class="btn btn-primary">
                    <i class="ki-duotone ki-plus fs-2"></i> {{ __('app.add_new') }}
                </a>
            </div>
        </div>
        <div class="card-body py-4">
            <table id="users_table" class="table align-middle table-row-dashed gy-5 admin-datatable">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold text-uppercase gs-0">
                        <th>{{ __('app.photo') }}</th>
                        <th>{{ __('app.name') }}</th>
                        <th>{{ __('app.email') }}</th>
                        <th>{{ __('app.role') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600"></tbody>
            </table>
        </div>
    </div>

    @include('admin.components.datatable-init', [
        'tableId' => 'users_table',
        'ajaxUrl' => route('users.list'),
        'columns' => [
            ['data' => 'photo', 'name' => 'photo', 'title' => __('app.photo')],
            ['data' => 'name', 'name' => 'name', 'title' => __('app.name')],
            ['data' => 'email', 'name' => 'email', 'title' => __('app.email')],
            ['data' => 'role', 'name' => 'role', 'title' => __('app.role')],
            ['data' => 'status', 'name' => 'status', 'title' => __('app.status')],
            ['data' => 'actions', 'name' => 'actions', 'title' => __('app.actions'), 'className' => 'text-end'],
        ],
    ])
@endsection
