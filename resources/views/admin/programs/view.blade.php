@extends('layouts.admin')

@section('title', __('app.programs'))

@php($pageTitle = __('app.programs'))

@section('content')
    @include('admin.components.search-filter', ['route' => 'programs.view', 'placeholder' => __('app.program_name_ar')])

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
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th>{{ __('app.image') }}</th>
                        <th>{{ __('app.name') }}</th>
                        <th>{{ __('app.program_type') }}</th>
                        <th>{{ __('app.subjects') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @foreach ($programs as $program)
                        @include('admin.programs.parts.row', ['program' => $program])
                    @endforeach
                </tbody>
            </table>
            {{ $programs->links() }}
        </div>
    </div>
@endsection
