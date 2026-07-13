    <header class="dashboard-header">
      <div class="d-flex align-items-center">
        <!-- Mobile Sidebar Toggle -->
        <button class="btn btn-glass icon-btn d-lg-none me-3" onclick="toggleSidebar()">
          <i class="bi bi-list fs-4"></i>
        </button>
        <h2 class="h5 mb-0 fw-bold d-none d-md-block" style="color: var(--text-primary);"
            data-en="@yield('page_title_en', 'Overview')" data-ar="@yield('page_title_ar', 'نظرة عامة')">@yield('page_title_en', 'Overview')</h2>
      </div>

      @php($headerStudent = auth('student')->user())
      <div class="d-flex align-items-center gap-3">
        <!-- Language Switcher -->
        <button id="langToggleBtn" class="btn btn-glass icon-btn" onclick="toggleLanguage()" title="Toggle language">
          <i class="bi bi-globe2"></i>
        </button>

        <!-- Theme Cycle -->
        <button id="themeCycleBtn" class="btn btn-glass icon-btn" type="button" title="Switch theme">
          <i class="bi bi-award-fill"></i>
        </button>

        <!-- Profile Mini -->
        <a href="{{ route('student.profile') }}" class="d-flex align-items-center gap-2 ms-2 cursor-pointer p-1 rounded-pill text-decoration-none" style="background: var(--bg-secondary); border: 1px solid var(--separator-color);">
          <img src="{{ $headerStudent && $headerStudent->image ? asset('storage/'.$headerStudent->image) : asset('site/images/logo_v2_gold.png') }}" alt="Avatar" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
          <span class="fw-semibold d-none d-sm-block me-3 text-sm" style="color: var(--text-primary);">{{ $headerStudent?->name }}</span>
        </a>
      </div>
    </header>
