@php
    $isRtl = app()->getLocale() === 'ar';
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" direction="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Full Mark Academy'))</title>

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
        <link href="{{ asset('assets/plugins/global/plugins.bundle.rtl.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ asset('assets/css/style.bundle.rtl.css') }}" rel="stylesheet" type="text/css">
        <style>body { font-family: 'Cairo', 'Inter', sans-serif; }</style>
    @else
        <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css">
    @endif

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
<body id="kt_body" class="app-blank">

    @php
        $currentLocale = app()->getLocale();
        $localeFlags = ['en' => 'united-states.svg', 'ar' => 'palestine.svg'];
        $localeNames = ['en' => 'English', 'ar' => 'العربية'];
    @endphp

    <div class="d-flex flex-column flex-root" id="kt_app_root">
        <div class="d-flex flex-column flex-column-fluid flex-lg-row">

            <div class="d-flex flex-center w-lg-50 pt-15 pt-lg-0 px-10">
                <div class="position-absolute top-0 {{ $isRtl ? 'start-0 ms-5' : 'end-0 me-5' }} mt-5 d-flex gap-2">
                    <div class="app-navbar-item">
                        <a href="#" class="btn btn-icon btn-custom btn-icon-muted btn-active-light btn-active-color-primary w-30px h-30px w-md-40px h-md-40px"
                           data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                            <img class="rounded-1 w-20px" src="{{ asset('assets/media/flags/'.($localeFlags[$currentLocale] ?? 'united-states.svg')) }}" alt="{{ $currentLocale }}">
                        </a>
                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-base w-150px"
                             data-kt-menu="true">
                            @foreach (array_keys(config('laravellocalization.supportedLocales')) as $locale)
                                <div class="menu-item px-3 my-0">
                                    <a href="{{ route('admin.lang', $locale) }}" class="menu-link px-3 py-2 {{ $locale === $currentLocale ? 'active' : '' }}">
                                        <span class="symbol symbol-20px me-4">
                                            <img class="rounded-1" src="{{ asset('assets/media/flags/'.($localeFlags[$locale] ?? 'united-states.svg')) }}" alt="{{ $locale }}">
                                        </span>
                                        {{ $localeNames[$locale] ?? strtoupper($locale) }}
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="app-navbar-item">
                        <a href="#" class="btn btn-icon btn-custom btn-icon-muted btn-active-light btn-active-color-primary w-30px h-30px w-md-40px h-md-40px"
                           data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                            <i class="ki-duotone ki-night-day theme-light-show fs-2 fs-lg-1">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                <span class="path4"></span><span class="path5"></span><span class="path6"></span>
                                <span class="path7"></span><span class="path8"></span><span class="path9"></span>
                                <span class="path10"></span>
                            </i>
                            <i class="ki-duotone ki-moon theme-dark-show fs-2 fs-lg-1">
                                <span class="path1"></span><span class="path2"></span>
                            </i>
                        </a>
                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-150px"
                             data-kt-menu="true" data-kt-element="theme-mode-menu">
                            <div class="menu-item px-3 my-0">
                                <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="light">
                                    <span class="menu-icon" data-kt-element="icon">
                                        <i class="ki-duotone ki-night-day fs-2">
                                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                            <span class="path4"></span><span class="path5"></span><span class="path6"></span>
                                            <span class="path7"></span><span class="path8"></span><span class="path9"></span>
                                            <span class="path10"></span>
                                        </i>
                                    </span>
                                    <span class="menu-title">Light</span>
                                </a>
                            </div>
                            <div class="menu-item px-3 my-0">
                                <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="dark">
                                    <span class="menu-icon" data-kt-element="icon">
                                        <i class="ki-duotone ki-moon fs-2">
                                            <span class="path1"></span><span class="path2"></span>
                                        </i>
                                    </span>
                                    <span class="menu-title">Dark</span>
                                </a>
                            </div>
                            <div class="menu-item px-3 my-0">
                                <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="system">
                                    <span class="menu-icon" data-kt-element="icon">
                                        <i class="ki-duotone ki-screen fs-2">
                                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                            <span class="path4"></span>
                                        </i>
                                    </span>
                                    <span class="menu-title">System</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-center flex-lg-row-fluid">
                    <div class="w-lg-500px p-10">
                        @yield('content')
                    </div>
                </div>
            </div>

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

        </div>
    </div>

    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/admin-login-particles.js') }}"></script>
    <script src="{{ asset('site/js/theme-manager.js') }}"></script>
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
    @stack('scripts')
</body>
</html>
