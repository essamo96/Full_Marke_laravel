@extends('layouts.site')

@section('title', 'FULL MARKS ACADEMY | Premium Landing Page')

@section('content')
  <!-- Hero Section -->
  <section id="hero" class="hero-section">
    <!-- Two-stage video background: video1 -> crossfade -> video2 -> crossfade -> still -->
    <div class="hero-frame-stage" aria-hidden="true">
      <video id="hero-bg-video-1"
             class="hero-frame-stage__media hero-frame-stage__video is-active"
             src="{{ asset('site/images/slider1.mp4') }}"
             muted
             playsinline
             preload="auto"></video>
      <video id="hero-bg-video-2"
             class="hero-frame-stage__media hero-frame-stage__video"
             src="{{ asset('site/images/slider1.mp4') }}"
             muted
             playsinline
             preload="auto"></video>
      <img id="hero-bg-still"
           class="hero-frame-stage__media hero-frame-stage__still"
           src="{{ asset('site/images/bg-main.jpg') }}"
           alt="">
    </div>

    <div class="hero-overlay"></div>
    <canvas id="particles-canvas" class="particles-canvas-class"></canvas>

    <!-- Floating Background Shapes -->
    <div class="absolute top-1/4 left-10 w-24 h-24 bg-gold opacity-10 rounded-full blur-xl float-slow parallax-layer" data-parallax-speed="0.03"></div>
    <div class="absolute bottom-1/4 right-20 w-36 h-36 bg-cyan-400 opacity-10 rounded-full blur-2xl float-medium parallax-layer" data-parallax-speed="-0.02"></div>
    <div class="absolute top-1/3 right-1/4 w-16 h-16 bg-purple-500 opacity-5 rounded-full blur-lg float-fast parallax-layer" data-parallax-speed="0.05"></div>

    <div id="hero-content-overlay" class="hero-content hero-content-overlay container px-4">
      <div class="reveal-scale">
        <h1 class="hero-stagger text-4xl md:text-6xl font-extrabold tracking-tight mb-4 uppercase" data-stagger="1" style="color: var(--text-primary);">
          <span class="bg-clip-text text-transparent bg-gradient-to-r from-gold via-gold-light to-gold-dark" data-en="FULL MARKS ACADEMY" data-ar="أكاديمية العلامة الكاملة">FULL MARKS ACADEMY</span>
        </h1>
        <p class="hero-stagger text-lg md:text-2xl text-secondary-class mb-8 max-w-2xl mx-auto leading-relaxed" data-stagger="2" style="color: var(--text-secondary);"
           data-en="Step Into Professional Excellence. Approved Full Mark Test Center & Global Academic Preparation."
           data-ar="بوابتك للتميز الأكاديمي والمهني. مركز اختبارات العلامة الكاملة المعتمد والتدريب اللغوي الدولي.">
          Step Into Professional Excellence. Approved Full Mark Test Center & Global Academic Preparation.
        </p>

        <!-- CTA Buttons -->
        <div class="hero-stagger d-flex flex-column flex-sm-row justify-content-center align-items-center gap-4" data-stagger="3">
          <a href="#actions" class="btn btn-luxury px-5 py-3 rounded-xl w-60 sm:w-auto text-lg d-flex align-items-center justify-content-center"
             data-en="Placement Test Booking" data-ar="حجز تحديد المستوى">
            Placement Test Booking
          </a>
          <a href="#contact" class="btn btn-glass px-5 py-3 rounded-xl w-60 sm:w-auto text-lg d-flex align-items-center justify-content-center"
             data-en="Book A Course" data-ar="حجز مقعد دراسي">
            Book A Course
          </a>
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
          <h2 class="section-title" data-en="Leading Academic Training Institution" data-ar="المؤسسة الأكاديمية الرائدة للتدريب">Leading Academic Training Institution</h2>
          <div class="section-divider"></div>
          <p class="text-lg leading-relaxed mb-6" style="color: var(--text-secondary);"
             data-en="FULL MARKS ACADEMY stands at the forefront of language education and testing. As an approved Full Mark Test of English (OTE) center, we deliver globally recognized certifications alongside premier academic instruction tailored for IELTS preparation, general levels, and corporate business communication."
             data-ar="تقف أكاديمية العلامة الكاملة في طليعة تعليم اللغات والتقييم الدولي. بصفتنا مركزًا معتمدًا لاختبار Full Mark Test of English (OTE)، نقدم شهادات معترف بها عالميًا إلى جانب تدريب أكاديمي متميز مُعد خصيصًا لاجتياز اختبار آيلتس، المستويات العامة، والمحادثة المهنية للمؤسسات.">
            FULL MARKS ACADEMY stands at the forefront of language education and testing. As an approved Full Mark Test of English (OTE) center, we deliver globally recognized certifications alongside premier academic instruction tailored for IELTS preparation, general levels, and corporate business communication.
          </p>
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
          <a href="{{ asset('site/images/doc/brochur.pdf') }}" target="_blank" class="btn btn-luxury px-4 py-3 rounded-lg" data-en="Download Academy Brochure" data-ar="تحميل كتيب الأكاديمية">Download Academy Brochure</a>
        </div>

        <!-- Video/Image Column -->
        <div class="col-lg-6 reveal-right">
          <div class="relative p-3 glass-panel">
            <div class="about-video-wrapper relative overflow-hidden rounded-lg h-80 md:h-[450px]">
              <video id="about-video"
                     class="about-video"
                     src="{{ asset('site/images/aboutUs.mp4') }}"
                     muted
                     loop
                     playsinline
                     preload="metadata"
                     poster="{{ asset('site/images/img/news/news1.png') }}"></video>
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
        <!-- Program 1: Tawjihi -->
        <div class="col-md-6 col-lg-3">
          <div class="glass-panel news-card hover-premium-card h-100 d-flex flex-column justify-content-between">
            <a href="{{ route('programs.show', 'tawjihi') }}" class="news-img-wrapper d-block overflow-hidden">
              <img src="{{ asset('site/images/img/programs/prog1.png') }}" alt="Tawjihi Program" class="news-img">
            </a>
            <div class="card-content-wrapper flex-grow d-flex flex-column justify-content-between">
              <div>
                <h4 class="text-xl font-bold mb-3">
                  <a href="{{ route('programs.show', 'tawjihi') }}" class="stretched-link text-decoration-none hover-text-accent" style="color: var(--text-primary);" data-en="Tawjihi Program" data-ar="برنامج التوجيهي">Tawjihi Program</a>
                </h4>
                <p class="opacity-75 text-sm mb-4" style="color: var(--text-secondary);"
                   data-en="Comprehensive preparation for high school students with elite teachers to ensure top scores and academic excellence."
                   data-ar="تأهيل شامل لطلبة الثانوية العامة مع نخبة من أفضل المعلمين لضمان التفوق والحصول على العلامة الكاملة.">
                  Comprehensive preparation for high school students with elite teachers to ensure top scores and academic excellence.
                </p>
              </div>
              <div class="btn btn-luxury w-100 py-2.5 rounded-lg text-center mt-3" data-en="Join Program" data-ar="الانضمام للبرنامج">
                Join Program
              </div>
            </div>
          </div>
        </div>

        <!-- Program 2: Children -->
        <div class="col-md-6 col-lg-3">
          <div class="glass-panel news-card hover-premium-card h-100 d-flex flex-column justify-content-between">
            <a href="{{ route('programs.show', 'children') }}" class="news-img-wrapper d-block overflow-hidden">
              <img src="{{ asset('site/images/img/programs/prog2.png') }}" alt="Children Program" class="news-img">
            </a>
            <div class="card-content-wrapper flex-grow d-flex flex-column justify-content-between">
              <div>
                <h4 class="text-xl font-bold mb-3">
                  <a href="{{ route('programs.show', 'children') }}" class="stretched-link text-decoration-none hover-text-accent" style="color: var(--text-primary);" data-en="Children Program" data-ar="برنامج الأطفال">Children Program</a>
                </h4>
                <p class="opacity-75 text-sm mb-4" style="color: var(--text-secondary);"
                   data-en="Fun, interactive language learning designed to build a strong foundation for young learners using modern tools."
                   data-ar="تعليم تفاعلي مرح يهدف إلى بناء لغوي قوي للأطفال من سن مبكر باستخدام وسائل تعليمية مبتكرة.">
                  Fun, interactive language learning designed to build a strong foundation for young learners using modern tools.
                </p>
              </div>
              <div class="btn btn-luxury w-100 py-2.5 rounded-lg text-center mt-3" data-en="Join Program" data-ar="الانضمام للبرنامج">
                Join Program
              </div>
            </div>
          </div>
        </div>

        <!-- Program 3: Speech Therapy -->
        <div class="col-md-6 col-lg-3">
          <div class="glass-panel news-card hover-premium-card h-100 d-flex flex-column justify-content-between">
            <a href="{{ route('programs.show', 'speech') }}" class="news-img-wrapper d-block overflow-hidden">
              <img src="{{ asset('site/images/img/programs/prog3.png') }}" alt="Speech Therapy" class="news-img">
            </a>
            <div class="card-content-wrapper flex-grow d-flex flex-column justify-content-between">
              <div>
                <h4 class="text-xl font-bold mb-3">
                  <a href="{{ route('programs.show', 'speech') }}" class="stretched-link text-decoration-none hover-text-accent" style="color: var(--text-primary);" data-en="Speech Therapy Program" data-ar="برنامج النطق">Speech Therapy Program</a>
                </h4>
                <p class="opacity-75 text-sm mb-4" style="color: var(--text-secondary);"
                   data-en="Specialized therapy sessions to resolve speech difficulties and enhance articulation for children and adults."
                   data-ar="جلسات متخصصة لعلاج مشاكل النطق والتخاطب وتحسين النطق السليم لدى الأطفال والبالغين بأحدث الأساليب.">
                  Specialized therapy sessions to resolve speech difficulties and enhance articulation for children and adults.
                </p>
              </div>
              <div class="btn btn-luxury w-100 py-2.5 rounded-lg text-center mt-3" data-en="Join Program" data-ar="الانضمام للبرنامج">
                Join Program
              </div>
            </div>
          </div>
        </div>

        <!-- Program 4: Rehabilitation -->
        <div class="col-md-6 col-lg-3">
          <div class="glass-panel news-card hover-premium-card h-100 d-flex flex-column justify-content-between">
            <a href="{{ route('programs.show', 'rehab') }}" class="news-img-wrapper d-block overflow-hidden">
              <img src="{{ asset('site/images/img/programs/prog4.png') }}" alt="Rehabilitation Program" class="news-img">
            </a>
            <div class="card-content-wrapper flex-grow d-flex flex-column justify-content-between">
              <div>
                <h4 class="text-xl font-bold mb-3">
                  <a href="{{ route('programs.show', 'rehab') }}" class="stretched-link text-decoration-none hover-text-accent" style="color: var(--text-primary);" data-en="Rehabilitation Program" data-ar="برنامج التأهيلي">Rehabilitation Program</a>
                </h4>
                <p class="opacity-75 text-sm mb-4" style="color: var(--text-secondary);"
                   data-en="Intensive training designed to build key academic skills and prepare students for integration into standard tracks."
                   data-ar="برنامج مكثف لتطوير المهارات الأكاديمية والاجتماعية وتأهيل الطلاب للاندماج الفعال في البيئات التعليمية.">
                  Intensive training designed to build key academic skills and prepare students for integration into standard tracks.
                </p>
              </div>
              <div class="btn btn-luxury w-100 py-2.5 rounded-lg text-center mt-3" data-en="Join Program" data-ar="الانضمام للبرنامج">
                Join Program
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

  <!-- Core Strengths Section (Hexagon Grid) -->
  <section id="strengths" style="background: var(--bg-primary);">
    <div class="container px-4">
      <div class="text-center max-w-2xl mx-auto mb-16 reveal">
        <h5 class="section-subtitle" data-en="WHY FULL MARK?" data-ar="لماذا العلامة الكاملة؟">WHY FULL MARK?</h5>
        <h2 class="section-title" data-en="Core Pillars of Academic Success" data-ar="الركائز الأساسية للنجاح الأكاديمي">Core Pillars of Academic Success</h2>
        <div class="section-divider mx-auto"></div>
      </div>

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
        <h2 class="section-title" data-en="Enroll and Booking Gateways" data-ar="بوابات التسجيل والحجز الفوري">Enroll and Booking Gateways</h2>
        <div class="section-divider mx-auto"></div>
      </div>

      <div class="row g-4 reveal">
        <!-- Brochure -->
        <div class="col-lg-4">
          <div class="glass-panel hover-premium-card text-center h-100 d-flex flex-column justify-content-between">
            <div class="card-content-wrapper flex-grow d-flex flex-column justify-content-between h-100">
              <div>
                <div class="w-16 h-16 bg-gold bg-opacity-20 text-gold rounded-full d-flex align-items-center justify-content-center text-3xl mx-auto mb-4">
                  <i class="bi bi-file-pdf"></i>
                </div>
                <h4 class="text-xl font-bold mb-3" style="color: var(--text-primary);" data-en="Our Syllabus" data-ar="منهج الأكاديمية">Our Syllabus</h4>
                <p class="opacity-75 text-sm mb-6" data-en="Download the comprehensive academy brochure detailing levels, learning paths, pricing, and OTE structure."
                   data-ar="قم بتحميل كتيب الأكاديمية الشامل لاستعراض المستويات، الخطط التعليمية، الأسعار، وبنية اختبارات OTE المعتمدة.">
                  Download the comprehensive academy brochure detailing levels, learning paths, pricing, and OTE structure.
                </p>
              </div>
              <a href="{{ asset('site/images/doc/brochur.pdf') }}" target="_blank" class="btn btn-luxury w-100 py-3 rounded-lg" data-en="Download PDF" data-ar="تحميل الكتيب">Download PDF</a>
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
  <section class="counter-section" style="background-image: url('{{ asset('site/images/img/banner/nos.jpg') }}');">
    <div class="counter-overlay"></div>
    <div class="container relative z-2 px-4">
      <div class="row g-4 text-center">
        <!-- Stat 1 -->
        <div class="col-md-4 reveal">
          <div class="glass-panel stat-card p-5">
            <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
            <h2 class="text-5xl font-extrabold mb-3 counter-number" data-target="15000">0</h2>
            <p class="text-lg font-bold uppercase tracking-wider mb-0" style="color: var(--text-primary);" data-en="Training Hours" data-ar="ساعات تدريبية">Training Hours</p>
          </div>
        </div>
        <!-- Stat 2 -->
        <div class="col-md-4 reveal delay-2">
          <div class="glass-panel stat-card p-5">
            <div class="stat-icon"><i class="bi bi-mortarboard-fill"></i></div>
            <h2 class="text-5xl font-extrabold mb-3 counter-number" data-target="320">0</h2>
            <p class="text-lg font-bold uppercase tracking-wider mb-0" style="color: var(--text-primary);" data-en="Total Courses" data-ar="الدورات المنجزة">Total Courses</p>
          </div>
        </div>
        <!-- Stat 3 -->
        <div class="col-md-4 reveal delay-4">
          <div class="glass-panel stat-card p-5">
            <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
            <h2 class="text-5xl font-extrabold mb-3 counter-number" data-target="8700">0</h2>
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

      <div class="row g-4 reveal">
        <!-- News item 1 -->
        <div class="col-md-4">
          <div class="glass-panel news-card hover-premium-card">
            <div class="news-img-wrapper">
              <img src="{{ asset('site/images/img/banner/ote_hall.png') }}" alt="News" class="news-img">
              <div class="news-date-badge">
                <span class="block">28</span>
                <span class="text-xs font-bold uppercase">MAY</span>
              </div>
            </div>
            <div class="card-content-wrapper flex-grow d-flex flex-column justify-content-between">
              <div>
                <h4 class="text-xl font-bold mb-3 hover-text-accent" style="color: var(--text-primary);" data-en="New OTE Placement Sessions" data-ar="جلسات اختبار OTE جديدة">New OTE Placement Sessions</h4>
                <p class="opacity-75 text-sm mb-4" style="color: var(--text-secondary);"
                   data-en="Approved dates for the upcoming Full Mark Test of English assessment are now open for registration."
                   data-ar="المواعيد المعتمدة لجلسات تقييم اختبار العلامة الكاملة للغة الإنجليزية القادمة مفتوحة للتسجيل الآن.">
                  Approved dates for the upcoming Full Mark Test of English assessment are now open for registration.
                </p>
              </div>
              <a href="#contact" class="text-gold font-bold text-decoration-none d-flex align-items-center mt-3" data-en="Read More" data-ar="اقرأ المزيد">
                Read More <i class="bi bi-arrow-right ms-2"></i>
              </a>
            </div>
          </div>
        </div>

        <!-- News item 2 -->
        <div class="col-md-4">
          <div class="glass-panel news-card hover-premium-card">
            <div class="news-img-wrapper">
              <img src="{{ asset('site/images/img/news/news2.png') }}" alt="News" class="news-img">
              <div class="news-date-badge">
                <span class="block">12</span>
                <span class="text-xs font-bold uppercase">JUN</span>
              </div>
            </div>
            <div class="card-content-wrapper flex-grow d-flex flex-column justify-content-between">
              <div>
                <h4 class="text-xl font-bold mb-3 hover-text-accent" style="color: var(--text-primary);" data-en="IELTS Prep Program Starting" data-ar="بدء التسجيل لدورة الآيلتس المكثفة">IELTS Prep Program Starting</h4>
                <p class="opacity-75 text-sm mb-4" style="color: var(--text-secondary);"
                   data-en="Register for our academic IELTS training cohort led by certified British trainers with mock testing."
                   data-ar="سجل الآن في المجموعة الأكاديمية الجديدة للتحضير لاختبار آيلتس بإشراف مدربين مؤهلين واختبارات تجريبية.">
                  Register for our academic IELTS training cohort led by certified British trainers with mock testing.
                </p>
              </div>
              <a href="#contact" class="text-gold font-bold text-decoration-none d-flex align-items-center mt-3" data-en="Read More" data-ar="اقرأ المزيد">
                Read More <i class="bi bi-arrow-right ms-2"></i>
              </a>
            </div>
          </div>
        </div>

        <!-- News item 3 -->
        <div class="col-md-4">
          <div class="glass-panel news-card hover-premium-card">
            <div class="news-img-wrapper">
              <img src="{{ asset('site/images/img/news/news3.png') }}" alt="News" class="news-img">
              <div class="news-date-badge">
                <span class="block">05</span>
                <span class="text-xs font-bold uppercase">JUL</span>
              </div>
            </div>
            <div class="card-content-wrapper flex-grow d-flex flex-column justify-content-between">
              <div>
                <h4 class="text-xl font-bold mb-3 hover-text-accent" style="color: var(--text-primary);" data-en="Academic Speaking & Writing" data-ar="ورشة عمل المحادثة الأكاديمية">Academic Speaking & Writing</h4>
                <p class="opacity-75 text-sm mb-4" style="color: var(--text-secondary);"
                   data-en="Improve your essay structures and academic speaking fluency with our intensive workshops."
                   data-ar="قم بتحسين صياغة المقالات الأكاديمية وطلاقة المحادثة العلمية من خلال ورشنا التدريبية المركزة.">
                  Improve your essay structures and academic speaking fluency with our intensive workshops.
                </p>
              </div>
              <a href="#contact" class="text-gold font-bold text-decoration-none d-flex align-items-center mt-3" data-en="Read More" data-ar="اقرأ المزيد">
                Read More <i class="bi bi-arrow-right ms-2"></i>
              </a>
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

  <!-- Teachers Section -->
  <section id="teachers" style="background: var(--bg-primary);">
    <div class="container px-4">
      <div class="text-center max-w-2xl mx-auto mb-16 reveal">
        <h5 class="section-subtitle" data-en="EXPERT INSTRUCTORS" data-ar="نخبة المعلمين">EXPERT INSTRUCTORS</h5>
        <h2 class="section-title" data-en="Meet Our Academic Team" data-ar="تعرف على طاقمنا الأكاديمي">Meet Our Academic Team</h2>
        <div class="section-divider mx-auto"></div>
      </div>

      <div class="row g-4 justify-content-center reveal">
        <!-- Teacher 1 -->
        <div class="col-md-6 col-lg-3">
          <div class="glass-panel teacher-card hover-premium-card h-100 d-flex flex-column justify-content-between overflow-hidden">
            <div style="height: 250px; overflow: hidden; position: relative;">
              <img src="{{ asset('site/images/img/students/teacher_1.png') }}" alt="Ahmad Al-Saeed" class="w-100 h-100 object-cover" style="object-fit: cover;">
            </div>
            <div class="card-content-wrapper text-center flex-grow d-flex flex-column justify-content-between">
              <div>
                <h4 class="text-xl font-bold mb-1 hover-text-accent" style="color: var(--text-primary);">Ahmad Al-Saeed</h4>
                <p class="text-sm font-bold text-gold mb-3" data-en="IELTS Expert" data-ar="خبير آيلتس">IELTS Expert</p>
                <p class="opacity-75 text-sm mb-4" style="color: var(--text-secondary);" data-en="Certified British Council trainer." data-ar="مدرب معتمد من المجلس الثقافي البريطاني.">
                  Certified British Council trainer.
                </p>
              </div>
              <div class="teacher-social-wrapper relative">
                <button class="btn btn-glass w-100 py-2 rounded-lg text-center text-sm teacher-connect-btn" data-en="Connect" data-ar="تواصل">Connect</button>
                <div class="social-arc-menu">
                  <a href="#" class="social-arc-item item-1"><i class="bi bi-linkedin"></i></a>
                  <a href="#" class="social-arc-item item-2"><i class="bi bi-twitter"></i></a>
                  <a href="#" class="social-arc-item item-3"><i class="bi bi-envelope"></i></a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Teacher 2 -->
        <div class="col-md-6 col-lg-3">
          <div class="glass-panel teacher-card hover-premium-card h-100 d-flex flex-column justify-content-between overflow-hidden">
            <div style="height: 250px; overflow: hidden; position: relative;">
              <img src="{{ asset('site/images/img/students/teacher_2.png') }}" alt="Mariam Naser" class="w-100 h-100 object-cover" style="object-fit: cover;">
            </div>
            <div class="card-content-wrapper text-center flex-grow d-flex flex-column justify-content-between">
              <div>
                <h4 class="text-xl font-bold mb-1 hover-text-accent" style="color: var(--text-primary);">Mariam Naser</h4>
                <p class="text-sm font-bold text-gold mb-3" data-en="General English" data-ar="لغة إنجليزية عامة">General English</p>
                <p class="opacity-75 text-sm mb-4" style="color: var(--text-secondary);" data-en="Specialist in communicative language." data-ar="متخصصة في مهارات التواصل والمحادثة.">
                  Specialist in communicative language.
                </p>
              </div>
              <div class="teacher-social-wrapper relative">
                <button class="btn btn-glass w-100 py-2 rounded-lg text-center text-sm teacher-connect-btn" data-en="Connect" data-ar="تواصل">Connect</button>
                <div class="social-arc-menu">
                  <a href="#" class="social-arc-item item-1"><i class="bi bi-linkedin"></i></a>
                  <a href="#" class="social-arc-item item-2"><i class="bi bi-twitter"></i></a>
                  <a href="#" class="social-arc-item item-3"><i class="bi bi-envelope"></i></a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Teacher 3 -->
        <div class="col-md-6 col-lg-3">
          <div class="glass-panel teacher-card hover-premium-card h-100 d-flex flex-column justify-content-between overflow-hidden">
            <div style="height: 250px; overflow: hidden; position: relative;">
              <img src="{{ asset('site/images/img/students/teacher_3.png') }}" alt="Dr. Omar Fayed" class="w-100 h-100 object-cover" style="object-fit: cover;">
            </div>
            <div class="card-content-wrapper text-center flex-grow d-flex flex-column justify-content-between">
              <div>
                <h4 class="text-xl font-bold mb-1 hover-text-accent" style="color: var(--text-primary);">Dr. Omar Fayed</h4>
                <p class="text-sm font-bold text-gold mb-3" data-en="Tawjihi Coordinator" data-ar="منسق التوجيهي">Tawjihi Coordinator</p>
                <p class="opacity-75 text-sm mb-4" style="color: var(--text-secondary);" data-en="Ensuring top academic scores." data-ar="ضمان الحصول على أعلى العلامات الأكاديمية.">
                  Ensuring top academic scores.
                </p>
              </div>
              <div class="teacher-social-wrapper relative">
                <button class="btn btn-glass w-100 py-2 rounded-lg text-center text-sm teacher-connect-btn" data-en="Connect" data-ar="تواصل">Connect</button>
                <div class="social-arc-menu">
                  <a href="#" class="social-arc-item item-1"><i class="bi bi-linkedin"></i></a>
                  <a href="#" class="social-arc-item item-2"><i class="bi bi-twitter"></i></a>
                  <a href="#" class="social-arc-item item-3"><i class="bi bi-envelope"></i></a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Teacher 4 -->
        <div class="col-md-6 col-lg-3">
          <div class="glass-panel teacher-card hover-premium-card h-100 d-flex flex-column justify-content-between overflow-hidden">
            <div style="height: 250px; overflow: hidden; position: relative;">
              <img src="{{ asset('site/images/img/students/teacher_4.png') }}" alt="Sarah Jones" class="w-100 h-100 object-cover" style="object-fit: cover;">
            </div>
            <div class="card-content-wrapper text-center flex-grow d-flex flex-column justify-content-between">
              <div>
                <h4 class="text-xl font-bold mb-1 hover-text-accent" style="color: var(--text-primary);">Sarah Jones</h4>
                <p class="text-sm font-bold text-gold mb-3" data-en="Native Speaker" data-ar="متحدثة أصلية">Native Speaker</p>
                <p class="opacity-75 text-sm mb-4" style="color: var(--text-secondary);" data-en="Focus on advanced conversation." data-ar="التركيز على مهارات المحادثة المتقدمة.">
                  Focus on advanced conversation.
                </p>
              </div>
              <div class="teacher-social-wrapper relative">
                <button class="btn btn-glass w-100 py-2 rounded-lg text-center text-sm teacher-connect-btn" data-en="Connect" data-ar="تواصل">Connect</button>
                <div class="social-arc-menu">
                  <a href="#" class="social-arc-item item-1"><i class="bi bi-linkedin"></i></a>
                  <a href="#" class="social-arc-item item-2"><i class="bi bi-twitter"></i></a>
                  <a href="#" class="social-arc-item item-3"><i class="bi bi-envelope"></i></a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Connect Buttons Logic -->
      <script>
        document.addEventListener('DOMContentLoaded', () => {
          const connectBtns = document.querySelectorAll('.teacher-connect-btn');
          connectBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
              e.preventDefault();
              const menu = btn.nextElementSibling;
              menu.classList.toggle('is-open');
            });
          });
          
          // Close menus if clicking outside
          document.addEventListener('click', (e) => {
            if (!e.target.closest('.teacher-social-wrapper')) {
              document.querySelectorAll('.social-arc-menu.is-open').forEach(menu => {
                menu.classList.remove('is-open');
              });
            }
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

      <div class="swiper testimonials-swiper reveal">
        <div class="swiper-wrapper">
          <!-- Slide 1 -->
          <div class="swiper-slide">
            <div class="glass-panel testimonial-card-swiper">
              <div class="d-flex align-items-center mb-4">
                <div class="testimonial-avatar">
                  <img src="{{ asset('site/images/img/students/student1.jpg') }}" alt="Ahmad Al-Saeed" loading="lazy">
                </div>
                <div>
                  <h5 class="font-bold mb-1" style="color: var(--text-primary);">Ahmad Al-Saeed</h5>
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
                 data-en="&ldquo;I passed my academic IELTS test with a score of 7.5 thanks to the intensive courses at Full Mark. The native instructors understand testing methodologies perfectly.&rdquo;"
                 data-ar="&ldquo;اجتزت اختبار آيلتس الأكاديمي بنتيجة 7.5 بفضل الدورة المكثفة في الأكاديمية. المدربون على دراية كاملة بمنهجية الاختبار الدقيقة.&rdquo;">
                "I passed my academic IELTS test with a score of 7.5 thanks to the intensive courses at Full Mark. The native instructors understand testing methodologies perfectly."
              </p>
            </div>
          </div>

          <!-- Slide 2 -->
          <div class="swiper-slide">
            <div class="glass-panel testimonial-card-swiper">
              <div class="d-flex align-items-center mb-4">
                <div class="testimonial-avatar">
                  <img src="{{ asset('site/images/img/students/student2.jpg') }}" alt="Mariam Naser" loading="lazy">
                </div>
                <div>
                  <h5 class="font-bold mb-1" style="color: var(--text-primary);">Mariam Naser</h5>
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
                 data-en="&ldquo;The flexible timings were crucial for me as a working pharmacist. The educational platforms and level assessments are exceptionally professional.&rdquo;"
                 data-ar="&ldquo;كانت الأوقات المرنة حاسمة بالنسبة لي كصيدلانية عاملة. المنصات التعليمية وتقييمات المستويات تفوق التوقعات احترافية.&rdquo;">
                "The flexible timings were crucial for me as a working pharmacist. The educational platforms and level assessments are exceptionally professional."
              </p>
            </div>
          </div>

          <!-- Slide 3 -->
          <div class="swiper-slide">
            <div class="glass-panel testimonial-card-swiper">
              <div class="d-flex align-items-center mb-4">
                <div class="testimonial-avatar">
                  <img src="{{ asset('site/images/img/students/student3_new.png') }}" alt="Samer Kabbani" loading="lazy">
                </div>
                <div>
                  <h5 class="font-bold mb-1" style="color: var(--text-primary);">Samer Kabbani</h5>
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
                 data-en="&ldquo;Full Mark Test of English (OTE) at their center was a seamless experience. They provided pre-test mock software which built my confidence.&rdquo;"
                 data-ar="&ldquo;كان خوض اختبار العلامة الكاملة (OTE) في المركز تجربة سلسة للغاية. وفروا لنا اختبارًا تجريبيًا مسبقًا ساعدني على كسب الثقة.&rdquo;">
                "Full Mark Test of English (OTE) at their center was a seamless experience. They provided pre-test mock software which built my confidence."
              </p>
            </div>
          </div>
        </div>

        <!-- Navigation bullets -->
        <div class="swiper-pagination mt-8"></div>
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

            <!-- Item 1 -->
            <div class="accordion-item border-0">
              <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne"
                        data-en="What is the Full Mark Test of English (OTE)?"
                        data-ar="ما هو اختبار العلامة الكاملة للغة الإنجليزية (OTE)؟">
                  What is the Full Mark Test of English (OTE)?
                </button>
              </h2>
              <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                <div class="accordion-body"
                     data-en="The Full Mark Test of English (OTE) is a multi-level general English proficiency test certified by the Full Mark University. It assesses Listening, Speaking, Reading, and Writing skills at levels A2, B1, and B2 of the CEFR."
                     data-ar="اختبار العلامة الكاملة للغة الإنجليزية (OTE) هو اختبار كفاءة لغوي متعدد المستويات معتمد من جامعة العلامة الكاملة. يقيم مهارات الاستماع والمحادثة والقراءة والكتابة للمستويات A2 و B1 و B2 في الإطار الأوروبي المشترك.">
                  The Full Mark Test of English (OTE) is a multi-level general English proficiency test certified by the Full Mark University. It assesses Listening, Speaking, Reading, and Writing skills at levels A2, B1, and B2 of the CEFR.
                </div>
              </div>
            </div>

            <!-- Item 2 -->
            <div class="accordion-item border-0">
              <h2 class="accordion-header" id="headingTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo"
                        data-en="How can I book a placement test?"
                        data-ar="كيف يمكنني حجز اختبار تحديد المستوى؟">
                  How can I book a placement test?
                </button>
              </h2>
              <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                <div class="accordion-body"
                     data-en="You can easily book a placement test online by filling out our quick contact form, choosing 'Placement Test' in the Contact Type dropdown, or contacting us via our direct phone line."
                     data-ar="يمكنك حجز موعد اختبار تحديد المستوى مباشرة عبر الإنترنت من خلال ملء نموذج الاتصال بنا وتحديد 'تحديد المستوى' من قائمة نوع الطلب، أو من خلال الاتصال المباشر بخدمة العملاء.">
                  You can easily book a placement test online by filling out our quick contact form, choosing 'Placement Test' in the Contact Type dropdown, or contacting us via our direct phone line.
                </div>
              </div>
            </div>

            <!-- Item 3 -->
            <div class="accordion-item border-0">
              <h2 class="accordion-header" id="headingThree">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree"
                        data-en="Are your certificates internationally recognized?"
                        data-ar="هل الشهادات الصادرة معترف بها دولياً؟">
                  Are your certificates internationally recognized?
                </button>
              </h2>
              <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                <div class="accordion-body"
                     data-en="Yes, FULL MARKS ACADEMY is a registered test centre. The Full Mark Test of English certificate is officially approved by embassies, universities, and international organizations globally."
                     data-ar="نعم، أكاديمية العلامة الكاملة هي مركز اختبارات مسجل ومعتمد رسميًا. شهادة اختبار العلامة الكاملة معترف بها ومصادق عليها من قبل السفارات والجامعات والمؤسسات الدولية حول العالم.">
                  Yes, FULL MARKS ACADEMY is a registered test centre. The Full Mark Test of English certificate is officially approved by embassies, universities, and international organizations globally.
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
                <a href="tel:+96279000000" class="text-decoration-none opacity-75 text-sm" style="color: var(--text-primary);">+962 79 123 4567</a>
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
                <p class="opacity-75 text-sm mb-0" style="color: var(--text-primary);" data-en="University Street, Building 45, Amman, Jordan" data-ar="شارع الجامعة، مبنى 45، عمان، الأردن">University Street, Building 45, Amman, Jordan</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Contact Form -->
        <div class="col-lg-7">
          <div class="glass-panel p-6 md:p-12">
            <form onsubmit="handleFormSubmit(event)">
              <div class="row">
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <input type="text" id="contactName" placeholder=" " required>
                    <label for="contactName" data-en="Full Name" data-ar="الاسم الكامل">Full Name</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <input type="email" id="contactEmail" placeholder=" " required>
                    <label for="contactEmail" data-en="Email Address" data-ar="البريد الإلكتروني">Email Address</label>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="floating-input-group">
                    <select id="contactType" required>
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
                    <input type="text" id="contactPhone" placeholder=" ">
                    <label for="contactPhone" data-en="Phone Number" data-ar="رقم الهاتف">Phone Number</label>
                  </div>
                </div>
              </div>

              <div class="floating-input-group">
                <textarea id="contactMessage" rows="5" placeholder=" " required></textarea>
                <label for="contactMessage" data-en="Your Message" data-ar="رسالتك">Your Message</label>
              </div>

              <button type="submit" class="btn btn-luxury w-100 py-3 rounded-lg text-lg d-flex align-items-center justify-content-center">
                <span data-en="Send Message" data-ar="إرسال الرسالة">Send Message</span>
                <i class="bi bi-arrow-right ms-2 rtl:rotate-180"></i>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
@endsection
