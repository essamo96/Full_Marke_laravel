/* ============================================================
   Hero image slider:
   1) One .hero-slide <img> per active admin-configured slider row,
      cross-faded + Ken-Burns'd on a fixed interval (SLIDE_INTERVAL_MS).
   2) The title/description/buttons overlay swaps in sync with the
      background, sourced from window.HERO_SLIDES (see home.blade.php).
   3) Hero content reveals once with staggered delays and then stays
      visible continuously (no more hide/show loop — the old video
      intro needed that to hide the "seam" between clips; a plain image
      crossfade has no seam to hide).
   Also:
   - Particles wait for the first 'hero:complete' before starting.
   - Single scroll-track, scrollbar appears only when hero is done.
   ============================================================ */
(() => {
  'use strict';

  const SLIDE_INTERVAL_MS = 6000;          // how long each slide stays fully visible
  const SAFETY_TIMEOUT_MS = 25000;
  const REDUCED_MOTION = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Note: the global splash (#hero-preloader) is owned by site-splash.js —
  // this file only drives the homepage's looping hero background/content.
  const html = document.documentElement;
  const stage = document.querySelector('.hero-frame-stage');
  const slides = stage ? Array.from(stage.querySelectorAll('.hero-slide')) : [];
  const overlay = document.getElementById('hero-content-overlay');
  const dots = Array.from(document.querySelectorAll('.hero-slide-dot'));
  const slideTitle = document.getElementById('heroSlideTitle');
  const slideDesc = document.getElementById('heroSlideDesc');
  const slideBtn1 = document.getElementById('heroSlideBtn1');
  const slideBtn2 = document.getElementById('heroSlideBtn2');
  const slideData = Array.isArray(window.HERO_SLIDES) ? window.HERO_SLIDES : [];

  if (!stage || !slides.length || !overlay) return;

  // ---------- Preload helpers ----------
  function whenImageReady(img) {
    return new Promise((resolve) => {
      if (img.complete && img.naturalWidth) return resolve();
      img.addEventListener('load', resolve, { once: true });
      img.addEventListener('error', resolve, { once: true });
      setTimeout(resolve, SAFETY_TIMEOUT_MS);
    });
  }

  function preloadAll() {
    return Promise.all(slides.map(whenImageReady));
  }

  // ---------- Overlay text/button sync ----------
  function applyBtn(el, data, textKeyAr, textKeyEn, linkKey) {
    if (!el) return;
    const hasText = data[textKeyAr] || data[textKeyEn];
    el.classList.toggle('d-none', !hasText);
    if (!hasText) return;
    el.setAttribute('data-ar', data[textKeyAr] || '');
    el.setAttribute('data-en', data[textKeyEn] || '');
    if (data[linkKey]) el.setAttribute('href', data[linkKey]);
    const lang = (window.currentLang === 'ar') ? 'ar' : 'en';
    el.textContent = data[lang === 'ar' ? textKeyAr : textKeyEn] || '';
  }

  function syncOverlayToSlide(index) {
    const data = slideData[index];
    if (!data) return;
    const lang = (window.currentLang === 'ar') ? 'ar' : 'en';

    if (slideTitle) {
      slideTitle.setAttribute('data-ar', data.title_ar || '');
      slideTitle.setAttribute('data-en', data.title_en || '');
      slideTitle.textContent = (lang === 'ar' ? data.title_ar : data.title_en) || '';
    }
    if (slideDesc) {
      slideDesc.setAttribute('data-ar', data.desc_ar || '');
      slideDesc.setAttribute('data-en', data.desc_en || '');
      slideDesc.textContent = (lang === 'ar' ? data.desc_ar : data.desc_en) || '';
    }
    applyBtn(slideBtn1, data, 'btn1_text_ar', 'btn1_text_en', 'btn1_link');
    applyBtn(slideBtn2, data, 'btn2_text_ar', 'btn2_text_en', 'btn2_link');
  }

  // ---------- Slide cycling (background crossfade + Ken Burns) ----------
  let activeIndex = 0;
  function goToSlide(nextIndex) {
    if (nextIndex === activeIndex && slides[activeIndex].classList.contains('is-active')) return;

    slides.forEach((el, i) => el.classList.toggle('is-active', i === nextIndex));
    dots.forEach((el, i) => el.classList.toggle('is-active', i === nextIndex));

    // Brief fade of the text block so title/desc changes don't jump-cut.
    overlay.classList.add('is-swapping');
    setTimeout(() => {
      syncOverlayToSlide(nextIndex);
      overlay.classList.remove('is-swapping');
    }, slides.length > 1 ? 350 : 0);

    activeIndex = nextIndex;
  }

  let autoAdvanceTimer = null;
  function startAutoAdvance() {
    if (slides.length < 2 || REDUCED_MOTION) return;
    stopAutoAdvance();
    autoAdvanceTimer = setInterval(() => {
      goToSlide((activeIndex + 1) % slides.length);
    }, SLIDE_INTERVAL_MS);
  }
  function stopAutoAdvance() {
    if (autoAdvanceTimer) clearInterval(autoAdvanceTimer);
    autoAdvanceTimer = null;
  }

  dots.forEach((dot) => {
    dot.addEventListener('click', () => {
      const i = Number(dot.dataset.slideIndex);
      if (Number.isNaN(i)) return;
      goToSlide(i);
      startAutoAdvance(); // restart the timer so a manual click doesn't get immediately overridden
    });
  });

  // Re-sync overlay text whenever the language toggle fires, so a slide
  // change mid-session still respects whichever language is active.
  window.addEventListener('languageChanged', () => syncOverlayToSlide(activeIndex));

  html.classList.add('hero-loading');

  // ---------- Content reveal ----------
  function revealContent() {
    overlay.classList.remove('is-hiding');
    overlay.classList.add('is-revealed');
    // Re-enable particles fade-in
    const pc = document.getElementById('particles-canvas');
    if (pc) pc.classList.remove('is-fading-out');
  }

  // ---------- About-section looping video: play in view, pause out of view ----------
  function initAboutVideo() {
    const aboutVid = document.getElementById('about-video');
    // The admin can configure either a video or an image for this slot — if
    // it's an <img>, there's nothing to play/pause.
    if (!aboutVid || aboutVid.tagName !== 'VIDEO') return;
    aboutVid.muted = true;
    aboutVid.loop = true;

    if (!('IntersectionObserver' in window)) {
      const p = aboutVid.play(); if (p && p.catch) p.catch(() => { });
      return;
    }
    const obs = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          const p = aboutVid.play();
          if (p && p.catch) p.catch(() => { });
        } else {
          aboutVid.pause();
        }
      });
    }, { threshold: 0.25 });
    obs.observe(aboutVid);
  }

  // ---------- Navbar scrolled-state toggle (controls border/glass) ----------
  function initNavbarScroll() {
    const header = document.getElementById('header-nav');
    if (!header) return;
    let isScrolling = false;
    const onScroll = () => {
      if (window.scrollY > 60) header.classList.add('scrolled');
      else header.classList.remove('scrolled');
      isScrolling = false;
    };
    window.addEventListener('scroll', () => {
      if (!isScrolling) {
        window.requestAnimationFrame(onScroll);
        isScrolling = true;
      }
    }, { passive: true });
    onScroll();
  }

  // ---------- Global section reveal ----------
  function initSectionReveal() {
    // 1) Tag each section / footer / separator for the fade-up envelope
    document.querySelectorAll('section:not(#hero), footer, .section-separator')
      .forEach((el) => el.classList.add('io-reveal'));

    // 2) For each .row inside a section, give children alternating
    //    slide directions (L, R, L, R…) with a 140ms stagger.
    document.querySelectorAll('section:not(#hero) .row').forEach((row) => {
      Array.from(row.children).forEach((col, i) => {
        col.classList.add(i % 2 === 0 ? 'io-slide-l' : 'io-slide-r');
        col.style.setProperty('--io-delay', `${i * 140}ms`);
      });
    });

    // 3) Section headings fade upward (no slide)
    document.querySelectorAll(
      'section:not(#hero) > .container > .text-center, ' +
      'section:not(#hero) .faq-header-block'
    ).forEach((el) => el.classList.add('io-fade-up'));

    const targets = document.querySelectorAll(
      '.io-reveal, .io-slide-l, .io-slide-r, .io-fade-up'
    );

    if (!('IntersectionObserver' in window)) {
      targets.forEach((el) => el.classList.add('is-in-view'));
      return;
    }
    const io = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-in-view');
          io.unobserve(entry.target);
        }
      });
    }, { root: null, rootMargin: '0px 0px -8% 0px', threshold: 0.12 });
    targets.forEach((el) => io.observe(el));
  }

  // ---------- Orchestrate ----------
  function startHero() {
    syncOverlayToSlide(activeIndex);
    revealContent();
    startAutoAdvance();

    html.classList.remove('hero-loading');
    html.classList.add('hero-done');
    window.dispatchEvent(new CustomEvent('hero:complete'));
  }

  async function run() {
    initSectionReveal();
    initAboutVideo();
    initNavbarScroll();

    await preloadAll();

    if (splashPending()) {
      window.addEventListener('splash:complete', startHero, { once: true });
    } else {
      startHero();
    }
  }

  function splashPending() {
    const splashContainer = document.getElementById('hero-preloader');
    return !!splashContainer && !splashContainer.classList.contains('is-hidden');
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', run, { once: true });
  } else {
    run();
  }
})();
