<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <base href=""/>
    <title>@yield('title', 'File Manager')</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('site/images/logo_v2_blue.png') }}" />

    <link href="{{ asset('assets/admin/plugins/global/plugins.bundle.css?v=7.2.9') }}" rel="stylesheet" type="text/css" />
    @if (App::isLocale('ar'))
        <link href="{{ asset('assets/admin/css/style.bundle.rtl.css?v=7.2.9') }}" rel="stylesheet" type="text/css" />
        <style>body, * { font-family: 'Cairo', sans-serif !important; }</style>
    @else
        <link href="{{ asset('assets/admin/css/style.bundle.css?v=7.2.9') }}" rel="stylesheet" type="text/css" />
        <style>body, * { font-family: 'Inter', sans-serif !important; }</style>
    @endif

    <style>
        body { background-color: var(--bs-body-bg); }
        .app-blank { padding: 1.25rem; }
    </style>
</head>
<body id="kt_app_body" data-kt-app-layout="light-sidebar" class="app-blank">
    <script>
        var defaultThemeMode = "light"; var themeMode; if ( document.documentElement ) { if ( document.documentElement.hasAttribute("data-bs-theme-mode")) { themeMode = document.documentElement.getAttribute("data-bs-theme-mode"); } else { if ( localStorage.getItem("data-bs-theme") !== null ) { themeMode = localStorage.getItem("data-bs-theme"); } else { themeMode = defaultThemeMode; } } if (themeMode === "system") { themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light"; } document.documentElement.setAttribute("data-bs-theme", themeMode); }
    </script>

    @yield('page-content')

    <script>var hostUrl = "{{ asset('assets/admin/') }}";</script>
    <script src="{{ asset('assets/admin/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/admin/js/scripts.bundle.js') }}"></script>

    @stack('scripts')
    @yield('js')
</body>
</html>
