<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<!--begin::Head-->
<head>
    <base href=""/>
    <title>@yield('title', 'Admin Dashboard')</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600&display=swap&subset=arabic" rel="stylesheet" />
    <!--end::Fonts-->
    
    <!-- datatable css -->
    <link href="{{ asset('assets/admin/plugins/custom/datatables/datatables.bundle.css?v=7.2.9  ') }}" rel="stylesheet" type="text/css" />
    
    <!--begin::Global Stylesheets Bundle-->
    <link href="{{ asset('assets/admin/plugins/global/plugins.bundle.css?v=7.2.9') }}" rel="stylesheet" type="text/css" />
    
    @if (App::isLocale('ar'))
        <link href="{{ asset('assets/admin/css/style.bundle.rtl.css?v=7.2.9') }}" rel="stylesheet" type="text/css" />
        <style>
            body, .app-sidebar-menu * { font-family: 'Cairo', sans-serif !important; }
            [data-kt-app-sidebar-minimize="on"] .my-custom-logo { display: none !important; }
            #kt_app_header { z-index: 1050 !important; }
        </style>
    @else
        <link href="{{ asset('assets/admin/css/style.bundle.css?v=7.2.9') }}" rel="stylesheet" type="text/css" />
        <style>
            body, .app-sidebar-menu * { font-family: 'Inter', sans-serif !important; }
            [data-kt-app-sidebar-minimize="on"] .my-custom-logo { display: none !important; }
            #kt_app_header { z-index: 1050 !important; }
        </style>
    @endif
    <!--end::Global Stylesheets Bundle-->

    @stack('styles')
    
    <style>
        .btn.btn-xs {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        .btn.btn-icon.btn-xs {
            height: 28px;
            width: 28px;
        }
        .btn.btn-icon.btn-xs i {
            font-size: 0.85rem !important;
        }
    </style>
</head>
<!--end::Head-->

<!--begin::Body-->
<body id="kt_app_body" data-kt-app-layout="dark-sidebar" data-kt-app-header-fixed="true" data-kt-app-header-fixed-mobile="true" data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-hoverable="true" data-kt-app-sidebar-push-toolbar="true" data-kt-app-sidebar-push-footer="true" data-kt-app-toolbar-enabled="true" data-kt-app-aside-enabled="true" data-kt-app-aside-fixed="true" data-kt-app-aside-push-toolbar="true" data-kt-app-aside-push-footer="true" class="app-default">
    <!--begin::Theme mode setup on page load-->
    <script>
        var defaultThemeMode = "light"; var themeMode; if ( document.documentElement ) { if ( document.documentElement.hasAttribute("data-bs-theme-mode")) { themeMode = document.documentElement.getAttribute("data-bs-theme-mode"); } else { if ( localStorage.getItem("data-bs-theme") !== null ) { themeMode = localStorage.getItem("data-bs-theme"); } else { themeMode = defaultThemeMode; } } if (themeMode === "system") { themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light"; } document.documentElement.setAttribute("data-bs-theme", themeMode); }
    </script>
    <!--end::Theme mode setup on page load-->

    <!--begin::App-->
    <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
        <!--begin::Page-->
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
            
            <!--begin::Header-->
            @include('admin.layout.mainLayouts.header')
            <!--end::Header-->
            
            <!--begin::Wrapper-->
            <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
                
                <!--begin::Sidebar-->
                @include('admin.layout.mainLayouts.sidebar')
                <!--end::Sidebar-->
                
                <!--begin::Main-->
                <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                    <!--begin::Content wrapper-->
                    <div class="d-flex flex-column flex-column-fluid">
                        <div id="kt_app_toolbar" class="app-toolbar pt-6 pb-2">
                            <!--begin::Toolbar container-->
                            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex align-items-stretch">
                                <!--begin::Toolbar wrapper-->
                                <div class="app-toolbar-wrapper d-flex flex-stack flex-wrap gap-4 w-100">
                                    <!--begin::Page title-->
                                    <div class="page-title d-flex flex-column justify-content-center gap-1 me-3">
                                        <!--begin::Title-->
                                        <h1 class="page-heading d-flex flex-column justify-content-center text-dark fw-bold fs-3 m-0">@yield('title', 'Dashboard')</h1>
                                        <!--end::Title-->
                                        <!--begin::Breadcrumb-->
                                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0">
                                            <!--begin::Item-->
                                            <li class="breadcrumb-item text-muted">
                                                <a href="{{ url('admin/dashboard') }}" class="text-muted text-hover-primary">{{ \App\Helpers\translate('home') }}</a>
                                            </li>
                                            <!--end::Item-->
                                            <!--begin::Item-->
                                            <li class="breadcrumb-item">
                                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                                            </li>
                                            <!--end::Item-->
                                            @hasSection('breadcrumb')
                                                @yield('breadcrumb')
                                            @else
                                                <!--begin::Item-->
                                                <li class="breadcrumb-item text-muted">{{ \App\Helpers\translate($active_menu ?? 'pages') }}</li>
                                                <!--end::Item-->
                                                <!--begin::Item-->
                                                <li class="breadcrumb-item">
                                                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                                                </li>
                                                <!--end::Item-->
                                                <!--begin::Item-->
                                                <li class="breadcrumb-item text-muted">@yield('title', 'Dashboard')</li>
                                                <!--end::Item-->
                                            @endif
                                        </ul>
                                        <!--end::Breadcrumb-->
                                    </div>
                                    <!--end::Page title-->
                                    <!--begin::Actions-->
                                    <div class="d-flex align-items-center gap-2 gap-lg-3">
                                        @yield('toolbar-actions')
                                    </div>
                                    <!--end::Actions-->
                                </div>
                                <!--end::Toolbar wrapper-->
                            </div>
                            <!--end::Toolbar container-->
                        </div>
                        @yield('page-content')
                    </div>
                    <!--end::Content wrapper-->

                    <!--begin::Footer-->
                    @include('admin.layout.mainLayouts.footer')
                    <!--end::Footer-->
                </div>
                <!--end:::Main-->
                
                <!--begin::aside-->
                @include('admin.layout.mainLayouts.aside')
                <!--end::aside-->
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Page-->
    </div>
    <!--end::App-->

    <!--begin::Javascript-->
    <script>var hostUrl = "{{ asset('assets/admin/') }}";</script>
    <script src="{{ asset('assets/admin/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/admin/js/scripts.bundle.js') }}"></script>
    
    <!-- datatables js -->
    <script src="{{ asset('assets/admin/plugins/custom/datatables/datatables.bundle.js?v=7.2.9') }}"></script>

    <!-- tinymce js -->
    <script src="{{ asset('assets/admin/plugins/custom/tinymce/tinymce.bundle.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof tinymce !== 'undefined') {
                tinymce.init({
                    selector: 'textarea',
                    plugins: 'advlist autolink lists link image charmap preview anchor pagebreak searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking table directionality emoticons template',
                    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify | ltr rtl | bullist numlist outdent indent | link image media table | removeformat | charmap emoticons | fullscreen preview code',
                    directionality: document.documentElement.dir || 'ltr',
                    menubar: false,
                    promotion: false,
                    extended_valid_elements: 'i[class|id|style|data-*],span[class|id|style|data-*],div[*]',
                    verify_html: false
                });
            }
        });
    </script>
    
    @stack('scripts')
    @yield('js')
    <!--end::Javascript-->
</body>
<!--end::Body-->
</html>
