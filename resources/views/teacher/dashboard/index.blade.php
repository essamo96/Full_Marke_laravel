@extends('layouts.teacher')

@section('title', 'Teacher Dashboard | FULL MARK ACADEMY')

@section('content')
        
        <!-- Welcome Banner -->
        <section class="glass-panel bg-pattern-gold rounded-4 p-5 mb-5 position-relative overflow-hidden d-flex flex-column flex-md-row align-items-center justify-content-between gap-5 border-1 border-white/10" style="box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
          <div class="position-absolute top-0 end-0 w-50 h-100 bg-gold/10 blur-[80px]"></div>
          <div class="position-relative z-1 text-center text-md-start w-100">
            <h1 class="display-5 fw-bold mb-3" style="color: var(--text-off-white, #fdfbf7); font-family: 'Tajawal', 'Almarai', sans-serif;">
              <span data-en="Hello," data-ar="مرحباً،">Hello,</span> <span style="background: var(--accent-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Dr. Sarah</span>
            </h1>
            <p class="fs-5 opacity-75 mb-0" data-en="You have 3 assignments waiting for review and 2 upcoming sessions today." data-ar="لديك 3 واجبات بانتظار التقييم وجلستين مباشرتين مبرمجتين لليوم.">
              You have 3 assignments waiting for review and 2 upcoming sessions today.
            </p>
          </div>
          <div class="position-relative z-1 d-flex flex-wrap gap-3 mt-4 mt-md-0 ms-md-auto align-items-center justify-content-center">
             <button class="btn btn-luxury px-4 py-3 d-flex align-items-center gap-2 fw-bold"><i class="bi bi-file-earmark-check fs-5"></i> <span data-en="Grade Now" data-ar="قيّم الآن">Grade Now</span></button>
          </div>
        </section>

        <!-- Stats Grid -->
        <section class="row g-4 g-lg-5 mb-5">
          <!-- Stat 1 -->
          <div class="col-md-6 col-xl-3">
            <div class="glass-panel rounded-4 p-4 p-xl-5 h-100 tilt-card glow-card transition-all hover-glow" style="border: 1px solid var(--separator-color);">
              <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="p-3 rounded-circle shadow-sm" style="background: rgba(197, 168, 128, 0.1); color: var(--accent-color);">
                  <i class="bi bi-people fs-4"></i>
                </div>
                <span class="badge bg-success bg-opacity-25 text-success rounded-pill px-3 py-2">+15 <i class="bi bi-arrow-up"></i></span>
              </div>
              <p class="mb-2 text-sm opacity-75 fw-medium" data-en="Total Students" data-ar="إجمالي الطلاب">Total Students</p>
              <h3 class="fw-bold mb-0 display-6" style="color: var(--text-off-white, #fdfbf7);">240</h3>
            </div>
          </div>
          <!-- Stat 2 -->
          <div class="col-md-6 col-xl-3">
            <div class="glass-panel rounded-4 p-4 p-xl-5 h-100 tilt-card glow-card transition-all hover-glow" style="border: 1px solid var(--separator-color);">
              <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="p-3 rounded-circle shadow-sm" style="background: rgba(197, 168, 128, 0.1); color: var(--accent-color);">
                  <i class="bi bi-camera-video fs-4"></i>
                </div>
              </div>
              <p class="mb-2 text-sm opacity-75 fw-medium" data-en="Active Classes" data-ar="الصفوف النشطة">Active Classes</p>
              <h3 class="fw-bold mb-0 display-6" style="color: var(--text-off-white, #fdfbf7);">8</h3>
            </div>
          </div>
          <!-- Stat 3 -->
          <div class="col-md-6 col-xl-3">
            <div class="glass-panel rounded-4 p-4 p-xl-5 h-100 tilt-card glow-card transition-all hover-glow" style="border: 1px solid var(--separator-color);">
              <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="p-3 rounded-circle shadow-sm" style="background: rgba(255, 71, 87, 0.1); color: #ff4757;">
                  <i class="bi bi-check2-square fs-4"></i>
                </div>
              </div>
              <p class="mb-2 text-sm opacity-75 fw-medium" data-en="Pending Reviews" data-ar="مراجعات معلقة">Pending Reviews</p>
              <h3 class="fw-bold mb-0 display-6" style="color: var(--text-off-white, #fdfbf7);">12</h3>
            </div>
          </div>
          <!-- Stat 4 -->
          <div class="col-md-6 col-xl-3">
            <div class="glass-panel rounded-4 p-4 p-xl-5 h-100 tilt-card glow-card transition-all hover-glow" style="border: 1px solid var(--separator-color);">
              <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="p-3 rounded-circle shadow-sm" style="background: rgba(197, 168, 128, 0.1); color: var(--accent-color);">
                  <i class="bi bi-star-fill fs-4"></i>
                </div>
                <span class="text-sm fw-bold px-3 py-2 rounded-pill" style="background: rgba(197,168,128,0.1); color: var(--accent-color);">/ 5.0</span>
              </div>
              <p class="mb-2 text-sm opacity-75 fw-medium" data-en="Average Rating" data-ar="متوسط التقييم">Average Rating</p>
              <h3 class="fw-bold mb-0 display-6" style="color: var(--text-off-white, #fdfbf7);">4.9</h3>
            </div>
          </div>
        </section>

        <div class="row g-4 g-lg-5">
          <!-- Upcoming Sessions -->
          <div class="col-xl-8">
            <div class="d-flex align-items-center justify-content-between mb-4">
              <h3 class="h4 fw-bold mb-0 border-start border-4 ps-3" style="border-color: var(--accent-color) !important; font-family: 'Tajawal', 'Almarai', sans-serif;" data-en="Today's Sessions" data-ar="جلسات اليوم">Today's Sessions</h3>
              <a href="#" class="text-decoration-none text-sm fw-medium d-flex align-items-center gap-1" style="color: var(--accent-color);">
                <span data-en="Schedule" data-ar="الجدول">Schedule</span>
                <i class="bi bi-arrow-right rtl:rotate-180"></i>
              </a>
            </div>
            
            <div class="d-flex flex-column gap-4">
              <!-- Session Item -->
              <div class="glass-panel rounded-4 p-4 p-md-5 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-4 transition-all hover-glow border-start border-4" style="border-left-color: #ff4757 !important;">
                <div class="d-flex align-items-center gap-4">
                  <div class="rounded-circle p-3 d-flex align-items-center justify-content-center shadow-sm" style="background: rgba(255,71,87,0.1); width: 64px; height: 64px;">
                    <i class="bi bi-camera-video fs-3" style="color: #ff4757;"></i>
                  </div>
                  <div>
                    <h5 class="mb-2 fs-5 fw-bold" style="color: var(--text-off-white, #fdfbf7);" data-en="IELTS Preparation Group A" data-ar="مجموعة تحضير الآيلتس (أ)">IELTS Preparation Group A</h5>
                    <span class="text-sm opacity-75 fw-medium"><i class="bi bi-clock me-2 text-gold"></i> 10:00 AM - 11:30 AM</span>
                  </div>
                </div>
                <button class="btn btn-luxury px-5 py-3 align-self-start align-self-sm-center fw-bold rounded-pill" data-en="Start Broadcast" data-ar="بدء البث">Start Broadcast</button>
              </div>

              <!-- Session Item -->
              <div class="glass-panel rounded-4 p-4 p-md-5 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-4 transition-all hover-glow border-start border-4" style="border-left-color: var(--accent-color) !important;">
                <div class="d-flex align-items-center gap-4">
                  <div class="rounded-circle p-3 d-flex align-items-center justify-content-center shadow-sm" style="background: rgba(197, 168, 128, 0.1); width: 64px; height: 64px;">
                    <i class="bi bi-mic fs-3" style="color: var(--accent-color);"></i>
                  </div>
                  <div>
                    <h5 class="mb-2 fs-5 fw-bold" style="color: var(--text-off-white, #fdfbf7);" data-en="Academic Speaking Masterclass" data-ar="كورس المحادثة الأكاديمية">Academic Speaking Masterclass</h5>
                    <span class="text-sm opacity-75 fw-medium"><i class="bi bi-clock me-2 text-gold"></i> 02:00 PM - 03:30 PM</span>
                  </div>
                </div>
                <button class="btn btn-glass px-5 py-3 align-self-start align-self-sm-center fw-bold rounded-pill" data-en="Prepare Room" data-ar="تجهيز القاعة">Prepare Room</button>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="col-xl-4">
            <h3 class="h4 fw-bold mb-4 border-start border-4 ps-3" style="border-color: var(--accent-color) !important; font-family: 'Tajawal', 'Almarai', sans-serif;" data-en="Quick Actions" data-ar="إجراءات سريعة">Quick Actions</h3>
            
            <div class="d-flex flex-column gap-4">
              <button class="btn glass-panel text-start p-4 w-100 d-flex align-items-center gap-4 glow-hover tilt-card rounded-4 border-1" style="border-color: var(--separator-color);">
                <div class="rounded-circle p-3 shadow-sm d-flex justify-content-center align-items-center" style="background: rgba(197, 168, 128, 0.1); color: var(--accent-color); width: 56px; height: 56px;">
                  <i class="bi bi-plus-lg fs-4"></i>
                </div>
                <div>
                  <span class="fw-bold d-block mb-1 fs-5" style="color: var(--text-off-white, #fdfbf7);" data-en="Create New Course" data-ar="إنشاء مساق جديد">Create New Course</span>
                  <span class="text-sm opacity-75" data-en="Draft a new syllabus" data-ar="إعداد خطة دراسية جديدة">Draft a new syllabus</span>
                </div>
              </button>

              <button class="btn glass-panel text-start p-4 w-100 d-flex align-items-center gap-4 glow-hover tilt-card rounded-4 border-1" style="border-color: var(--separator-color);">
                <div class="rounded-circle p-3 shadow-sm d-flex justify-content-center align-items-center" style="background: rgba(197, 168, 128, 0.1); color: var(--accent-color); width: 56px; height: 56px;">
                  <i class="bi bi-cloud-upload fs-4"></i>
                </div>
                <div>
                  <span class="fw-bold d-block mb-1 fs-5" style="color: var(--text-off-white, #fdfbf7);" data-en="Upload Material" data-ar="رفع ملفات/مواد">Upload Material</span>
                  <span class="text-sm opacity-75" data-en="Share PDFs or Videos" data-ar="مشاركة الملفات ومقاطع الفيديو">Share PDFs or Videos</span>
                </div>
              </button>

              <button class="btn glass-panel text-start p-4 w-100 d-flex align-items-center gap-4 glow-hover tilt-card rounded-4 border-1" style="border-color: var(--separator-color);">
                <div class="rounded-circle p-3 shadow-sm d-flex justify-content-center align-items-center" style="background: rgba(197, 168, 128, 0.1); color: var(--accent-color); width: 56px; height: 56px;">
                  <i class="bi bi-megaphone fs-4"></i>
                </div>
                <div>
                  <span class="fw-bold d-block mb-1 fs-5" style="color: var(--text-off-white, #fdfbf7);" data-en="Post Announcement" data-ar="نشر تعميم">Post Announcement</span>
                  <span class="text-sm opacity-75" data-en="Notify all students" data-ar="تنبيه لكافة الطلاب">Notify all students</span>
                </div>
              </button>
            </div>
          </div>
        </div>

@endsection

@push('styles')
<style>
/* Enhanced Hover states for Pro Max Aesthetic */
.hover-glow:hover {
    box-shadow: 0 15px 35px rgba(197, 168, 128, 0.15) !important;
    border-color: var(--accent-color) !important;
    transform: translateY(-5px);
}
.btn.glass-panel:hover {
    box-shadow: 0 10px 30px rgba(197, 168, 128, 0.1) !important;
    border-color: var(--accent-color) !important;
}
</style>
@endpush
