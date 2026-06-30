  <!-- Lets section-anchor links (#programs, #about, ...) navigate home first when
       clicked from a page that doesn't contain those sections (login, register, ...) -->
  <script>window.SITE_HOME_URL = @json(route('site.home'));</script>

  <!-- Sticky Navbar -->
  <header id="header-nav" class="sticky-header d-flex align-items-center">
    <div class="container d-flex align-items-center justify-content-between">
      <!-- Logo -->
      <a href="{{ route('site.home') }}" class="d-flex align-items-center text-decoration-none">
        <img id="navbar-logo" src="{{ asset('site/images/logo_v2_gold.png') }}" alt="Full Mark Logo" class="object-contain py-1 me-3">
      </a>

      <!-- Primary nav — exactly 7 items, all always inline (no "More" dropdown
           needed). News and FAQ sections still exist on the page but are
           intentionally not linked from the navbar. -->
      <nav id="primary-nav" class="primary-nav d-flex align-items-center">
        <a href="#hero" class="desktop-nav-link nav-item-primary text-decoration-none font-medium active" style="color: var(--text-primary);" data-en="Home" data-ar="الرئيسية">Home</a>
        <a href="#about" class="desktop-nav-link nav-item-primary text-decoration-none font-medium" style="color: var(--text-primary);" data-en="About" data-ar="عن الأكاديمية">About</a>
        <a href="#programs" class="desktop-nav-link nav-item-primary text-decoration-none font-medium" style="color: var(--text-primary);" data-en="Programs" data-ar="البرامج">Programs</a>
        <a href="#strengths" class="desktop-nav-link nav-item-primary text-decoration-none font-medium" style="color: var(--text-primary);" data-en="Strengths" data-ar="مميزاتنا">Strengths</a>
        <a href="#actions" class="desktop-nav-link nav-item-primary text-decoration-none font-medium" style="color: var(--text-primary);" data-en="Services" data-ar="خدماتنا">Services</a>
        <a href="#testimonials" class="desktop-nav-link nav-item-primary text-decoration-none font-medium" style="color: var(--text-primary);" data-en="Testimonials" data-ar="التقييمات">Testimonials</a>
        <a href="#contact" class="desktop-nav-link nav-item-primary text-decoration-none font-medium" style="color: var(--text-primary);" data-en="Contact" data-ar="اتصل بنا">Contact</a>
      </nav>

      <!-- Action Buttons — visible from tablet up -->
      <div class="header-actions d-flex align-items-center">
        <!-- Language Switcher (icon-only) — session-based, no /ar or /en in the URL -->
        @if(\App\Models\SiteSetting::current()->show_translation_button)
        <a href="{{ route('site.lang', app()->getLocale() === 'ar' ? 'en' : 'ar') }}"
           id="langToggleBtn" class="btn btn-glass icon-btn" aria-label="Toggle language" title="Toggle language">
          <i class="bi bi-globe2"></i>
        </a>
        @endif

        <!-- Theme Cycle (icon-only, cycles on click) -->
        <button id="themeCycleBtn" class="btn btn-glass icon-btn" type="button" aria-label="Switch theme" title="Switch theme">
          <i class="bi bi-award-fill"></i>
        </button>

        <!-- Student Cart Button -->
        <button id="cartBtn" class="btn btn-glass icon-btn position-relative" onclick="toggleCartModal()" aria-label="Open cart" title="Open cart">
          <i class="bi bi-bag-fill"></i>
          <span id="cartCountBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display: none; font-size: 0.65rem;">0</span>
        </button>

        @auth('student')
          <div class="dropdown">
            <button class="btn btn-glass d-flex align-items-center gap-2 px-2 py-1 rounded-lg" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <img src="{{ Auth::guard('student')->user()->image ? asset('storage/'.Auth::guard('student')->user()->image) : asset('site/images/img/logo_backup.png') }}"
                   alt="{{ Auth::guard('student')->user()->full_name_en }}" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
              <span class="d-none d-sm-inline font-medium" style="color: var(--text-primary);">{{ Auth::guard('student')->user()->full_name_en }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="{{ route('student.profile') }}" data-en="My Profile" data-ar="ملفي الشخصي">My Profile</a></li>
              <li><a class="dropdown-item" href="{{ route('student.registrations') }}" data-en="My Registrations" data-ar="تسجيلاتي">My Registrations</a></li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <form method="POST" action="{{ route('student.logout') }}">
                  @csrf
                  <button type="submit" class="dropdown-item" data-en="Logout" data-ar="تسجيل الخروج">Logout</button>
                </form>
              </li>
            </ul>
          </div>
        @else
          <a href="{{ route('student.login') }}" class="btn btn-glass student-gate-link px-3 py-2 rounded-lg" data-en="Student Login" data-ar="دخول الطالب">Student Login</a>
        @endauth

        <!-- Teacher Login grouped with Apply Now (separated from Student Login) -->
        <div class="d-flex align-items-center gap-2 ms-2">
            <a href="{{ route('teacher.login') }}" class="btn btn-glass teacher-gate-link px-3 py-2 rounded-lg" data-en="Teacher Login" data-ar="دخول المعلم">Teacher Login</a>
            <a href="{{ route('apply.create') }}" class="btn btn-luxury px-4 py-2 rounded-lg" data-en="Apply Now" data-ar="سجل الآن">Apply Now</a>
        </div>
      </div>

      <!-- Mobile Menu Toggle (visible <768px only) -->
      <button id="mobileMenuToggle" class="mobile-menu-toggle text-2xl p-2 rounded-lg" style="color: var(--text-primary);" aria-label="Open menu">
        <i class="bi bi-list"></i>
      </button>
    </div>
  </header>

  <!-- Mobile Navigation Drawer -->
  <div id="mobileMenuBackdrop" class="mobile-menu-backdrop"></div>
  <div id="mobileNavMenu" class="mobile-nav-menu d-flex flex-column justify-content-between">
    <div>
      <div class="d-flex align-items-center justify-content-between mb-8">
        <span class="text-xl font-bold tracking-wider" style="color: var(--text-primary);" data-en="FULL MARKS ACADEMY" data-ar="أكاديمية العلامة الكاملة">FULL MARKS ACADEMY</span>
        <button id="closeMobileMenu" class="text-2xl p-2" style="color: var(--text-primary);">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      <nav class="d-flex flex-column space-y-4">
        <a href="#hero" class="text-lg text-decoration-none font-medium py-2" style="color: var(--text-primary);" data-en="Home" data-ar="الرئيسية">Home</a>
        <a href="#about" class="text-lg text-decoration-none font-medium py-2" style="color: var(--text-primary);" data-en="About" data-ar="عن الأكاديمية">About</a>
        <a href="#programs" class="text-lg text-decoration-none font-medium py-2" style="color: var(--text-primary);" data-en="Programs" data-ar="البرامج">Programs</a>
        <a href="#strengths" class="text-lg text-decoration-none font-medium py-2" style="color: var(--text-primary);" data-en="Strengths" data-ar="مميزاتنا">Strengths</a>
        <a href="#actions" class="text-lg text-decoration-none font-medium py-2" style="color: var(--text-primary);" data-en="Services" data-ar="خدماتنا">Services</a>
        <a href="#testimonials" class="text-lg text-decoration-none font-medium py-2" style="color: var(--text-primary);" data-en="Testimonials" data-ar="التقييمات">Testimonials</a>
        <a href="#contact" class="text-lg text-decoration-none font-medium py-2" style="color: var(--text-primary);" data-en="Contact" data-ar="اتصل بنا">Contact</a>
      </nav>
    </div>

    <!-- Mobile controls -->
    <div class="d-flex flex-column gap-3 mt-8">
      <div class="d-flex justify-content-center gap-3">
        <a href="{{ route('site.lang', app()->getLocale() === 'ar' ? 'en' : 'ar') }}"
           class="btn btn-glass icon-btn icon-btn-lg" aria-label="Toggle language">
          <i class="bi bi-globe2"></i>
        </a>
        <button id="themeCycleBtnMobile" class="btn btn-glass icon-btn icon-btn-lg" type="button" aria-label="Switch theme">
          <i class="bi bi-award-fill"></i>
        </button>
        <!-- Mobile Student Cart Button -->
        <button id="cartBtnMobile" class="btn btn-glass icon-btn icon-btn-lg position-relative" onclick="toggleCartModal()" aria-label="Open cart">
          <i class="bi bi-bag-fill"></i>
          <span id="cartCountBadgeMobile" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display: none; font-size: 0.65rem;">0</span>
        </button>
      </div>

      @auth('student')
        <a href="{{ route('student.profile') }}" class="btn btn-glass w-100 py-3 rounded-lg text-center d-flex align-items-center justify-content-center gap-2">
          <img src="{{ Auth::guard('student')->user()->image ? asset('storage/'.Auth::guard('student')->user()->image) : asset('site/images/img/logo_backup.png') }}"
               alt="{{ Auth::guard('student')->user()->full_name_en }}" class="rounded-circle" style="width: 28px; height: 28px; object-fit: cover;">
          {{ Auth::guard('student')->user()->full_name_en }}
        </a>
        <form method="POST" action="{{ route('student.logout') }}">
          @csrf
          <button type="submit" class="btn btn-glass w-100 py-3 rounded-lg text-center" data-en="Logout" data-ar="تسجيل الخروج">Logout</button>
        </form>
      @else
        <a href="{{ route('student.login') }}" class="btn btn-glass w-100 py-3 rounded-lg text-center" data-en="Student Login" data-ar="دخول الطالب">Student Login</a>
      @endauth
      <a href="{{ route('teacher.login') }}" class="btn btn-glass w-100 py-3 rounded-lg text-center" data-en="Teacher Login" data-ar="دخول المعلم">Teacher Login</a>
      <a href="{{ route('apply.create') }}" class="btn btn-luxury w-100 py-3 rounded-lg text-center" data-en="Apply Now" data-ar="سجل الآن">Apply Now</a>
    </div>
  </div>
