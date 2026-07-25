<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<!--begin::Head-->
<head>
    <base href=""/>
    <title>@yield('title', 'Admin Dashboard')</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('site/images/logo_v2_blue.png') }}" />
    
    <!--begin::Fonts-->
    <!-- Fonts commented out to resolve ERR_NAME_NOT_RESOLVED -->
    <!-- <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" /> -->
    <!-- <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600&display=swap&subset=arabic" rel="stylesheet" /> -->
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
            .select2-container--bootstrap5 .select2-selection--multiple .select2-selection__choice {
                display: flex;
                flex-direction: row-reverse;
            }
            .select2-container--bootstrap5 .select2-selection--multiple .select2-selection__choice__remove {
                margin-right: 0.5rem !important;
                margin-left: 0 !important;
                border-right: 1px solid rgba(255,255,255,0.2) !important;
                border-left: 0 !important;
            }
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
        
        /* Dynamic Sidebar Colors */
        :root {
            --custom-sidebar-bg-color: {{ \App\Models\Setting::getValue('sidebar_bg_color', '#1e1e2d') }};
            --custom-sidebar-text-color: {{ \App\Models\Setting::getValue('sidebar_text_color', '#9899ac') }};
            --custom-sidebar-icon-color: {{ \App\Models\Setting::getValue('sidebar_icon_color', '#494b74') }};
            --custom-sidebar-active-color: {{ \App\Models\Setting::getValue('sidebar_active_color', '#1b1b28') }};
            --custom-sidebar-arrow-color: {{ \App\Models\Setting::getValue('sidebar_arrow_color', '#494b74') }};
        }
        
        [data-kt-app-layout="dark-sidebar"] .app-sidebar {
            background-color: var(--custom-sidebar-bg-color) !important;
            border-right: 0 !important;
            border-left: 0 !important;
            box-shadow: none !important;
        }
        [data-kt-app-layout="dark-sidebar"] .app-header-logo {
            background-color: var(--custom-sidebar-bg-color) !important;
            border-bottom: 0 !important;
            border-right: 0 !important;
            border-left: 0 !important;
            box-shadow: none !important;
        }
        [data-kt-app-layout="dark-sidebar"] .app-sidebar-toggle {
            background-color: var(--custom-sidebar-bg-color) !important;
            border: 1px solid var(--custom-sidebar-text-color) !important;
            color: var(--custom-sidebar-text-color) !important;
        }
        .app-sidebar .menu-item .menu-link .menu-title {
            color: var(--custom-sidebar-text-color) !important;
        }
        .app-sidebar .menu-item .menu-icon i {
            color: var(--custom-sidebar-icon-color) !important;
        }
        .app-sidebar .menu-item.here > .menu-link,
        .app-sidebar .menu-item.active > .menu-link,
        .app-sidebar .menu-item.show > .menu-link,
        .app-sidebar .menu-link:hover {
            background-color: var(--custom-sidebar-active-color) !important;
        }
        .app-sidebar .menu-item .menu-arrow:after {
            background-color: var(--custom-sidebar-arrow-color) !important;
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
                    selector: 'textarea.editor, textarea.ckeditor, textarea[name*="description"]:not(.no-editor), textarea[name*="details"]:not(.no-editor), textarea[name*="content"]:not(.no-editor), textarea[name*="about"]:not(.no-editor), textarea[name*="text"]:not(.no-editor), textarea[name*="bio"]:not(.no-editor)',
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
    
    {{-- Global "pick from File Manager" popup helper — available on every admin page.
         Used by the admin.components.file-picker component so any upload field in
         the project can route through the shared File Manager screen. --}}
    <script>
        function openFileManagerPicker(targetInputId, options) {
            options = options || {};
            const width = 1100, height = 720;
            const left = (screen.width - width) / 2;
            const top = (screen.height - height) / 2;
            let url = "{{ route('file_manager.view') }}?picker=1&target=" + encodeURIComponent(targetInputId);
            if (options.folder) url += "&folder=" + encodeURIComponent(options.folder);
            window.open(url, 'fileManagerPicker', `width=${width},height=${height},left=${left},top=${top}`);
        }

        window.fmPickerCallback = function (url, path, target) {
            const input = document.getElementById(target);
            if (input) {
                input.value = path;
                input.dispatchEvent(new Event('change'));
            }
            const isImage = /\.(jpe?g|png|webp|gif|svg|bmp)$/i.test(path);
            const previewImg = document.getElementById(target + '_preview_img');
            const previewIcon = document.getElementById(target + '_preview_icon');
            if (previewImg) {
                if (isImage) {
                    previewImg.src = url;
                    previewImg.classList.remove('d-none');
                    if (previewIcon) previewIcon.classList.add('d-none');
                } else {
                    previewImg.classList.add('d-none');
                    if (previewIcon) previewIcon.classList.remove('d-none');
                }
            }
            const previewName = document.getElementById(target + '_preview_name');
            if (previewName) previewName.textContent = path.split('/').pop();
            const viewLink = document.getElementById(target + '_view_link');
            if (viewLink) {
                viewLink.href = url;
                viewLink.classList.remove('d-none');
            }
        };
    </script>

    @stack('scripts')
    @yield('js')

    @if(auth('admin')->check())
    <!-- Pusher via JSDelivr to resolve ERR_TIMED_OUT on js.pusher.com -->
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.3.0/dist/web/pusher.min.js"></script>
    <script>
        // Enable pusher logging - don't include this in production
        Pusher.logToConsole = true;
        document.addEventListener('DOMContentLoaded', function () {
            // Check if Pusher is available
            if (typeof Pusher !== 'undefined') {
                var connection = '{{ config("broadcasting.default", "pusher") }}';
                var isReverb = connection === 'reverb';
                var appKey = isReverb ? '{{ config("broadcasting.connections.reverb.key") }}' : '{{ config("broadcasting.connections.pusher.key") }}';

                if(appKey) {
                    var pusherOptions = {
                        cluster: '{{ config("broadcasting.connections.pusher.options.cluster", "mt1") }}',
                        forceTLS: true,
                        disableStats: true,
                        enabledTransports: ['ws', 'wss']
                    };

                    if (isReverb) {
                        pusherOptions.wsHost = window.location.hostname;
                        pusherOptions.wsPort = {{ config('broadcasting.connections.reverb.options.port', 8080) }};
                        pusherOptions.forceTLS = false;
                    }

                    var pusher = new Pusher(appKey, pusherOptions);
                    window.pusherInstance = pusher;

                    var channel = pusher.subscribe('admin-notifications');
                    channel.bind('NewStudentEvent', function(data) {
                        // Play sound
                        var audio = new Audio('{{ asset("assets/sounds/notification.mp3") }}');
                        var playPromise = audio.play();
                        if (playPromise !== undefined) {
                            playPromise.catch(function(error) {
                                console.log('Audio play failed: ', error);
                            });
                        }

                        // Configure Toastr
                        if (typeof toastr !== 'undefined') {
                            toastr.options = {
                                "closeButton": true,
                                "debug": false,
                                "newestOnTop": true,
                                "progressBar": true,
                                "rtl": {{ app()->getLocale() == 'ar' ? 'true' : 'false' }},
                                "positionClass": "toast-top-right",
                                "preventDuplicates": false,
                                "onclick": null,
                                "showDuration": "300",
                                "hideDuration": "1000",
                                "timeOut": "6000",
                                "extendedTimeOut": "1000",
                                "showEasing": "swing",
                                "hideEasing": "linear",
                                "showMethod": "fadeIn",
                                "hideMethod": "fadeOut",
                                "escapeHtml": false
                            };

                            var imageUrl = data.student_image ? data.student_image : '{{ asset("assets/admin/media/avatars/blank.png") }}';
                            var messageHtml = `
                            <div class="d-flex align-items-center mt-2">
                                <div class="symbol symbol-40px me-3">
                                    <img src="${imageUrl}" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;" alt=""/>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold fs-6 text-white">${data.student_name}</span>
                                    <span class="text-white-50 fs-7">تم التسجيل حديثاً</span>
                                </div>
                            </div>
                            `;
                            toastr.success(messageHtml, '{{ __("app.new_student_registered") }}');
                        }

                        // Update Notification Badge
                        var badge = document.getElementById('notification_count_badge');
                        if (badge) {
                            badge.classList.remove('d-none');
                            badge.innerText = parseInt(badge.innerText || 0) + 1;
                        }

                        var textCount = document.getElementById('notification_count_text');
                        if (textCount) {
                            textCount.innerText = (parseInt(textCount.innerText || 0) + 1) + ' {{ __("app.new") }}';
                        }

                        // Update Sidebar Badge
                        var sidebarBadge = document.getElementById('sidebar_pending_badge');
                        if (sidebarBadge) {
                            sidebarBadge.classList.remove('d-none');
                            sidebarBadge.innerText = parseInt(sidebarBadge.innerText || 0) + 1;
                        }

                        // Add new notification item to the top of the list
                        var list = document.getElementById('notifications_list');
                        var noMsg = document.getElementById('no_notifications_msg');
                        if (noMsg) {
                            noMsg.remove();
                        }

                        if (list) {
                            var itemHtml = `
                            <div class="d-flex flex-stack py-4">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-35px me-4">
                                        <img src="${data.student_image ?? '{{ asset("assets/admin/media/avatars/blank.png") }}'}" alt="image" style="border-radius: 50%; width: 35px; height: 35px; object-fit: cover;" />
                                    </div>
                                    <div class="mb-0 me-2">
                                        <a href="${data.url ?? '#'}" class="fs-6 text-gray-800 text-hover-primary fw-bold">{{ __('app.new_student_registered') }}</a>
                                        <div class="text-gray-400 fs-7">${data.student_name}</div>
                                    </div>
                                </div>
                                <span class="badge badge-light fs-8">{{ __('app.now') }}</span>
                            </div>`;
                            list.insertAdjacentHTML('afterbegin', itemHtml);
                        }
                    });

                    // Handle New Payment Event
                    channel.bind('NewPaymentEvent', function(data) {
                        // Play sound
                        var audio = new Audio('{{ asset("assets/sounds/notification.mp3") }}');
                        var playPromise = audio.play();
                        if (playPromise !== undefined) {
                            playPromise.catch(function(error) {
                                console.log('Audio play failed: ', error);
                            });
                        }

                        // Configure Toastr
                        if (typeof toastr !== 'undefined') {
                            toastr.options = {
                                "closeButton": true,
                                "debug": false,
                                "newestOnTop": true,
                                "progressBar": true,
                                "rtl": {{ app()->getLocale() == 'ar' ? 'true' : 'false' }},
                                "positionClass": "toast-top-right",
                                "preventDuplicates": false,
                                "onclick": null,
                                "showDuration": "300",
                                "hideDuration": "1000",
                                "timeOut": "6000",
                                "extendedTimeOut": "1000",
                                "showEasing": "swing",
                                "hideEasing": "linear",
                                "showMethod": "fadeIn",
                                "hideMethod": "fadeOut",
                                "escapeHtml": false
                            };

                            var messageHtml = `
                            <div class="d-flex align-items-center mt-2">
                                <div class="symbol symbol-40px me-3">
                                    <img src="${data.student_image ?? '{{ asset("assets/admin/media/avatars/blank.png") }}'}" alt="image" style="border-radius: 50%; width: 40px; height: 40px; object-fit: cover;" />
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold fs-6 text-white">${data.student_name}</span>
                                    <span class="text-white-50 fs-7">${data.message}</span>
                                </div>
                            </div>
                            `;
                            toastr.success(messageHtml, 'طلب دفعة جديدة');
                        }

                        // Update Notification Badge
                        var badge = document.getElementById('notification_count_badge');
                        if (badge) {
                            badge.classList.remove('d-none');
                            badge.innerText = parseInt(badge.innerText || 0) + 1;
                        }

                        var textCount = document.getElementById('notification_count_text');
                        if (textCount) {
                            textCount.innerText = (parseInt(textCount.innerText || 0) + 1) + ' {{ __("app.new") }}';
                        }

                        // Update Sidebar Badge for Approvals
                        var sidebarBadge = document.getElementById('sidebar_approvals_badge');
                        if (sidebarBadge) {
                            sidebarBadge.classList.remove('d-none');
                            sidebarBadge.innerText = parseInt(sidebarBadge.innerText || 0) + 1;
                        }

                        // Add new notification item to the top of the list
                        var list = document.getElementById('notifications_list');
                        var noMsg = document.getElementById('no_notifications_msg');
                        if (noMsg) {
                            noMsg.remove();
                        }

                        if (list) {
                            var itemHtml = `
                            <div class="d-flex flex-stack py-4">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-35px me-4">
                                        <img src="${data.student_image ?? '{{ asset("assets/admin/media/avatars/blank.png") }}'}" alt="image" style="border-radius: 50%; width: 35px; height: 35px; object-fit: cover;" />
                                    </div>
                                    <div class="mb-0 me-2">
                                        <a href="${data.url ?? '#'}" class="fs-6 text-gray-800 text-hover-primary fw-bold">طلب دفعة جديدة</a>
                                        <div class="text-gray-400 fs-7">${data.student_name}</div>
                                    </div>
                                </div>
                                <span class="badge badge-light fs-8">{{ __('app.now') }}</span>
                            </div>`;
                            list.insertAdjacentHTML('afterbegin', itemHtml);
                        }
                    });

                    // Handle Contact Us Event
                    channel.bind('NewContactEvent', function(data) {
                        // Play sound
                        var audio = new Audio('{{ asset("assets/sounds/notification.mp3") }}');
                        var playPromise = audio.play();
                        if (playPromise !== undefined) {
                            playPromise.catch(function(error) {
                                console.log('Audio play failed: ', error);
                            });
                        }

                        // Configure Toastr
                        if (typeof toastr !== 'undefined') {
                            toastr.options = {
                                "closeButton": true,
                                "debug": false,
                                "newestOnTop": true,
                                "progressBar": true,
                                "rtl": {{ app()->getLocale() == 'ar' ? 'true' : 'false' }},
                                "positionClass": "toast-top-right",
                                "preventDuplicates": false,
                                "onclick": null,
                                "showDuration": "300",
                                "hideDuration": "1000",
                                "timeOut": "6000",
                                "extendedTimeOut": "1000",
                                "showEasing": "swing",
                                "hideEasing": "linear",
                                "showMethod": "fadeIn",
                                "hideMethod": "fadeOut",
                                "escapeHtml": false
                            };

                            var messageHtmlContact = `
                            <div class="d-flex align-items-center mt-2">
                                <div class="symbol symbol-40px me-3">
                                    <span class="symbol-label bg-light-primary text-primary fs-3 rounded-circle" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-envelope-fill"></i>
                                    </span>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold fs-6 text-white">${data.name}</span>
                                    <span class="text-white-50 fs-7">رسالة تواصل جديدة</span>
                                </div>
                            </div>
                            `;
                            toastr.info(messageHtmlContact, 'New Contact Message');
                        }

                        // Update Notification Badge
                        var badge = document.getElementById('notification_count_badge');
                        if (badge) {
                            badge.classList.remove('d-none');
                            badge.innerText = parseInt(badge.innerText || 0) + 1;
                        }

                        var textCount = document.getElementById('notification_count_text');
                        if (textCount) {
                            textCount.innerText = (parseInt(textCount.innerText || 0) + 1) + ' {{ __("app.new") }}';
                        }
                        
                        var list = document.getElementById('notifications_list');
                        var noMsg = document.getElementById('no_notifications_msg');
                        if (noMsg) {
                            noMsg.remove();
                        }

                        if (list) {
                            var itemHtml = `
                            <div class="d-flex flex-stack py-4">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-35px me-4">
                                        <span class="symbol-label bg-light-success">
                                            <i class="ki-outline ki-sms fs-2 text-success"></i>
                                        </span>
                                    </div>
                                    <div class="mb-0 me-2">
                                        <a href="#" class="fs-6 text-gray-800 text-hover-primary fw-bold">New Contact Message</a>
                                        <div class="text-gray-400 fs-7">${data.name}</div>
                                    </div>
                                </div>
                                <span class="badge badge-light fs-8">{{ __('app.now') }}</span>
                            </div>`;
                            list.insertAdjacentHTML('afterbegin', itemHtml);
                        }
                    });
                }
            }
        });
    </script>
    @endif
    <!--end::Javascript-->
    @include('admin.enrolment._modal')
    @include('admin.enrolment._modal_transfer')
    @include('admin.partials.student_sidebar')
</body>
<!--end::Body-->
</html>
