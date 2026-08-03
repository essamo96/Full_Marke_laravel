@php
    $isRtl = app()->getLocale() === 'ar';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="theme-gold" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="color-scheme" content="dark light">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Exam | FULL MARK ACADEMY')</title>

    <!-- Favicons -->
    <link rel="icon" type="image/png" href="{{ asset('site/images/logo_v2_blue.png') }}">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    @vite('resources/css/app.css')

    <link rel="stylesheet" href="{{ asset('site/css/variables.css') }}">
    <link rel="stylesheet" href="{{ asset('site/css/themes/dark.css') }}">
    <link rel="stylesheet" href="{{ asset('site/css/themes/light.css') }}">
    <link rel="stylesheet" href="{{ asset('site/css/themes/gold.css') }}">
    <link rel="stylesheet" href="{{ asset('student/css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('site/css/rtl.css') }}">

    @stack('styles')
    
    <style>
        body { background: var(--bg-primary); }
        .exam-header {
            background: rgba(255,255,255,0.02);
            border-bottom: 1px solid rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="selection:bg-gold-light selection:text-black">

    <div class="exam-header sticky-top py-3 px-4 px-md-5 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <img src="{{ asset('site/images/full_mark_dark.png') }}" alt="Logo" class="h-40px hidden dark:block">
            <h4 class="fw-bold m-0 text-white d-none d-md-block">@yield('exam_title')</h4>
        </div>
        <div>
            @yield('exam_timer')
        </div>
    </div>

    <main class="py-5">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <!-- Load Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('scripts')
</body>
</html>
