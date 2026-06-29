@php
    $isRtl = app()->getLocale() === 'ar';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="theme-gold" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Student Dashboard | FULL MARK ACADEMY')</title>
  <meta name="description" content="Student Dashboard for Full Mark Academy">

  <!-- Favicons -->
  <link rel="icon" type="image/png" href="{{ asset('site/images/img/logo_backup.png') }}">
  <link rel="shortcut icon" href="{{ asset('site/images/img/logo_backup.png') }}">

  <!-- Typography -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Tajawal:wght@400;500;700;800&family=Outfit:wght@300;400;600;700;800&family=Almarai:wght@300;400;700&display=swap" rel="stylesheet">

  <!-- CSS Libraries -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            gold: { light: '#e8d0ad', DEFAULT: '#c5a880', dark: '#a3875f' }
          }
        }
      }
    }
  </script>

  <!-- Custom Theme Stylesheets -->
  <link rel="stylesheet" href="{{ asset('site/css/variables.css') }}">
  <link rel="stylesheet" href="{{ asset('site/css/themes/dark.css') }}">
  <link rel="stylesheet" href="{{ asset('site/css/themes/light.css') }}">
  <link rel="stylesheet" href="{{ asset('site/css/themes/gold.css') }}">
  <link rel="stylesheet" href="{{ asset('site/css/animations/transitions.css') }}">
  <link rel="stylesheet" href="{{ asset('site/css/animations/scroll-effects.css') }}">
  <link rel="stylesheet" href="{{ asset('site/css/landing.css') }}">
  <link rel="stylesheet" href="{{ asset('site/css/hero-animation.css') }}">
  <link rel="stylesheet" href="{{ asset('student/css/dashboard.css') }}">
  <link rel="stylesheet" href="{{ asset('site/css/rtl.css') }}">

  <!-- Theme loading script -->
  <script src="{{ asset('site/js/theme-manager.js') }}"></script>

  @stack('styles')
</head>
<body class="position-relative overflow-x-hidden selection:bg-gold-light selection:text-black">

@include('layouts.partials.student-sidebar')

@include('layouts.partials.student-header')

    <!-- ════ MAIN CONTENT ════ -->
    <main class="dashboard-main">
      <div class="container-fluid px-0">

@yield('content')

      </div>
    </main>


  </div>

  <!-- Chat Widget (Floating) -->
  <button class="btn btn-luxury rounded-circle position-fixed bottom-0 end-0 m-4 d-flex align-items-center justify-content-center shadow-lg" style="width: 60px; height: 60px; z-index: 1000;">
    <i class="bi bi-chat-dots-fill fs-4"></i>
  </button>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('site/js/lang.js') }}"></script>
  <script src="{{ asset('student/js/dashboard.js') }}"></script>
  <script>
    // Initialize Theme Cycle logic dynamically using existing mechanism if available, 
    // or handle it individually for the dashboard.
    const cycleBtn = document.getElementById('themeCycleBtn');
    if(cycleBtn) {
      cycleBtn.addEventListener('click', () => {
        window.dispatchEvent(new Event('themeCycleRequest'));
      });
    }

    // Sidebar Mobile Toggle
    function toggleSidebar() {
      document.getElementById('dashboardSidebar').classList.toggle('mobile-open');
      document.getElementById('sidebarOverlay').classList.toggle('active');
    }

    // Border RTL fix script
    document.addEventListener('languageChanged', (e) => {
      // Dynamic adjust border-start to border-end based on RTL
      const borders = document.querySelectorAll('.border-start, .border-end');
      borders.forEach(el => {
        if (e.detail.dir === 'rtl') {
          if (el.classList.contains('border-start')) {
            el.classList.replace('border-start', 'border-end');
          }
        } else {
          if (el.classList.contains('border-end')) {
            el.classList.replace('border-end', 'border-start');
          }
        }
      });
    });
  </script>
</body>
</html>
@stack('scripts')
