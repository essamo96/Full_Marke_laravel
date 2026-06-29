    <header class="dashboard-header">
      <div class="d-flex align-items-center">
        <!-- Mobile Sidebar Toggle -->
        <button class="btn btn-glass icon-btn d-lg-none me-3" onclick="toggleSidebar()">
          <i class="bi bi-list fs-4"></i>
        </button>
        <h2 class="h5 mb-0 fw-bold d-none d-md-block" style="color: var(--text-primary);" data-en="Overview" data-ar="نظرة عامة">Overview</h2>
      </div>

      <div class="d-flex align-items-center gap-3">
        <!-- Language Switcher -->
        <button id="langToggleBtn" class="btn btn-glass icon-btn" onclick="toggleLanguage()" title="Toggle language">
          <i class="bi bi-globe2"></i>
        </button>

        <!-- Theme Cycle -->
        <button id="themeCycleBtn" class="btn btn-glass icon-btn" type="button" title="Switch theme">
          <i class="bi bi-award-fill"></i>
        </button>

        <!-- Notifications -->
        <button class="btn btn-glass icon-btn position-relative">
          <i class="bi bi-bell-fill"></i>
          <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
            <span class="visually-hidden">New alerts</span>
          </span>
        </button>

        <!-- Profile Mini -->
        <div class="d-flex align-items-center gap-2 ms-2 cursor-pointer p-1 rounded-pill" style="background: var(--bg-secondary); border: 1px solid var(--separator-color);">
          <img src="{{ asset('site/images/img/logo_backup.png') }}" alt="Avatar" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
          <span class="fw-semibold d-none d-sm-block me-3 text-sm" style="color: var(--text-primary);">Ahmad M.</span>
        </div>
      </div>
    </header>
