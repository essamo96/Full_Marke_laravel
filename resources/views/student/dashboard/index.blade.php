@extends('layouts.student')

@section('title', 'Student Dashboard | FULL MARK ACADEMY')

@section('content')
        
        <!-- Welcome Banner -->
        <section class="glass-panel rounded-4 p-4 p-md-5 mb-4 position-relative overflow-hidden d-flex flex-column flex-md-row align-items-center justify-content-between gap-4">
          <div class="position-absolute top-0 end-0 w-50 h-100 bg-gold/10 blur-[80px]"></div>
          <div class="position-relative z-1 text-center text-md-start">
            <h1 class="display-6 fw-bold mb-2" style="color: var(--text-primary);">
              <span data-en="Welcome back," data-ar="مرحباً بعودتك،">Welcome back,</span> <span style="background: var(--accent-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Ahmad M.</span>
            </h1>
            <p class="fs-5 opacity-75 mb-0" data-en="You have completed 75% of your weekly goals. Keep it up!" data-ar="لقد أكملت 75% من أهدافك التعليمية لهذا الأسبوع. واصل التقدم!">
              You have completed 75% of your weekly goals. Keep it up!
            </p>
          </div>
          <div class="position-relative z-1 d-flex align-items-center justify-content-center" style="width: 120px; height: 120px;">
            <svg class="w-100 h-100 transform -rotate-90" viewBox="0 0 100 100">
              <circle cx="50" cy="50" r="40" fill="none" stroke="var(--bg-tertiary)" stroke-width="8"></circle>
              <circle class="progress-ring-circle" cx="50" cy="50" r="40" fill="none" stroke="var(--accent-color)" stroke-width="8" stroke-dasharray="251.2" stroke-dashoffset="62.8" stroke-linecap="round"></circle>
            </svg>
            <div class="position-absolute d-flex flex-column align-items-center justify-content-center">
              <span class="fs-4 fw-bold" style="color: var(--accent-color);">75%</span>
            </div>
          </div>
        </section>

        <!-- Stats Grid -->
        <section class="row g-4 mb-4">
          <!-- Stat 1 -->
          <div class="col-md-6 col-lg-3">
            <div class="glass-panel rounded-4 p-4 h-100 tilt-card glow-card">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="p-2 rounded" style="background: rgba(197, 168, 128, 0.1); color: var(--accent-color);">
                  <i class="bi bi-award fs-4"></i>
                </div>
                <span class="badge bg-success bg-opacity-25 text-success">+0.2 <i class="bi bi-graph-up"></i></span>
              </div>
              <p class="mb-1 text-sm opacity-75" data-en="Current GPA" data-ar="المعدل التراكمي (GPA)">Current GPA</p>
              <h3 class="fw-bold mb-0" style="color: var(--text-primary);">3.85</h3>
            </div>
          </div>
          <!-- Stat 2 -->
          <div class="col-md-6 col-lg-3">
            <div class="glass-panel rounded-4 p-4 h-100 tilt-card glow-card">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="p-2 rounded" style="background: rgba(197, 168, 128, 0.1); color: var(--accent-color);">
                  <i class="bi bi-journal-check fs-4"></i>
                </div>
              </div>
              <p class="mb-1 text-sm opacity-75" data-en="Completed Courses" data-ar="الدورات المكتملة">Completed Courses</p>
              <h3 class="fw-bold mb-0" style="color: var(--text-primary);">12</h3>
            </div>
          </div>
          <!-- Active Lecture (Spans 2 cols) -->
          <div class="col-lg-6">
            <div class="glass-panel rounded-4 p-4 h-100 tilt-card glow-card position-relative overflow-hidden d-flex flex-column justify-content-center">
              <div class="position-absolute start-0 top-0 h-100 w-50 bg-gradient-to-r from-black/20 to-transparent z-0 pointer-events-none"></div>
              <div class="position-relative z-1 d-flex flex-column flex-sm-row justify-content-between align-items-start h-100 gap-3">
                <div>
                  <span class="badge bg-danger mb-2 d-inline-flex align-items-center gap-2">
                    <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                    <span data-en="LIVE NOW" data-ar="مباشر الآن">LIVE NOW</span>
                  </span>
                  <h4 class="fw-bold mb-1" style="color: var(--text-primary);" data-en="Advanced Physics - Lec 4" data-ar="الفيزياء المتقدمة - المحاضرة 4">Advanced Physics - Lec 4</h4>
                  <p class="text-sm opacity-75 mb-0"><i class="bi bi-clock me-1"></i> <span data-en="Started 15 mins ago" data-ar="بدأ منذ 15 دقيقة">Started 15 mins ago</span></p>
                </div>
                <button class="btn btn-luxury px-4 py-2 mt-auto mt-sm-0 align-self-sm-center" data-en="Join Session" data-ar="انضمام">Join Session</button>
              </div>
            </div>
          </div>
        </section>

        <div class="row g-4">
          <!-- My Courses List -->
          <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-4">
              <h3 class="h4 fw-bold mb-0 border-start border-4 ps-3" style="border-color: var(--accent-color) !important;" data-en="My Current Courses" data-ar="دوراتي الحالية">My Current Courses</h3>
              <a href="#" class="text-decoration-none text-sm fw-medium d-flex align-items-center gap-1" style="color: var(--accent-color);">
                <span data-en="View All" data-ar="عرض الكل">View All</span>
                <i class="bi bi-arrow-right rtl:rotate-180"></i>
              </a>
            </div>
            
            <div class="d-flex flex-column gap-3">
              <!-- Course Item -->
              <div class="glass-panel rounded-3 p-3 d-flex align-items-center gap-3 transition-all hover:bg-white/5 cursor-pointer">
                <div class="rounded p-3 d-flex align-items-center justify-content-center border" style="background: var(--bg-secondary); border-color: var(--separator-color); width: 64px; height: 64px;">
                  <i class="bi bi-calculator fs-2" style="color: var(--accent-color);"></i>
                </div>
                <div class="flex-1 w-100">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <h5 class="mb-0 fs-6 fw-bold" style="color: var(--text-primary);" data-en="Mathematics - Calculus" data-ar="الرياضيات - التفاضل والتكامل">Mathematics - Calculus</h5>
                    <span class="text-sm fw-bold" style="color: var(--accent-color);">60%</span>
                  </div>
                  <div class="progress mb-1" style="height: 6px; background: var(--bg-tertiary);">
                    <div class="progress-bar" role="progressbar" style="width: 60%; background: var(--accent-gradient);" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                  </div>
                  <span class="text-xs opacity-75"><i class="bi bi-book me-1"></i> <span data-en="12 out of 20 Lessons" data-ar="12 من 20 درس">12 out of 20 Lessons</span></span>
                </div>
              </div>

              <!-- Course Item -->
              <div class="glass-panel rounded-3 p-3 d-flex align-items-center gap-3 transition-all hover:bg-white/5 cursor-pointer">
                <div class="rounded p-3 d-flex align-items-center justify-content-center border" style="background: var(--bg-secondary); border-color: var(--separator-color); width: 64px; height: 64px;">
                  <i class="bi bi-virus fs-2" style="color: #00f0ff;"></i>
                </div>
                <div class="flex-1 w-100">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <h5 class="mb-0 fs-6 fw-bold" style="color: var(--text-primary);" data-en="Organic Chemistry" data-ar="الكيمياء العضوية">Organic Chemistry</h5>
                    <span class="text-sm fw-bold" style="color: var(--accent-color);">35%</span>
                  </div>
                  <div class="progress mb-1" style="height: 6px; background: var(--bg-tertiary);">
                    <div class="progress-bar" role="progressbar" style="width: 35%; background: var(--accent-gradient);" aria-valuenow="35" aria-valuemin="0" aria-valuemax="100"></div>
                  </div>
                  <span class="text-xs opacity-75"><i class="bi bi-book me-1"></i> <span data-en="7 out of 20 Lessons" data-ar="7 من 20 درس">7 out of 20 Lessons</span></span>
                </div>
              </div>
            </div>
          </div>

          <!-- Deadlines Timeline -->
          <div class="col-lg-4">
            <h3 class="h4 fw-bold mb-4 border-start border-4 ps-3" style="border-color: var(--accent-color) !important;" data-en="Upcoming Deadlines" data-ar="المواعيد القادمة">Upcoming Deadlines</h3>
            
            <div class="glass-panel rounded-4 p-4 position-relative">
              <div class="position-absolute end-0 top-0 bottom-0 ms-4" style="width: 2px; background: var(--separator-color); margin-right: 28px;"></div>
              
              <div class="d-flex flex-column gap-4 position-relative z-1">
                <!-- Timeline Item -->
                <div class="d-flex gap-3">
                  <div class="rounded-circle border border-4 mt-1 flex-shrink-0" style="width: 20px; height: 20px; background: #ff4757; border-color: var(--bg-primary); z-index: 2; box-shadow: 0 0 10px rgba(255,71,87,0.5);"></div>
                  <div>
                    <span class="text-sm fw-bold d-block mb-1" style="color: #ff4757;" data-en="Tomorrow - 10:00 AM" data-ar="غداً - 10:00 ص">Tomorrow - 10:00 AM</span>
                    <div class="rounded p-3 border" style="background: var(--bg-secondary); border-color: var(--separator-color);">
                      <h6 class="fw-bold mb-1" style="color: var(--text-primary);" data-en="Midterm Physics Exam" data-ar="امتحان الفيزياء النصفي">Midterm Physics Exam</h6>
                      <span class="text-xs opacity-75" data-en="Virtual Hall A" data-ar="القاعة الافتراضية أ">Virtual Hall A</span>
                    </div>
                  </div>
                </div>

                <!-- Timeline Item -->
                <div class="d-flex gap-3">
                  <div class="rounded-circle border border-4 mt-1 flex-shrink-0" style="width: 20px; height: 20px; background: var(--accent-color); border-color: var(--bg-primary); z-index: 2;"></div>
                  <div>
                    <span class="text-sm fw-bold d-block mb-1" style="color: var(--accent-color);" data-en="Oct 15 - 11:59 PM" data-ar="15 أكتوبر - 11:59 م">Oct 15 - 11:59 PM</span>
                    <div class="rounded p-3 border" style="background: var(--bg-secondary); border-color: var(--separator-color);">
                      <h6 class="fw-bold mb-1" style="color: var(--text-primary);" data-en="History Assignment Due" data-ar="تسليم بحث التاريخ">History Assignment Due</h6>
                      <span class="text-xs opacity-75" data-en="Via Education Portal" data-ar="عبر البوابة التعليمية">Via Education Portal</span>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>

@endsection
