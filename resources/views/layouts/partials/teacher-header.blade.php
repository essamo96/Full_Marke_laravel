    <header class="dashboard-header">
      <div class="d-flex align-items-center">
        <!-- Mobile Sidebar Toggle -->
        <button class="btn btn-glass icon-btn d-md-none me-3" onclick="toggleSidebar()">
          <i class="bi bi-list fs-4"></i>
        </button>

        @if(!Request::is('teacher/dashboard') && !Request::is('teacher'))
        <!-- Back Button -->
        <button class="btn btn-glass icon-btn me-3" onclick="history.back()" title="عودة">
          <i class="bi bi-arrow-right rtl:rotate-180"></i>
        </button>
        @endif

        <h2 class="h5 mb-0 fw-bold d-none d-md-block" style="color: var(--text-primary);"
            data-en="@yield('page_title_en', 'Overview')" data-ar="@yield('page_title_ar', 'الرئيسية')">@yield('page_title_en', 'Overview')</h2>
      </div>

      @php
        $headerTeacher = auth('teacher')->user();
        $headerUnreadNotifications = $headerTeacher ? $headerTeacher->unreadNotifications()->latest()->limit(10)->get() : collect();
        $headerUnreadCount = $headerTeacher ? $headerTeacher->unreadNotifications()->count() : 0;
      @endphp
      <div class="d-flex align-items-center gap-1 gap-md-3">
        <!-- Language Switcher -->
        <button id="langToggleBtn" class="btn btn-glass icon-btn" onclick="toggleLanguage()" title="Toggle language">
          <i class="bi bi-globe2"></i>
        </button>

        <!-- Accessibility Menu (سهولة الوصول) -->
        <div class="dropdown">
          <button class="btn btn-glass icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="سهولة الوصول">
            <i class="bi bi-universal-access"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end p-2 shadow-lg" style="background: var(--bg-secondary); border: 1px solid var(--separator-color);">
            <li><button class="dropdown-item rounded d-flex align-items-center gap-2 mb-1" onclick="document.documentElement.style.fontSize='110%'" style="color: var(--text-primary); transition: all 0.2s;"><i class="bi bi-zoom-in"></i> <span data-en="Increase Font" data-ar="تكبير الخط">تكبير الخط</span></button></li>
            <li><button class="dropdown-item rounded d-flex align-items-center gap-2 mb-1" onclick="document.documentElement.style.fontSize='100%'" style="color: var(--text-primary); transition: all 0.2s;"><i class="bi bi-type"></i> <span data-en="Default Font" data-ar="الخط الافتراضي">الخط الافتراضي</span></button></li>
            <li><button class="dropdown-item rounded d-flex align-items-center gap-2" onclick="document.documentElement.style.fontSize='90%'" style="color: var(--text-primary); transition: all 0.2s;"><i class="bi bi-zoom-out"></i> <span data-en="Decrease Font" data-ar="تصغير الخط">تصغير الخط</span></button></li>
          </ul>
        </div>

        <!-- Theme Cycle -->
        <button id="themeCycleBtn" class="btn btn-glass icon-btn" type="button" title="Switch theme">
          <i class="bi bi-award-fill"></i>
        </button>

        <!-- Notifications -->
        <div class="dropdown">
          <button class="btn btn-glass icon-btn position-relative" type="button" id="teacherNotificationsBell" data-bs-toggle="dropdown" aria-expanded="false" title="الإشعارات">
            <i class="bi bi-bell-fill"></i>
            <span id="teacherNotificationsBadge" class="position-absolute badge rounded-pill bg-danger {{ $headerUnreadCount > 0 ? '' : 'd-none' }}" style="top: -2px; inset-inline-end: -2px; font-size: 0.65rem;">{{ $headerUnreadCount }}</span>
          </button>
          <div class="dropdown-menu dropdown-menu-end p-0" style="width: 340px; max-width: 90vw;" aria-labelledby="teacherNotificationsBell">
            <div class="d-flex align-items-center justify-content-between p-3 border-bottom" style="border-color: var(--separator-color) !important;">
              <span class="fw-bold" style="color: var(--text-primary);">الإشعارات</span>
              <button type="button" class="btn btn-link btn-sm p-0" id="teacherMarkAllReadBtn">تعليم الكل كمقروء</button>
            </div>
            <div id="teacherNotificationsList" class="p-2" style="max-height: 360px; overflow-y: auto;">
              @forelse($headerUnreadNotifications as $notification)
                <a href="{{ $notification->data['url'] ?? '#' }}" class="d-block text-decoration-none p-2 rounded mb-1 teacher-notification-item" data-id="{{ $notification->id }}" style="background: var(--bg-secondary); color: var(--text-primary);">
                  <div class="fs-7">{{ $notification->data['message'] ?? '' }}</div>
                  <div class="text-muted" style="font-size: 0.7rem;">{{ $notification->created_at->diffForHumans() }}</div>
                </a>
              @empty
                <p class="text-muted text-center py-6 mb-0" id="teacherNoNotificationsMsg">لا توجد إشعارات جديدة</p>
              @endforelse
            </div>
          </div>
        </div>

        <!-- Profile Mini -->
        <a href="{{ route('teacher.profile.edit') }}" class="d-flex align-items-center gap-2 ms-2 cursor-pointer p-1 rounded-pill text-decoration-none" style="background: var(--bg-secondary); border: 1px solid var(--separator-color);">
          <img src="{{ $headerTeacher && $headerTeacher->photo ? asset('storage/'.$headerTeacher->photo) : asset('site/images/img/logo_backup.png') }}" alt="Avatar" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
          <span class="fw-semibold d-none d-sm-block me-3 text-sm" style="color: var(--text-primary);">{{ $headerTeacher?->name }}</span>
        </a>
      </div>
    </header>
