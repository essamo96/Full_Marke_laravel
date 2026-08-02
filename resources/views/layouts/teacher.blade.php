@php
    $isRtl = app()->getLocale() === 'ar';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="theme-gold" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Teacher Dashboard | FULL MARK ACADEMY')</title>
  <meta name="description" content="Teacher Dashboard for Full Mark Academy">

  <!-- Favicons -->
  <link rel="icon" type="image/png" href="{{ asset('site/images/logo_v2_blue.png') }}">
  <link rel="shortcut icon" href="{{ asset('site/images/logo_v2_blue.png') }}">

  <!-- Typography -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Tajawal:wght@400;500;700;800&family=Outfit:wght@300;400;600;700;800&family=Almarai:wght@300;400;700&display=swap" rel="stylesheet">

  <!-- CSS Libraries -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

  <!-- Tailwind CSS -->
  @vite('resources/css/app.css')

  <!-- Custom Theme Stylesheets -->
  <link rel="stylesheet" href="{{ asset('site/css/variables.css') }}">
  <link rel="stylesheet" href="{{ asset('site/css/themes/dark.css') }}">
  <link rel="stylesheet" href="{{ asset('site/css/themes/light.css') }}">
  <link rel="stylesheet" href="{{ asset('site/css/themes/gold.css') }}">
  <link rel="stylesheet" href="{{ asset('site/css/animations/transitions.css') }}">
  <link rel="stylesheet" href="{{ asset('site/css/animations/scroll-effects.css') }}">
  <link rel="stylesheet" href="{{ asset('site/css/landing.css') }}">
  <link rel="stylesheet" href="{{ asset('site/css/hero-animation.css') }}">
  <link rel="stylesheet" href="{{ asset('teacher/css/dashboard.css') }}">
  <link rel="stylesheet" href="{{ asset('site/css/rtl.css') }}">

  <!-- Theme loading script -->
  <script src="{{ asset('site/js/theme-manager.js') }}"></script>

  @stack('styles')
</head>
<body class="position-relative overflow-x-hidden selection:bg-gold-light selection:text-black">

@include('layouts.partials.teacher-sidebar')

@include('layouts.partials.teacher-header')

    <!-- ════ MAIN CONTENT ════ -->
    <main class="dashboard-main">
      <div class="container-fluid px-0">

@yield('content')

      </div>
    </main>


  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('site/js/lang.js') }}"></script>
  <script src="{{ asset('teacher/js/dashboard.js') }}"></script>
  <script>
    const cycleBtn = document.getElementById('themeCycleBtn');
    if(cycleBtn) {
      cycleBtn.addEventListener('click', () => {
        window.dispatchEvent(new Event('themeCycleRequest'));
      });
    }

    function toggleSidebar() {
      document.getElementById('dashboardSidebar').classList.toggle('mobile-open');
      document.getElementById('sidebarOverlay').classList.toggle('active');
    }

    document.addEventListener('languageChanged', (e) => {
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

  @auth('teacher')
  <!-- Notifications: mark-read handlers -->
  <script>
    (function () {
      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

      function postJson(url) {
        return fetch(url, {
          method: 'POST',
          keepalive: true,
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          }
        });
      }

      document.addEventListener('click', function (e) {
        const item = e.target.closest('.teacher-notification-item');
        if (item) {
          postJson(`/teacher/notifications/${item.dataset.id}/read`);
        }
      });

      const markAllBtn = document.getElementById('teacherMarkAllReadBtn');
      if (markAllBtn) {
        markAllBtn.addEventListener('click', function () {
          postJson('{{ route("teacher.notifications.read-all") }}').then(function () {
            document.querySelectorAll('.teacher-notification-item').forEach(el => el.remove());
            const list = document.getElementById('teacherNotificationsList');
            if (list && !document.getElementById('teacherNoNotificationsMsg')) {
              const p = document.createElement('p');
              p.className = 'text-muted text-center py-6 mb-0';
              p.id = 'teacherNoNotificationsMsg';
              p.textContent = 'لا توجد إشعارات جديدة';
              list.appendChild(p);
            }
            const badge = document.getElementById('teacherNotificationsBadge');
            if (badge) {
              badge.classList.add('d-none');
              badge.textContent = '0';
            }
          });
        });
      }
    })();
  </script>
  @endauth
</body>
</html>
@stack('scripts')
