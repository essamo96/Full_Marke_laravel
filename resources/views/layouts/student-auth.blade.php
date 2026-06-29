@php
    $isRtl = app()->getLocale() === 'ar';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="theme-gold" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Student Login | FULL MARK ACADEMY')</title>
  <meta name="description" content="Student portal login for FULL MARK ACADEMY.">

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
    .page-main { flex: 1; display: flex; align-items: center; justify-content: center; padding: clamp(3rem, 8vw, 6rem) 1rem; position: relative; }

    /* Ambient orbs */
    .ambient-orb { position: fixed; border-radius: 50%; pointer-events: none; z-index: 0; filter: blur(120px); }
    .ambient-orb-1 { width: 600px; height: 600px; top: -15%; right: -10%; background: var(--accent-glow); opacity: 0.6; animation: breathe 7s ease-in-out infinite; }
    .ambient-orb-2 { width: 400px; height: 400px; bottom: -10%; left: -8%; background: var(--accent-glow); opacity: 0.35; animation: breathe 9s ease-in-out infinite reverse; }
    @keyframes breathe { 0%,100%{transform:scale(1)} 50%{transform:scale(1.1)} }

    /* Subtle grid pattern */
    .bg-grid-pattern {
      position: fixed; inset: 0; z-index: 0; pointer-events: none;
      background-image: radial-gradient(circle at 1px 1px, rgba(197,168,128,0.04) 1px, transparent 0);
      background-size: 40px 40px;
    }

    /* Login card — larger for visual impact */
    .login-card {
      position: relative; z-index: 1;
      width: 100%; max-width: 500px;
    }
    .login-card-inner {
      background: var(--bg-secondary, rgba(13,10,6,0.93));
      backdrop-filter: blur(28px);
      -webkit-backdrop-filter: blur(28px);
      border: 1px solid rgba(197,168,128,0.2);
      border-radius: var(--radius-xl, 24px);
      box-shadow: 0 40px 100px rgba(0,0,0,0.55), 0 0 60px rgba(197,168,128,0.04) inset;
      overflow: hidden;
      padding: 3rem 3rem 2.5rem;
      transition: transform 0.6s cubic-bezier(.22,.7,.2,1), box-shadow 0.6s cubic-bezier(.22,.7,.2,1);
    }
    .login-card-inner:hover { transform: translateY(-4px); box-shadow: 0 50px 120px rgba(0,0,0,0.65), 0 0 60px rgba(197,168,128,0.06) inset; }

    /* Top accent line */
    .login-card-inner::before {
      content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
      background: var(--accent-gradient, linear-gradient(135deg, #c5a880, #7d633f));
    }

    /* Card header */
    .card-header-block { text-align: center; margin-bottom: 2rem; }
    .card-title {
      font-family: var(--font-en, 'Outfit', sans-serif);
      font-size: clamp(1.4rem, 3.5vw, 1.9rem);
      font-weight: 800;
      color: var(--accent-color, #c5a880);
      letter-spacing: -0.02em;
      margin-bottom: 0.4rem;
    }
    [dir="rtl"] .card-title { font-family: var(--font-ar, 'Tajawal', sans-serif); }
    .card-subtitle { color: var(--text-muted, #8c8276); font-size: 0.9rem; }

    /* Input groups */
    .input-group-luxe {
      position: relative;
      margin-bottom: 0;
    }
    .input-group-luxe label {
      display: block;
      font-size: 0.8rem;
      font-weight: 600;
      letter-spacing: 0.04em;
      color: var(--text-secondary, #d1c7bd);
      margin-bottom: 0.5rem;
    }
    .input-group-luxe .lux-input {
      width: 100%;
      padding: 0.9rem 1rem 0.9rem 2.8rem;
      background: rgba(5,4,2,0.45);
      border: 1px solid rgba(197,168,128,0.15);
      border-radius: var(--radius-md, 12px);
      color: var(--text-primary, #fbfbf9);
      font-size: 0.95rem;
      font-family: inherit;
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
      outline: none;
    }
    [dir="rtl"] .input-group-luxe .lux-input { padding: 0.9rem 2.8rem 0.9rem 1rem; }
    .input-group-luxe .lux-input:focus {
      border-color: var(--accent-color, #c5a880);
      box-shadow: 0 0 0 3px rgba(197,168,128,0.12);
    }
    .input-group-luxe .lux-input::placeholder { color: var(--text-muted, #8c8276); font-size: 0.9rem; }
    .input-group-luxe .input-icon {
      position: absolute;
      left: 0.9rem;
      bottom: 0.85rem;
      color: var(--text-muted, #8c8276);
      font-size: 1.05rem;
      pointer-events: none;
      transition: color 0.2s ease;
    }
    [dir="rtl"] .input-group-luxe .input-icon { left: auto; right: 0.9rem; }
    .input-group-luxe:focus-within .input-icon { color: var(--accent-color, #c5a880); }

    .pw-toggle-btn {
      position: absolute;
      right: 0.9rem;
      bottom: 0.8rem;
      background: none;
      border: none;
      color: var(--text-muted, #8c8276);
      cursor: pointer;
      padding: 0.25rem;
      transition: color 0.2s ease;
    }
    [dir="rtl"] .pw-toggle-btn { right: auto; left: 0.9rem; }
    .pw-toggle-btn:hover { color: var(--accent-color, #c5a880); }
    .pw-input-wrap .lux-input { padding-right: 2.8rem; }
    [dir="rtl"] .pw-input-wrap .lux-input { padding-right: 2.8rem; padding-left: 2.8rem; }

    /* Remember me & forgot */
    .form-meta {
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: 0.85rem;
    }
    .form-meta label { display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--text-secondary, #d1c7bd); }
    .form-meta input[type="checkbox"] { accent-color: var(--accent-color, #c5a880); width: 1rem; height: 1rem; }
    .form-meta a { color: var(--accent-color, #c5a880); text-decoration: none; font-weight: 600; }
    .form-meta a:hover { text-decoration: underline; }

    /* Divider */
    .or-divider { display: flex; align-items: center; gap: 0.75rem; color: var(--text-muted, #8c8276); font-size: 0.8rem; }
    .or-divider::before, .or-divider::after { content: ''; flex: 1; height: 1px; background: rgba(197,168,128,0.12); }

    /* Sign up link */
    .signup-link { text-align: center; font-size: 0.88rem; color: var(--text-muted, #8c8276); }
    .signup-link a { color: var(--accent-color, #c5a880); font-weight: 700; text-decoration: none; margin-inline-start: 0.3rem; }
    .signup-link a:hover { text-decoration: underline; }

    /* Quick access chips */
    .quick-access { display: flex; gap: 0.6rem; justify-content: center; flex-wrap: wrap; }
    .quick-chip {
      display: inline-flex; align-items: center; gap: 0.4rem;
      padding: 0.4rem 0.9rem;
      background: rgba(197,168,128,0.06);
      border: 1px solid rgba(197,168,128,0.15);
      border-radius: 9999px;
      color: var(--text-secondary, #d1c7bd);
      font-size: 0.78rem;
      text-decoration: none;
      transition: all 0.2s ease;
      cursor: pointer;
    }
    .quick-chip:hover { background: rgba(197,168,128,0.14); border-color: rgba(197,168,128,0.35); color: var(--accent-color, #c5a880); }

    /* Loading button state */
    .btn-luxury.loading { opacity: 0.75; pointer-events: none; }
    @media (max-width: 576px) { .login-card-inner { padding: 2rem 1.5rem; } }
  </style>
  @stack('styles')
</head>
<body class="smooth-transition">

  <div class="bg-grid-pattern" aria-hidden="true"></div>
  <div class="ambient-orb ambient-orb-1" aria-hidden="true"></div>
  <div class="ambient-orb ambient-orb-2" aria-hidden="true"></div>

@include('layouts.partials.site-header')

  <!-- ════ MAIN CONTENT ════ -->
  <div class="page-wrapper">
    <main class="page-main">
      <div class="login-card reveal-scale">
        <div class="login-card-inner">

          <!-- Card Header -->
          <div class="card-header-block">
            <h1 class="card-title" data-en="Student Portal" data-ar="بوابة الطالب">Student Portal</h1>
            <p class="card-subtitle" data-en="Welcome back — sign in to continue your learning journey." data-ar="مرحباً بك — سجّل دخولك لمواصلة رحلتك الأكاديمية.">
              Welcome back — sign in to continue your learning journey.
            </p>
          </div>

          <!-- Login Form -->

@yield('content')

        </div><!-- /inner -->
      </div><!-- /card -->
    </main>

@include('layouts.partials.site-footer')
  </div><!-- /page-wrapper -->

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('site/js/landing.js') }}"></script>
  <script src="{{ asset('site/js/animations.js') }}"></script>
  <script src="{{ asset('site/js/scroll-effects.js') }}"></script>

@stack('scripts')
</body>
</html>
