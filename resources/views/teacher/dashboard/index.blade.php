@extends('layouts.teacher')

@section('title', 'Teacher Dashboard | FULL MARK ACADEMY')

@section('content')
        
        <!-- Welcome Banner -->
        <section class="glass-panel rounded-4 p-4 p-md-5 mb-4 position-relative overflow-hidden d-flex flex-column flex-md-row align-items-center justify-content-between gap-4">
          <div class="position-absolute top-0 end-0 w-50 h-100 bg-gold/10 blur-[80px]"></div>
          <div class="position-relative z-1 text-center text-md-start w-100">
            <h1 class="display-6 fw-bold mb-2" style="color: var(--text-primary);">
              <span data-en="Hello," data-ar="مرحباً،">Hello,</span> <span style="background: var(--accent-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Dr. Sarah</span>
            </h1>
            <p class="fs-5 opacity-75 mb-0" data-en="You have 3 assignments waiting for review and 2 upcoming sessions today." data-ar="لديك 3 واجبات بانتظار التقييم وجلستين مباشرتين مبرمجتين لليوم.">
              You have 3 assignments waiting for review and 2 upcoming sessions today.
            </p>
          </div>
          <div class="position-relative z-1 d-flex flex-wrap gap-3 mt-3 mt-md-0 ms-md-auto align-items-center justify-content-center">
             <button class="btn btn-luxury px-4 py-2 d-flex align-items-center gap-2"><i class="bi bi-file-earmark-check"></i> <span data-en="Grade Now" data-ar="قيّم الآن">Grade Now</span></button>
          </div>
        </section>

        <!-- Stats Grid -->
        <section class="row g-4 mb-4">
          <!-- Stat 1 -->
          <div class="col-md-6 col-lg-3">
            <div class="glass-panel rounded-4 p-4 h-100 tilt-card glow-card">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="p-2 rounded" style="background: rgba(197, 168, 128, 0.1); color: var(--accent-color);">
                  <i class="bi bi-people fs-4"></i>
                </div>
                <span class="badge bg-success bg-opacity-25 text-success">+15 <i class="bi bi-arrow-up"></i></span>
              </div>
              <p class="mb-1 text-sm opacity-75" data-en="Total Students" data-ar="إجمالي الطلاب">Total Students</p>
              <h3 class="fw-bold mb-0" style="color: var(--text-primary);">240</h3>
            </div>
          </div>
          <!-- Stat 2 -->
          <div class="col-md-6 col-lg-3">
            <div class="glass-panel rounded-4 p-4 h-100 tilt-card glow-card">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="p-2 rounded" style="background: rgba(197, 168, 128, 0.1); color: var(--accent-color);">
                  <i class="bi bi-camera-video fs-4"></i>
                </div>
              </div>
              <p class="mb-1 text-sm opacity-75" data-en="Active Classes" data-ar="الصفوف النشطة">Active Classes</p>
              <h3 class="fw-bold mb-0" style="color: var(--text-primary);">8</h3>
            </div>
          </div>
          <!-- Stat 3 -->
          <div class="col-md-6 col-lg-3">
            <div class="glass-panel rounded-4 p-4 h-100 tilt-card glow-card">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="p-2 rounded" style="background: rgba(197, 168, 128, 0.1); color: #ff4757;">
                  <i class="bi bi-check2-square fs-4"></i>
                </div>
              </div>
              <p class="mb-1 text-sm opacity-75" data-en="Pending Reviews" data-ar="مراجعات معلقة">Pending Reviews</p>
              <h3 class="fw-bold mb-0" style="color: var(--text-primary);">12</h3>
            </div>
          </div>
          <!-- Stat 4 -->
          <div class="col-md-6 col-lg-3">
            <div class="glass-panel rounded-4 p-4 h-100 tilt-card glow-card">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="p-2 rounded" style="background: rgba(197, 168, 128, 0.1); color: var(--accent-color);">
                  <i class="bi bi-star-fill fs-4"></i>
                </div>
                <span class="text-sm" style="color: var(--accent-color);">/ 5.0</span>
              </div>
              <p class="mb-1 text-sm opacity-75" data-en="Average Rating" data-ar="متوسط التقييم">Average Rating</p>
              <h3 class="fw-bold mb-0" style="color: var(--text-primary);">4.9</h3>
            </div>
          </div>
        </section>

        <div class="row g-4">
          <!-- Upcoming Sessions -->
          <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-4">
              <h3 class="h4 fw-bold mb-0 border-start border-4 ps-3" style="border-color: var(--accent-color) !important;" data-en="Today's Sessions" data-ar="جلسات اليوم">Today's Sessions</h3>
              <a href="#" class="text-decoration-none text-sm fw-medium d-flex align-items-center gap-1" style="color: var(--accent-color);">
                <span data-en="Schedule" data-ar="الجدول">Schedule</span>
                <i class="bi bi-arrow-right rtl:rotate-180"></i>
              </a>
            </div>
            
            <div class="d-flex flex-column gap-3">
              <!-- Session Item -->
              <div class="glass-panel rounded-3 p-4 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 transition-all hover:bg-white/5 border-start border-4" style="border-left-color: #ff4757 !important;">
                <div class="d-flex align-items-center gap-3">
                  <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="background: rgba(255,71,87,0.1); width: 48px; height: 48px;">
                    <i class="bi bi-camera-video fs-4" style="color: #ff4757;"></i>
                  </div>
                  <div>
                    <h5 class="mb-1 fs-6 fw-bold" style="color: var(--text-primary);" data-en="IELTS Preparation Group A" data-ar="مجموعة تحضير الآيلتس (أ)">IELTS Preparation Group A</h5>
                    <span class="text-sm opacity-75"><i class="bi bi-clock me-1"></i> 10:00 AM - 11:30 AM</span>
                  </div>
                </div>
                <button class="btn btn-luxury px-4 py-2 align-self-start align-self-sm-center" data-en="Start Broadcast" data-ar="بدء البث">Start Broadcast</button>
              </div>

              <!-- Session Item -->
              <div class="glass-panel rounded-3 p-4 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 transition-all hover:bg-white/5 border-start border-4" style="border-left-color: var(--accent-color) !important;">
                <div class="d-flex align-items-center gap-3">
                  <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="background: rgba(197, 168, 128, 0.1); width: 48px; height: 48px;">
                    <i class="bi bi-mic fs-4" style="color: var(--accent-color);"></i>
                  </div>
                  <div>
                    <h5 class="mb-1 fs-6 fw-bold" style="color: var(--text-primary);" data-en="Academic Speaking Masterclass" data-ar="كورس المحادثة الأكاديمية">Academic Speaking Masterclass</h5>
                    <span class="text-sm opacity-75"><i class="bi bi-clock me-1"></i> 02:00 PM - 03:30 PM</span>
                  </div>
                </div>
                <button class="btn btn-glass px-4 py-2 align-self-start align-self-sm-center" data-en="Prepare Room" data-ar="تجهيز القاعة">Prepare Room</button>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="col-lg-4">
            <h3 class="h4 fw-bold mb-4 border-start border-4 ps-3" style="border-color: var(--accent-color) !important;" data-en="Quick Actions" data-ar="إجراءات سريعة">Quick Actions</h3>
            
            <div class="d-flex flex-column gap-3">
              <button class="btn glass-panel text-start p-3 w-100 d-flex align-items-center gap-3 glow-hover tilt-card">
                <div class="rounded p-2" style="background: rgba(197, 168, 128, 0.1); color: var(--accent-color);">
                  <i class="bi bi-plus-lg fs-5"></i>
                </div>
                <div>
                  <span class="fw-bold d-block" style="color: var(--text-primary);" data-en="Create New Course" data-ar="إنشاء مساق جديد">Create New Course</span>
                  <span class="text-xs opacity-75" data-en="Draft a new syllabus" data-ar="إعداد خطة دراسية جديدة">Draft a new syllabus</span>
                </div>
              </button>

              <button class="btn glass-panel text-start p-3 w-100 d-flex align-items-center gap-3 glow-hover tilt-card">
                <div class="rounded p-2" style="background: rgba(197, 168, 128, 0.1); color: var(--accent-color);">
                  <i class="bi bi-cloud-upload fs-5"></i>
                </div>
                <div>
                  <span class="fw-bold d-block" style="color: var(--text-primary);" data-en="Upload Material" data-ar="رفع ملفات/مواد">Upload Material</span>
                  <span class="text-xs opacity-75" data-en="Share PDFs or Videos" data-ar="مشاركة الملفات ومقاطع الفيديو">Share PDFs or Videos</span>
                </div>
              </button>

              <button class="btn glass-panel text-start p-3 w-100 d-flex align-items-center gap-3 glow-hover tilt-card">
                <div class="rounded p-2" style="background: rgba(197, 168, 128, 0.1); color: var(--accent-color);">
                  <i class="bi bi-megaphone fs-5"></i>
                </div>
                <div>
                  <span class="fw-bold d-block" style="color: var(--text-primary);" data-en="Post Announcement" data-ar="نشر تعميم">Post Announcement</span>
                  <span class="text-xs opacity-75" data-en="Notify all students" data-ar="تنبيه لكافة الطلاب">Notify all students</span>
                </div>
              </button>
            </div>
          </div>
        </div>

@endsection
