@extends('layouts.admin')

@section('title', __('app.dashboard'))

@php($pageTitle = __('app.dashboard'))

@section('content')

    <div class="row g-5 g-xl-8 mb-5 mb-xl-8">
        <div class="col-xl-3">
            <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-100"
                 style="background-color:#009ef7;background-image:url('{{ asset('assets/media/patterns/vector-1.png') }}')">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ $stats['students'] }}</span>
                        <span class="text-white opacity-75 pt-1 fw-semibold fs-6">{{ __('app.students') }}</span>
                    </div>
                </div>
                <div class="card-body d-flex align-items-end pt-0">
                    <span class="text-white opacity-75 fw-semibold fs-7">{{ $stats['students_active'] }} {{ __('app.active') }}</span>
                </div>
            </div>
        </div>

        <div class="col-xl-3">
            <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-100"
                 style="background-color:#50cd89;background-image:url('{{ asset('assets/media/patterns/vector-1.png') }}')">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ $stats['teachers'] }}</span>
                        <span class="text-white opacity-75 pt-1 fw-semibold fs-6">{{ __('app.teachers') }}</span>
                    </div>
                </div>
                <div class="card-body d-flex align-items-end pt-0">
                    <span class="text-white opacity-75 fw-semibold fs-7">{{ $stats['teachers_active'] }} {{ __('app.active') }}</span>
                </div>
            </div>
        </div>

        <div class="col-xl-3">
            <div class="card card-flush h-md-100">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2">{{ $stats['branches'] }}</span>
                        <span class="text-gray-400 pt-1 fw-semibold fs-6">{{ __('app.branches') }}</span>
                    </div>
                </div>
                <div class="card-body d-flex align-items-end pt-0">
                    <span class="symbol symbol-40px symbol-circle">
                        <span class="symbol-label bg-light-primary">
                            <i class="ki-duotone ki-bank fs-2 text-primary">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                <span class="path4"></span><span class="path5"></span><span class="path6"></span>
                            </i>
                        </span>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-xl-3">
            <div class="card card-flush h-md-100">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2">{{ $stats['admins'] }}</span>
                        <span class="text-gray-400 pt-1 fw-semibold fs-6">{{ __('app.admins') }}</span>
                    </div>
                </div>
                <div class="card-body d-flex align-items-end pt-0">
                    <span class="symbol symbol-40px symbol-circle">
                        <span class="symbol-label bg-light-warning">
                            <i class="ki-duotone ki-security-user fs-2 text-warning">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                        </span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-5 g-xl-8">
        <div class="col-xl-4">
            <a href="{{ route('users.view') }}" class="card card-flush h-100 hoverable">
                <div class="card-body d-flex flex-column justify-content-between">
                    <span class="symbol symbol-50px symbol-circle mb-5">
                        <span class="symbol-label bg-light-info">
                            <i class="ki-duotone ki-people fs-1 text-info">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                <span class="path4"></span><span class="path5"></span>
                            </i>
                        </span>
                    </span>
                    <div>
                        <div class="fs-4 fw-bold text-gray-900 mb-1">{{ __('app.users') }}</div>
                        <div class="fs-7 text-gray-500">{{ __('app.manage_admin_users') }}</div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-4">
            <a href="{{ route('permissions.view') }}" class="card card-flush h-100 hoverable">
                <div class="card-body d-flex flex-column justify-content-between">
                    <span class="symbol symbol-50px symbol-circle mb-5">
                        <span class="symbol-label bg-light-success">
                            <i class="ki-duotone ki-shield-tick fs-1 text-success">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                        </span>
                    </span>
                    <div>
                        <div class="fs-4 fw-bold text-gray-900 mb-1">{{ __('app.permissions') }}</div>
                        <div class="fs-7 text-gray-500">{{ $stats['roles'] }} {{ __('app.roles') }}</div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-4">
            <a href="{{ route('sidebar.view') }}" class="card card-flush h-100 hoverable">
                <div class="card-body d-flex flex-column justify-content-between">
                    <span class="symbol symbol-50px symbol-circle mb-5">
                        <span class="symbol-label bg-light-danger">
                            <i class="ki-duotone ki-element-11 fs-1 text-danger">
                                <span class="path1"></span><span class="path2"></span>
                                <span class="path3"></span><span class="path4"></span>
                            </i>
                        </span>
                    </span>
                    <div>
                        <div class="fs-4 fw-bold text-gray-900 mb-1">{{ __('app.sidebar') }}</div>
                        <div class="fs-7 text-gray-500">{{ $stats['permission_groups'] }} {{ __('app.modules') }}</div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    @push('styles')
        <style>
            .hoverable { transition: transform .2s ease, box-shadow .2s ease; }
            .hoverable:hover { transform: translateY(-3px); box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1); }
        </style>
    @endpush

@endsection
