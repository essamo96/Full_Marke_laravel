@php
    $isRtl = app()->getLocale() === 'ar';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="theme-gold" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Student Dashboard | FULL MARK ACADEMY')</title>
  <meta name="description" content="Student Dashboard for Full Mark Academy">

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

  @auth('student')
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
        const item = e.target.closest('.student-notification-item');
        if (item) {
          postJson(`/student/notifications/${item.dataset.id}/read`);
        }
      });

      const markAllBtn = document.getElementById('studentMarkAllReadBtn');
      if (markAllBtn) {
        markAllBtn.addEventListener('click', function () {
          postJson('{{ route("student.notifications.read-all") }}').then(function () {
            document.querySelectorAll('.student-notification-item').forEach(el => el.remove());
            const list = document.getElementById('studentNotificationsList');
            if (list && !document.getElementById('studentNoNotificationsMsg')) {
              const p = document.createElement('p');
              p.className = 'text-muted text-center py-6 mb-0';
              p.id = 'studentNoNotificationsMsg';
              p.textContent = 'لا توجد إشعارات جديدة';
              list.appendChild(p);
            }
            const badge = document.getElementById('studentNotificationsBadge');
            if (badge) {
              badge.classList.add('d-none');
              badge.textContent = '0';
            }
          });
        });
      }
    })();
  </script>

  <!-- Notifications: real-time delivery -->
  <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.3.0/dist/web/pusher.min.js"></script>
  <script>
    (function () {
      if (typeof Pusher === 'undefined') return;

      const connection = '{{ config("broadcasting.default", "pusher") }}';
      const isReverb = connection === 'reverb';
      const appKey = isReverb ? '{{ config("broadcasting.connections.reverb.key") }}' : '{{ config("broadcasting.connections.pusher.key") }}';
      if (!appKey) return;

      const pusherOptions = {
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

      const pusher = new Pusher(appKey, pusherOptions);
      const channel = pusher.subscribe('student-notifications.{{ auth("student")->id() }}');

      function handleIncoming(data) {
        const message = typeof data.message === 'object' ? data.message.message : data.message;
        const url = data.url || '#';

        // Badge
        const badge = document.getElementById('studentNotificationsBadge');
        if (badge) {
          const current = parseInt(badge.textContent || '0', 10) || 0;
          badge.textContent = current + 1;
          badge.classList.remove('d-none');
        }

        // Dropdown list
        const list = document.getElementById('studentNotificationsList');
        const noMsg = document.getElementById('studentNoNotificationsMsg');
        if (noMsg) noMsg.remove();
        if (list) {
          const item = document.createElement('a');
          item.href = url;
          item.className = 'd-block text-decoration-none p-3 rounded-3 mb-2 student-notification-item';
          item.dataset.id = data.id || ''; // Wait, data.id might not be available, but just in case
          item.style.color = 'var(--text-primary)';
          item.innerHTML = `
            <div class="d-flex gap-3 align-items-start">
              <div class="flex-shrink-0 mt-1">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(197, 168, 128, 0.15);">
                  <i class="bi bi-info-circle-fill" style="color: var(--accent-color); font-size: 1.1rem;"></i>
                </div>
              </div>
              <div>
                <div class="fs-7 fw-medium mb-1" style="line-height: 1.4;">${message}</div>
                <div class="d-flex align-items-center gap-1 opacity-75" style="font-size: 0.75rem;">
                  <i class="bi bi-clock"></i> الآن
                </div>
              </div>
            </div>
          `;
          list.insertAdjacentElement('afterbegin', item);
        }

        showStudentToast(message);

        // Play Notification Sound
        var audio = new Audio('{{ asset("assets/sounds/notification.mp3") }}');
        var playPromise = audio.play();
        if (playPromise !== undefined) {
          playPromise.catch(function(error) {
            console.log('Audio play failed: ', error);
          });
        }
      }

      function handleNewExam(data) {
          // First, do the regular processing (add to dropdown list, badge, sound)
          handleIncoming(data);
          
          const message = typeof data.message === 'object' ? data.message.message : data.message;
          const url = data.url || '#';
          
          // Show a large SweetAlert popup
          if (typeof Swal !== 'undefined') {
              Swal.fire({
                  title: 'امتحان جديد متاح!',
                  text: message,
                  icon: 'info',
                  showCancelButton: true,
                  confirmButtonText: 'الذهاب للامتحان الآن',
                  cancelButtonText: 'إغلاق',
                  confirmButtonColor: 'var(--accent-color)',
                  backdrop: `rgba(0,0,0,0.8)`
              }).then((result) => {
                  if (result.isConfirmed) {
                      window.location.href = url;
                  }
              });
          }
      }

      channel.bind('StudentFeeDuesEvent', handleIncoming);
      channel.bind('StudentPaymentConfirmedEvent', handleIncoming);
      channel.bind('NewExamPublishedEvent', handleNewExam);
      channel.bind('NewGroupNoteEvent', handleIncoming);
      channel.bind('ExamStartingNowEvent', handleNewExam);
    })();

    function showStudentToast(message) {
      const toast = document.createElement('div');
      toast.className = 'position-fixed shadow-lg';
      toast.style.cssText = 'top: 20px; inset-inline-end: 20px; z-index: 2000; max-width: 320px; background: var(--bg-secondary); color: var(--text-primary); border: 1px solid var(--separator-color); border-radius: 12px; padding: 14px 16px; opacity: 0; transform: translateY(-10px); transition: all .3s ease;';
      toast.innerHTML = `
        <div class="d-flex align-items-start gap-2">
          <i class="bi bi-bell-fill" style="color: var(--gold, #c5a880);"></i>
          <div class="fs-7">${message}</div>
        </div>
      `;
      document.body.appendChild(toast);
      requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
      });
      setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-10px)';
        setTimeout(() => toast.remove(), 300);
      }, 6000);
    }
  </script>
  @endauth
</body>
</html>
@stack('scripts')
