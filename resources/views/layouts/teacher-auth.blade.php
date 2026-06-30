@php
    $isRtl = app()->getLocale() === 'ar';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="theme-gold" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Teacher Login | FULL MARK ACADEMY')</title>
  <meta name="description" content="Staff and teacher login portal for FULL MARK ACADEMY management system.">

  <!-- Favicons -->
  <link rel="icon" type="image/png" href="{{ asset('site/images/img/logo_backup.png') }}">
  <link rel="shortcut icon" href="{{ asset('site/images/img/logo_backup.png') }}">

  <!-- Typography -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Tajawal:wght@400;500;700;800&family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">

  <!-- CSS Libraries -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

  <!-- Tailwind -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { theme: { extend: { colors: { gold: { light: '#e8d0ad', DEFAULT: '#c5a880', dark: '#a3875f' } } } } }
  </script>

  <!-- Custom Stylesheets -->
  <link rel="stylesheet" href="{{ asset('site/css/variables.css') }}">
  <link rel="stylesheet" href="{{ asset('site/css/themes/dark.css') }}">
  <link rel="stylesheet" href="{{ asset('site/css/themes/light.css') }}">
  <link rel="stylesheet" href="{{ asset('site/css/themes/gold.css') }}">
  <link rel="stylesheet" href="{{ asset('site/css/animations/transitions.css') }}">
  <link rel="stylesheet" href="{{ asset('site/css/animations/scroll-effects.css') }}">
  <link rel="stylesheet" href="{{ asset('site/css/landing.css') }}">
  <link rel="stylesheet" href="{{ asset('site/css/hero-animation.css') }}">
  <link rel="stylesheet" href="{{ asset('site/css/rtl.css') }}">

  <script src="{{ asset('site/js/theme-manager.js') }}"></script>

  <style>
    .page-wrapper { min-height: 100dvh; display: flex; flex-direction: column; padding-top: var(--navbar-height, 80px); }

    /* Split layout: left panel + right card */
    .teacher-main {
      flex: 1;
      display: grid;
      grid-template-columns: 1fr 1fr;
    }
    @media (max-width: 991px) { .teacher-main { grid-template-columns: 1fr; } }

    /* Left decorative panel */
    .teacher-panel-left {
      background: var(--bg-secondary, #0d0a06);
      border-right: 1px solid rgba(197,168,128,0.12);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 4rem 3rem;
      position: relative;
      overflow: hidden;
    }
    @media (max-width: 991px) { .teacher-panel-left { display: none; } }

    .panel-bg-orb {
      position: absolute;
      border-radius: 50%;
      pointer-events: none;
      filter: blur(80px);
    }
    .panel-bg-orb-1 { width: 300px; height: 300px; background: var(--accent-glow); top: 10%; left: 20%; opacity: 0.5; animation: breathe 6s ease-in-out infinite; }
    .panel-bg-orb-2 { width: 200px; height: 200px; background: var(--accent-glow); bottom: 15%; right: 10%; opacity: 0.3; animation: breathe 8s ease-in-out infinite reverse; }
    @keyframes breathe { 0%,100%{transform:scale(1)} 50%{transform:scale(1.15)} }

    .panel-content { position: relative; z-index: 1; text-align: center; }
    .panel-icon-ring {
      width: 120px; height: 120px;
      border-radius: 50%;
      border: 2px solid rgba(197,168,128,0.3);
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 2rem;
      position: relative;
    }
    .panel-icon-ring::before {
      content: '';
      position: absolute; inset: -8px;
      border-radius: 50%;
      border: 1px solid rgba(197,168,128,0.12);
    }
    .panel-icon-ring::after {
      content: '';
      position: absolute; inset: -16px;
      border-radius: 50%;
      border: 1px solid rgba(197,168,128,0.06);
    }
    .panel-icon-ring i { font-size: 3.2rem; color: var(--accent-color, #c5a880); filter: drop-shadow(0 0 20px var(--accent-glow)); }

    .panel-title {
      font-family: var(--font-en, 'Outfit', sans-serif);
      font-size: 1.5rem;
      font-weight: 800;
      color: var(--accent-color, #c5a880);
      letter-spacing: -0.02em;
      margin-bottom: 0.75rem;
    }
    [dir="rtl"] .panel-title { font-family: var(--font-ar, 'Tajawal', sans-serif); }
    .panel-desc { color: var(--text-secondary, #d1c7bd); font-size: 0.9rem; line-height: 1.7; max-width: 300px; }

    /* Feature pills */
    .panel-pills { display: flex; flex-direction: column; gap: 0.75rem; margin-top: 2.5rem; }
    .panel-pill {
      display: flex; align-items: center; gap: 0.75rem;
      padding: 0.75rem 1.25rem;
      background: rgba(197,168,128,0.06);
      border: 1px solid rgba(197,168,128,0.12);
      border-radius: var(--radius-md, 12px);
      text-align: start;
    }
    .panel-pill i { color: var(--accent-color, #c5a880); font-size: 1rem; flex-shrink: 0; }
    .panel-pill span { font-size: 0.85rem; color: var(--text-secondary, #d1c7bd); }

    /* Right: login form */
    .teacher-panel-right {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: clamp(2.5rem, 6vw, 5rem) clamp(1.5rem, 5vw, 4rem);
      background: var(--bg-primary, #050402);
      position: relative;
    }

    /* Background grid */
    .teacher-panel-right::before {
      content: '';
      position: absolute; inset: 0; pointer-events: none;
      background-image: radial-gradient(circle at 1px 1px, rgba(197,168,128,0.04) 1px, transparent 0);
      background-size: 40px 40px;
    }

    .teacher-form-wrap { position: relative; z-index: 1; width: 100%; max-width: 440px; }

    /* Top security badge */
    .security-badge {
      display: inline-flex; align-items: center; gap: 0.5rem;
      padding: 0.35rem 0.9rem;
      background: rgba(197,168,128,0.06);
      border: 1px solid rgba(197,168,128,0.18);
      border-radius: 9999px;
      font-size: 0.75rem;
      color: var(--accent-color, #c5a880);
      margin-bottom: 2rem;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      font-weight: 600;
    }

    .teacher-form-title {
      font-family: var(--font-en, 'Outfit', sans-serif);
      font-size: clamp(1.5rem, 3.5vw, 2.1rem);
      font-weight: 800;
      color: var(--text-primary, #fbfbf9);
      letter-spacing: -0.02em;
      margin-bottom: 0.5rem;
      line-height: 1.2;
    }
    [dir="rtl"] .teacher-form-title { font-family: var(--font-ar, 'Tajawal', sans-serif); }
    .teacher-form-subtitle { font-size: 0.9rem; color: var(--text-muted, #8c8276); margin-bottom: 2.5rem; line-height: 1.6; }

    /* Inputs */
    .input-row { display: flex; flex-direction: column; gap: 1.5rem; }
    .lux-field label {
      display: block;
      font-size: 0.8rem;
      font-weight: 600;
      letter-spacing: 0.04em;
      color: var(--text-secondary, #d1c7bd);
      margin-bottom: 0.5rem;
    }
    .lux-field .lux-input-wrap { position: relative; }
    .lux-field .lux-input {
      width: 100%;
      padding: 1rem 1rem 1rem 3rem;
      background: var(--bg-tertiary, rgba(10,8,4,0.5));
      border: 1px solid rgba(197,168,128,0.16);
      border-radius: var(--radius-md, 12px);
      color: var(--text-primary, #fbfbf9);
      font-size: 0.95rem;
      font-family: inherit;
      transition: border-color 0.22s ease, box-shadow 0.22s ease;
      outline: none;
    }
    [dir="rtl"] .lux-field .lux-input { padding: 1rem 3rem 1rem 1rem; }
    .lux-field .lux-input:focus {
      border-color: var(--accent-color, #c5a880);
      box-shadow: 0 0 0 3px rgba(197,168,128,0.1);
    }
    .lux-field .lux-input::placeholder { color: var(--text-muted, #8c8276); font-size: 0.88rem; }
    .lux-field .field-icon {
      position: absolute;
      left: 1rem; top: 50%; transform: translateY(-50%);
      color: var(--text-muted, #8c8276);
      pointer-events: none;
      transition: color 0.2s ease;
      font-size: 1.05rem;
    }
    [dir="rtl"] .lux-field .field-icon { left: auto; right: 1rem; }
    .lux-field .lux-input-wrap:focus-within .field-icon { color: var(--accent-color, #c5a880); }
    .lux-field .pw-reveal-btn {
      position: absolute;
      right: 1rem; top: 50%; transform: translateY(-50%);
      background: none; border: none;
      color: var(--text-muted, #8c8276);
      cursor: pointer; padding: 0;
      transition: color 0.2s ease;
    }
    [dir="rtl"] .lux-field .pw-reveal-btn { right: auto; left: 1rem; }
    .lux-field .pw-reveal-btn:hover { color: var(--accent-color, #c5a880); }
    .lux-field .pw-input .lux-input { padding-right: 3rem; }
    [dir="rtl"] .lux-field .pw-input .lux-input { padding-right: 3rem; padding-left: 3rem; }

    /* Support link */
    .support-link {
      display: flex; align-items: center; justify-content: center; gap: 0.5rem;
      font-size: 0.85rem;
      color: var(--text-muted, #8c8276);
      text-decoration: none;
      transition: color 0.2s ease;
      padding-top: 0.5rem;
    }
    .support-link:hover { color: var(--accent-color, #c5a880); }

    /* Student link */
    .student-portal-link { text-align: center; font-size: 0.85rem; color: var(--text-muted, #8c8276); padding-top: 1rem; border-top: 1px solid rgba(197,168,128,0.1); margin-top: 1rem; }
    .student-portal-link a { color: var(--accent-color, #c5a880); font-weight: 600; text-decoration: none; margin-inline-start: 0.3rem; }
    .student-portal-link a:hover { text-decoration: underline; }

    /* Loading */
    .btn-luxury.loading { opacity: 0.75; pointer-events: none; }

    @media (max-width: 576px) { .teacher-panel-right { padding: 2rem 1rem; } }
  </style>
  @stack('styles')
</head>
<body class="smooth-transition">

@include('layouts.partials.site-header')

  <!-- ════ MAIN — SPLIT LAYOUT ════ -->
  <div class="page-wrapper">
    <main class="teacher-main">

      <!-- LEFT — Decorative brand panel -->
      <div class="teacher-panel-left">
        <div class="panel-bg-orb panel-bg-orb-1" aria-hidden="true"></div>
        <div class="panel-bg-orb panel-bg-orb-2" aria-hidden="true"></div>

        <div class="panel-content">

          <div class="panel-icon-ring">
            <i class="bi bi-shield-lock-fill"></i>
          </div>

          <h2 class="panel-title" data-en="Staff Management Portal" data-ar="بوابة إدارة الكادر التعليمي">Staff Management Portal</h2>
          <p class="panel-desc" data-en="Secure access to the academy's full teaching and administrative management system. Exclusive for authorized staff members." data-ar="وصول آمن إلى نظام الإدارة الأكاديمي الشامل. مخصص للكادر المعتمد فقط.">
            Secure access to the academy's full teaching and administrative management system. Exclusive for authorized staff members.
          </p>

          <div class="panel-pills">
            <div class="panel-pill">
              <i class="bi bi-calendar-check-fill"></i>
              <span data-en="Schedule & Attendance Management" data-ar="إدارة الجداول والحضور">Schedule &amp; Attendance Management</span>
            </div>
            <div class="panel-pill">
              <i class="bi bi-graph-up-arrow"></i>
              <span data-en="Student Progress Tracking" data-ar="متابعة تقدم الطلاب">Student Progress Tracking</span>
            </div>
            <div class="panel-pill">
              <i class="bi bi-file-earmark-richtext-fill"></i>
              <span data-en="Curriculum & Resources" data-ar="المناهج والمحتوى التعليمي">Curriculum &amp; Resources</span>
            </div>
            <div class="panel-pill">
              <i class="bi bi-bell-fill"></i>
              <span data-en="Communications & Notifications" data-ar="التواصل والإشعارات">Communications &amp; Notifications</span>
            </div>
          </div>
        </div>
      </div>

      <!-- RIGHT — Login form -->
      <div class="teacher-panel-right">
        <div class="teacher-form-wrap reveal-scale">

@yield('content')

      </div>
    </main>

@include('layouts.partials.site-footer')
  </div><!-- /page-wrapper -->

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>window.currentLang = '{{ app()->getLocale() }}';</script>
  <script src="{{ asset('site/js/language-manager.js') }}?v=1.1"></script>
  <script src="{{ asset('site/js/landing.js') }}"></script>
  <script src="{{ asset('site/js/animations.js') }}"></script>
  <script src="{{ asset('site/js/scroll-effects.js') }}"></script>
@stack('scripts')
</body>
</html>
