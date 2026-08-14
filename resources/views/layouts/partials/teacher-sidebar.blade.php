
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
      @php
        $sidebarTeacher = auth('teacher')->user();
        $openAcademic = request()->routeIs('teacher.subjects.*', 'teacher.groups.*', 'teacher.schedule.*');
        $openStudents = request()->routeIs('teacher.students.*', 'teacher.grading.*');
        $openContent = request()->routeIs('teacher.content.*');
        $openExams = request()->routeIs('teacher.exams.*', 'teacher.grading.*');
        $openFinance = request()->routeIs('teacher.finance.*');
        $openComm = false;
        $openProfile = request()->routeIs('teacher.profile.*');
      @endphp
      <div class="sidebar-profile d-flex flex-column align-items-center mb-4 border-b pb-4" style="border-color: var(--separator-color) !important;">
        <div class="w-24 h-24 rounded-circle border-2 p-1 mb-3 position-relative overflow-hidden shadow-lg" style="border-color: var(--accent-color);">
          <img src="{{ $sidebarTeacher && $sidebarTeacher->photo ? asset('storage/'.$sidebarTeacher->photo) : asset('site/images/img/logo_backup.png') }}" alt="Teacher Profile" class="w-100 h-100 object-cover rounded-circle">
        </div>
        @if($sidebarTeacher)
          <span class="fw-bold fs-6 text-center" style="color: var(--text-primary);">{{ $sidebarTeacher->name }}</span>
        @endif
      </div>

      <nav class="sidebar-nav d-flex flex-column gap-2 w-100 px-3 pb-2" style="font-family: 'Tajawal', 'Almarai', sans-serif;">
        <!-- Overview -->
        <a href="{{ route('teacher.dashboard') }}" class="sidebar-nav-item {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }} rounded-3 mb-1">
          <i class="bi bi-grid-1x2-fill sidebar-nav-item-icon"></i>
          <span class="sidebar-nav-item-text fw-medium fs-6" data-en="Overview" data-ar="الرئيسية">Overview</span>
        </a>

        <!-- Academic Management -->
        <a href="#menuAcademic" class="sidebar-nav-item accordion-trigger" aria-expanded="{{ $openAcademic ? 'true' : 'false' }}" aria-controls="menuAcademic">
          <i class="bi bi-journal-bookmark sidebar-nav-item-icon"></i>
          <span class="sidebar-nav-item-text" data-en="Academic Mgmt" data-ar="الإدارة الأكاديمية">Academic Mgmt</span>
          <i class="bi bi-chevron-down sidebar-nav-item-chevron"></i>
        </a>
        <ul class="sidebar-submenu-wrapper sidebar-submenu {{ $openAcademic ? 'expanded' : '' }}" id="menuAcademic">
          <li><a href="{{ route('teacher.subjects.index') }}" class="sidebar-submenu-item" data-en="Programs" data-ar="البرامج الدراسية">Programs</a></li>
          <li><a href="{{ route('teacher.subjects.index') }}" class="sidebar-submenu-item" data-en="Courses" data-ar="المواد التعليمية">Courses</a></li>
          <li><a href="{{ route('teacher.groups.index') }}" class="sidebar-submenu-item" data-en="Study Groups" data-ar="المجموعات الدراسية">Study Groups</a></li>
          <li><a href="{{ route('teacher.schedule.index') }}" class="sidebar-submenu-item" data-en="Academic Schedule" data-ar="الجدول الأكاديمي">Academic Schedule</a></li>
        </ul>

        <!-- Students -->
        <a href="#menuStudents" class="sidebar-nav-item accordion-trigger" aria-expanded="{{ $openStudents ? 'true' : 'false' }}" aria-controls="menuStudents">
          <i class="bi bi-people sidebar-nav-item-icon"></i>
          <span class="sidebar-nav-item-text" data-en="Students" data-ar="الطلاب">Students</span>
          <i class="bi bi-chevron-down sidebar-nav-item-chevron"></i>
        </a>
        <ul class="sidebar-submenu-wrapper sidebar-submenu {{ $openStudents ? 'expanded' : '' }}" id="menuStudents">
          <li><a href="{{ route('teacher.students.index') }}" class="sidebar-submenu-item" data-en="All Students" data-ar="جميع الطلاب">All Students</a></li>
          <li><a href="{{ route('teacher.students.index') }}" class="sidebar-submenu-item" data-en="Course Students" data-ar="طلاب المادة">Course Students</a></li>
          <li><a href="{{ route('teacher.groups.index') }}" class="sidebar-submenu-item" data-en="Attendance" data-ar="الحضور والغياب">Attendance</a></li>
          <li><a href="{{ route('teacher.grading.index') }}" class="sidebar-submenu-item" data-en="Performance" data-ar="الأداء الأكاديمي">Performance</a></li>
          <li><a href="{{ route('teacher.groups.index') }}" class="sidebar-submenu-item" data-en="Academic Notes" data-ar="الملاحظات الأكاديمية">Academic Notes</a></li>
        </ul>

        <!-- Educational Content -->
        <a href="#menuContent" class="sidebar-nav-item accordion-trigger" aria-expanded="{{ $openContent ? 'true' : 'false' }}" aria-controls="menuContent">
          <i class="bi bi-collection-play sidebar-nav-item-icon"></i>
          <span class="sidebar-nav-item-text" data-en="Content" data-ar="المحتوى التعليمي">Content</span>
          <i class="bi bi-chevron-down sidebar-nav-item-chevron"></i>
        </a>
        <ul class="sidebar-submenu-wrapper sidebar-submenu {{ $openContent ? 'expanded' : '' }}" id="menuContent">
          <li><a href="{{ route('teacher.content.hub') }}" class="sidebar-submenu-item" data-en="Content Hub" data-ar="مركز المحتوى">Content Hub</a></li>
          <li><a href="{{ route('teacher.content.index') }}" class="sidebar-submenu-item" data-en="Files" data-ar="الملفات التعليمية">Files</a></li>
          <li><a href="{{ route('teacher.content.index') }}" class="sidebar-submenu-item" data-en="Videos" data-ar="الفيديوهات">Videos</a></li>
          <li><a href="#" class="sidebar-submenu-item" data-en="Recordings" data-ar="التسجيلات">Recordings</a></li>
          <li><a href="#" class="sidebar-submenu-item" data-en="Assignments" data-ar="الواجبات">Assignments</a></li>
          <li><a href="{{ route('teacher.content.index') }}" class="sidebar-submenu-item" data-en="Resources" data-ar="الموارد التعليمية">Resources</a></li>
        </ul>

        <!-- Sessions -->
        <a href="#menuSessions" class="sidebar-nav-item accordion-trigger" aria-expanded="{{ $openSessions ? 'true' : 'false' }}" aria-controls="menuSessions">
          <i class="bi bi-camera-video sidebar-nav-item-icon"></i>
          <span class="sidebar-nav-item-text" data-en="Sessions" data-ar="الجلسات والمحاضرات">Sessions</span>
          <i class="bi bi-chevron-down sidebar-nav-item-chevron"></i>
        </a>
        <ul class="sidebar-submenu-wrapper sidebar-submenu {{ $openSessions ? 'expanded' : '' }}" id="menuSessions">
          <li><a href="#" class="sidebar-submenu-item" data-en="Upcoming" data-ar="الجلسات القادمة">Upcoming</a></li>
          <li><a href="#" class="sidebar-submenu-item" data-en="Live Sessions" data-ar="الجلسات المباشرة">Live Sessions</a></li>
          <li><a href="#" class="sidebar-submenu-item" data-en="Create Session" data-ar="إنشاء جلسة جديدة">Create Session</a></li>
          <li><a href="#" class="sidebar-submenu-item" data-en="Archive" data-ar="أرشيف الجلسات">Archive</a></li>
        </ul>

        <!-- Exams & Assessments -->
        <a href="#menuExams" class="sidebar-nav-item accordion-trigger" aria-expanded="{{ $openExams ? 'true' : 'false' }}" aria-controls="menuExams">
          <i class="bi bi-pencil-square sidebar-nav-item-icon"></i>
          <span class="sidebar-nav-item-text" data-en="Exams" data-ar="الامتحانات والتقييمات">Exams</span>
          <i class="bi bi-chevron-down sidebar-nav-item-chevron"></i>
        </a>
        <ul class="sidebar-submenu-wrapper sidebar-submenu {{ $openExams ? 'expanded' : '' }}" id="menuExams">
          <li><a href="{{ route('teacher.exams.index') }}" class="sidebar-submenu-item" data-en="Tests Management" data-ar="الامتحانات">Tests Management</a></li>
          <li><a href="{{ route('teacher.grading.index') }}" class="sidebar-submenu-item" data-en="Grading" data-ar="التصحيح">Grading</a></li>
          <li><a href="{{ route('teacher.grading.index') }}" class="sidebar-submenu-item" data-en="Scores" data-ar="الدرجات">Scores</a></li>
          <li><a href="#" class="sidebar-submenu-item" data-en="Surveys" data-ar="الاستبيانات والتقييمات">Surveys</a></li>
        </ul>

        <!-- Finance -->
        <a href="#menuFinance" class="sidebar-nav-item accordion-trigger" aria-expanded="{{ $openFinance ? 'true' : 'false' }}" aria-controls="menuFinance">
          <i class="bi bi-wallet2 sidebar-nav-item-icon"></i>
          <span class="sidebar-nav-item-text" data-en="Finance" data-ar="المالية">Finance</span>
          <i class="bi bi-chevron-down sidebar-nav-item-chevron"></i>
        </a>
        <ul class="sidebar-submenu-wrapper sidebar-submenu {{ $openFinance ? 'expanded' : '' }}" id="menuFinance">
          <li><a href="{{ route('teacher.finance.index') }}" class="sidebar-submenu-item {{ request()->routeIs('teacher.finance.index') ? 'active' : '' }}" data-en="Overview" data-ar="نظرة عامة">Overview</a></li>
          <li><a href="{{ route('teacher.finance.groups') }}" class="sidebar-submenu-item {{ request()->routeIs('teacher.finance.groups') || request()->routeIs('teacher.finance.group') ? 'active' : '' }}" data-en="By Group" data-ar="حسب المجموعة">By Group</a></li>
          <li><a href="{{ route('teacher.finance.students') }}" class="sidebar-submenu-item {{ request()->routeIs('teacher.finance.students') || request()->routeIs('teacher.finance.registration') ? 'active' : '' }}" data-en="By Student" data-ar="حسب الطالب">By Student</a></li>
          <li><a href="{{ route('teacher.finance.students', ['filter' => 'outstanding']) }}" class="sidebar-submenu-item" data-en="Outstanding" data-ar="المبالغ المتبقية">Outstanding</a></li>
          <li><a href="{{ route('teacher.finance.payments') }}" class="sidebar-submenu-item {{ request()->routeIs('teacher.finance.payments') ? 'active' : '' }}" data-en="Payments" data-ar="سجل المدفوعات">Payments</a></li>
        </ul>

        <!-- Reports -->
        <a href="#" class="sidebar-nav-item rounded-3 mb-1">
          <i class="bi bi-bar-chart sidebar-nav-item-icon"></i>
          <span class="sidebar-nav-item-text fw-medium fs-6" data-en="Reports" data-ar="التقارير">Reports</span>
        </a>

        <!-- Communication -->
        <a href="#menuComm" class="sidebar-nav-item accordion-trigger rounded-3 mb-1" aria-expanded="{{ $openComm ? 'true' : 'false' }}" aria-controls="menuComm">
          <i class="bi bi-chat-left-dots sidebar-nav-item-icon"></i>
          <span class="sidebar-nav-item-text fw-medium fs-6" data-en="Communication" data-ar="التواصل">Communication</span>
          <i class="bi bi-chevron-down sidebar-nav-item-chevron"></i>
        </a>
        <ul class="sidebar-submenu-wrapper sidebar-submenu {{ $openComm ? 'expanded' : '' }}" id="menuComm">
          <li><a href="#" class="sidebar-submenu-item rounded-3" data-en="Messages" data-ar="الرسائل">Messages</a></li>
          <li><a href="#" class="sidebar-submenu-item rounded-3" data-en="Announcements" data-ar="الإعلانات">Announcements</a></li>
          <li><a href="#" class="sidebar-submenu-item rounded-3" data-en="Mass Notification" data-ar="إرسال إشعار جماعي">Mass Notification</a></li>
        </ul>

        <!-- Profile -->
        <a href="#menuProfile" class="sidebar-nav-item accordion-trigger rounded-3 mb-1" aria-expanded="{{ $openProfile ? 'true' : 'false' }}" aria-controls="menuProfile">
          <i class="bi bi-person sidebar-nav-item-icon"></i>
          <span class="sidebar-nav-item-text fw-medium fs-6" data-en="My Account" data-ar="حسابي">My Account</span>
          <i class="bi bi-chevron-down sidebar-nav-item-chevron"></i>
        </a>
        <ul class="sidebar-submenu-wrapper sidebar-submenu {{ $openProfile ? 'expanded' : '' }}" id="menuProfile">
          <li><a href="{{ route('teacher.profile.edit') }}" class="sidebar-submenu-item rounded-3" data-en="Profile" data-ar="الملف الشخصي">Profile</a></li>
          <li><a href="#" class="sidebar-submenu-item rounded-3" data-en="Resume & Certs" data-ar="الشهادات والخبرات">Resume & Certs</a></li>
          <li><a href="#" class="sidebar-submenu-item rounded-3" data-en="Settings" data-ar="إعدادات الحساب">Settings</a></li>
        </ul>
      </nav>

      <div class="sidebar-footer mt-3 pt-3 border-t d-flex flex-column gap-2 px-3" style="border-color: var(--separator-color) !important;">
        <a href="#" class="sidebar-nav-item rounded-3">
          <i class="bi bi-gear sidebar-nav-item-icon"></i>
          <span class="sidebar-nav-item-text fw-medium fs-6" data-en="Settings" data-ar="الإعدادات">Settings</span>
        </a>

        <form method="POST" action="{{ route('teacher.logout') }}">
          @csrf
          <button type="submit" class="btn btn-glass w-100 d-flex justify-content-center align-items-center gap-2 text-danger fw-bold rounded-pill mt-2">
            <i class="bi bi-box-arrow-left rtl:rotate-180"></i>
            <span data-en="Logout" data-ar="تسجيل الخروج">Logout</span>
          </button>
        </form>
      </div>
    </aside>
