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
        <p id="program-desc" class="text-lg md:text-2xl mb-8 max-w-2xl mx-auto leading-relaxed" style="color: var(--text-secondary);"
           data-en="{{ $program->description_en }}" data-ar="{{ $program->description_ar }}">{{ $program->description }}</p>
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
        @forelse ($program->subjects as $subject)
          <div class="col-lg-6">
            <div class="glass-panel subject-card p-4 relative">
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
                    <p class="opacity-75 text-sm mb-4" style="color: var(--text-secondary);"
                       data-en="{{ strip_tags(html_entity_decode($subject->description_en ?? '')) }}" data-ar="{{ strip_tags(html_entity_decode($subject->description_ar ?? '')) }}">{!! $subject->description !!}</p>

                    <div class="d-flex gap-2 mb-3">
                      <div class="date-box">
                        <span class="text-xs block uppercase" style="color: var(--text-muted);" data-en="Reg Start" data-ar="بدء التسجيل">Reg Start</span>
                        <span class="text-sm font-bold" style="color: var(--text-primary);">{{ optional($subject->reg_start_date)->format('Y-m-d') }}</span>
                      </div>
                      <div class="date-box">
                        <span class="text-xs block uppercase" style="color: var(--text-muted);" data-en="Reg End" data-ar="انتهاء التسجيل">Reg End</span>
                        <span class="text-sm font-bold" style="color: var(--text-primary);">{{ optional($subject->reg_end_date)->format('Y-m-d') }}</span>
                      </div>
                    </div>
                  </div>

                  <div class="fee-box d-flex justify-content-between align-items-center mb-3">
                    <div>
                      <span class="text-xs block" style="color: var(--text-muted);" data-en="Min Reg Payment" data-ar="رسوم التسجيل (الحد الأدنى)">Min Reg Payment</span>
                      <span class="text-base font-medium" style="color: var(--text-primary);">{{ number_format($subject->min_payment, 2) }} JOD</span>
                    </div>
                    <div class="text-end">
                      <span class="text-xs block" style="color: var(--text-muted);" data-en="Total Fee" data-ar="إجمالي الرسوم">Total Fee</span>
                      <span class="text-lg font-bold text-gold">{{ number_format($subject->fee, 2) }} JOD</span>
                    </div>
                  </div>

                  <div class="d-flex gap-2">
                    @if ($subject->groups->isNotEmpty())
                      <select id="group-select-{{ $subject->id }}" class="form-select form-select-sm" style="background: var(--input-bg); border-color: var(--input-border); color: var(--text-primary); max-width: 150px;">
                        <option value="" data-en="Later" data-ar="شعبة لاحقاً">شعبة لاحقاً</option>
                        @foreach ($subject->groups as $group)
                          <option value="{{ $group->id }}" @disabled(! $group->hasAvailableCapacity())>
                            {{ $group->name }} @if (! $group->hasAvailableCapacity()) ({{ __('app.full') }}) @endif
                          </option>
                        @endforeach
                      </select>
                    @endif
                    @auth('student')
                      <button type="button" class="btn btn-luxury w-100 py-2.5 rounded-lg text-center add-to-cart-btn"
                              data-id="{{ $subject->id }}"
                              data-name-ar="{{ $subject->name_ar }}"
                              data-name-en="{{ $subject->name_en }}"
                              data-fee="{{ number_format($subject->min_payment, 2) }} JOD"
                              data-total-fee="{{ number_format($subject->fee, 2) }} JOD"
                              data-image="{{ $subject->image ? (str_starts_with($subject->image, 'site/') ? asset($subject->image) : asset('storage/' . $subject->image)) : asset('site/images/img/logo_backup.png') }}"
                              data-program-ar="{{ $program->name_ar }}"
                              data-program-en="{{ $program->name_en }}">
                        <span data-en="Add to Cart" data-ar="أضف للسلة">Add to Cart</span>
                      </button>
                    @else
                      <a href="{{ route('student.register') }}" class="btn btn-luxury w-100 py-2.5 rounded-lg text-center">
                        <span data-en="Sign Up to Register" data-ar="سجل حساباً للتسجيل">سجل حساباً للتسجيل</span>
                      </a>
                    @endauth
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

        // Get group selection if exists
        const select = document.getElementById(`group-select-${id}`);
        const groupId = select ? select.value : null;

        const item = {
          id: id,
          name: { ar: nameAr, en: nameEn },
          fee: fee,
          totalFee: totalFee,
          image: image,
          program: { ar: programAr, en: programEn },
          group_id: groupId || null
        };

        if (window.CartSystem) {
          window.CartSystem.addItem(item);
        }
      });
    });
  });
</script>
@endpush
