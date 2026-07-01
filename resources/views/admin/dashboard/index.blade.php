@extends('layouts.admin')

@section('title', __('app.dashboard'))

@php($pageTitle = __('app.dashboard'))

@section('content')

    {{-- Stat cards --}}
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

    {{-- Academy overview hero + trend chart --}}
    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        {{-- Overview tile widget (ported from Metronic "Lists Widget 19") --}}
        <div class="col-xl-4 mb-xl-10">
            <div class="card card-flush h-xl-100">
                <div class="card-header rounded bgi-no-repeat bgi-size-cover bgi-position-y-top bgi-position-x-center align-items-start h-200px"
                     style="background-image:url('{{ asset('assets/media/svg/shapes/top-green.png') }}')" data-bs-theme="light">
                    <h3 class="card-title align-items-start flex-column text-white pt-15">
                        <span class="fw-bold fs-2x mb-3">{{ __('app.quick_links') }}</span>
                        <div class="fs-6 text-white opacity-75">{{ __('app.dashboard') }}</div>
                    </h3>
                </div>
                <div class="card-body mt-n20">
                    <div class="mt-n20 position-relative">
                        <div class="row g-3 g-lg-6">
                            <div class="col-6">
                                <div class="bg-gray-100 bg-opacity-70 rounded-2 px-6 py-5">
                                    <div class="symbol symbol-30px me-5 mb-8">
                                        <span class="symbol-label">
                                            <i class="ki-duotone ki-teacher fs-1 text-primary">
                                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                            </i>
                                        </span>
                                    </div>
                                    <div class="m-0">
                                        <span class="text-gray-700 fw-bolder d-block fs-2qx lh-1 ls-n1 mb-1">{{ $stats['programs'] }}</span>
                                        <span class="text-gray-500 fw-semibold fs-6">{{ __('app.programs') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-gray-100 bg-opacity-70 rounded-2 px-6 py-5">
                                    <div class="symbol symbol-30px me-5 mb-8">
                                        <span class="symbol-label">
                                            <i class="ki-duotone ki-book fs-1 text-primary">
                                                <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span>
                                            </i>
                                        </span>
                                    </div>
                                    <div class="m-0">
                                        <span class="text-gray-700 fw-bolder d-block fs-2qx lh-1 ls-n1 mb-1">{{ $stats['subjects'] }}</span>
                                        <span class="text-gray-500 fw-semibold fs-6">{{ __('app.subjects') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-gray-100 bg-opacity-70 rounded-2 px-6 py-5">
                                    <div class="symbol symbol-30px me-5 mb-8">
                                        <span class="symbol-label">
                                            <i class="ki-duotone ki-people fs-1 text-primary">
                                                <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span>
                                            </i>
                                        </span>
                                    </div>
                                    <div class="m-0">
                                        <span class="text-gray-700 fw-bolder d-block fs-2qx lh-1 ls-n1 mb-1">{{ $stats['groups'] }}</span>
                                        <span class="text-gray-500 fw-semibold fs-6">{{ __('app.groups') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-gray-100 bg-opacity-70 rounded-2 px-6 py-5">
                                    <div class="symbol symbol-30px me-5 mb-8">
                                        <span class="symbol-label">
                                            <i class="ki-duotone ki-document fs-1 text-primary">
                                                <span class="path1"></span><span class="path2"></span>
                                            </i>
                                        </span>
                                    </div>
                                    <div class="m-0">
                                        <span class="text-gray-700 fw-bolder d-block fs-2qx lh-1 ls-n1 mb-1">{{ $stats['applications_pending'] }}</span>
                                        <span class="text-gray-500 fw-semibold fs-6">{{ __('app.pending') }} {{ __('app.applications') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- New students trend chart (ApexCharts) --}}
        <div class="col-xl-8 mb-5 mb-xl-10">
            <div class="card card-flush h-xl-100">
                <div class="card-header pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">{{ __('app.new_students_last_7_days') }}</span>
                        <span class="text-gray-400 mt-1 fw-bold fs-7">{{ $stats['registrations_active'] }} {{ __('app.registrations') }} {{ __('app.active') }}</span>
                    </h3>
                </div>
                <div class="card-body py-6">
                    <div id="kt_dashboard_students_chart" style="height: 320px"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Top programs / Active groups / Recent applications --}}
    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        {{-- Top programs --}}
        <div class="col-xl-4 mb-xl-10">
            <div class="card h-xl-100">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-dark">{{ __('app.top_programs') }}</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">{{ __('app.top_programs_desc') }}</span>
                    </h3>
                    <div class="card-toolbar">
                        <a href="{{ route('programs.view') }}" class="btn btn-sm btn-light">{{ __('app.view_all') }}</a>
                    </div>
                </div>
                <div class="card-body pt-6">
                    @forelse($topPrograms as $index => $program)
                        @php($colors = ['danger', 'success', 'info', 'primary', 'warning'])
                        @php($color = $colors[$index % count($colors)])
                        <div class="d-flex flex-stack">
                            <div class="symbol symbol-40px me-4">
                                <div class="symbol-label fs-2 fw-semibold bg-{{ $color }} text-inverse-{{ $color }}">
                                    {{ mb_substr($program->title, 0, 1) }}
                                </div>
                            </div>
                            <div class="d-flex align-items-center flex-row-fluid flex-wrap">
                                <div class="flex-grow-1 me-2">
                                    <span class="text-gray-800 fs-6 fw-bold">{{ $program->title }}</span>
                                    <span class="text-muted fw-semibold d-block fs-7">{{ $program->subjects_count }} {{ __('app.subjects') }}</span>
                                </div>
                                <a href="{{ route('programs.view') }}" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                    <i class="ki-duotone ki-arrow-left fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </a>
                            </div>
                        </div>
                        @if (!$loop->last)
                            <div class="separator separator-dashed my-4"></div>
                        @endif
                    @empty
                        <div class="text-center text-muted py-10">{{ __('app.no_data') }}</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Active groups by capacity --}}
        <div class="col-xl-4 mb-xl-10">
            <div class="card card-flush h-xl-100">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-dark">{{ __('app.active_groups') }}</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">{{ __('app.active_groups_desc') }}</span>
                    </h3>
                    <div class="card-toolbar">
                        <a href="{{ route('groups.view') }}" class="btn btn-sm btn-light">{{ __('app.view_all') }}</a>
                    </div>
                </div>
                <div class="card-body pt-5">
                    @forelse($topGroups as $group)
                        @php($percent = $group->max_capacity > 0 ? min(100, (int) round(($group->current_count / $group->max_capacity) * 100)) : 0)
                        <div class="d-flex flex-stack">
                            <div class="d-flex align-items-center me-3">
                                <div class="symbol symbol-40px me-4">
                                    <span class="symbol-label bg-light-primary">
                                        <i class="ki-duotone ki-people fs-2 text-primary">
                                            <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span>
                                        </i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="text-gray-800 fs-6 fw-bold lh-0 d-block">{{ $group->name }}</span>
                                    <span class="text-gray-400 fw-semibold d-block fs-7">{{ $group->subject?->name }}</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center w-100 mw-125px">
                                <div class="progress h-6px w-100 me-2 bg-light-success">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percent }}%" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <span class="text-gray-400 fw-semibold">{{ $percent }}%</span>
                            </div>
                        </div>
                        @if (!$loop->last)
                            <div class="separator separator-dashed my-3"></div>
                        @endif
                    @empty
                        <div class="text-center text-muted py-10">{{ __('app.no_data') }}</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Recent applications --}}
        <div class="col-xl-4 mb-xl-10">
            <div class="card card-flush h-xl-100">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-dark">{{ __('app.recent_applications') }}</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">{{ __('app.recent_applications_desc') }}</span>
                    </h3>
                </div>
                <div class="card-body pt-5">
                    @forelse($recentApplications as $application)
                        <div class="d-flex align-items-center mb-7">
                            <div class="symbol symbol-45px me-5">
                                <span class="symbol-label bg-light-info">
                                    <i class="ki-duotone ki-profile-user fs-2 text-info">
                                        <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span>
                                    </i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <span class="text-gray-800 text-hover-primary fw-bold d-block fs-6">{{ $application->full_name_ar ?: $application->full_name_en }}</span>
                                <span class="text-muted fw-semibold d-block fs-7">{{ $application->program?->title }} @if($application->subject) &middot; {{ $application->subject->name }} @endif</span>
                            </div>
                            <span class="badge badge-light-{{ $application->status === 'pending' ? 'warning' : ($application->status === 'approved' ? 'success' : 'danger') }}">
                                {{ __('app.application_status_'.$application->status) }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center text-muted py-10">{{ __('app.no_data') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Quick management links --}}
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

    @push('scripts')
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                var trendLabels = @json($registrationsTrend->pluck('label'));
                var trendData = @json($registrationsTrend->pluck('count'));

                var chartEl = document.querySelector("#kt_dashboard_students_chart");
                if (chartEl && window.ApexCharts) {
                    var options = {
                        series: [{
                            name: "{{ __('app.students') }}",
                            data: trendData
                        }],
                        chart: {
                            fontFamily: 'inherit',
                            type: 'area',
                            height: 320,
                            toolbar: { show: false }
                        },
                        plotOptions: {},
                        legend: { show: false },
                        dataLabels: { enabled: false },
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.4,
                                opacityTo: 0.1,
                                stops: [0, 90, 100]
                            }
                        },
                        stroke: { curve: 'smooth', show: true, width: 3, colors: ['#009ef7'] },
                        xaxis: {
                            categories: trendLabels,
                            axisBorder: { show: false },
                            axisTicks: { show: false },
                            labels: { style: { colors: '#a1a5b7', fontSize: '12px' } }
                        },
                        yaxis: {
                            labels: { style: { colors: '#a1a5b7', fontSize: '12px' } }
                        },
                        states: {
                            normal: { filter: { type: 'none', value: 0 } },
                            hover: { filter: { type: 'none', value: 0 } },
                            active: { allowMultipleDataPointsSelection: false, filter: { type: 'none', value: 0 } }
                        },
                        tooltip: {
                            style: { fontSize: '12px' },
                            y: { formatter: function (val) { return val; } }
                        },
                        colors: ['#009ef7'],
                        markers: { strokeColors: '#009ef7', strokeWidth: 3 }
                    };

                    new ApexCharts(chartEl, options).render();
                }
            });
        </script>
    @endpush

@endsection
