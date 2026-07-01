@php
    $isRtl = app()->getLocale() === 'ar';
    $sidebarMinimized = request()->cookie('sidebar_minimize_state') === 'on';
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" direction="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Full Mark Academy') . ' — Admin')</title>

    <script>
        var defaultThemeMode = "light"; var themeMode;
        if (document.documentElement) {
            if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
                themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
            } else {
                if (localStorage.getItem("data-bs-theme") !== null) {
                    themeMode = localStorage.getItem("data-bs-theme");
                } else {
                    themeMode = defaultThemeMode;
                }
            }
            if (themeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            }
            document.documentElement.setAttribute("data-bs-theme", themeMode);
        }
    </script>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700">

    @if ($isRtl)
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap">
    @endif

    @if ($isRtl)
        <link href="{{ asset('assets/plugins/global/plugins.bundle.rtl.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ asset('assets/css/style.bundle.rtl.css') }}" rel="stylesheet" type="text/css">
    @else
        <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css">
    @endif

    @if ($isRtl)
        <style>
            .app-sidebar,
            .app-sidebar .menu-title,
            .app-sidebar .menu-heading {
                font-family: 'Cairo', 'Inter', sans-serif;
            }
            .app-sidebar .menu-title {
                font-weight: 600;
            }
        </style>
    @endif

    {{-- Tables (including DataTables) use the same font as the sidebar, at a larger, more readable size --}}
    <style>
        .table, table.dataTable, .dataTables_wrapper {
            font-family: {{ $isRtl ? "'Cairo', 'Inter', sans-serif" : "'Inter', sans-serif" }};
            font-size: 1.05rem;
        }
        .table thead th {
            font-size: 0.95rem;
            font-weight: 700;
        }
        .dataTables_wrapper .dataTables_processing {
            font-family: {{ $isRtl ? "'Cairo', 'Inter', sans-serif" : "'Inter', sans-serif" }};
        }
        table.dataTable.admin-datatable td,
        table.dataTable.admin-datatable th {
            vertical-align: middle;
        }
    </style>

    @stack('styles')
</head>
<body id="kt_app_body" data-kt-app-layout="dark-sidebar" data-kt-app-header-fixed="true"
      data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-hoverable="true"
      data-kt-app-sidebar-push-header="true" data-kt-app-sidebar-push-toolbar="true"
      data-kt-app-sidebar-push-footer="true" @if ($sidebarMinimized) data-kt-app-sidebar-minimize="on" @endif
      class="app-default">

    <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">

            @include('layouts.partials.admin-header')

            <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">

                @include('layouts.partials.admin-sidebar')

                <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                    <div class="d-flex flex-column flex-column-fluid">
                        <div id="kt_app_content" class="app-content flex-column-fluid">
                            <div id="kt_app_content_container" class="app-container container-fluid">

                                @if (isset($pageTitle))
                                    <div class="d-flex flex-wrap flex-stack mb-6">
                                        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 my-0">
                                            {{ $pageTitle }}
                                        </h1>
                                    </div>
                                @endif

                                @yield('content')

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Generic AJAX delete-with-confirm for any <form class="ajax-delete-form">
            document.querySelectorAll('.ajax-delete-form').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        text: '{{ __('app.confirm_delete') }}',
                        icon: 'warning',
                        showCancelButton: true,
                        buttonsStyling: false,
                        confirmButtonText: '{{ __('app.yes_delete') }}',
                        cancelButtonText: '{{ __('app.cancel') }}',
                        customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-light' },
                    }).then(function (result) {
                        if (!result.isConfirmed) return;

                        fetch(form.action, {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                            body: new FormData(form),
                        })
                            .then(function (r) { return r.json(); })
                            .then(function (res) {
                                if (res.success) {
                                    Swal.fire({
                                        text: '{{ __('app.delete_success') }}',
                                        icon: 'success',
                                        timer: 1500,
                                        showConfirmButton: false,
                                    }).then(function () { window.location.reload(); });
                                } else {
                                    Swal.fire({ text: '{{ __('app.execution_error') }}', icon: 'error' });
                                }
                            })
                            .catch(function () {
                                Swal.fire({ text: '{{ __('app.execution_error') }}', icon: 'error' });
                            });
                    });
                });
            });

            // Generic AJAX status-toggle for any <form class="ajax-status-form">
            document.querySelectorAll('.ajax-status-form').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();

                    fetch(form.action, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: new FormData(form),
                    })
                        .then(function (r) { return r.json(); })
                        .then(function (res) {
                            if (res.success) {
                                Swal.fire({
                                    text: '{{ __('app.update_success') }}',
                                    icon: 'success',
                                    timer: 1200,
                                    showConfirmButton: false,
                                }).then(function () { window.location.reload(); });
                            } else {
                                Swal.fire({ text: '{{ __('app.execution_error') }}', icon: 'error' });
                            }
                        })
                        .catch(function () {
                            Swal.fire({ text: '{{ __('app.execution_error') }}', icon: 'error' });
                        });
                });
            });
        });
    </script>

    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    text: @json(session('success')),
                    icon: 'success',
                    buttonsStyling: false,
                    confirmButtonText: '{{ __('app.ok') }}',
                    customClass: { confirmButton: 'btn btn-primary' },
                });
            });
        </script>
    @endif

    @if (session('danger'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    text: @json(session('danger')),
                    icon: 'error',
                    buttonsStyling: false,
                    confirmButtonText: '{{ __('app.ok') }}',
                    customClass: { confirmButton: 'btn btn-danger' },
                });
            });
        </script>
    @endif

    @stack('scripts')
</body>
</html>
