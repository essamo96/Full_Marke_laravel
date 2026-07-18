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
      @php($headerUnreadNotifications = $headerStudent ? $headerStudent->unreadNotifications()->latest()->limit(10)->get() : collect())
      @php($headerUnreadCount = $headerStudent ? $headerStudent->unreadNotifications()->count() : 0)
      <div class="d-flex align-items-center gap-3">
        <!-- Language Switcher -->
        <button id="langToggleBtn" class="btn btn-glass icon-btn" onclick="toggleLanguage()" title="Toggle language">
          <i class="bi bi-globe2"></i>
        </button>

        <!-- Theme Cycle -->
        <button id="themeCycleBtn" class="btn btn-glass icon-btn" type="button" title="Switch theme">
          <i class="bi bi-award-fill"></i>
        </button>

        <!-- Notifications Bell -->
        <div class="dropdown">
          <button class="btn btn-glass icon-btn position-relative" type="button" id="studentNotificationsBell" data-bs-toggle="dropdown" aria-expanded="false" title="الإشعارات">
            <i class="bi bi-bell-fill"></i>
            <span id="studentNotificationsBadge" class="position-absolute badge rounded-pill bg-danger {{ $headerUnreadCount > 0 ? '' : 'd-none' }}" style="top: -2px; inset-inline-end: -2px; font-size: 0.65rem;">{{ $headerUnreadCount }}</span>
          </button>
          <div class="dropdown-menu dropdown-menu-end p-0" style="width: 340px; max-width: 90vw;" aria-labelledby="studentNotificationsBell">
            <div class="d-flex align-items-center justify-content-between p-3 border-bottom" style="border-color: var(--separator-color) !important;">
              <span class="fw-bold" style="color: var(--text-primary);">الإشعارات</span>
              <button type="button" class="btn btn-link btn-sm p-0" id="studentMarkAllReadBtn">تعليم الكل كمقروء</button>
            </div>
            <div id="studentNotificationsList" class="p-2" style="max-height: 360px; overflow-y: auto;">
              @forelse($headerUnreadNotifications as $notification)
                <a href="{{ $notification->data['url'] ?? '#' }}" class="d-block text-decoration-none p-2 rounded mb-1 student-notification-item" data-id="{{ $notification->id }}" style="background: var(--bg-secondary); color: var(--text-primary);">
                  <div class="fs-7">{{ $notification->data['message'] ?? '' }}</div>
                  <div class="text-muted" style="font-size: 0.7rem;">{{ $notification->created_at->diffForHumans() }}</div>
                </a>
              @empty
                <p class="text-muted text-center py-6 mb-0" id="studentNoNotificationsMsg">لا توجد إشعارات جديدة</p>
              @endforelse
            </div>
          </div>
        </div>

        <!-- Profile Mini -->
        <a href="{{ route('student.profile') }}" class="d-flex align-items-center gap-2 ms-2 cursor-pointer p-1 rounded-pill text-decoration-none" style="background: var(--bg-secondary); border: 1px solid var(--separator-color);">
          <img src="{{ $headerStudent && $headerStudent->image ? asset('storage/'.$headerStudent->image) : asset('site/images/logo_v2_gold.png') }}" alt="Avatar" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
          <span class="fw-semibold d-none d-sm-block me-3 text-sm" style="color: var(--text-primary);">{{ $headerStudent?->name }}</span>
        </a>
      </div>
    </header>
