
  <!-- Ambient Animated Background -->
  <div class="fixed inset-0 pointer-events-none" style="z-index: -1;">
    <div class="absolute top-0 left-0 w-1/2 h-1/2 bg-gold/10 rounded-full blur-[120px] mix-blend-screen opacity-50 float-slow"></div>
    <div class="absolute bottom-0 right-0 w-1/2 h-1/2 bg-gold/5 rounded-full blur-[150px] mix-blend-screen opacity-50 float-medium"></div>
  </div>

  <div class="dashboard-wrapper">
    <!-- Overlay for mobile sidebar -->
    <div id="sidebarOverlay" class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <!-- ════ SIDEBAR ════ -->
    <aside id="dashboardSidebar" class="dashboard-sidebar">
      @php($sidebarStudent = auth('student')->user())
      <div class="d-flex flex-column align-items-center mb-5 border-b pb-4" style="border-color: var(--separator-color) !important;">
        <div class="w-20 h-20 rounded-circle border-2 p-1 mb-3 position-relative overflow-hidden" style="border-color: var(--accent-color);">
          <img src="{{ $sidebarStudent?->photo_url ?? asset('assets/admin/media/avatars/blank.png') }}"
               alt="{{ $sidebarStudent->name ?? 'Student' }}" class="w-100 h-100 object-cover rounded-circle">
        </div>
        <h6 class="fw-bold mb-0 text-center d-flex align-items-center justify-content-center" style="color: var(--text-primary);">
          {{ $sidebarStudent->name ?? '' }}
          @if($sidebarStudent && $sidebarStudent->is_active)
            <i class="bi bi-patch-check-fill verified-badge ms-1" title="حساب مفعل"></i>
          @endif
        </h6>
        <span class="text-xs opacity-75 mt-1" data-en="Student" data-ar="طالب">Student</span>
      </div>

      <nav class="flex-1 d-flex flex-column gap-1 w-100 px-2">
        <!-- Dashboard -->
        <a href="{{ route('student.dashboard') }}" class="sidebar-nav-item {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
          <i class="bi bi-grid-1x2-fill sidebar-nav-item-icon"></i>
          <span class="sidebar-nav-item-text" data-en="Overview" data-ar="الرئيسية">Overview</span>
        </a>

        <!-- Academy -->
        <a href="#menuAcademy" class="sidebar-nav-item accordion-trigger"  aria-expanded="false" aria-controls="menuAcademy">
          <i class="bi bi-mortarboard sidebar-nav-item-icon"></i>
          <span class="sidebar-nav-item-text" data-en="Academy" data-ar="الأكاديمية">Academy</span>
          <i class="bi bi-chevron-down sidebar-nav-item-chevron"></i>
        </a>
        <ul class="sidebar-submenu-wrapper sidebar-submenu" id="menuAcademy">
          <li><a href="{{ route('student.programs') }}" class="sidebar-submenu-item {{ request()->routeIs('student.programs') ? 'active' : '' }}" data-en="Programs" data-ar="البرامج الدراسية">Programs</a></li>
          <li><a href="{{ route('student.groups') }}" class="sidebar-submenu-item {{ request()->routeIs('student.groups') ? 'active' : '' }}" data-en="Study Groups & Sessions" data-ar="المجموعات والجلسات الدراسية">Study Groups &amp; Sessions</a></li>
          <li><a href="{{ route('student.schedule') }}" class="sidebar-submenu-item {{ request()->routeIs('student.schedule') ? 'active' : '' }}" data-en="My Schedule" data-ar="جدولي الدراسي">My Schedule</a></li>
          <li><a href="{{ route('student.resources') }}" class="sidebar-submenu-item {{ request()->routeIs('student.resources') ? 'active' : '' }}" data-en="Resources" data-ar="الموارد التعليمية">Resources</a></li>
        </ul>

        <!-- Exams (not built yet — kept visible but clearly marked, never a silent dead link) -->
        <a href="#menuExams" class="sidebar-nav-item accordion-trigger"  aria-expanded="false" aria-controls="menuExams">
          <i class="bi bi-journal-check sidebar-nav-item-icon"></i>
          <span class="sidebar-nav-item-text" data-en="Exams & Assessments" data-ar="الامتحانات والتقييمات">Exams & Assessments</span>
          <i class="bi bi-chevron-down sidebar-nav-item-chevron"></i>
        </a>
        <ul class="sidebar-submenu-wrapper sidebar-submenu" id="menuExams">
          <li><a href="{{ route('student.exams.index') }}" class="sidebar-submenu-item {{ request()->routeIs('student.exams.*') ? 'active' : '' }}" data-en="Exams" data-ar="الامتحانات">Exams</a></li>
          <li><a href="{{ route('student.results.index') }}" class="sidebar-submenu-item {{ request()->routeIs('student.results.*') ? 'active' : '' }}" data-en="Results" data-ar="النتائج">Results</a></li>
          <li><span class="sidebar-submenu-item sidebar-item-disabled" data-en="Assessments" data-ar="التقييمات">Assessments</span> <span class="sidebar-soon-badge" data-en="Soon" data-ar="قريباً">Soon</span></li>
          <li><span class="sidebar-submenu-item sidebar-item-disabled" data-en="Certificates" data-ar="الشهادات">Certificates</span> <span class="sidebar-soon-badge" data-en="Soon" data-ar="قريباً">Soon</span></li>
        </ul>

        <!-- Attendance -->
        <a href="#menuAttendance" class="sidebar-nav-item accordion-trigger"  aria-expanded="false" aria-controls="menuAttendance">
          <i class="bi bi-calendar-check sidebar-nav-item-icon"></i>
          <span class="sidebar-nav-item-text" data-en="Attendance" data-ar="الحضور والغياب">Attendance</span>
          <i class="bi bi-chevron-down sidebar-nav-item-chevron"></i>
        </a>
        <ul class="sidebar-submenu-wrapper sidebar-submenu" id="menuAttendance">
          <li><a href="{{ route('student.attendance.index') }}" class="sidebar-submenu-item {{ request()->routeIs('student.attendance.*') ? 'active' : '' }}" data-en="My Attendance" data-ar="سجل الحضور">My Attendance</a></li>
        </ul>

        <!-- Financials -->
        <a href="#menuFinance" class="sidebar-nav-item accordion-trigger"  aria-expanded="false" aria-controls="menuFinance">
          <i class="bi bi-wallet2 sidebar-nav-item-icon"></i>
          <span class="sidebar-nav-item-text" data-en="Financials" data-ar="المالية">Financials</span>
          <i class="bi bi-chevron-down sidebar-nav-item-chevron"></i>
        </a>
        <ul class="sidebar-submenu-wrapper sidebar-submenu" id="menuFinance">
          <li><a href="{{ route('student.registrations') }}" class="sidebar-submenu-item {{ request()->routeIs('student.registrations') || request()->routeIs('student.registrations.show') ? 'active' : '' }}" data-en="My Registrations" data-ar="طلباتي">My Registrations</a></li>
          <li><a href="{{ route('student.checkout') }}" class="sidebar-submenu-item {{ request()->routeIs('student.checkout') ? 'active' : '' }}" data-en="Checkout" data-ar="الدفع">Checkout</a></li>
        </ul>

        <!-- Communication -->
        <a href="#menuComm" class="sidebar-nav-item accordion-trigger"  aria-expanded="false" aria-controls="menuComm">
          <i class="bi bi-chat-left-dots sidebar-nav-item-icon"></i>
          <span class="sidebar-nav-item-text" data-en="Communication" data-ar="التواصل">Communication</span>
          <i class="bi bi-chevron-down sidebar-nav-item-chevron"></i>
        </a>
        <ul class="sidebar-submenu-wrapper sidebar-submenu" id="menuComm">
          <li><span class="sidebar-submenu-item sidebar-item-disabled" data-en="Messages" data-ar="الرسائل">Messages</span> <span class="sidebar-soon-badge" data-en="Soon" data-ar="قريباً">Soon</span></li>
          <li><span class="sidebar-submenu-item sidebar-item-disabled" data-en="Announcements" data-ar="الإعلانات">Announcements</span> <span class="sidebar-soon-badge" data-en="Soon" data-ar="قريباً">Soon</span></li>
          <li><a href="{{ route('site.home') }}#contact" class="sidebar-submenu-item" data-en="Support" data-ar="الدعم الفني">Support</a></li>
        </ul>

        <!-- Profile -->
        <a href="#menuProfile" class="sidebar-nav-item accordion-trigger"  aria-expanded="false" aria-controls="menuProfile">
          <i class="bi bi-person sidebar-nav-item-icon"></i>
          <span class="sidebar-nav-item-text" data-en="My Account" data-ar="حسابي">My Account</span>
          <i class="bi bi-chevron-down sidebar-nav-item-chevron"></i>
        </a>
        <ul class="sidebar-submenu-wrapper sidebar-submenu" id="menuProfile">
          <li><a href="{{ route('student.profile') }}" class="sidebar-submenu-item" data-en="Profile & Security" data-ar="الملف الشخصي والأمان">Profile &amp; Security</a></li>
          <li>
            <form action="{{ route('student.logout') }}" method="POST">
              @csrf
              <button type="submit" class="sidebar-submenu-item text-danger w-100 text-start border-0 bg-transparent" data-en="Logout" data-ar="تسجيل الخروج">Logout</button>
            </form>
          </li>
        </ul>

        <!-- Cart -->
        <a href="{{ route('student.cart') }}" class="sidebar-nav-item {{ request()->routeIs('student.cart') ? 'active' : '' }}">
          <i class="bi bi-cart3 sidebar-nav-item-icon"></i>
          <span class="sidebar-nav-item-text" data-en="My Cart" data-ar="سلة التسجيل">My Cart</span>
        </a>
      </nav>

      <div class="mt-4 pt-4 border-t d-flex flex-column gap-2" style="border-color: var(--separator-color) !important;">
        <form action="{{ route('student.logout') }}" method="POST">
          @csrf
          <button type="submit" class="sidebar-nav-item text-danger w-100 border-0 bg-transparent text-start">
            <i class="bi bi-box-arrow-left"></i>
            <span data-en="Logout" data-ar="تسجيل الخروج">Logout</span>
          </button>
        </form>
      </div>
    </aside>
