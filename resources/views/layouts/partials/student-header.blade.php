    <header class="dashboard-header">
      <div class="d-flex align-items-center">
        <!-- Mobile Sidebar Toggle -->
        <button class="btn btn-glass icon-btn d-md-none me-3" onclick="toggleSidebar()">
          <i class="bi bi-list fs-4"></i>
        </button>

        @if(!Request::is('student/dashboard') && !Request::is('student'))
        <!-- Back Button -->
        <button class="btn btn-glass icon-btn me-3" onclick="history.back()" title="عودة">
          <i class="bi bi-arrow-right rtl:rotate-180"></i>
        </button>
        @endif

        <h2 class="h5 mb-0 fw-bold d-none d-md-block" style="color: var(--text-primary);"
            data-en="@yield('page_title_en', 'Overview')" data-ar="@yield('page_title_ar', 'نظرة عامة')">@yield('page_title_en', 'Overview')</h2>
      </div>

      @php($headerStudent = auth('student')->user())
      @php($headerUnreadNotifications = $headerStudent ? $headerStudent->unreadNotifications()->latest()->limit(10)->get() : collect())
      @php($headerUnreadCount = $headerStudent ? $headerStudent->unreadNotifications()->count() : 0)
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

        <!-- Notifications Bell -->
        <div class="dropdown">
          <button class="btn btn-glass icon-btn position-relative" type="button" id="studentNotificationsBell" data-bs-toggle="dropdown" aria-expanded="false" title="الإشعارات">
            <i class="bi bi-bell-fill"></i>
            <span id="studentNotificationsBadge" class="position-absolute badge rounded-pill bg-danger {{ $headerUnreadCount > 0 ? '' : 'd-none' }}" style="top: -2px; inset-inline-end: -2px; font-size: 0.65rem;">{{ $headerUnreadCount }}</span>
          </button>
          <div class="dropdown-menu dropdown-menu-end p-0 shadow-lg border-0 notification-dropdown" style="width: 360px; max-width: 90vw; background: var(--bg-primary); overflow: hidden; border-radius: 1rem;" aria-labelledby="studentNotificationsBell">
            <style>
              .notification-dropdown {
                background-image: radial-gradient(var(--separator-color) 1px, transparent 1px);
                background-size: 20px 20px;
                background-position: 0 0, 10px 10px;
              }
              .student-notification-item {
                background: var(--bg-secondary);
                border: 1px solid var(--separator-color);
                transition: all 0.3s ease;
                position: relative;
                overflow: hidden;
              }
              .student-notification-item::before {
                content: '';
                position: absolute;
                top: 0; right: 0; bottom: 0; left: 0;
                background: linear-gradient(45deg, transparent, rgba(197, 168, 128, 0.05), transparent);
                transform: translateX(100%);
                transition: 0.5s ease;
              }
              .student-notification-item:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                border-color: var(--accent-color);
              }
              .student-notification-item:hover::before {
                transform: translateX(-100%);
              }
            </style>
            <div class="d-flex align-items-center justify-content-between p-3 border-bottom" style="border-color: var(--separator-color) !important; background: var(--bg-secondary); backdrop-filter: blur(10px);">
              <span class="fw-bold" style="color: var(--text-primary);"><i class="bi bi-bell-fill me-2" style="color: var(--accent-color);"></i>الإشعارات</span>
              <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" id="studentMarkAllReadBtn" style="color: var(--accent-color);">تعليم الكل كمقروء</button>
            </div>
            <div id="studentNotificationsList" class="p-3" style="max-height: 380px; overflow-y: auto;">
              @forelse($headerUnreadNotifications as $notification)
                <a href="{{ str_replace('/public/student', '/student', $notification->data['url'] ?? '#') }}" class="d-block text-decoration-none p-3 rounded-3 mb-2 student-notification-item" data-id="{{ $notification->id }}" style="color: var(--text-primary);">
                  <div class="d-flex gap-3 align-items-start">
                    <div class="flex-shrink-0 mt-1">
                      <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(197, 168, 128, 0.15);">
                        <i class="bi bi-info-circle-fill" style="color: var(--accent-color); font-size: 1.1rem;"></i>
                      </div>
                    </div>
                    <div>
                      <div class="fs-7 fw-medium mb-1" style="line-height: 1.4;">{{ $notification->data['message'] ?? '' }}</div>
                      <div class="d-flex align-items-center gap-1 opacity-75" style="font-size: 0.75rem;">
                        <i class="bi bi-clock"></i>
                        {{ $notification->created_at->diffForHumans() }}
                      </div>
                    </div>
                  </div>
                </a>
              @empty
                <div class="text-center py-5" id="studentNoNotificationsMsg">
                  <i class="bi bi-bell-slash text-muted mb-2" style="font-size: 2rem; opacity: 0.5;"></i>
                  <p class="text-muted mb-0">لا توجد إشعارات جديدة</p>
                </div>
              @endforelse
            </div>
          </div>
        </div>

        <!-- Profile Mini — opens a dropdown with profile & logout -->
        <div class="dropdown">
          <button type="button" class="d-flex align-items-center gap-2 ms-2 cursor-pointer p-1 rounded-pill border-0" data-bs-toggle="dropdown" aria-expanded="false" style="background: var(--bg-secondary); border: 1px solid var(--separator-color) !important;">
            <div class="position-relative">
              <img src="{{ $headerStudent?->photo_url ?? asset('assets/admin/media/avatars/blank.png') }}" alt="Avatar" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
              @if($headerStudent && $headerStudent->email_verified_at)
                <span class="position-absolute d-flex align-items-center justify-content-center bg-gold rounded-circle shadow-sm pulse-glow" style="bottom: -2px; right: -2px; width: 14px; height: 14px; border: 2px solid var(--bg-secondary);" title="طالب موثوق">
                  <i class="bi bi-check" style="color: #fff; font-size: 10px; line-height: 1; -webkit-text-stroke: 1px #fff;"></i>
                </span>
              @endif
            </div>
            <span class="fw-semibold d-none d-sm-flex align-items-center me-2 text-sm" style="color: var(--text-primary);">
              {{ $headerStudent?->name }}
              @if($headerStudent && $headerStudent->email_verified_at)
                <i class="bi bi-patch-check-fill ms-2 pulse-glow" style="color: var(--accent-color); font-size: 1rem;" title="حساب موثق"></i>
              @endif
            </span>
            <i class="bi bi-chevron-down d-none d-sm-inline me-2" style="color: var(--text-muted); font-size: .7rem;"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow-lg p-2" style="background: var(--bg-secondary); border: 1px solid var(--separator-color); min-width: 200px;">
            <li>
              <a class="dropdown-item rounded d-flex align-items-center gap-2" href="{{ route('student.profile') }}" style="color: var(--text-primary);">
                <i class="bi bi-person-circle" style="color: var(--accent-color);"></i>
                <span data-en="My Profile" data-ar="الملف الشخصي">الملف الشخصي</span>
              </a>
            </li>
            <li><hr class="dropdown-divider" style="border-color: var(--separator-color);"></li>
            <li>
              <form action="{{ route('student.logout') }}" method="POST">
                @csrf
                <button type="submit" class="dropdown-item rounded d-flex align-items-center gap-2 text-danger">
                  <i class="bi bi-box-arrow-left"></i>
                  <span data-en="Logout" data-ar="تسجيل الخروج">تسجيل الخروج</span>
                </button>
              </form>
            </li>
          </ul>
        </div>
      </div>
    </header>
