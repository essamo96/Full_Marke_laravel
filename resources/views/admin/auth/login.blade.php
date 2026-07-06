@php
    $isRtl = app()->getLocale() === 'ar';
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<!--begin::Head-->
<head>
    <title>Admin Login - Full Mark Academy</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <link rel="shortcut icon" href="{{ asset('assets/admin/media/logos/favicon.ico') }}" />
    
    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600&display=swap&subset=arabic" rel="stylesheet" />
    <!--end::Fonts-->
    
    <!--begin::Global Stylesheets Bundle-->
    <link href="{{ asset('assets/admin/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    
    @if ($isRtl)
        <link href="{{ asset('assets/admin/css/style.bundle.rtl.css') }}" rel="stylesheet" type="text/css" />
        <style>
            body { font-family: 'Cairo', sans-serif !important; }
        </style>
    @else
        <link href="{{ asset('assets/admin/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
        <style>
            body { font-family: 'Inter', sans-serif !important; }
        </style>
    @endif
    <!--end::Global Stylesheets Bundle-->

    <style>
        .admin-bg-slide {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            opacity: 0;
            transition: opacity 1.4s ease-in-out;
        }
        .admin-bg-slide.active { opacity: 1; }
        .admin-brand-captions { position: relative; min-height: 2.5em; max-width: 680px; width: 90%; }
        .admin-brand-caption {
            position: absolute;
            inset-inline-start: 0;
            inset-inline-end: 0;
            opacity: 0;
            transform: translateY(8px);
            transition: opacity 1.4s ease-in-out, transform 1.4s ease-in-out;
            text-shadow: 0 1px 6px rgba(0, 0, 0, .65);
        }
        .admin-brand-caption.active { opacity: 1; transform: translateY(0); }
    </style>
</head>
<!--end::Head-->

<!--begin::Body-->
<body id="kt_body" class="app-blank">
    <!--begin::Theme mode setup on page load-->
    <script>
        var defaultThemeMode = "light"; var themeMode; if ( document.documentElement ) { if ( document.documentElement.hasAttribute("data-bs-theme-mode")) { themeMode = document.documentElement.getAttribute("data-bs-theme-mode"); } else { if ( localStorage.getItem("data-bs-theme") !== null ) { themeMode = localStorage.getItem("data-bs-theme"); } else { themeMode = defaultThemeMode; } } if (themeMode === "system") { themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light"; } document.documentElement.setAttribute("data-bs-theme", themeMode); }
    </script>
    <!--end::Theme mode setup on page load-->

    <!--begin::Root-->
    <div class="d-flex flex-column flex-root" id="kt_app_root">
        <!--begin::Authentication - Sign-in -->
        <div class="d-flex flex-column flex-lg-row flex-column-fluid">
            <!--begin::Body-->
            <div class="d-flex flex-column flex-lg-row-fluid w-lg-50 p-10 order-2 order-lg-1">
                <!--begin::Form-->
                <div class="d-flex flex-center flex-column flex-lg-row-fluid">
                    <!--begin::Wrapper-->
                    <div class="w-lg-500px p-10">
                        <!--begin::Form-->
                        <form class="form w-100" method="POST" action="{{ route('admin.login.submit') }}">
                            @csrf
                            <!--begin::Heading-->
                            <div class="text-center mb-11">
                                <!--begin::Logo-->
                                <img alt="Logo" src="{{ asset('site/images/logo_v2_blue.png') }}" class="h-80px mb-5 theme-light-show" />
                                <img alt="Logo" src="{{ asset('site/images/full_mark_dark.png') }}" class="h-150px mb-5 theme-dark-show" />
                                <!--end::Logo-->
                                <!--begin::Title-->
                                <h1 class="text-dark fw-bolder mb-3">{{ __('app.Sign In') }}</h1>
                                <!--end::Title-->
                                <!--begin::Subtitle-->
                                <div class="text-gray-500 fw-semibold fs-6">{{ __('app.Admin Panel') }}</div>
                                <!--end::Subtitle=-->
                            </div>
                            <!--begin::Heading-->

                            @if ($errors->any())
                                <div class="alert alert-danger mb-8">
                                    {{ $errors->first() }}
                                </div>
                            @endif

                            <!--begin::Input group=-->
                            <div class="fv-row mb-8">
                                <!--begin::Email-->
                                <input type="email" placeholder="{{ __('app.Email_Placeholder') }}" name="email" value="{{ old('email') }}" autocomplete="off" class="form-control bg-transparent" required autofocus />
                                <!--end::Email-->
                            </div>
                            <!--end::Input group=-->
                            
                            <div class="fv-row mb-3">
                                <!--begin::Password-->
                                <input type="password" placeholder="{{ __('app.Password_Placeholder') }}" name="password" autocomplete="off" class="form-control bg-transparent" required />
                                <!--end::Password-->
                            </div>
                            <!--end::Input group=-->

                            <!--begin::Wrapper-->
                            <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-8">
                                <div>
                                    <label class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="remember" id="remember"/>
                                        <span class="form-check-label fw-semibold text-gray-700 fs-base ms-1">{{ __('app.Remember me') }}</span>
                                    </label>
                                </div>
                                <!--begin::Link-->
                                <a href="#" class="link-primary">{{ __('app.Forgot Password ?') }}</a>
                                <!--end::Link-->
                            </div>
                            <!--end::Wrapper-->
                            
                            <!--begin::Submit button-->
                            <div class="d-grid mb-10">
                                <button type="submit" class="btn btn-primary">
                                    <!--begin::Indicator label-->
                                    <span class="indicator-label">{{ __('app.Sign In') }}</span>
                                    <!--end::Indicator label-->
                                </button>
                            </div>
                            <!--end::Submit button-->
                        </form>
                        <!--end::Form-->
                    </div>
                    <!--end::Wrapper-->
                </div>
                <!--end::Form-->
                
                <!--begin::Footer-->
                <div class="w-lg-500px d-flex flex-stack px-10 mx-auto">
                    <!--begin::Languages & Theme-->
                    <div class="d-flex align-items-center gap-4">
                        <!--begin::Theme Toggle-->
                        <div class="app-navbar-item">
                            <button class="btn btn-flex btn-link btn-color-gray-700 btn-active-color-primary fs-base p-0" data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                                <i class="ki-outline ki-night-day theme-light-show fs-2"></i>
                                <i class="ki-outline ki-moon theme-dark-show fs-2"></i>
                                <span class="ms-2">{{ __('app.Theme') }}</span>
                            </button>
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-150px" data-kt-menu="true" data-kt-element="theme-mode-menu">
                                <div class="menu-item px-3 my-0">
                                    <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="light">
                                        <span class="menu-icon" data-kt-element="icon">
                                            <i class="ki-outline ki-night-day fs-2"></i>
                                        </span>
                                        <span class="menu-title">{{ __('app.Light') }}</span>
                                    </a>
                                </div>
                                <div class="menu-item px-3 my-0">
                                    <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="dark">
                                        <span class="menu-icon" data-kt-element="icon">
                                            <i class="ki-outline ki-moon fs-2"></i>
                                        </span>
                                        <span class="menu-title">{{ __('app.Dark') }}</span>
                                    </a>
                                </div>
                                <div class="menu-item px-3 my-0">
                                    <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="system">
                                        <span class="menu-icon" data-kt-element="icon">
                                            <i class="ki-outline ki-screen fs-2"></i>
                                        </span>
                                        <span class="menu-title">{{ __('app.System') }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!--end::Theme Toggle-->

                        <!--begin::Language Toggle-->
                        <div class="app-navbar-item">
                            <button class="btn btn-flex btn-link btn-color-gray-700 btn-active-color-primary rotate fs-base p-0" data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-start" data-kt-menu-offset="0px, 0px">
                                <span data-kt-element="current-lang-name" class="me-1">{{ app()->getLocale() == 'ar' ? 'العربية' : 'English' }}</span>
                                <i class="ki-outline ki-down fs-5 text-muted rotate-180 m-0"></i>
                            </button>
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-200px py-4 fs-7" data-kt-menu="true" id="kt_auth_lang_menu">
                                <div class="menu-item px-3">
                                    <a href="{{ route('admin.lang', 'en') }}" class="menu-link d-flex px-5" data-kt-lang="English">
                                        <span data-kt-element="lang-name">English</span>
                                    </a>
                                </div>
                                <div class="menu-item px-3">
                                    <a href="{{ route('admin.lang', 'ar') }}" class="menu-link d-flex px-5" data-kt-lang="Arabic">
                                        <span data-kt-element="lang-name">العربية</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!--end::Language Toggle-->
                    </div>
                    <!--end::Languages & Theme-->
                    
                    <!--begin::Links-->
                    <div class="d-flex fw-semibold text-primary fs-base gap-5">
                        <a href="#" target="_blank">{{ __('app.Terms') }}</a>
                        <a href="#" target="_blank">{{ __('app.Contact Us') }}</a>
                    </div>
                    <!--end::Links-->
                </div>
                <!--end::Footer-->
            </div>
            <!--end::Body-->
            
            <!--begin::Aside-->
            @php
                $brandSlides = [
                    ['image' => asset('site/images/bg-main.jpg'), 'text' => $isRtl ? 'كل عملية تسجيل دخول هي خطوة نحو التميّز.' : 'Every sign-in is a step toward excellence.'],
                    ['image' => asset('site/images/img/banner/ote_hall.png'), 'text' => $isRtl ? 'نبني مستقبل طلابنا بعلم وثقة.' : 'Building our students\' future with knowledge and confidence.'],
                    ['image' => asset('site/images/img/featured/children.png'), 'text' => $isRtl ? 'العلامة الكاملة... شغفنا نحو القمة.' : 'Full Mark... our passion toward the top.'],
                ];
            @endphp
            <div class="d-flex flex-lg-row-fluid w-lg-50 bgi-size-cover bgi-position-center order-1 order-lg-2 bg-dark position-relative overflow-hidden">
                <div class="admin-bg-slideshow position-absolute top-0 start-0 w-100 h-100" style="z-index: 1;">
                    @foreach ($brandSlides as $i => $slide)
                        <div class="admin-bg-slide {{ $i === 0 ? 'active' : '' }}" style="background-image: url('{{ $slide['image'] }}');"></div>
                    @endforeach
                </div>
                <div class="position-absolute top-0 start-0 w-100 h-100" style="z-index: 2; background: linear-gradient(180deg, rgba(11,18,32,.55) 0%, rgba(11,18,32,.75) 60%, rgba(11,18,32,.92) 100%);"></div>
                <canvas class="admin-particles-canvas position-absolute top-0 start-0 w-100 h-100" style="z-index: 3;"></canvas>
                <div class="d-flex flex-column flex-center py-15 px-5 px-md-15 w-100 position-relative" style="z-index: 4;">
                    <img id="admin-login-logo" src="{{ asset('site/images/full_mark_dark.png') }}" alt="Full Mark Academy" class="mb-7" style="max-height: 310px;">
                    <div class="admin-brand-captions text-center">
                        @foreach ($brandSlides as $i => $slide)
                            <span class="admin-brand-caption fs-base text-white {{ $i === 0 ? 'active' : '' }}">{{ $slide['text'] }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
            <!--end::Aside-->
        </div>
        <!--end::Authentication - Sign-in-->
    </div>
    <!--end::Root-->

    <!--begin::Javascript-->
    <script>var hostUrl = "{{ asset('assets/admin/') }}";</script>
    <!--begin::Global Javascript Bundle(mandatory for all pages)-->
    <script src="{{ asset('assets/admin/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/admin/js/scripts.bundle.js') }}"></script>
    
    <script src="{{ asset('site/js/theme-manager.js') }}"></script>
    <!-- Particles and Slideshow Script -->
    <script src="{{ asset('assets/js/admin-login-particles.js') }}"></script>
    <script>
        (function () {
            var slides = document.querySelectorAll('.admin-bg-slide');
            var captions = document.querySelectorAll('.admin-brand-caption');
            if (!slides.length) return;

            var index = 0;
            setInterval(function () {
                slides[index].classList.remove('active');
                if (captions[index]) captions[index].classList.remove('active');

                index = (index + 1) % slides.length;

                slides[index].classList.add('active');
                if (captions[index]) captions[index].classList.add('active');
            }, 4000);
        })();
    </script>
    <!--end::Global Javascript Bundle-->
    <!--end::Javascript-->
</body>
<!--end::Body-->
</html>