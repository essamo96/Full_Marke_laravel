@extends('layouts.teacher')

@section('title', 'Teacher Dashboard | FULL MARK ACADEMY')

@section('content')

        <!-- Teacher Profile Card -->
        <section class="fade-in-up glass-panel rounded-4 p-4 p-md-5 mb-5 position-relative overflow-hidden d-flex flex-column flex-md-row align-items-center gap-5 border-1 border-white/10" style="background: linear-gradient(135deg, var(--bg-tertiary) 0%, rgba(197, 168, 128, 0.15) 100%); box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
          <div class="position-absolute top-0 end-0 w-50 h-100 blur-[80px] floating-orb" style="background: rgba(197, 168, 128, 0.2);"></div>

          <!-- Profile Image -->
          <div class="position-relative z-1 flex-shrink-0">
            <div class="rounded-circle p-1" style="background: linear-gradient(135deg, var(--accent-color), transparent); width: 120px; height: 120px;">
              @if($teacher->photo)
                <img src="{{ Storage::url($teacher->photo) }}" alt="{{ $teacher->name }}" class="rounded-circle w-100 h-100 object-fit-cover shadow-sm">
              @else
                <div class="rounded-circle w-100 h-100 d-flex align-items-center justify-content-center bg-dark text-white fs-1 fw-bold shadow-sm">
                  {{ mb_substr($teacher->name, 0, 1) }}
                </div>
              @endif
            </div>
          </div>

          <!-- Teacher Details -->
          <div class="position-relative z-1 flex-grow-1" style="text-align: {{ app()->getLocale() == 'ar' ? 'right' : 'left' }};">
            <h1 class="display-6 fw-bold mb-2" style="color: var(--text-primary); font-family: 'Tajawal', 'Almarai', sans-serif;">
              <span data-en="Welcome," data-ar="مرحباً،">Welcome,</span> <span style="background: var(--accent-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">{{ $teacher->name }}</span>
            </h1>
            <div class="d-flex flex-column flex-sm-row align-items-center justify-content-start gap-3 mt-3">
              <span class="fs-6 opacity-75 fw-medium d-flex align-items-center gap-2">
                <i class="bi bi-envelope text-gold fs-5"></i> {{ $teacher->email ?? '-' }}
              </span>
              <span class="fs-6 opacity-75 fw-medium d-flex align-items-center gap-2">
                <i class="bi bi-telephone text-gold fs-5"></i> <bdi>{{ $teacher->phone ?? '-' }}</bdi>
              </span>
            </div>
          </div>

          <div class="position-relative z-1 d-flex flex-wrap gap-3 mt-4 mt-md-0 align-items-center justify-content-center">
             <a href="{{ route('teacher.profile.edit') }}" class="btn btn-luxury px-4 py-3 d-flex align-items-center gap-2 fw-bold">
               <i class="bi bi-person-gear fs-5"></i> <span data-en="Edit Profile" data-ar="تعديل الملف">Edit Profile</span>
             </a>
          </div>
        </section>

        <!-- Stats Grid -->
        <section class="row g-4 g-lg-5 mb-5 fade-in-up delay-1">
          <!-- Stat 1 -->
          <div class="col-md-6 col-xl-3">
            <div class="glass-panel rounded-4 p-4 p-xl-5 h-100 tilt-card glow-card transition-all hover-glow" style="border: 1px solid var(--separator-color);">
              <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="p-3 rounded-circle shadow-sm" style="background: rgba(197, 168, 128, 0.1); color: var(--accent-color);">
                  <i class="bi bi-people fs-4"></i>
                </div>
              </div>
              <p class="mb-2 text-sm opacity-75 fw-medium" data-en="Total Students" data-ar="إجمالي الطلاب">Total Students</p>
              <h3 class="fw-bold mb-0 display-6" style="color: var(--text-primary);">{{ $totalStudents }}</h3>
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
              <h3 class="fw-bold mb-0 display-6" style="color: var(--text-primary);">{{ $activeClassesCount }}</h3>
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
              <h3 class="fw-bold mb-0 display-6" style="color: var(--text-primary);">{{ $pendingReviewsCount }}</h3>
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
              <h3 class="fw-bold mb-0 display-6" style="color: var(--text-primary);">{{ number_format($averageRating, 1) }}</h3>
            </div>
          </div>
        </section>

        <div class="row g-4 g-lg-5">
          <!-- Upcoming Sessions -->
          <div class="col-xl-8 fade-in-up delay-2">
            <div class="d-flex align-items-center justify-content-between mb-4">
              <h3 class="h4 fw-bold mb-0 border-start border-4 ps-3" style="border-color: var(--accent-color) !important; font-family: 'Tajawal', 'Almarai', sans-serif;" data-en="Today's Sessions" data-ar="جلسات اليوم">Today's Sessions</h3>
              <a href="{{ route('teacher.schedule.index') }}" class="text-decoration-none text-sm fw-medium d-flex align-items-center gap-1" style="color: var(--accent-color);">
                <span data-en="Schedule" data-ar="الجدول">Schedule</span>
                <i class="bi bi-arrow-right rtl:rotate-180"></i>
              </a>
            </div>

            <div class="d-flex flex-column gap-4">
              @forelse($todaysSessions as $sessionGroup)
              <!-- Session Item -->
              <div class="glass-panel rounded-4 p-4 p-md-5 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-4 transition-all hover-glow border-start border-4" style="border-left-color: var(--accent-color) !important;">
                <div class="d-flex align-items-center gap-4">
                  <div class="rounded-circle p-3 d-flex align-items-center justify-content-center shadow-sm" style="background: rgba(197, 168, 128, 0.1); width: 64px; height: 64px;">
                    <i class="bi bi-camera-video fs-3" style="color: var(--accent-color);"></i>
                  </div>
                  <div>
                    <h5 class="mb-2 fs-5 fw-bold" style="color: var(--text-primary);">{{ $sessionGroup->name }} - {{ $sessionGroup->subject->name ?? '' }}</h5>
                    <span class="text-sm opacity-75 fw-medium">
                        <i class="bi bi-clock me-2 text-gold"></i>
                        {{ \Carbon\Carbon::parse($sessionGroup->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($sessionGroup->end_time)->format('h:i A') }}
                    </span>
                  </div>
                </div>
                <a href="{{ route('teacher.groups.show', \Illuminate\Support\Facades\Crypt::encryptString($sessionGroup->id)) }}" class="btn btn-luxury px-5 py-3 align-self-start align-self-sm-center fw-bold rounded-pill" data-en="Open Group" data-ar="فتح المجموعة">Open Group</a>
              </div>
              @empty
              <div class="text-center opacity-75 py-5 glass-panel rounded-4">
                  <i class="bi bi-calendar-x fs-1 mb-3 d-block text-gold"></i>
                  <p class="fs-5 fw-medium" data-en="No sessions scheduled for today." data-ar="لا يوجد جلسات مجدولة لليوم.">No sessions scheduled for today.</p>
              </div>
              @endforelse
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="col-xl-4 fade-in-up delay-3">
            <h3 class="h4 fw-bold mb-4 border-start border-4 ps-3" style="border-color: var(--accent-color) !important; font-family: 'Tajawal', 'Almarai', sans-serif;" data-en="Quick Actions" data-ar="إجراءات سريعة">Quick Actions</h3>

            <div class="d-flex flex-column gap-4">
              <a href="{{ route('teacher.content.hub') }}" class="btn glass-panel text-start p-4 w-100 d-flex align-items-center gap-4 glow-hover tilt-card rounded-4 border-1 text-decoration-none" style="border-color: var(--separator-color);">
                <div class="rounded-circle p-3 shadow-sm d-flex justify-content-center align-items-center" style="background: rgba(197, 168, 128, 0.1); color: var(--accent-color); width: 56px; height: 56px;">
                  <i class="bi bi-journal-richtext fs-4"></i>
                </div>
                <div>
                  <span class="fw-bold d-block mb-1 fs-5" style="color: var(--text-primary);" data-en="Content & Groups" data-ar="المحتوى والمجموعات">Content & Groups</span>
                  <span class="text-sm opacity-75" data-en="Add content and manage your teaching groups" data-ar="أضف محتوى وادرس مجموعاتك التعليمية">Add content and manage your teaching groups</span>
                </div>
              </a>

              <a href="{{ route('teacher.exams.create') }}" class="btn glass-panel text-start p-4 w-100 d-flex align-items-center gap-4 glow-hover tilt-card rounded-4 border-1 text-decoration-none" style="border-color: var(--separator-color);">
                <div class="rounded-circle p-3 shadow-sm d-flex justify-content-center align-items-center" style="background: rgba(197, 168, 128, 0.1); color: var(--accent-color); width: 56px; height: 56px;">
                  <i class="bi bi-journal-plus fs-4"></i>
                </div>
                <div>
                  <span class="fw-bold d-block mb-1 fs-5" style="color: var(--text-primary);" data-en="Create New Exam" data-ar="إنشاء امتحان جديد">Create New Exam</span>
                  <span class="text-sm opacity-75" data-en="Draft a new exam" data-ar="إعداد امتحان جديد">Draft a new exam</span>
                </div>
              </a>

              <a href="{{ route('teacher.grading.index') }}" class="btn glass-panel text-start p-4 w-100 d-flex align-items-center gap-4 glow-hover tilt-card rounded-4 border-1 text-decoration-none" style="border-color: var(--separator-color);">
                <div class="rounded-circle p-3 shadow-sm d-flex justify-content-center align-items-center" style="background: rgba(197, 168, 128, 0.1); color: var(--accent-color); width: 56px; height: 56px;">
                  <i class="bi bi-file-earmark-check fs-4"></i>
                </div>
                <div>
                  <span class="fw-bold d-block mb-1 fs-5" style="color: var(--text-primary);" data-en="Grade Exams" data-ar="رصد درجات الطلاب">Grade Exams</span>
                  <span class="text-sm opacity-75" data-en="Review and grade submissions" data-ar="مراجعة ورصد الدرجات للامتحانات">Review and grade submissions</span>
                </div>
              </a>

              <a href="{{ route('teacher.exams.index') }}" class="btn glass-panel text-start p-4 w-100 d-flex align-items-center gap-4 glow-hover tilt-card rounded-4 border-1 text-decoration-none" style="border-color: var(--separator-color);">
                <div class="rounded-circle p-3 shadow-sm d-flex justify-content-center align-items-center" style="background: rgba(197, 168, 128, 0.1); color: var(--accent-color); width: 56px; height: 56px;">
                  <i class="bi bi-card-checklist fs-4"></i>
                </div>
                <div>
                  <span class="fw-bold d-block mb-1 fs-5" style="color: var(--text-primary);" data-en="Reviews" data-ar="مراجعات">Reviews</span>
                  <span class="text-sm opacity-75" data-en="Review exams and questions" data-ar="مراجعة الامتحانات والأسئلة">Review exams and questions</span>
                </div>
              </a>
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
