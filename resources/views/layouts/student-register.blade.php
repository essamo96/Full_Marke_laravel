@php
    $isRtl = app()->getLocale() === 'ar';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="theme-gold" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Student Registration | FULL MARK ACADEMY')</title>
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
  <link rel="stylesheet" href="{{ asset('site/css/rtl.css') }}">

  <!-- Theme loading script (prevents theme flash) -->
  <script src="{{ asset('site/js/theme-manager.js') }}"></script>

  <style>
    /* ── Page-specific styles ── */
    .page-wrapper {
      min-height: 100dvh;
      display: flex;
      flex-direction: column;
      padding-top: var(--navbar-height, 80px);
    }
    .page-main {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: clamp(2rem, 5vw, 4rem) 1rem;
      position: relative;
    }

    /* Ambient decorative orbs */
    .ambient-orb {
      position: fixed;
      border-radius: 50%;
      pointer-events: none;
      z-index: 0;
      filter: blur(100px);
      animation: orbFloat 8s ease-in-out infinite;
    }
    .ambient-orb-1 {
      width: 500px; height: 500px;
      top: -10%; left: -8%;
      background: var(--accent-glow, rgba(197,168,128,0.08));
    }
    .ambient-orb-2 {
      width: 400px; height: 400px;
      bottom: -10%; right: -8%;
      background: var(--accent-glow, rgba(197,168,128,0.06));
      animation-delay: 3s;
    }
    @keyframes orbFloat {
      0%, 100% { transform: translateY(0) scale(1); }
      50% { transform: translateY(-30px) scale(1.05); }
    }

    /* Registration card */
    .register-card {
      position: relative;
      z-index: 1;
      width: 100%;
      max-width: 860px;
    }
    .register-card-inner {
      background: var(--bg-secondary, rgba(13,10,6,0.92));
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      border: 1px solid rgba(197, 168, 128, 0.18);
      border-radius: var(--radius-xl, 24px);
      box-shadow: 0 32px 80px rgba(0,0,0,0.5), 0 0 0 1px rgba(197,168,128,0.05) inset;
      overflow: hidden;
    }
    /* Gold accent top bar */
    .register-card-inner::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 2px;
      background: var(--accent-gradient, linear-gradient(135deg, #c5a880, #7d633f));
    }

    /* Form section header */
    .form-section-head {
      text-align: center;
      padding: 3rem 3rem 1.5rem;
    }
    .form-title {
      font-family: var(--font-en, 'Outfit', sans-serif);
      font-size: clamp(1.6rem, 4vw, 2.4rem);
      font-weight: 800;
      color: var(--accent-color, #c5a880);
      letter-spacing: -0.02em;
      line-height: 1.15;
      margin-bottom: 0.5rem;
    }
    [dir="rtl"] .form-title { font-family: var(--font-ar, 'Tajawal', sans-serif); }
    .form-subtitle {
      color: var(--text-secondary, #d1c7bd);
      font-size: 1rem;
      max-width: 540px;
      margin: 0 auto;
      line-height: 1.6;
    }
    .gold-divider {
      width: 64px;
      height: 3px;
      background: var(--accent-gradient, linear-gradient(135deg, #c5a880, #7d633f));
      border-radius: 9999px;
      margin: 1.25rem auto;
    }

    /* Form body */
    .form-body {
      padding: 0 3rem 3rem;
    }
    @media (max-width: 576px) {
      .form-section-head { padding: 2rem 1.5rem 1rem; }
      .form-body { padding: 0 1.5rem 2rem; }
    }

    /* Floating label inputs */
    .floating-input-group {
      position: relative;
      margin-bottom: 0;
    }
    .floating-input-group .fi-label {
      position: absolute;
      top: 1.1rem;
      inset-inline-start: 1rem;
      font-size: 0.9rem;
      color: var(--text-muted, #8c8276);
      pointer-events: none;
      transition: all 0.22s cubic-bezier(.22,.7,.2,1);
      background: transparent;
      padding: 0 0.3rem;
    }
    .floating-input-group .fi-input:focus ~ .fi-label,
    .floating-input-group .fi-input:not(:placeholder-shown) ~ .fi-label,
    .floating-input-group .fi-select:focus ~ .fi-label,
    .floating-input-group .fi-select:not([value=""]):valid ~ .fi-label {
      top: -0.55rem;
      font-size: 0.75rem;
      color: var(--accent-color, #c5a880);
      background: var(--bg-secondary, #0d0a06);
    }
    .floating-input-group .fi-input,
    .floating-input-group .fi-select,
    .floating-input-group .fi-textarea {
      width: 100%;
      padding: 1rem 1rem 0.75rem;
      background: var(--bg-tertiary, rgba(10,8,4,0.5));
      border: 1px solid rgba(197,168,128,0.18);
      border-radius: var(--radius-md, 12px);
      color: var(--text-primary, #fbfbf9);
      font-size: 0.95rem;
      font-family: inherit;
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
      outline: none;
      appearance: none;
    }
    .floating-input-group .fi-input:focus,
    .floating-input-group .fi-select:focus,
    .floating-input-group .fi-textarea:focus {
      border-color: var(--accent-color, #c5a880);
      box-shadow: 0 0 0 3px rgba(197,168,128,0.12);
    }
    .floating-input-group .fi-input::placeholder { color: transparent; }
    .floating-input-group .fi-textarea::placeholder { color: transparent; }
    .fi-select option { background: var(--bg-secondary, #0d0a06); color: var(--text-primary, #fbfbf9); }

    /* Icon inside input */
    .fi-icon {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      inset-inline-end: 1rem;
      color: var(--text-muted, #8c8276);
      pointer-events: none;
      font-size: 1.1rem;
      transition: color 0.2s ease;
    }
    .floating-input-group:focus-within .fi-icon { color: var(--accent-color, #c5a880); }
    .floating-input-group .fi-input:not([type="date"]) { padding-inline-end: 2.8rem; }

    /* Terms checkbox */
    .terms-block {
      display: flex;
      gap: 1rem;
      align-items: flex-start;
      padding: 1.25rem;
      background: rgba(197,168,128,0.05);
      border: 1px solid rgba(197,168,128,0.1);
      border-radius: var(--radius-md, 12px);
    }
    .terms-block input[type="checkbox"] {
      flex-shrink: 0;
      width: 1.2rem;
      height: 1.2rem;
      margin-top: 0.2rem;
      accent-color: var(--accent-color, #c5a880);
      cursor: pointer;
    }
    .terms-block label {
      font-size: 0.9rem;
      color: var(--text-secondary, #d1c7bd);
      line-height: 1.6;
      cursor: pointer;
    }
    .terms-block a { color: var(--accent-color, #c5a880); font-weight: 700; text-decoration: none; }
    .terms-block a:hover { text-decoration: underline; }

    /* Section divider */
    .form-section-label {
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: var(--accent-color, #c5a880);
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    .form-section-label::after {
      content: '';
      flex: 1;
      height: 1px;
      background: rgba(197,168,128,0.18);
    }

    /* Login link */
    .login-link-block {
      text-align: center;
      padding-top: 1rem;
      font-size: 0.9rem;
      color: var(--text-muted, #8c8276);
    }
    .login-link-block a {
      color: var(--accent-color, #c5a880);
      font-weight: 700;
      text-decoration: none;
      margin-inline-start: 0.35rem;
    }
    .login-link-block a:hover { text-decoration: underline; }

    /* Submit button animation */
    .btn-luxury.loading { opacity: 0.7; pointer-events: none; }

    /* Step indicator */
    .step-indicator {
      display: flex;
      justify-content: center;
      gap: 0.5rem;
      margin-bottom: 2rem;
    }
    .step-dot {
      width: 8px; height: 8px;
      border-radius: 50%;
      background: rgba(197,168,128,0.25);
      transition: all 0.3s ease;
    }
    .step-dot.active { background: var(--accent-color, #c5a880); width: 24px; border-radius: 4px; }
  </style>
  @stack('styles')
</head>
<body class="smooth-transition">

  <div class="ambient-orb ambient-orb-1" aria-hidden="true"></div>
  <div class="ambient-orb ambient-orb-2" aria-hidden="true"></div>

@include('layouts.partials.site-header')

  <!-- ════ MAIN CONTENT ════ -->
  <div class="page-wrapper">
    <main class="page-main">
      <div class="register-card reveal-scale">
        <div class="register-card-inner">

@yield('content')

        </div><!-- /register-card-inner -->
      </div><!-- /register-card -->
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
