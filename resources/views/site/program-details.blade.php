@extends('layouts.site')

@section('title', $program->title.' | FULL MARKS ACADEMY')

@push('styles')
<style>
  .detail-hero {
    position: relative;
    padding: 160px 0 100px 0;
    background-size: cover;
    background-position: center;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .detail-hero-overlay {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    background: radial-gradient(circle at center, rgba(5,4,2,0.4) 0%, rgba(5,4,2,0.95) 100%);
    z-index: 1;
  }
  .theme-light .detail-hero-overlay { background: radial-gradient(circle at center, rgba(255,255,255,0.4) 0%, rgba(248,250,252,0.95) 100%); }
  .theme-dark .detail-hero-overlay { background: radial-gradient(circle at center, rgba(5,7,15,0.4) 0%, rgba(5,7,15,0.95) 100%); }
  .detail-hero-content { position: relative; z-index: 2; }
  .subject-card {
    overflow: hidden; height: 100%; display: flex; flex-direction: column;
    transition: transform var(--transition-normal), box-shadow var(--transition-normal);
    border: 1px solid var(--glass-border); border-radius: var(--radius-lg);
  }
  .subject-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-lg); }
  .badge-discount {
    position: absolute; top: 10px; right: 10px; background: #dc2626; color: #ffffff;
    padding: 4px 10px; border-radius: var(--radius-sm); font-weight: 700; font-size: 12px;
    box-shadow: 0 4px 10px rgba(220, 38, 38, 0.4); z-index: 5;
  }
  .badge-program {
    background: rgba(197, 168, 128, 0.15); color: var(--accent-color);
    border: 1px solid rgba(197, 168, 128, 0.3); padding: 4px 10px;
    border-radius: var(--radius-full); font-size: 12px; font-weight: 600; display: inline-block;
  }
  .badge-google {
    background: rgba(0, 120, 255, 0.1); color: #3b82f6; border: 1px solid rgba(0, 120, 255, 0.2);
    padding: 4px 10px; border-radius: var(--radius-full); font-size: 12px; font-weight: 600; display: inline-block;
  }
  .tag-container { display: flex; flex-wrap: wrap; gap: 6px; }
  .date-box { background: var(--bg-tertiary); border: 1px solid var(--separator-color); border-radius: var(--radius-md); padding: 10px; text-align: center; flex: 1; }
  .fee-box { border-top: 1px dashed var(--separator-color); margin-top: 15px; padding-top: 15px; }
  [dir="rtl"] .badge-discount { right: auto; left: 10px; }
  
  @keyframes pulseRed {
      0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
      70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
      100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
  }
  .pulse-red-bg {
      animation: pulseRed 2s infinite;
      background: linear-gradient(45deg, #dc3545, #b02a37) !important;
      border: 1px solid #ff4d5e;
  }
</style>
@endpush

@section('content')
  <!-- Hero Section -->
  <section id="program-hero" class="detail-hero" style="background-image: url('{{ $program->image ? (str_starts_with($program->image, 'site/') ? asset($program->image) : asset('storage/' . $program->image)) : asset('site/images/img/programs/prog1.png') }}');">
    <div class="detail-hero-overlay"></div>
    <div class="container px-4 text-center detail-hero-content">
      <div class="reveal-scale">
        <h1 id="program-title" class="text-4xl md:text-6xl font-extrabold tracking-tight mb-4 uppercase" style="color: var(--text-primary);"
            data-en="{{ $program->title_en }}" data-ar="{{ $program->title_ar }}">{{ $program->title }}</h1>
        <div id="program-desc" class="text-lg md:text-2xl mb-8 max-w-2xl mx-auto leading-relaxed" style="color: var(--text-secondary);"
           data-en="{{ strip_tags(html_entity_decode($program->description_en ?? '')) }}" data-ar="{{ strip_tags(html_entity_decode($program->description_ar ?? '')) }}">{!! $program->description !!}</div>
        <div class="mx-auto w-16 h-1 bg-gold rounded-full"></div>
      </div>
    </div>
  </section>

  <!-- Subjects Section -->
  <section style="background: var(--bg-primary);">
    <div class="container px-4">
      <div class="text-center max-w-2xl mx-auto mb-16 reveal">
        <h5 class="section-subtitle" data-en="PROGRAM SUBJECTS" data-ar="المواد الدراسية للبرنامج">PROGRAM SUBJECTS</h5>
        <h2 class="section-title" data-en="Taught Course Modules" data-ar="المساقات الدراسية المتاحة">Taught Course Modules</h2>
        <div class="section-divider mx-auto"></div>
      </div>

      <div id="subjects-container" class="row g-4 justify-content-center reveal">
        @php
            $currency = \App\Models\SiteSetting::current()->options['currency'] ?? 'JOD';
        @endphp
        @forelse ($program->subjects as $subject)
          @php
              $now = now();
              $canRegister = true;
              if ($subject->reg_start_date && $now->lt($subject->reg_start_date)) {
                  $canRegister = false;
              }
              if ($subject->reg_end_date && $now->gt($subject->reg_end_date)) {
                  $canRegister = false;
              }
              $showCountdown = false;
              if ($canRegister && $subject->reg_end_date) {
                  $showCountdown = true;
              }
          @endphp
          <div class="col-lg-6">
            <div class="glass-panel subject-card p-4 relative" id="subject-{{ $subject->id }}">
              <div class="row g-4 align-items-center">
                <div class="col-md-5">
                  <div class="news-img-wrapper rounded-lg relative" style="padding-top: 65%;">
                    @if ($subject->discount_percent)
                      <div class="badge-discount">
                        <span data-en="{{ $subject->discount_percent }}% OFF" data-ar="{{ $subject->discount_percent }}% خصم">{{ $subject->discount_percent }}% OFF</span>
                      </div>
                    @endif
                    <img src="{{ $subject->image ? (str_starts_with($subject->image, 'site/') ? asset($subject->image) : asset('storage/' . $subject->image)) : asset('site/images/img/logo_backup.png') }}" alt="{{ $subject->name }}" class="news-img">
                  </div>
                  @if ($showCountdown)
                      <div class="countdown-timer pulse-red-bg mt-2 p-2 text-center" style="border-radius: 8px;" data-end="{{ $subject->reg_end_date->format('Y-m-d\TH:i:s') }}">
                        <div class="text-white fw-bold mb-1 marketing-text" style="font-size: 0.8rem; transition: opacity 0.5s;" id="marketing-text-{{$subject->id}}">
                           <i class="fa-solid fa-fire text-warning me-1"></i> سارعوا بالتسجيل! الفرصة الأخيرة
                        </div>
                        <div class="timer-display d-flex justify-content-center align-items-center gap-1" style="direction: ltr;">
                            <div class="timer-part text-white"><div class="timer-val fw-bolder fs-5 mb-0" style="line-height:1;" id="c-day-{{$subject->id}}">00</div><div class="timer-lbl text-white-50 mt-1" style="font-size:0.6rem;">يوم</div></div>
                            <div class="text-white-50 fs-6 pb-2">:</div>
                            <div class="timer-part text-white"><div class="timer-val fw-bolder fs-5 mb-0" style="line-height:1;" id="c-hr-{{$subject->id}}">00</div><div class="timer-lbl text-white-50 mt-1" style="font-size:0.6rem;">ساعة</div></div>
                            <div class="text-white-50 fs-6 pb-2">:</div>
                            <div class="timer-part text-white"><div class="timer-val fw-bolder fs-5 mb-0" style="line-height:1;" id="c-min-{{$subject->id}}">00</div><div class="timer-lbl text-white-50 mt-1" style="font-size:0.6rem;">دقيقة</div></div>
                            <div class="text-white-50 fs-6 pb-2">:</div>
                            <div class="timer-part text-white"><div class="timer-val fw-bolder fs-5 mb-0" style="line-height:1;" id="c-sec-{{$subject->id}}">00</div><div class="timer-lbl text-white-50 mt-1" style="font-size:0.6rem;">ثانية</div></div>
                        </div>
                      </div>
                  @endif
                </div>
                <div class="col-md-7 d-flex flex-column justify-content-between">
                  <div>
                    <div class="tag-container mb-3">
                      <span class="badge-program">#{{ str_replace(' ', '_', $program->title) }}</span>
                      @foreach ($subject->google_tags ?? [] as $tag)
                        <span class="badge-google">#{{ $tag }}</span>
                      @endforeach
                    </div>
                    <h3 class="text-xl font-bold mb-2" style="color: var(--text-primary);"
                        data-en="{{ $subject->name_en }}" data-ar="{{ $subject->name_ar }}">{{ $subject->name }}</h3>
                    <div class="opacity-75 text-sm mb-4" style="color: var(--text-secondary);"
                       data-en="{{ strip_tags(html_entity_decode($subject->description_en ?? '')) }}" data-ar="{{ strip_tags(html_entity_decode($subject->description_ar ?? '')) }}">{!! $subject->description !!}</div>

                    <div class="d-flex gap-2 mb-3">
                      <div class="date-box" style="flex:1;">
                        <span class="text-xs block uppercase" style="color: var(--text-muted);" data-en="Reg Start" data-ar="بدء التسجيل">بدء التسجيل</span>
                        <span class="text-sm font-bold" style="color: var(--text-primary);">{{ $subject->reg_start_date ? $subject->reg_start_date->format('Y-m-d') : 'مفتوح' }}</span>
                      </div>
                      <div class="date-box" style="flex:1;">
                        <span class="text-xs block uppercase" style="color: var(--text-muted);" data-en="Reg End" data-ar="انتهاء التسجيل">انتهاء التسجيل</span>
                        <span class="text-sm font-bold" style="color: var(--text-primary);">{{ $subject->reg_end_date ? $subject->reg_end_date->format('Y-m-d') : 'مفتوح' }}</span>
                      </div>
                    </div>
                  </div>

                  <div class="fee-box d-flex justify-content-between align-items-center mb-3">
                    <div>
                      <span class="text-xs block" style="color: var(--text-muted);" data-en="Min Reg Payment" data-ar="رسوم التسجيل (الحد الأدنى)">رسوم التسجيل (الحد الأدنى)</span>
                      <span class="text-base font-medium" style="color: var(--text-primary);">{{ number_format($subject->min_payment, 2) }} {{ $currency }}</span>
                    </div>
                    <div class="text-end">
                      <span class="text-xs block" style="color: var(--text-muted);" data-en="Total Fee" data-ar="إجمالي الرسوم">إجمالي الرسوم</span>
                      <span class="text-lg font-bold text-gold">{{ number_format($subject->fee, 2) }} {{ $currency }}</span>
                    </div>
                  </div>

                  <div class="d-flex gap-2">
                    <button type="button" class="btn btn-glass icon-btn px-3" data-bs-toggle="modal" data-bs-target="#subjectQrModal{{ $subject->id }}" title="QR Code" aria-label="QR Code">
                      <i class="bi bi-qr-code"></i>
                    </button>
                    @if(!$canRegister)
                        <div class="alert alert-danger w-100 mb-0 py-2 text-center fs-6 fw-bold" role="alert">
                            <i class="fa-solid fa-lock me-1"></i> التسجيل غير متاح حالياً
                        </div>
                    @else
                        @auth('student')
                          <button type="button" class="btn btn-luxury w-100 py-2.5 rounded-lg text-center add-to-cart-btn"
                                  data-id="{{ $subject->id }}"
                                  data-name-ar="{{ $subject->name_ar }}"
                                  data-name-en="{{ $subject->name_en }}"
                                  data-fee="{{ number_format($subject->min_payment, 2) }} {{ $currency }}"
                                  data-total-fee="{{ number_format($subject->fee, 2) }} {{ $currency }}"
                                  data-image="{{ $subject->image ? (str_starts_with($subject->image, 'site/') ? asset($subject->image) : asset('storage/' . $subject->image)) : asset('site/images/img/logo_backup.png') }}"
                                  data-program-ar="{{ $program->name_ar }}"
                                  data-program-en="{{ $program->name_en }}">
                            <span data-en="Add to Cart" data-ar="أضف للسلة">أضف للسلة</span>
                          </button>
                        @else
                          <a href="{{ route('student.register') }}" class="btn btn-luxury w-100 py-2.5 rounded-lg text-center">
                            <span data-en="Sign Up to Register" data-ar="سجل حساباً للتسجيل">سجل حساباً للتسجيل</span>
                          </a>
                        @endauth
                    @endif
                  </div>
                </div>
              </div>
            </div>
          </div>
        @empty
          <p class="text-center opacity-75" data-en="No subjects available yet." data-ar="لا توجد مواد متاحة حالياً.">No subjects available yet.</p>
        @endforelse
      </div>
    </div>
  </section>

  {{-- QR modals are rendered outside the .reveal (transformed) container --}}
  {{-- since Bootstrap's fixed-position modal breaks under a transformed ancestor. --}}
  @foreach ($program->subjects as $subject)
    <div class="modal fade" id="subjectQrModal{{ $subject->id }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content glass-panel text-center">
          <div class="modal-header border-0">
            <h5 class="modal-title fw-bold mx-auto" data-en="{{ $subject->name_en }}" data-ar="{{ $subject->name_ar }}">{{ $subject->name }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body pb-8 d-flex flex-column align-items-center justify-content-center">
            <img src="{{ route('qr.subject', $subject->id) }}" alt="QR Code" class="rounded-lg d-block mx-auto" style="width: 220px; height: 220px; background: #fff; padding: 10px;">
            <p class="opacity-75 text-sm mt-4 mb-0" data-en="Scan to open this subject" data-ar="امسح الرمز للانتقال إلى هذه المادة">Scan to open this subject</p>
          </div>
        </div>
      </div>
    </div>
  @endforeach
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const nameAr = this.dataset.nameAr;
        const nameEn = this.dataset.nameEn;
        const fee = this.dataset.fee;
        const totalFee = this.dataset.totalFee;
        const image = this.dataset.image;
        const programAr = this.dataset.programAr;
        const programEn = this.dataset.programEn;

        const item = {
          id: id,
          name: { ar: nameAr, en: nameEn },
          fee: fee,
          totalFee: totalFee,
          image: image,
          program: { ar: programAr, en: programEn },
          group_id: null
        };

        if (window.CartSystem) {
          window.CartSystem.addItem(item);
        }
      });
    });

    // Countdown Timers logic
    document.querySelectorAll('.countdown-timer').forEach(function(el) {
        var endDateStr = el.getAttribute('data-end');
        if (!endDateStr) return;
        var endDate = new Date(endDateStr).getTime();
        var displaySpan = el.querySelector('.timer-display');
        var dayEl = el.querySelector('[id^="c-day-"]');
        var hrEl = el.querySelector('[id^="c-hr-"]');
        var minEl = el.querySelector('[id^="c-min-"]');
        var secEl = el.querySelector('[id^="c-sec-"]');

        var x = setInterval(function() {
            var now = new Date().getTime();
            var distance = endDate - now;

            if (distance < 0) {
                clearInterval(x);
                displaySpan.innerHTML = "<div class='text-white fw-bold fs-5'>التسجيل انتهى</div>";
                // Optionally reload to update the server-side UI
                setTimeout(() => window.location.reload(), 2000);
                return;
            }

            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            if(dayEl) dayEl.innerText = days.toString().padStart(2, '0');
            if(hrEl) hrEl.innerText = hours.toString().padStart(2, '0');
            if(minEl) minEl.innerText = minutes.toString().padStart(2, '0');
            if(secEl) secEl.innerText = seconds.toString().padStart(2, '0');
        }, 1000);
        
        // Slider logic for marketing text
        var slidesText = [
            '<i class="fa-solid fa-fire text-warning me-1"></i> سارعوا بالتسجيل! الفرصة الأخيرة',
            '<i class="fa-solid fa-bolt text-warning me-1"></i> المقاعد محدودة! لا تفوت الفرصة',
            '<i class="fa-solid fa-star text-warning me-1"></i> التسجيل يغلق قريباً جداً!'
        ];
        var textContainer = el.querySelector('.marketing-text');
        var slideIndex = 0;
        if(textContainer) {
            setInterval(function() {
                textContainer.style.opacity = 0;
                setTimeout(function() {
                    slideIndex = (slideIndex + 1) % slidesText.length;
                    textContainer.innerHTML = slidesText[slideIndex];
                    textContainer.style.opacity = 1;
                }, 500);
            }, 3000);
        }
    });
  });
</script>
@endpush
