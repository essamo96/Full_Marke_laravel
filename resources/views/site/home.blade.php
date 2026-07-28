@extends('layouts.site')

@section('title', \App\Models\SiteSetting::current()->translation->seo_title ?? 'FULL MARKS ACADEMY')

@section('content')
  <!-- Hero Section -->
  <section id="hero" class="hero-section">
    @php
        $about = $pages['about_us'] ?? null;
        $aboutAr = $about?->translations->where('locale', 'ar')->first();
        $aboutEn = $about?->translations->where('locale', 'en')->first();

        // The admin panel's video field for this page accepts any file via the
        // File Manager picker, so it may actually hold an image instead of a
        // video — detect which one it is so the right tag gets rendered.
        $aboutMediaPath = $about->video ?? null;
        $aboutMediaIsImage = $aboutMediaPath && in_array(strtolower(pathinfo($aboutMediaPath, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp']);
        $aboutMediaUrl = $aboutMediaPath ? asset('storage/' . $aboutMediaPath) : null;

        $features = $pages['features'] ?? null;
        $featuresAr = $features?->translations->where('locale', 'ar')->first();
        $featuresEn = $features?->translations->where('locale', 'en')->first();

        $services = $pages['services'] ?? null;
        $servicesAr = $services?->translations->where('locale', 'ar')->first();
        $servicesEn = $services?->translations->where('locale', 'en')->first();

        $training = $pages['training_hours'] ?? null;
        $trainingAr = $training?->translations->where('locale', 'ar')->first();
        $trainingEn = $training?->translations->where('locale', 'en')->first();

        $firstSlider = $sliders->first();
        $hasMedia = $firstSlider && ($firstSlider->video1 || $firstSlider->video2 || $firstSlider->image);
    @endphp

    @if($sliders->count() > 0 && !$hasMedia)
      <!-- Moving Slider (Swiper) when no media is present -->
      <div class="swiper hero-moving-swiper w-100 h-100">
        <div class="swiper-wrapper">
          @foreach($sliders as $slider)
          <div class="swiper-slide d-flex align-items-center justify-content-center" style="background: var(--bg-primary);">
            <div id="hero-content-overlay-{{ $slider->id }}" class="hero-content container px-4 text-center">
              <div class="reveal-scale">
                <h1 class="hero-stagger text-3xl md:text-5xl font-extrabold tracking-tight mb-3 uppercase" style="color: var(--text-primary);">
                  <span class="hero-academy-title" data-en="{{ $slider->title_en }}" data-ar="{{ $slider->title_ar }}">
                    {{ app()->getLocale() == 'ar' ? $slider->title_ar : $slider->title_en }}
                  </span>
                </h1>
                <p class="hero-stagger text-base md:text-xl text-secondary-class mb-6 max-w-2xl mx-auto leading-relaxed" style="color: var(--text-secondary);"
                   data-en="{{ $slider->desc_en }}" data-ar="{{ $slider->desc_ar }}">
                   {{ app()->getLocale() == 'ar' ? $slider->desc_ar : $slider->desc_en }}
                </p>
                <div class="hero-stagger d-flex flex-column flex-sm-row justify-content-center align-items-center gap-3">
                  @if($slider->btn1_text_ar || $slider->btn1_text_en)
                  <a href="{{ $slider->btn1_link }}" class="btn btn-luxury px-4 py-2 rounded-xl w-60 sm:w-auto text-base d-flex align-items-center justify-content-center"
                     data-en="{{ $slider->btn1_text_en }}" data-ar="{{ $slider->btn1_text_ar }}">
                    {{ app()->getLocale() == 'ar' ? $slider->btn1_text_ar : $slider->btn1_text_en }}
                  </a>
                  @endif
                  @if($slider->btn2_text_ar || $slider->btn2_text_en)
                  <a href="{{ $slider->btn2_link }}" class="btn btn-glass px-4 py-2 rounded-xl w-60 sm:w-auto text-base d-flex align-items-center justify-content-center"
                     data-en="{{ $slider->btn2_text_en }}" data-ar="{{ $slider->btn2_text_ar }}">
                    {{ app()->getLocale() == 'ar' ? $slider->btn2_text_ar : $slider->btn2_text_en }}
                  </a>
                  @endif
                </div>
              </div>
            </div>
          </div>
          @endforeach
        </div>
        <div class="swiper-button-prev hero-swiper-prev"></div>
        <div class="swiper-button-next hero-swiper-next"></div>
      </div>
      <script>
        document.addEventListener('DOMContentLoaded', function () {
          const heroSwiperEl = document.querySelector('.hero-moving-swiper');
          if (heroSwiperEl) {
            new Swiper('.hero-moving-swiper', {
              // Loop needs at least 2 slides — with one slide Swiper logs a
              // console warning and disables it anyway.
              loop: heroSwiperEl.querySelectorAll('.swiper-slide').length > 1,
              autoplay: {
                delay: 5000,
                disableOnInteraction: false,
              },
              navigation: {
                nextEl: '.hero-swiper-next',
                prevEl: '.hero-swiper-prev',
              },
              effect: 'fade',
              fadeEffect: {
                crossFade: true
              }
            });
          }
        });
      </script>

      <!-- Symmetrical Floating 3D Cards (Static underneath) -->
      <div class="hero-stagger hidden md:grid grid-cols-3 gap-4 mt-16 max-w-4xl mx-auto position-absolute bottom-10 start-50 translate-middle-x z-3" style="width: 100%;">
        <div class="glass-panel tilt-card p-4 text-center cursor-pointer float-slow">
          <i class="bi bi-shield-check text-3xl mb-2 text-gold"></i>
          <h4 class="font-bold text-sm mb-1" data-en="Approved OTE Center" data-ar="مركز اختبار معتمد">Approved OTE Center</h4>
          <p class="text-xs opacity-75" data-en="Direct licensing from Full Mark University Press" data-ar="ترخيص مباشر من مطبعة جامعة العلامة الكاملة">Direct licensing from Full Mark University Press</p>
        </div>
        <div class="glass-panel tilt-card p-4 text-center cursor-pointer float-medium">
          <i class="bi bi-person-workspace text-3xl mb-2 text-gold"></i>
          <h4 class="font-bold text-sm mb-1" data-en="Expert Instructors" data-ar="مدربون ذوو خبرة">Expert Instructors</h4>
          <p class="text-xs opacity-75" data-en="Certified ESL trainers with global experience" data-ar="مدربون لغة معتمدون ذوو خبرة عالمية">Certified ESL trainers with global experience</p>
        </div>
        <div class="glass-panel tilt-card p-4 text-center cursor-pointer float-fast">
          <i class="bi bi-clock-history text-3xl mb-2 text-gold"></i>
          <h4 class="font-bold text-sm mb-1" data-en="Flexible Hours" data-ar="أوقات مرنة">Flexible Hours</h4>
          <p class="text-xs opacity-75" data-en="Morning & evening schedules fit for work and school" data-ar="مواعيد صباحية ومسائية تناسب العمل والدراسة">Morning & evening schedules fit for work and school</p>
        </div>
      </div>

    @else
      <!-- Static Hero (either has media from db or original fallback) -->
      <div class="hero-frame-stage" aria-hidden="true">
        @php
            $heroPoster = ($hasMedia && $firstSlider->image) ? asset('storage/' . $firstSlider->image) : asset('site/images/bg-main.jpg');
        @endphp
        @if($hasMedia)
            @if($firstSlider->video1)
            <video id="hero-bg-video-1" class="hero-frame-stage__media hero-frame-stage__video is-active" src="{{ asset('storage/' . $firstSlider->video1) }}" poster="{{ $heroPoster }}" muted playsinline preload="auto" fetchpriority="high"></video>
            @endif
            @if($firstSlider->video2)
            <video id="hero-bg-video-2" class="hero-frame-stage__media hero-frame-stage__video {{ !$firstSlider->video1 ? 'is-active' : '' }}" src="{{ asset('storage/' . $firstSlider->video2) }}" poster="{{ $heroPoster }}" muted playsinline preload="auto"></video>
            @endif
            @if($firstSlider->image)
            <img id="hero-bg-still" class="hero-frame-stage__media hero-frame-stage__still {{ (!$firstSlider->video1 && !$firstSlider->video2) ? 'is-active' : '' }}" src="{{ asset('storage/' . $firstSlider->image) }}" alt="">
            @endif
        @else
            <!-- Fallback to original backgrounds -->
            <video id="hero-bg-video-1" class="hero-frame-stage__media hero-frame-stage__video is-active" src="{{ asset('site/images/slider1.mp4') }}" poster="{{ $heroPoster }}" muted playsinline preload="auto" fetchpriority="high"></video>
            <video id="hero-bg-video-2" class="hero-frame-stage__media hero-frame-stage__video" src="{{ asset('site/images/slider1.mp4') }}" poster="{{ $heroPoster }}" muted playsinline preload="auto"></video>
            <img id="hero-bg-still" class="hero-frame-stage__media hero-frame-stage__still" src="{{ asset('site/images/bg-main.jpg') }}" alt="">
        @endif
      </div>

      <div class="hero-overlay"></div>
      <canvas id="particles-canvas" class="particles-canvas-class"></canvas>

      <!-- Floating Background Shapes -->
      <div class="absolute top-1/4 left-10 w-24 h-24 bg-gold opacity-10 rounded-full blur-xl float-slow parallax-layer" data-parallax-speed="0.03"></div>
      <div class="absolute bottom-1/4 right-20 w-36 h-36 bg-cyan-400 opacity-10 rounded-full blur-2xl float-medium parallax-layer" data-parallax-speed="-0.02"></div>
      <div class="absolute top-1/3 right-1/4 w-16 h-16 bg-purple-500 opacity-5 rounded-full blur-lg float-fast parallax-layer" data-parallax-speed="0.05"></div>

      <div id="hero-content-overlay" class="hero-content hero-content-overlay container px-4">
        <div class="reveal-scale">
          <h1 class="hero-stagger text-3xl md:text-5xl font-extrabold tracking-tight mb-3 uppercase" data-stagger="1" style="color: var(--text-primary);">
            @if($firstSlider)
                <span class="hero-academy-title" data-en="{{ $firstSlider->title_en }}" data-ar="{{ $firstSlider->title_ar }}">
                    {{ app()->getLocale() == 'ar' ? $firstSlider->title_ar : $firstSlider->title_en }}
                </span>
            @else
                <span class="hero-academy-title" data-en="FULL MARKS ACADEMY" data-ar="أكاديمية العلامة الكاملة">FULL MARKS ACADEMY</span>
            @endif
          </h1>
          
          <p class="hero-stagger text-base md:text-xl text-secondary-class mb-6 max-w-2xl mx-auto leading-relaxed" data-stagger="2" style="color: var(--text-secondary);"
             @if($firstSlider)
             data-en="{{ $firstSlider->desc_en }}" data-ar="{{ $firstSlider->desc_ar }}"
             @else
             data-en="Step Into Professional Excellence. Approved Full Mark Test Center & Global Academic Preparation."
             data-ar="بوابتك للتميز الأكاديمي والمهني. مركز اختبارات العلامة الكاملة المعتمد والتدريب اللغوي الدولي."
             @endif
             >
            @if($firstSlider)
                {{ app()->getLocale() == 'ar' ? $firstSlider->desc_ar : $firstSlider->desc_en }}
            @else
                {{ app()->getLocale() == 'ar' ? 'بوابتك للتميز الأكاديمي والمهني. مركز اختبارات العلامة الكاملة المعتمد والتدريب اللغوي الدولي.' : 'Step Into Professional Excellence. Approved Full Mark Test Center & Global Academic Preparation.' }}
            @endif
          </p>

          <!-- CTA Buttons -->
          <div class="hero-stagger d-flex flex-column flex-sm-row justify-content-center align-items-center gap-3" data-stagger="3">
            @if($firstSlider)
                @if($firstSlider->btn1_text_ar || $firstSlider->btn1_text_en)
                <a href="{{ $firstSlider->btn1_link }}" class="btn btn-luxury px-4 py-2 rounded-xl w-60 sm:w-auto text-base d-flex align-items-center justify-content-center"
                   data-en="{{ $firstSlider->btn1_text_en }}" data-ar="{{ $firstSlider->btn1_text_ar }}">
                  {{ app()->getLocale() == 'ar' ? $firstSlider->btn1_text_ar : $firstSlider->btn1_text_en }}
                </a>
                @endif
                @if($firstSlider->btn2_text_ar || $firstSlider->btn2_text_en)
                <a href="{{ $firstSlider->btn2_link }}" class="btn btn-glass px-4 py-2 rounded-xl w-60 sm:w-auto text-base d-flex align-items-center justify-content-center"
                   data-en="{{ $firstSlider->btn2_text_en }}" data-ar="{{ $firstSlider->btn2_text_ar }}">
                  {{ app()->getLocale() == 'ar' ? $firstSlider->btn2_text_ar : $firstSlider->btn2_text_en }}
                </a>
                @endif
            @else
                <a href="#actions" class="btn btn-luxury px-4 py-2 rounded-xl w-60 sm:w-auto text-base d-flex align-items-center justify-content-center"
                   data-en="Placement Test Booking" data-ar="حجز تحديد المستوى">
                  Placement Test Booking
                </a>
                <a href="#contact" class="btn btn-glass px-4 py-2 rounded-xl w-60 sm:w-auto text-base d-flex align-items-center justify-content-center"
                   data-en="Book A Course" data-ar="حجز مقعد دراسي">
                  Book A Course
                </a>
            @endif
          </div>
        </div>

        <!-- Symmetrical Floating 3D Cards -->
        <div class="hero-stagger hidden md:grid grid-cols-3 gap-4 mt-16 max-w-4xl mx-auto" data-stagger="4">
          <div class="glass-panel tilt-card p-4 text-center cursor-pointer float-slow">
            <i class="bi bi-shield-check text-3xl mb-2 text-gold"></i>
            <h4 class="font-bold text-sm mb-1" data-en="Approved OTE Center" data-ar="مركز اختبار معتمد">Approved OTE Center</h4>
            <p class="text-xs opacity-75" data-en="Direct licensing from Full Mark University Press" data-ar="ترخيص مباشر من مطبعة جامعة العلامة الكاملة">Direct licensing from Full Mark University Press</p>
          </div>
          <div class="glass-panel tilt-card p-4 text-center cursor-pointer float-medium">
            <i class="bi bi-person-workspace text-3xl mb-2 text-gold"></i>
            <h4 class="font-bold text-sm mb-1" data-en="Expert Instructors" data-ar="مدربون ذوو خبرة">Expert Instructors</h4>
            <p class="text-xs opacity-75" data-en="Certified ESL trainers with global experience" data-ar="مدربون لغة معتمدون ذوو خبرة عالمية">Certified ESL trainers with global experience</p>
          </div>
          <div class="glass-panel tilt-card p-4 text-center cursor-pointer float-fast">
            <i class="bi bi-clock-history text-3xl mb-2 text-gold"></i>
            <h4 class="font-bold text-sm mb-1" data-en="Flexible Hours" data-ar="أوقات مرنة">Flexible Hours</h4>
            <p class="text-xs opacity-75" data-en="Morning & evening schedules fit for work and school" data-ar="مواعيد صباحية ومسائية تناسب العمل والدراسة">Morning & evening schedules fit for work and school</p>
          </div>
        </div>

      </div>
    @endif
    <!-- Scroll Indicator (outside overlay so it positions relative to #hero) -->
    <div id="hero-scroll-indicator" class="hero-stagger" data-stagger="5">
        <a href="#about" class="text-decoration-none" style="color: var(--text-primary);">
          <span class="text-xs tracking-widest block mb-2 uppercase" data-en="SCROLL DOWN" data-ar="انزل للأسفل">SCROLL DOWN</span>
          <i class="bi bi-chevron-double-down animate-bounce text-lg"></i>
        </a>
      </div>
  </section>

  <!-- About Section -->
  <section id="about" style="background: var(--bg-secondary);">
    <div class="container px-4">
      <div class="row align-items-center gy-12">
        <!-- Text Column -->
        <div class="col-lg-6 reveal-left">
          <h5 class="section-subtitle" data-en="DISCOVER FULL MARK" data-ar="اكتشف العلامة الكاملة">DISCOVER FULL MARK</h5>
          <h2 class="section-title" data-en="{{ $aboutEn?->title ?? 'Leading Academic Training Institution' }}" data-ar="{{ $aboutAr?->title ?? 'المؤسسة الأكاديمية الرائدة للتدريب' }}">
            {{ app()->getLocale() == 'ar' ? ($aboutAr?->title ?? 'المؤسسة الأكاديمية الرائدة للتدريب') : ($aboutEn?->title ?? 'Leading Academic Training Institution') }}
          </h2>
          <div class="section-divider"></div>
          <div class="text-lg leading-relaxed mb-6" style="color: var(--text-secondary);">
             @if(app()->getLocale() == 'ar')
                 {!! $aboutAr?->details ?? 'تقف أكاديمية العلامة الكاملة في طليعة تعليم اللغات والتقييم الدولي. بصفتنا مركزًا معتمدًا لاختبار Full Mark Test of English (OTE)، نقدم شهادات معترف بها عالميًا إلى جانب تدريب أكاديمي متميز مُعد خصيصًا لاجتياز اختبار آيلتس، المستويات العامة، والمحادثة المهنية للمؤسسات.' !!}
             @else
                 {!! $aboutEn?->details ?? 'FULL MARKS ACADEMY stands at the forefront of language education and testing. As an approved Full Mark Test of English (OTE) center, we deliver globally recognized certifications alongside premier academic instruction tailored for IELTS preparation, general levels, and corporate business communication.' !!}
             @endif
          </div>
          <div class="row g-4 mb-8">
            <div class="col-6 d-flex align-items-start">
              <i class="bi bi-patch-check-fill text-gold text-2xl me-3"></i>
              <div>
                <h5 class="font-bold mb-1" style="color: var(--text-primary);" data-en="Prestige" data-ar="سمعة دولية">Prestige</h5>
                <p class="text-sm opacity-75" data-en="Full Mark standards in testing" data-ar="معايير العلامة الكاملة للتقييم">Full Mark standards in testing</p>
              </div>
            </div>
            <div class="col-6 d-flex align-items-start">
              <i class="bi bi-journal-bookmark-fill text-gold text-2xl me-3"></i>
              <div>
                <h5 class="font-bold mb-1" style="color: var(--text-primary);" data-en="Methodology" data-ar="مناهج حديثة">Methodology</h5>
                <p class="text-sm opacity-75" data-en="Immersive teaching styles" data-ar="طرق تعليم تفاعلية غامرة">Immersive teaching styles</p>
              </div>
            </div>
          </div>
          <a href="#contact" class="btn btn-luxury px-4 py-3 rounded-lg" data-en="Contact Us" data-ar="تواصل معنا">Contact Us</a>
        </div>

        <!-- Video/Image Column -->
        <div class="col-lg-6 reveal-right">
          <div class="relative p-3 glass-panel">
            <div class="about-video-wrapper relative overflow-hidden rounded-lg h-80 md:h-[450px]">
              @if($aboutMediaIsImage)
                <img id="about-video"
                     class="about-video"
                     src="{{ $aboutMediaUrl }}"
                     alt="">
              @else
                <video id="about-video"
                       class="about-video"
                       src="{{ $aboutMediaUrl ?: asset('site/images/aboutUs.mp4') }}"
                       muted
                       loop
                       playsinline
                       preload="metadata"
                       poster="{{ asset('site/images/img/news/news1.png') }}"></video>
              @endif
              <div class="about-video-tint"></div>
            </div>

            <!-- Floating overlay widget -->
            <div class="absolute -bottom-6 -left-6 glass-panel p-4 max-w-xs shadow-2xl hidden sm:block">
              <div class="d-flex align-items-center">
                <div class="w-12 h-12 bg-gold-dark bg-opacity-20 text-gold rounded-full d-flex align-items-center justify-content-center text-xl me-3">
                  <i class="bi bi-mortarboard-fill"></i>
                </div>
                <div>
                  <h6 class="font-bold mb-0" style="color: var(--text-primary);" data-en="Full Mark Promise" data-ar="عهد العلامة الكاملة">Full Mark Promise</h6>
                  <p class="text-xs opacity-75 mb-0" data-en="Guaranteed level improvements" data-ar="تحسن مضمون ومثبت في المستويات">Guaranteed level improvements</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Section Separator -->
  <div class="section-separator reveal">
    <div class="separator-icon"></div>
  </div>

  <!-- Programs Section -->
  <section id="programs" style="background: var(--bg-primary);">
    <div class="container px-4">
      <div class="text-center max-w-2xl mx-auto mb-16 reveal">
        <h5 class="section-subtitle" data-en="OUR ACADEMIC PROGRAMS" data-ar="برامجنا التعليمية">OUR ACADEMIC PROGRAMS</h5>
        <h2 class="section-title" data-en="Specialized Programs for a Brighter Future" data-ar="برامج متخصصة لبناء مستقبل واعد">Specialized Programs for a Brighter Future</h2>
        <div class="section-divider mx-auto"></div>
      </div>

      <div class="row g-4 justify-content-center reveal">
        @foreach ($programs as $prog)
        <div class="col-md-6 col-lg-3">
          <div class="glass-panel news-card hover-premium-card h-100 d-flex flex-column justify-content-between">
            <a href="{{ route('programs.show', $prog->slug) }}" class="news-img-wrapper d-block overflow-hidden">
              <img src="{{ $prog->image ? (str_starts_with($prog->image, 'site/') ? asset($prog->image) : asset('storage/' . $prog->image)) : asset('site/images/img/programs/prog1.png') }}" alt="{{ $prog->name }}" class="news-img">
            </a>
            <div class="card-content-wrapper flex-grow d-flex flex-column justify-content-between">
              <div>
                <h4 class="text-xl font-bold mb-3">
                  <a href="{{ route('programs.show', $prog->slug) }}" class="stretched-link text-decoration-none hover-text-accent" style="color: var(--text-primary);" data-en="{{ $prog->name_en }}" data-ar="{{ $prog->name_ar }}">
                    {{ app()->getLocale() == 'ar' ? $prog->name_ar : $prog->name_en }}
                  </a>
                </h4>
                <p class="opacity-75 text-sm mb-4" style="color: var(--text-secondary);"
                   data-en="{{ \Illuminate\Support\Str::limit(strip_tags(str_replace('&nbsp;', ' ', html_entity_decode($prog->short_description ?? ''))), 120) }}"
                   data-ar="{{ \Illuminate\Support\Str::limit(strip_tags(str_replace('&nbsp;', ' ', html_entity_decode($prog->long_description ?? $prog->short_description ?? ''))), 120) }}">
                  {{ \Illuminate\Support\Str::limit(strip_tags(str_replace('&nbsp;', ' ', html_entity_decode(app()->getLocale() == 'ar' ? ($prog->long_description ?? $prog->short_description ?? '') : ($prog->short_description ?? '')))), 120) }}
                </p>
              </div>
              <a href="{{ route('programs.show', $prog->slug) }}" class="btn btn-luxury w-100 py-2.5 rounded-lg text-center mt-3 position-relative z-10 text-decoration-none" data-en="Join Program" data-ar="الانضمام للبرنامج">
                {{ app()->getLocale() == 'ar' ? 'الانضمام للبرنامج' : 'Join Program' }}
              </a>
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </section>

  <!-- Section Separator -->
  <div class="section-separator reveal">
    <div class="separator-icon"></div>
  </div>

  <!-- Core Strengths Section (Hexagon Grid) -->
  <section id="strengths" style="background: var(--bg-primary);">
    <div class="container px-4">
      <div class="text-center max-w-2xl mx-auto mb-16 reveal">
        @php 
            $featSubtitleEn = $featuresEn?->subtitle ?? 'WHY FULL MARK?';
            $featSubtitleAr = $featuresAr?->subtitle ?? 'لماذا العلامة الكاملة؟';
        @endphp
        <h5 class="section-subtitle" data-en="{{ $featSubtitleEn }}" data-ar="{{ $featSubtitleAr }}">
            {{ app()->getLocale() == 'ar' ? $featSubtitleAr : $featSubtitleEn }}
        </h5>
        <h2 class="section-title" data-en="{{ $featuresEn?->title ?? 'Core Pillars of Academic Success' }}" data-ar="{{ $featuresAr?->title ?? 'الركائز الأساسية للنجاح الأكاديمي' }}">
            {{ app()->getLocale() == 'ar' ? ($featuresAr?->title ?? 'الركائز الأساسية للنجاح الأكاديمي') : ($featuresEn?->title ?? 'Core Pillars of Academic Success') }}
        </h2>
        <div class="section-divider mx-auto"></div>
      </div>

      @php
          $featDetails = app()->getLocale() == 'ar' ? $featuresAr?->details : $featuresEn?->details;
          $featDetailsPlain = trim(str_replace("\xc2\xa0", '', strip_tags(html_entity_decode($featDetails ?? ''))));
      @endphp
      @if(!empty($featDetailsPlain))
          <div class="reveal mt-4">
              {!! $featDetails !!}
          </div>
      @else
          <div class="hexagon-grid reveal">
            <!-- Timetable -->
            <div class="glass-panel hexagon-card">
              <div class="hexagon-wrapper">
                <div class="hexagon-wrapper-inner">
                  <i class="fa-solid fa-clock"></i>
                </div>
              </div>
              <h4 class="text-xl font-bold mb-3" style="color: var(--text-primary);" data-en="Timetable" data-ar="جدول مرن">Timetable</h4>
              <p class="text-sm leading-relaxed" style="color: var(--text-secondary);"
                 data-en="Structured scheduling designed to adapt to school, university, and employment constraints."
                 data-ar="جداول زمنية منظمة ومصممة بدقة لتناسب مواعيد المدارس والجامعات وساعات العمل.">
                Structured scheduling designed to adapt to school, university, and employment constraints.
              </p>
            </div>

            <!-- Teachers -->
            <div class="glass-panel hexagon-card">
              <div class="hexagon-wrapper">
                <div class="hexagon-wrapper-inner">
                  <i class="fa-solid fa-users-line"></i>
                </div>
              </div>
              <h4 class="text-xl font-bold mb-3" style="color: var(--text-primary);" data-en="Instructors" data-ar="طاقم التدريس">Instructors</h4>
              <p class="text-sm leading-relaxed" style="color: var(--text-secondary);"
                 data-en="Certified, highly experienced teachers employing advanced immersion educational methodologies."
                 data-ar="طاقم تدريس مؤهل دوليًا ومصنف لتطبيق أحدث مناهج التعليم التفاعلي المباشر.">
                Certified, highly experienced teachers employing advanced immersion educational methodologies.
              </p>
            </div>

            <!-- Value -->
            <div class="glass-panel hexagon-card">
              <div class="hexagon-wrapper">
                <div class="hexagon-wrapper-inner">
                  <i class="fa-solid fa-chart-line"></i>
                </div>
              </div>
              <h4 class="text-xl font-bold mb-3" style="color: var(--text-primary);" data-en="Academic Value" data-ar="القيمة الأكاديمية">Academic Value</h4>
              <p class="text-sm leading-relaxed" style="color: var(--text-secondary);"
                 data-en="High educational standards providing students with tangible, competitive market skills."
                 data-ar="معايير تعليمية رفيعة المستوى تمكن الطلاب من اكتساب مهارات تنافسية حقيقية في سوق العمل.">
                High educational standards providing students with tangible, competitive market skills.
              </p>
            </div>

            <!-- Students -->
            <div class="glass-panel hexagon-card">
              <div class="hexagon-wrapper">
                <div class="hexagon-wrapper-inner">
                  <i class="fa-regular fa-face-smile"></i>
                </div>
              </div>
              <h4 class="text-xl font-bold mb-3" style="color: var(--text-primary);" data-en="Student Growth" data-ar="تطوير الطالب">Student Growth</h4>
              <p class="text-sm leading-relaxed" style="color: var(--text-secondary);"
                 data-en="Fostering student confidence through continuous language workshops and support resources."
                 data-ar="تعزيز ثقة الطلاب بأنفسهم من خلال ورش العمل اللغوية المتخصصة ومصادر التعلم الإضافية.">
                Fostering student confidence through continuous language workshops and support resources.
              </p>
            </div>
          </div>
      @endif
    </div>
  </section>

  <!-- Section Separator -->
  <div class="section-separator reveal">
    <div class="separator-icon"></div>
  </div>

  <!-- Quick Action Grid -->
  <section id="actions" style="background: var(--bg-secondary);">
    <div class="container px-4">
      <div class="text-center max-w-2xl mx-auto mb-16 reveal">
        <h5 class="section-subtitle" data-en="EASY NAVIGATION" data-ar="وصول سريع">EASY NAVIGATION</h5>
        <h2 class="section-title" data-en="{{ $servicesEn?->title ?? 'Enroll and Booking Gateways' }}" data-ar="{{ $servicesAr?->title ?? 'بوابات التسجيل والحجز الفوري' }}">
            {{ app()->getLocale() == 'ar' ? ($servicesAr?->title ?? 'بوابات التسجيل والحجز الفوري') : ($servicesEn?->title ?? 'Enroll and Booking Gateways') }}
        </h2>
        <div class="section-divider mx-auto"></div>
        @php
            $servDetails = app()->getLocale() == 'ar' ? $servicesAr?->details : $servicesEn?->details;
            $servDetailsPlain = trim(str_replace("\xc2\xa0", '', strip_tags(html_entity_decode($servDetails ?? ''))));
        @endphp
        @if(!empty($servDetailsPlain))
        <div class="text-center mt-4 mb-8">
            {!! $servDetails !!}
        </div>
        @endif
      </div>

      <div class="row g-4 reveal">
        <!-- Contact Us -->
        <div class="col-lg-4">
          <div class="glass-panel hover-premium-card text-center h-100 d-flex flex-column justify-content-between">
            <div class="card-content-wrapper flex-grow d-flex flex-column justify-content-between h-100">
              <div>
                <div class="w-16 h-16 bg-gold bg-opacity-20 text-gold rounded-full d-flex align-items-center justify-content-center text-3xl mx-auto mb-4">
                  <i class="bi bi-chat-dots-fill"></i>
                </div>
                <h4 class="text-xl font-bold mb-3" style="color: var(--text-primary);" data-en="Get In Touch" data-ar="تواصل معنا">Get In Touch</h4>
                <p class="opacity-75 text-sm mb-6" data-en="Have a question about levels, learning paths, pricing, or our OTE structure? Reach out and we'll help you directly."
                   data-ar="لديك سؤال حول المستويات، الخطط التعليمية، الأسعار، أو بنية اختبارات OTE؟ تواصل معنا وسنساعدك مباشرة.">
                  Have a question about levels, learning paths, pricing, or our OTE structure? Reach out and we'll help you directly.
                </p>
              </div>
              <a href="#contact" class="btn btn-luxury w-100 py-3 rounded-lg" data-en="Contact Us" data-ar="تواصل معنا">Contact Us</a>
            </div>
          </div>
        </div>

        <!-- Placement Test -->
        <div class="col-lg-4">
          <div class="glass-panel hover-premium-card text-center h-100 d-flex flex-column justify-content-between">
            <div class="card-content-wrapper flex-grow d-flex flex-column justify-content-between h-100">
              <div>
                <div class="w-16 h-16 bg-gold bg-opacity-20 text-gold rounded-full d-flex align-items-center justify-content-center text-3xl mx-auto mb-4">
                  <i class="bi bi-person-check-fill"></i>
                </div>
                <h4 class="text-xl font-bold mb-3" style="color: var(--text-primary);" data-en="Placement Booking" data-ar="حجز تحديد المستوى">Placement Booking</h4>
                <p class="opacity-75 text-sm mb-6" data-en="Reserve your slot for the English language placement exam to accurately evaluate your current level."
                   data-ar="احجز موعد اختبار تحديد المستوى في اللغة الإنجليزية لتقييم مستواك الحالي وضمك للمستوى الأنسب لك.">
                  Reserve your slot for the English language placement exam to accurately evaluate your current level.
                </p>
              </div>
              <a href="#contact" class="btn btn-luxury w-100 py-3 rounded-lg" data-en="Reserve Now" data-ar="احجز موعدك">Reserve Now</a>
            </div>
          </div>
        </div>

        <!-- Book Course -->
        <div class="col-lg-4">
          <div class="glass-panel hover-premium-card text-center h-100 d-flex flex-column justify-content-between">
            <div class="card-content-wrapper flex-grow d-flex flex-column justify-content-between h-100">
              <div>
                <div class="w-16 h-16 bg-gold bg-opacity-20 text-gold rounded-full d-flex align-items-center justify-content-center text-3xl mx-auto mb-4">
                  <i class="bi bi-bookmark-star-fill"></i>
                </div>
                <h4 class="text-xl font-bold mb-3" style="color: var(--text-primary);" data-en="Course Booking" data-ar="تسجيل في دورة">Course Booking</h4>
                <p class="opacity-75 text-sm mb-6" data-en="Directly register in our IELTS prep modules, Academic writing workshops, or General levels."
                   data-ar="سجل مباشرة في دورات التحضير لآيلتس، ورش الكتابة الأكاديمية، أو مستويات تقوية المحادثة واللغة العامة.">
                  Directly register in our IELTS prep modules, Academic writing workshops, or General levels.
                </p>
              </div>
              <a href="#contact" class="btn btn-luxury w-100 py-3 rounded-lg" data-en="Secure Seat" data-ar="احجز مقعدك">Secure Seat</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Statistics Counter Section -->
  <section class="counter-section" style="background-image: url('{{ asset('site/images/img/banner/ote_hall.png') }}');">
    <div class="counter-overlay"></div>
    <div class="container relative z-2 px-4">
      @php
         $trainTitle = app()->getLocale() == 'ar' ? $trainingAr?->title : $trainingEn?->title;
         $trainDetails = app()->getLocale() == 'ar' ? $trainingAr?->details : $trainingEn?->details;
         $trainDetailsPlain = trim(str_replace("\xc2\xa0", '', strip_tags(html_entity_decode($trainDetails ?? ''))));
      @endphp
      @if(!empty(trim($trainTitle)) || !empty($trainDetailsPlain))
      <div class="text-center mb-8">
         @if(!empty(trim($trainTitle)))
         <h2 class="text-white text-3xl font-bold mb-4">{{ $trainTitle }}</h2>
         @endif
         @if(!empty($trainDetailsPlain))
         <div class="text-white opacity-75">
             {!! $trainDetails !!}
         </div>
         @endif
      </div>
      @endif
      <div class="row g-4 text-center">
        <!-- Stat 1 -->
        <div class="col-md-4 reveal">
          <div class="glass-panel stat-card p-5">
            <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
            <h2 class="text-5xl font-extrabold mb-3 counter-number" data-target="{{ \App\Models\SiteSetting::current()->training_hours_count ?? 15000 }}">0</h2>
            <p class="text-lg font-bold uppercase tracking-wider mb-0" style="color: var(--text-primary);" data-en="Training Hours" data-ar="ساعات تدريبية">Training Hours</p>
          </div>
        </div>
        <!-- Stat 2 -->
        <div class="col-md-4 reveal delay-2">
          <div class="glass-panel stat-card p-5">
            <div class="stat-icon"><i class="bi bi-mortarboard-fill"></i></div>
            <h2 class="text-5xl font-extrabold mb-3 counter-number" data-target="{{ \App\Models\SiteSetting::current()->completed_courses_count ?? 320 }}">0</h2>
            <p class="text-lg font-bold uppercase tracking-wider mb-0" style="color: var(--text-primary);" data-en="Total Courses" data-ar="الدورات المنجزة">Total Courses</p>
          </div>
        </div>
        <!-- Stat 3 -->
        <div class="col-md-4 reveal delay-4">
          <div class="glass-panel stat-card p-5">
            <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
            <h2 class="text-5xl font-extrabold mb-3 counter-number" data-target="{{ \App\Models\SiteSetting::current()->registered_students_count ?? 8700 }}">0</h2>
            <p class="text-lg font-bold uppercase tracking-wider mb-0" style="color: var(--text-primary);" data-en="Enrolled Students" data-ar="الطلاب المسجلين">Enrolled Students</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- News Section -->
  <section id="news" style="background: var(--bg-primary);">
    <div class="container px-4">
      <div class="text-center max-w-2xl mx-auto mb-16 reveal">
        <h5 class="section-subtitle" data-en="ACADEMY NEWS" data-ar="أخبار الأكاديمية">ACADEMY NEWS</h5>
        <h2 class="section-title" data-en="Latest News and Updates" data-ar="آخر الأخبار والفعاليات">Latest News and Updates</h2>
        <div class="section-divider mx-auto"></div>
      </div>

      <!-- News Swiper -->
      <div class="swiper news-swiper reveal position-relative overflow-hidden px-4 px-md-5">
        <div class="swiper-wrapper">
          @foreach($latestNews as $newsItem)
            @php
                $newsEn = $newsItem->translations->where('locale', 'en')->first();
                $newsAr = $newsItem->translations->where('locale', 'ar')->first();
                $day = $newsItem->created_at->format('d');
                $month = strtoupper($newsItem->created_at->format('M'));
            @endphp
            <div class="swiper-slide h-auto">
              <div class="glass-panel news-card hover-premium-card h-100 d-flex flex-column justify-content-between">
                <div class="news-img-wrapper">
                  <img src="{{ Str::startsWith($newsItem->image, ['http', 'site/']) ? asset($newsItem->image) : asset('storage/' . $newsItem->image) }}" alt="{{ app()->getLocale() == 'ar' ? ($newsAr->title ?? '') : ($newsEn->title ?? '') }}" class="news-img">
                  <div class="news-date-badge">
                    <span class="block">{{ $day }}</span>
                    <span class="text-xs font-bold uppercase">{{ $month }}</span>
                  </div>
                </div>
                <div class="card-content-wrapper flex-grow d-flex flex-column justify-content-between">
                  <div>
                    <h4 class="text-xl font-bold mb-3 hover-text-accent" style="color: var(--text-primary);" data-en="{{ strip_tags(str_replace('&nbsp;', ' ', html_entity_decode($newsEn->title ?? ''))) }}" data-ar="{{ strip_tags(str_replace('&nbsp;', ' ', html_entity_decode($newsAr->title ?? ''))) }}">
                        {{ app()->getLocale() == 'ar' ? ($newsAr->title ?? '') : ($newsEn->title ?? '') }}
                    </h4>
                    <p class="opacity-75 text-sm mb-4" style="color: var(--text-secondary);"
                       data-en="{{ \Illuminate\Support\Str::limit(strip_tags(str_replace('&nbsp;', ' ', html_entity_decode($newsEn->description ?? ''))), 120) }}"
                       data-ar="{{ \Illuminate\Support\Str::limit(strip_tags(str_replace('&nbsp;', ' ', html_entity_decode($newsAr->description ?? ''))), 120) }}">
                      {!! \Illuminate\Support\Str::limit(strip_tags(str_replace('&nbsp;', ' ', html_entity_decode(app()->getLocale() == 'ar' ? ($newsAr->description ?? '') : ($newsEn->description ?? '')))), 120) !!}
                    </p>
                  </div>
                  @php
                      $encryptedId = \Illuminate\Support\Facades\Crypt::encrypt($newsItem->id);
                  @endphp
                  <a href="{{ route('site.news.show', $encryptedId) }}" class="text-gold font-bold text-decoration-none d-flex align-items-center mt-3" data-en="Read More" data-ar="اقرأ المزيد">
                    {{ app()->getLocale() == 'ar' ? 'اقرأ المزيد' : 'Read More' }} <i class="bi bi-arrow-right ms-2"></i>
                  </a>
                </div>
              </div>
            </div>
          @endforeach
        </div>

        <!-- Navigation buttons -->
        <div class="swiper-button-prev news-swiper-prev"></div>
        <div class="swiper-button-next news-swiper-next"></div>
      </div>
    </div>
  </section>

  <!-- Section Separator -->
  <div class="section-separator reveal">
    <div class="separator-icon"></div>
  </div>

  <!-- Teachers Section -->
  <section id="teachers" style="background: var(--bg-primary);">
    <div class="container px-4">
      <div class="text-center max-w-2xl mx-auto mb-16 reveal">
        <h5 class="section-subtitle" data-en="EXPERT INSTRUCTORS" data-ar="نخبة المعلمين">EXPERT INSTRUCTORS</h5>
        <h2 class="section-title" data-en="Meet Our Academic Team" data-ar="تعرف على طاقمنا الأكاديمي">Meet Our Academic Team</h2>
        <div class="section-divider mx-auto"></div>
      </div>

      <!-- Teachers Swiper -->
      <div class="swiper teachers-swiper reveal position-relative overflow-hidden px-4 px-md-5">
        <div class="swiper-wrapper">
          @foreach($teams as $team)
          <div class="swiper-slide h-auto">
            <div class="glass-panel teacher-card hover-premium-card h-100 d-flex flex-column justify-content-between overflow-hidden">
              <div class="teacher-image-wrapper">
                <img src="{{ Str::startsWith($team->image, ['http', 'site/']) ? asset($team->image) : asset('storage/' . $team->image) }}" alt="{{ $team->translation->name ?? '' }}" class="teacher-image">
                <div class="teacher-overlay">
                  <button class="teacher-links-trigger btn btn-luxury rounded-full p-0 d-flex align-items-center justify-content-center" aria-label="Toggle links">
                    <i class="bi bi-plus-lg fs-4"></i>
                  </button>
                  <div class="teacher-links-menu">
                    @php
                        $socials = $team->socials ? json_decode($team->socials, true) : [];
                    @endphp
                    @foreach($socials as $social)
                        <a href="{{ $social['link'] ?? '#' }}" class="teacher-social-link" target="_blank"><i class="bi bi-{{ strtolower(trim($social['platform'] ?? 'link')) }}"></i></a>
                    @endforeach
                  </div>
                </div>
              </div>
              <div class="card-content-wrapper text-center">
                  <h4 class="text-xl font-bold mb-1 hover-text-accent" style="color: var(--text-primary);">{{ $team->translation->name ?? '' }}</h4>
                  <p class="text-sm font-bold mb-2" style="color: var(--accent-primary);">{{ $team->translation->address1 ?? '' }}</p>
                  <p class="opacity-75 text-sm mb-0" style="color: var(--text-secondary); line-height: 1.6;">
                    {{ \Illuminate\Support\Str::limit(strip_tags($team->translation->description ?? ''), 120) }}
                  </p>
              </div>
            </div>
          </div>
          @endforeach
        </div>

        <!-- Navigation buttons -->
        <div class="swiper-button-prev teachers-swiper-prev"></div>
        <div class="swiper-button-next teachers-swiper-next"></div>
      </div>

      <!-- Links Trigger Javascript -->
      <script>
        document.addEventListener('DOMContentLoaded', () => {
          const triggers = document.querySelectorAll('.teacher-links-trigger');
          triggers.forEach(trigger => {
            trigger.addEventListener('click', (e) => {
              e.preventDefault();
              e.stopPropagation();
              const menu = trigger.nextElementSibling;
              
              // Close other open menus
              document.querySelectorAll('.teacher-links-menu.is-open').forEach(m => {
                if (m !== menu) {
                  m.classList.remove('is-open');
                  m.previousElementSibling.classList.remove('is-active');
                }
              });
              
              trigger.classList.toggle('is-active');
              menu.classList.toggle('is-open');
            });
          });
          
          // Close menus if clicking outside
          document.addEventListener('click', () => {
            document.querySelectorAll('.teacher-links-menu.is-open').forEach(m => {
              m.classList.remove('is-open');
              m.previousElementSibling.classList.remove('is-active');
            });
          });
        });
      </script>
      </div>
    </div>
  </section>

  <!-- Section Separator -->
  <div class="section-separator reveal">
    <div class="separator-icon"></div>
  </div>

  <!-- Testimonials Section -->
  <section id="testimonials" style="background: var(--bg-secondary);">
    <div class="container px-4">
      <div class="text-center max-w-2xl mx-auto mb-16 reveal">
        <h5 class="section-subtitle" data-en="STUDENT FEEDBACK" data-ar="تقييمات الطلاب">STUDENT FEEDBACK</h5>
        <h2 class="section-title" data-en="What Our Students Say" data-ar="ماذا يقول طلابنا؟">What Our Students Say</h2>
        <div class="section-divider mx-auto"></div>
      </div>

      <div class="swiper testimonials-swiper reveal px-4 px-md-5">
        <div class="swiper-wrapper">
          @foreach($testimonials as $testimonial)
            @php
                $testEn = $testimonial->translations->where('locale', 'en')->first();
                $testAr = $testimonial->translations->where('locale', 'ar')->first();
            @endphp
            <div class="swiper-slide">
              <div class="glass-panel testimonial-card-swiper">
                <div class="d-flex align-items-center mb-4">
                  <div class="testimonial-avatar">
                    @php
                        $testimonialImage = $testimonial->image
                            ? (Str::startsWith($testimonial->image, ['http', 'site/']) ? asset($testimonial->image) : asset('storage/' . $testimonial->image))
                            : asset('assets/admin/media/avatars/blank.png');
                    @endphp
                    <img src="{{ $testimonialImage }}" alt="{{ app()->getLocale() == 'ar' ? ($testAr->name ?? '') : ($testEn->name ?? '') }}" loading="lazy">
                  </div>
                  <div>
                    <h5 class="font-bold mb-1" style="color: var(--text-primary);" data-en="{{ $testEn->name ?? '' }}" data-ar="{{ $testAr->name ?? '' }}">
                      {{ app()->getLocale() == 'ar' ? ($testAr->name ?? '') : ($testEn->name ?? '') }}
                    </h5>
                    @if(($testEn && $testEn->position) || ($testAr && $testAr->position))
                    <span class="d-block text-xs mb-1" style="color: var(--accent-primary);" data-en="{{ $testEn->position ?? '' }}" data-ar="{{ $testAr->position ?? '' }}">
                      {{ app()->getLocale() == 'ar' ? ($testAr->position ?? '') : ($testEn->position ?? '') }}
                    </span>
                    @endif
                    <div class="rating-stars">
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-fill"></i>
                    </div>
                  </div>
                </div>
                <p class="opacity-75 italic text-sm leading-relaxed"
                   data-en="&ldquo;{{ strip_tags(str_replace('&nbsp;', ' ', html_entity_decode($testEn->message ?? ''))) }}&rdquo;"
                   data-ar="&ldquo;{{ strip_tags(str_replace('&nbsp;', ' ', html_entity_decode($testAr->message ?? ''))) }}&rdquo;">
                  &ldquo;{!! str_replace('&nbsp;', ' ', html_entity_decode(app()->getLocale() == 'ar' ? ($testAr->message ?? '') : ($testEn->message ?? ''))) !!}&rdquo;
                </p>
              </div>
            </div>
          @endforeach
        </div>

        <!-- Navigation bullets -->
        <div class="swiper-pagination mt-8"></div>

        <!-- Navigation buttons -->
        <div class="swiper-button-prev testimonials-swiper-prev"></div>
        <div class="swiper-button-next testimonials-swiper-next"></div>
      </div>
    </div>
  </section>

  <!-- FAQ Section -->
  <section id="faq" style="background: var(--bg-primary);">
    <div class="container px-4">
      <div class="faq-header-block text-center max-w-2xl mx-auto mb-16 reveal">
        <h5 class="section-subtitle" data-en="QUESTIONS & ANSWERS" data-ar="الأسئلة والأجوبة">QUESTIONS & ANSWERS</h5>
        <h2 class="section-title" data-en="Frequently Asked Questions" data-ar="الأسئلة الشائعة للطلاب">Frequently Asked Questions</h2>
        <div class="section-divider mx-auto"></div>
      </div>

      <div class="row justify-content-center reveal">
        <div class="col-lg-8">
          <div class="accordion faq-accordion" id="faqAccordion">

            @foreach($faqs as $index => $faq)
            <div class="accordion-item border-0">
              <h2 class="accordion-header" id="heading{{ $index }}">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}" aria-expanded="false" aria-controls="collapse{{ $index }}">
                  {{ $faq->translation->question ?? '' }}
                </button>
              </h2>
              <div id="collapse{{ $index }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $index }}" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                  {{ $faq->translation->answer ?? '' }}
                </div>
              </div>
            </div>
            @endforeach

          </div>
        </div>
      </div>
    </div>
  </section>

  @if($posPoints->count() > 0)
  <!-- Section Separator -->
  <div class="section-separator reveal">
    <div class="separator-icon"></div>
  </div>

  <!-- Book Sale Points Section -->
  <section id="pos-points" style="background: var(--bg-primary);">
    <div class="container px-4">
      <div class="text-center max-w-2xl mx-auto mb-16 reveal">
        <h5 class="section-subtitle" data-en="WHERE TO BUY" data-ar="أين تجدنا">WHERE TO BUY</h5>
        <h2 class="section-title" data-en="Book Sale Points" data-ar="نقاط بيع الكتب">Book Sale Points</h2>
        <div class="section-divider mx-auto"></div>
        <p class="mt-4 opacity-75" data-en="Get your academy booklets from any of our nearest sale points."
           data-ar="احصل على ملازم الأكاديمية من أقرب نقطة بيع لك.">
           Get your academy booklets from any of our nearest sale points.
        </p>
      </div>

      <div class="row g-4 justify-content-center reveal">
        @foreach($posPoints as $point)
        <div class="col-md-6 col-lg-4">
          <div class="glass-panel news-card hover-premium-card h-100 d-flex flex-column justify-content-between overflow-hidden">
            <div class="news-img-wrapper d-block overflow-hidden">
              <img src="{{ $point->image ? asset('storage/' . $point->image) : asset('site/images/img/programs/prog1.png') }}" alt="{{ $point->name_ar }}" class="news-img">
            </div>
            <div class="card-content-wrapper flex-grow d-flex flex-column justify-content-between">
              <div>
                <h4 class="text-xl font-bold mb-3" style="color: var(--text-primary);" data-en="{{ $point->name_en }}" data-ar="{{ $point->name_ar }}">
                  {{ app()->getLocale() == 'ar' ? $point->name_ar : $point->name_en }}
                </h4>
                <div class="d-flex align-items-start mb-2">
                  <i class="bi bi-geo-alt-fill text-gold me-2 mt-1"></i>
                  <p class="opacity-75 text-sm mb-0" style="color: var(--text-secondary);"
                     data-en="{{ $point->address_en }}" data-ar="{{ $point->address_ar }}">
                    {{ app()->getLocale() == 'ar' ? $point->address_ar : $point->address_en }}
                  </p>
                </div>
                @if($point->working_hours_ar || $point->working_hours_en)
                <div class="d-flex align-items-start mb-2">
                  <i class="bi bi-clock-fill text-gold me-2 mt-1"></i>
                  <p class="opacity-75 text-sm mb-0" style="color: var(--text-secondary);"
                     data-en="{{ $point->working_hours_en }}" data-ar="{{ $point->working_hours_ar }}">
                    {{ app()->getLocale() == 'ar' ? $point->working_hours_ar : $point->working_hours_en }}
                  </p>
                </div>
                @endif
                <div class="d-flex align-items-center mb-4">
                  <i class="bi bi-telephone-fill text-gold me-2"></i>
                  <a href="tel:{{ $point->phone }}" class="opacity-75 text-sm text-decoration-none" style="color: var(--text-secondary);" dir="ltr">{{ $point->phone }}</a>
                </div>
              </div>
              <div class="d-flex gap-2">
                <a href="tel:{{ $point->phone }}" class="btn btn-glass flex-grow-1 py-2 rounded-lg text-center text-decoration-none" data-en="Call" data-ar="اتصال">
                  <i class="bi bi-telephone me-1"></i> {{ app()->getLocale() == 'ar' ? 'اتصال' : 'Call' }}
                </a>
                @if($point->latitude && $point->longitude)
                <a href="https://www.google.com/maps/search/?api=1&query={{ $point->latitude }},{{ $point->longitude }}" target="_blank" class="btn btn-luxury flex-grow-1 py-2 rounded-lg text-center text-decoration-none" data-en="Directions" data-ar="الاتجاهات">
                  <i class="bi bi-signpost-split-fill me-1"></i> {{ app()->getLocale() == 'ar' ? 'الاتجاهات' : 'Directions' }}
                </a>
                @endif
              </div>
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </section>
  @endif

  <!-- Section Separator -->
  <div class="section-separator reveal">
    <div class="separator-icon"></div>
  </div>

  <!-- Contact Us Section -->
  <section id="contact" style="background: var(--bg-secondary);">
    <div class="container px-4">
      <div class="row gy-12 reveal">
        <!-- Info Cards -->
        <div class="col-lg-5">
          <h5 class="section-subtitle" data-en="CONNECT WITH US" data-ar="تواصل معنا">CONNECT WITH US</h5>
          <h2 class="section-title" data-en="Get In Touch Today" data-ar="ابدأ رحلتك الأكاديمية الآن">Get In Touch Today</h2>
          <div class="section-divider"></div>

          <div class="d-flex flex-column space-y-6 mt-8">
            <div class="d-flex align-items-center p-4 glass-panel">
              <div class="w-12 h-12 bg-gold bg-opacity-20 text-gold rounded-full d-flex align-items-center justify-content-center text-2xl me-4">
                <i class="bi bi-telephone"></i>
              </div>
              <div>
                <h6 class="font-bold mb-1" style="color: var(--text-primary);" data-en="Call Us" data-ar="اتصل بنا">Call Us</h6>
                <a href="tel:+970566959697" class="text-decoration-none opacity-75 text-sm" style="color: var(--text-primary);">+970 56 695 9697</a>
              </div>
            </div>

            <div class="d-flex align-items-center p-4 glass-panel">
              <div class="w-12 h-12 bg-gold bg-opacity-20 text-gold rounded-full d-flex align-items-center justify-content-center text-2xl me-4">
                <i class="bi bi-whatsapp"></i>
              </div>
              <div>
                <h6 class="font-bold mb-1" style="color: var(--text-primary);" data-en="WhatsApp" data-ar="واتساب">WhatsApp</h6>
                <a href="https://wa.me/970566959697" target="_blank" class="text-decoration-none opacity-75 text-sm" style="color: var(--text-primary);">+970 56 695 9697</a>
              </div>
            </div>

            <div class="d-flex align-items-center p-4 glass-panel">
              <div class="w-12 h-12 bg-gold bg-opacity-20 text-gold rounded-full d-flex align-items-center justify-content-center text-2xl me-4">
                <i class="bi bi-envelope-open"></i>
              </div>
              <div>
                <h6 class="font-bold mb-1" style="color: var(--text-primary);" data-en="Email Address" data-ar="البريد الإلكتروني">Email Address</h6>
                <a href="mailto:info@fullmarkacademy.com" class="text-decoration-none opacity-75 text-sm" style="color: var(--text-primary);">info@fullmarkacademy.com</a>
              </div>
            </div>

            <div class="d-flex align-items-center p-4 glass-panel">
              <div class="w-12 h-12 bg-gold bg-opacity-20 text-gold rounded-full d-flex align-items-center justify-content-center text-2xl me-4">
                <i class="bi bi-geo-alt"></i>
              </div>
              <div>
                <h6 class="font-bold mb-1" style="color: var(--text-primary);" data-en="Location" data-ar="موقعنا">Location</h6>
                <p class="opacity-75 text-sm mb-0" style="color: var(--text-primary);" data-en="Gaza Strip - Khan Yunis, Palestine" data-ar="قطاع غزة - خانيونس">Gaza Strip - Khan Yunis, Palestine</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Contact Form -->
        <div class="col-lg-7">
          <div class="glass-panel p-6 md:p-12">
            <form id="contactForm" action="{{ route('contact.store') }}" method="POST">
              @csrf
              <!-- Spam Honeypot -->
              <div style="display:none;">
                  <input type="text" name="website_url" id="website_url">
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <input type="text" name="name" id="contactName" placeholder=" " required>
                    <label for="contactName" data-en="Full Name" data-ar="الاسم الكامل">Full Name</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <input type="email" name="email" id="contactEmail" placeholder=" " required>
                    <label for="contactEmail" data-en="Email Address" data-ar="البريد الإلكتروني">Email Address</label>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <select name="subject" id="contactType" required>
                      <option value="" disabled selected hidden></option>
                      <option value="placement" data-en="Placement Test Booking" data-ar="حجز تحديد المستوى">Placement Test Booking</option>
                      <option value="course" data-en="IELTS / General Course" data-ar="دورة آيلتس / لغة عامة">IELTS / General Course</option>
                      <option value="general" data-en="General Inquiry" data-ar="استفسار عام">General Inquiry</option>
                    </select>
                    <label for="contactType" data-en="Inquiry Type" data-ar="نوع الاستفسار">Inquiry Type</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <input type="text" name="phone" id="contactPhone" placeholder=" ">
                    <label for="contactPhone" data-en="Phone Number" data-ar="رقم الهاتف">Phone Number</label>
                  </div>
                </div>
              </div>

              <div class="floating-input-group">
                <textarea name="message" id="contactMessage" rows="5" placeholder=" " required></textarea>
                <label for="contactMessage" data-en="Your Message" data-ar="رسالتك">Your Message</label>
              </div>

              <div id="contactFormMessage" class="mb-3 text-center" style="display:none; font-weight:bold;"></div>

              <button type="submit" id="contactSubmitBtn" class="btn btn-luxury w-100 py-3 rounded-lg text-lg d-flex align-items-center justify-content-center">
                <span data-en="Send Message" data-ar="إرسال الرسالة">Send Message</span>
                <i class="bi bi-arrow-right ms-2 rtl:rotate-180"></i>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

  @if (session('welcome_student'))
    <div class="position-fixed bottom-4 end-4 z-50 reveal-scale" style="bottom: 20px; right: 20px; z-index: 9999;">
      <div class="glass-panel p-4 text-center d-flex align-items-center gap-4" style="border: 1px solid var(--accent-color); background: var(--bg-secondary); min-width: 320px; box-shadow: var(--shadow-lg);">
        <div class="d-flex align-items-center justify-content-center text-3xl text-gold" style="width: 48px; height: 48px; border-radius: 50%; background: rgba(197, 168, 128, 0.15);">
          <i class="bi bi-person-check-fill"></i>
        </div>
        <div class="text-start">
          <h5 class="font-bold mb-1" style="color: var(--text-primary);" data-en="Welcome Back!" data-ar="مرحباً بك!">Welcome Back!</h5>
          <p class="opacity-75 text-sm mb-0" style="color: var(--text-secondary);">
            {{ session('welcome_student') }}
          </p>
        </div>
        <button type="button" class="btn-close btn-close-white ms-auto" onclick="this.parentElement.parentElement.remove()" aria-label="Close"></button>
      </div>
    </div>
  @endif

  <!-- Footer -->
@endsection

@push('scripts')
<script>
  document.getElementById('contactForm').addEventListener('submit', function(e) {
      e.preventDefault();
      let form = this;
      let btn = document.getElementById('contactSubmitBtn');
      let msgDiv = document.getElementById('contactFormMessage');
      
      let formData = new FormData(form);
      btn.disabled = true;
      msgDiv.style.display = 'none';
      
      fetch(form.action, {
          method: 'POST',
          body: formData,
          headers: {
              'X-Requested-With': 'XMLHttpRequest'
          }
      })
      .then(response => response.json())
      .then(data => {
          btn.disabled = false;
          msgDiv.style.display = 'block';
          msgDiv.className = 'mb-3 text-center ' + (data.success ? 'text-success' : 'text-danger');
          msgDiv.textContent = data.message;
          if (data.success) {
              form.reset();
          }
      })
      .catch(error => {
          btn.disabled = false;
          msgDiv.style.display = 'block';
          msgDiv.className = 'mb-3 text-center text-danger';
          msgDiv.textContent = document.documentElement.lang === 'ar' ? 'حدث خطأ. يرجى المحاولة لاحقاً.' : 'An error occurred. Please try again later.';
      });
  });
</script>
@endpush
