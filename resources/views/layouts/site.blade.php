@php
    $isRtl = app()->getLocale() === 'ar';
    $siteSettings = \App\Models\SiteSetting::current();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="theme-gold" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', $siteSettings->seo_title ?? 'FULL MARKS ACADEMY')</title>
  <meta name="description" content="@yield('meta_description', $siteSettings->seo_description ?? '')">
  <meta name="keywords" content="{{ $siteSettings->seo_keywords ?? '' }}">
  
  <!-- Favicons -->
  <link rel="icon" type="image/png" href="{{ asset('site/images/logo_v2_gold.png') }}">
  <link rel="shortcut icon" href="{{ asset('site/images/logo_v2_gold.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('site/images/logo_v2_gold.png') }}">

  <!-- Modern academic-grade typography (English + Arabic) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

  <!-- CSS Library CDN Inclusions -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
  
  <!-- Tailwind CSS Play CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            gold: {
              light: '#e8d0ad',
              DEFAULT: '#c5a880',
              dark: '#a3875f',
            }
          }
        }
      }
    }
  </script>

  <!-- Custom Theme Stylesheets -->
  <link rel="stylesheet" href="{{ asset('site/css/variables.css') }}?v=1.0">
  <link rel="stylesheet" href="{{ asset('site/css/themes/dark.css') }}?v=1.1" id="theme-dark">
  <link rel="stylesheet" href="{{ asset('site/css/themes/light.css') }}?v=1.1" id="theme-light">
  <link rel="stylesheet" href="{{ asset('site/css/themes/gold.css') }}?v=1.1" id="theme-gold">
  <link rel="stylesheet" href="{{ asset('site/css/animations/transitions.css') }}?v=1.0">
  <link rel="stylesheet" href="{{ asset('site/css/animations/scroll-effects.css') }}?v=1.0">
  <link rel="stylesheet" href="{{ asset('site/css/animations/3d-effects.css') }}?v=1.0">
  <link rel="stylesheet" href="{{ asset('site/css/animations/hover-effects.css') }}?v=1.1">
  <link rel="stylesheet" href="{{ asset('site/css/landing.css') }}?v=1.2">
  <link rel="stylesheet" href="{{ asset('site/css/hero-animation.css') }}?v=1.2">
  <!-- Arabic / RTL stylesheet — only its [dir="rtl"] rules activate -->
  <link rel="stylesheet" href="{{ asset('site/css/rtl.css') }}?v=1.1">

  <!-- Theme loading script (prevents theme flash) -->
  <script src="{{ asset('site/js/theme-manager.js') }}"></script>
  @stack('styles')
</head>
<body class="smooth-transition">

@unless (request()->routeIs('apply.create', 'apply.store', 'student.login', 'student.register'))
  <!-- Global Splash — shown once per browser session via site-splash.js -->
  <div id="hero-preloader" class="hero-splash" aria-hidden="true">
    <div class="hero-splash__bg"></div>
    
    <!-- Motivational Words -->
    <div class="hero-splash__words d-flex align-items-center justify-content-center">
      <span class="splash-word" data-en="Learn." data-ar="تعلم.">Learn.</span>
      <span class="splash-word" data-en="Grow." data-ar="تطور.">Grow.</span>
      <span class="splash-word" data-en="Excel." data-ar="تفوق.">Excel.</span>
    </div>

    <!-- Logo Pulse -->
    <div class="hero-splash__logo-wrap">
      <span class="hero-splash__orbit"></span>
      <img id="hero-splash-logo" class="hero-splash__logo object-contain" src="{{ asset('site/images/logo_v2_gold.png') }}" alt="FULL MARKS ACADEMY">
    </div>
  </div>
  <script src="{{ asset('site/js/site-splash.js') }}"></script>
@endunless

@include('layouts.partials.site-header')

@yield('content')

@include('layouts.partials.site-footer')

  <!-- JS Libraries -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

  <script src="{{ asset('site/js/hero-animation.js') }}"></script>
  <script src="{{ asset('site/js/landing.js') }}"></script>
  <script src="{{ asset('site/js/animations.js') }}"></script>
  <script src="{{ asset('site/js/scroll-effects.js') }}"></script>
  <script src="{{ asset('site/js/particles.js') }}"></script>
  <script src="{{ asset('site/js/cart.js') }}"></script>

  <script>
    window.currentLang = '{{ app()->getLocale() }}';
    window.isStudentLoggedIn = {{ auth('student')->check() ? 'true' : 'false' }};
    window.csrfToken = '{{ csrf_token() }}';
    window.studentRegisterUrl = '{{ route('student.register') }}';
  </script>
  <script src="{{ asset('site/js/language-manager.js') }}?v=1.1"></script>

  <script>
    function initSwiper() {
      const dir = document.documentElement.getAttribute('dir') || 'ltr';
      window.mySwiperInstance = new Swiper('.testimonials-swiper', {
        loop: true,
        autoplay: {
          delay: 4000,
          disableOnInteraction: false,
        },
        pagination: {
          el: '.swiper-pagination',
          clickable: true,
        },
        navigation: {
          nextEl: '.testimonials-swiper-next',
          prevEl: '.testimonials-swiper-prev',
        },
        slidesPerView: 1,
        spaceBetween: 20,
        breakpoints: {
          768: {
            slidesPerView: 2,
            spaceBetween: 30
          },
          1024: {
            slidesPerView: 3,
            spaceBetween: 30
          }
        }
      });

      window.newsSwiperInstance = new Swiper('.news-swiper', {
        loop: true,
        autoplay: {
          delay: 4000,
          disableOnInteraction: false,
        },
        slidesPerView: 1,
        spaceBetween: 24,
        navigation: {
          nextEl: '.news-swiper-next',
          prevEl: '.news-swiper-prev',
        },
        breakpoints: {
          768: {
            slidesPerView: 2,
            spaceBetween: 24
          },
          1024: {
            slidesPerView: 3,
            spaceBetween: 30
          }
        }
      });

      window.teachersSwiperInstance = new Swiper('.teachers-swiper', {
        loop: true,
        autoplay: {
          delay: 4000,
          disableOnInteraction: false,
        },
        slidesPerView: 1,
        spaceBetween: 24,
        navigation: {
          nextEl: '.teachers-swiper-next',
          prevEl: '.teachers-swiper-prev',
        },
        breakpoints: {
          768: {
            slidesPerView: 2,
            spaceBetween: 24
          },
          992: {
            slidesPerView: 3,
            spaceBetween: 24
          },
          1200: {
            slidesPerView: 4,
            spaceBetween: 30
          }
        }
      });
    }

    // Load Swiper on DOM ready
    document.addEventListener('DOMContentLoaded', () => {
      initSwiper();
    });

    // Recreate swiper when language changes to preserve slider direction
    window.addEventListener('languageChanged', () => {
      if (window.mySwiperInstance) {
        window.mySwiperInstance.destroy(true, true);
      }
      if (window.newsSwiperInstance) {
        window.newsSwiperInstance.destroy(true, true);
      }
      if (window.teachersSwiperInstance) {
        window.teachersSwiperInstance.destroy(true, true);
      }
      initSwiper();
    });

    // Handle Contact Form Submit Mock
    function handleFormSubmit(e) {
      e.preventDefault();
      const name = document.getElementById('contactName').value;
      const email = document.getElementById('contactEmail').value;
      
      const successMsg = window.currentLang === 'ar' 
        ? `شكرًا لك يا ${name}، تم إرسال رسالتك بنجاح! سنتواصل معك قريبًا على ${email}.`
        : `Thank you, ${name}! Your message has been sent successfully. We will contact you soon at ${email}.`;
        
      alert(successMsg);
      e.target.reset();
    }
  </script>
  @if(session('success'))
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        if (window.CartSystem && typeof window.CartSystem.showNotification === 'function') {
          window.CartSystem.showNotification({!! json_encode(session('success')) !!}, 'success');
        }
      });
    </script>
  @endif
  @if(session('error') || (isset($errors) && $errors->any()))
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        if (window.CartSystem && typeof window.CartSystem.showNotification === 'function') {
          window.CartSystem.showNotification({!! json_encode(session('error') ?? $errors->first()) !!}, 'warning');
        }
      });
    </script>
  @endif
</body>
</html>
@stack('scripts')
