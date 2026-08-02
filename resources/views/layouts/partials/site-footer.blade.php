  <style>
    /* Each social icon idles in the site's neutral gold tone, then switches to
       its own platform's brand color on hover/focus — set per-icon via the
       --social-brand custom property computed in the loop below. */
    .footer-social-btn {
      background: rgba(197,168,128,0.1);
      color: var(--accent-color);
    }
    .footer-social-btn:hover,
    .footer-social-btn:focus-visible {
      background: var(--social-brand, var(--accent-color));
      color: #fff;
    }
  </style>
  <footer class="py-16" style="background: var(--bg-primary); border-top: 1px solid var(--separator-color);">
    <div class="container px-4">
      <div class="row g-8 mb-12">
        <!-- Col 1 -->
        <div class="col-lg-4 col-md-6">
          <div class="d-flex align-items-center mb-4">
            <img id="footer-logo" src="{{ asset('site/images/logo_v2_gold.png') }}" alt="Logo" class="object-contain mr-3">
          </div>
          <p class="opacity-75 text-sm mb-6" data-en="Empowering academic horizons under international standards. Full Mark Academy is committed to shaping the next generation of achievers." data-ar="تمكين الآفاق الأكاديمية تحت مظلة المعايير الدولية. أكاديمية العلامة الكاملة ملتزمة بصياغة جيل المتفوقين القادم.">
            Empowering academic horizons under international standards. Full Mark Academy is committed to shaping the next generation of achievers.
          </p>
          <div class="d-flex gap-3">
            @php
              // Read from the admin's Socials control panel (only active
              // entries), not hardcoded links, so adding/disabling a
              // platform there is reflected here automatically.
              $footerSocials = \App\Models\Social::active()->orderBy('id')->get();

              // Each platform's own recognizable brand color for the hover
              // state, instead of one uniform gold hover for every icon.
              $socialBrandColors = [
                'facebook' => '#1877F2',
                'twitter-x' => '#000000',
                'twitter' => '#1DA1F2',
                'instagram' => '#E1306C',
                'linkedin' => '#0A66C2',
                'youtube' => '#FF0000',
                'whatsapp' => '#25D366',
                'telegram' => '#26A5E4',
                'snapchat' => '#FFFC00',
                'tiktok' => '#000000',
              ];
            @endphp
            @foreach($footerSocials as $social)
              @php
                $iconClass = $social->icon ?: 'bi-link-45deg';
                // Bootstrap Icons need the base "bi" class alongside the glyph
                // class (e.g. "bi bi-facebook"); some seeded rows only stored
                // the glyph class ("bi-facebook"), which renders as invisible
                // text without it. Other icon sets (e.g. "ki-duotone ki-...")
                // already carry their own base class, so leave those alone.
                if (str_starts_with($iconClass, 'bi-') && !str_contains($iconClass, 'bi bi-')) {
                    $iconClass = 'bi ' . $iconClass;
                }
                $brandColor = null;
                foreach ($socialBrandColors as $needle => $color) {
                    if (str_contains($iconClass, $needle)) { $brandColor = $color; break; }
                }
                $brandColor = $brandColor ?? 'var(--accent-color)';
                $platformName = app()->getLocale() === 'ar' ? ($social->name_ar ?? $social->name_en) : ($social->name_en ?? $social->name_ar);
              @endphp
              <a href="{{ $social->link }}" target="_blank" rel="noopener" title="{{ $platformName }}"
                 class="footer-social-btn w-10 h-10 rounded-full d-flex align-items-center justify-content-center text-lg transition-colors"
                 style="--social-brand: {{ $brandColor }};">
                @if($social->image)
                  <img src="{{ $social->image_path }}" alt="{{ $platformName }}" style="width: 18px; height: 18px; object-fit: contain;">
                @else
                  <i class="{{ $iconClass }}"></i>
                @endif
              </a>
            @endforeach
          </div>
        </div>

        <!-- Col 2 -->
        <div class="col-lg-3 col-md-6">
          <h4 class="font-bold text-lg mb-4" style="color: var(--text-primary);" data-en="Our Courses" data-ar="دوراتنا">Our Courses</h4>
          <ul class="list-unstyled space-y-2 text-sm opacity-75">
            <li><a href="#" class="text-decoration-none hover:text-gold" style="color: var(--text-primary);" data-en="IELTS Preparation" data-ar="التحضير لاختبار آيلتس">IELTS Preparation</a></li>
            <li><a href="#" class="text-decoration-none hover:text-gold" style="color: var(--text-primary);" data-en="General English Levels" data-ar="مستويات اللغة العامة">General English Levels</a></li>
            <li><a href="#" class="text-decoration-none hover:text-gold" style="color: var(--text-primary);" data-en="Academic Speaking" data-ar="دورة المحادثة المتقدمة">Academic Speaking</a></li>
            <li><a href="#" class="text-decoration-none hover:text-gold" style="color: var(--text-primary);" data-en="Academic Writing" data-ar="الكتابة الأكاديمية والإنشاء">Academic Writing</a></li>
            <li><a href="#" class="text-decoration-none hover:text-gold" style="color: var(--text-primary);" data-en="Business English Modules" data-ar="الإنجليزية للأعمال والشركات">Business English Modules</a></li>
          </ul>
        </div>

        <!-- Col 3 -->
        <div class="col-lg-2 col-md-6">
          <h4 class="font-bold text-lg mb-4" style="color: var(--text-primary);" data-en="Quick Links" data-ar="روابط سريعة">Quick Links</h4>
          <ul class="list-unstyled space-y-2 text-sm opacity-75">
            <li><a href="#about" class="text-decoration-none hover:text-gold" style="color: var(--text-primary);" data-en="About Us" data-ar="من نحن">About Us</a></li>
            <li><a href="#strengths" class="text-decoration-none hover:text-gold" style="color: var(--text-primary);" data-en="Why Us" data-ar="لماذا نحن؟">Why Us</a></li>
            <li><a href="#news" class="text-decoration-none hover:text-gold" style="color: var(--text-primary);" data-en="Updates" data-ar="آخر التحديثات">Updates</a></li>
            <li><a href="#faq" class="text-decoration-none hover:text-gold" style="color: var(--text-primary);" data-en="Help FAQ" data-ar="الأسئلة المساعدة">Help FAQ</a></li>
            <li><a href="#contact" class="text-decoration-none hover:text-gold" style="color: var(--text-primary);" data-en="Contact Support" data-ar="الدعم الأكاديمي">Contact Support</a></li>
          </ul>
        </div>

        <!-- Col 4 -->
        <div class="col-lg-3 col-md-6 text-center text-md-start">
          <h4 class="font-bold text-lg mb-4" style="color: var(--text-primary);" data-en="Academy Centre" data-ar="مركز الأكاديمية">Academy Centre</h4>
          <img id="footer-approved-logo" src="{{ asset('site/images/logo_v2_gold.png') }}" alt="Academy Logo" class="object-contain mb-3 mx-auto mx-md-0">
          <p class="text-xs opacity-75" data-en="Licence verification code: FMA-JO-2026" data-ar="رقم الترخيص المعتمد: FMA-JO-2026">Licence verification code: FMA-JO-2026</p>
        </div>
      </div>

      <div class="pt-8 border-t d-flex flex-column flex-md-row justify-content-between align-items-center text-sm opacity-60" style="border-color: var(--separator-color) !important;">
        <p class="mb-0" data-en="&copy; 2026 FULL MARK ACADEMY. All Rights Reserved." data-ar="&copy; 2026 أكاديمية العلامة الكاملة. جميع الحقوق محفوظة.">&copy; 2026 FULL MARK ACADEMY. All Rights Reserved.</p>
        <p class="mb-0 mt-2 mt-md-0" data-en="Designed with Luxury Principles" data-ar="صمم بأرقى معايير واجهات المستخدم">Designed with Luxury Principles</p>
      </div>
    </div>
  </footer>
