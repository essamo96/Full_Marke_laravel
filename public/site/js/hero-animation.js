/* ============================================================
   Hero looping intro:
   1) video1 (slider1.mp4) → crossfade → video2 (ezgif-...mp4)
   2) ~2 frames before video2 ends, the 4K still image fades in
   3) Hero content reveals with staggered delays
   4) After 20s of content visible, content fades out and the
      whole sequence loops indefinitely (like a slider).
   Also:
   - Particles wait for the first 'hero:complete' before starting.
   - Single scroll-track, scrollbar appears only when hero is done.
   ============================================================ */
(() => {
  'use strict';

  const CROSSFADE_LEAD_SEC = 0.9;         // start video2 this many s before video1 ends
  const STILL_LEAD_FRAMES = 2;           // show still N frames before video2 ends
  const ASSUMED_FPS = 30;          // for the "frames" calculation
  const CONTENT_VISIBLE_MS = 20000;       // 20 s before looping
  const SAFETY_TIMEOUT_MS = 25000;
  const REDUCED_MOTION = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Note: the global splash (#hero-preloader) is owned by site-splash.js —
  // this file only drives the homepage's looping background video intro.
  const html = document.documentElement;
  const v1 = document.getElementById('hero-bg-video-1');
  const v2 = document.getElementById('hero-bg-video-2');
  const still = document.getElementById('hero-bg-still');
  const overlay = document.getElementById('hero-content-overlay');

  if (!v1 || !v2 || !overlay) return;

  // ---------- Swap video sources to portrait versions on mobile -----------
  // Mobile encoded copies (720x1280) with a blurred-fill background fix
  // the cropping problem on portrait screens.
  function pickResponsiveSources() {
    const isMobile = window.matchMedia('(max-width: 768px)').matches;
    const map = {
      'hero-bg-video-1': isMobile
        ? '/site/images/slider1.mp4'
        : '/site/images/slider1.mp4',
      'hero-bg-video-2': isMobile
        ? '/site/images/slider2_mobile.mp4'
        : '/site/images/slider2.mp4',
      'about-video': isMobile
        ? '/site/images/aboutUs_mobile.mp4'
        : '/site/images/aboutUs.mp4',
    };
    Object.entries(map).forEach(([id, src]) => {
      const el = document.getElementById(id);
      if (!el) return;
      // Compare fully resolved URLs to avoid redundant calls to load()
      const targetSrc = new URL(src, window.location.origin).href;
      if (el.src === targetSrc) return;

      const applySource = () => {
        el.setAttribute('src', src);
        try { el.load(); } catch (_) { }
      };

      // Don't reload a video mid-playback (e.g. on orientation change) —
      // that causes a visible stutter/black-frame. Defer until it pauses.
      if (el.paused) {
        applySource();
      } else {
        el.addEventListener('pause', applySource, { once: true });
      }
    });
  }
  pickResponsiveSources();
  // React to orientation / resize crossing the mobile breakpoint
  const mqMobile = window.matchMedia('(max-width: 768px)');
  if (mqMobile.addEventListener) mqMobile.addEventListener('change', pickResponsiveSources);
  else if (mqMobile.addListener) mqMobile.addListener(pickResponsiveSources);


  html.classList.add('hero-loading');

  // ---------- Preload helpers ----------
  function whenVideoReady(v) {
    return new Promise((resolve) => {
      if (v.readyState >= 3) return resolve();
      const done = () => resolve();
      v.addEventListener('canplay', done, { once: true });
      v.addEventListener('loadeddata', done, { once: true });
      setTimeout(done, SAFETY_TIMEOUT_MS);
    });
  }
  function whenImageReady(img) {
    return new Promise((resolve) => {
      if (img.complete && img.naturalWidth) return resolve();
      img.addEventListener('load', resolve, { once: true });
      img.addEventListener('error', resolve, { once: true });
      setTimeout(resolve, SAFETY_TIMEOUT_MS);
    });
  }

  function preloadAll() {
    return Promise.all([
      whenVideoReady(v1),
      whenVideoReady(v2),
      whenImageReady(still),
    ]);
  }

  // ---------- One full intro cycle ----------
  function resetLayers() {
    // Re-stage for a fresh cycle.
    v1.classList.add('is-active');
    v2.classList.remove('is-active');
    still.classList.remove('is-active');
    try {
      v1.pause(); v1.currentTime = 0;
      v2.pause(); v2.currentTime = 0;
    } catch (_) { }
  }

  function playCycle() {
    return new Promise((resolve) => {
      resetLayers();

      let safetyTimeout = null;

      // ----- Video1 -> Video2 -----
      let v1HandedOff = false;
      const handoffToV2 = () => {
        if (v1HandedOff) return;
        v1HandedOff = true;
        if (safetyTimeout) {
          clearTimeout(safetyTimeout);
          safetyTimeout = null;
        }
        try { v2.currentTime = 0; } catch (_) { }
        const p = v2.play();
        if (p && p.catch) {
          p.catch((err) => {
            console.warn("Autoplay blocked for video 2. Waiting for interaction to play it.", err);

            const forcePlayV2 = () => {
              v2.play().catch(() => { });
              document.removeEventListener('click', forcePlayV2);
              document.removeEventListener('touchstart', forcePlayV2);
              document.removeEventListener('scroll', forcePlayV2);
            };

            document.addEventListener('click', forcePlayV2, { once: true });
            document.addEventListener('touchstart', forcePlayV2, { once: true });
            document.addEventListener('scroll', forcePlayV2, { once: true });
          });
        }

        // Wait for v2 to actually be playing before hiding v1 to avoid black screen
        let v2Started = false;
        const completeHandoff = () => {
          if (v2Started) return;
          v2Started = true;
          v2.classList.add('is-active');
          v1.classList.remove('is-active');
        };

        v2.addEventListener('playing', completeHandoff, { once: true });
        // Fallback if event fails
        setTimeout(completeHandoff, 500);

        scheduleStillReveal();
      };

      v1.addEventListener('ended', handoffToV2, { once: true });

      // ----- Show still when video2 ends -----
      let stillShown = false;
      const showStill = () => {
        if (stillShown) return;
        stillShown = true;
        still.classList.add('is-active');
        v2.classList.remove('is-active');
      };

      const scheduleStillReveal = () => {
        v2.addEventListener('ended', () => {
          showStill();
          setTimeout(resolve, 700);
        }, { once: true });
        
        // Hard safety net (e.g. tab throttled).
        setTimeout(() => { showStill(); resolve(); },
          (isFinite(v2.duration) && v2.duration > 0 ? v2.duration * 1000 + 2000 : 10000));
      };

      // Start video1
      const p1 = v1.play();
      if (p1 && p1.catch) {
        p1.catch((err) => {
          console.warn("Autoplay blocked. Waiting for interaction to play video.", err);
          
          const forcePlay = () => {
            v1.play().catch(() => {});
            document.removeEventListener('click', forcePlay);
            document.removeEventListener('touchstart', forcePlay);
            document.removeEventListener('scroll', forcePlay);
          };
          
          document.addEventListener('click', forcePlay, { once: true });
          document.addEventListener('touchstart', forcePlay, { once: true });
          document.addEventListener('scroll', forcePlay, { once: true });
        });
      }

      // Hard safety net: if video 1 is blocked/frozen for a very long time
      safetyTimeout = setTimeout(() => {
        if (!v1HandedOff && v1.paused) {
          console.warn("Video 1 safety timeout reached. Skipping to next layer.");
          handoffToV2();
        }
      }, 15000); // give it plenty of time
    });
  }

  // ---------- Content reveal/hide ----------
  function revealContent() {
    overlay.classList.remove('is-hiding');
    overlay.classList.add('is-revealed');
    // Re-enable particles fade-in
    const pc = document.getElementById('particles-canvas');
    if (pc) pc.classList.remove('is-fading-out');
  }
  function hideContent() {
    // Trigger the REVERSE staggered fade-out
    overlay.classList.add('is-hiding');
    overlay.classList.remove('is-revealed');
    // Fade particles out alongside the content
    const pc = document.getElementById('particles-canvas');
    if (pc) pc.classList.add('is-fading-out');
  }

  // ---------- About-section looping video: play in view, pause out of view ----------
  function initAboutVideo() {
    const aboutVid = document.getElementById('about-video');
    if (!aboutVid) return;
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

  // ---------- Loop driver ----------
  let firstCycleDone = false;
  async function loopForever() {
    while (true) {
      await playCycle();          // video1 → video2 → still
      revealContent();            // staggered reveal kicks in

      // Unlock the page on the very first cycle so the user can scroll.
      if (!firstCycleDone) {
        firstCycleDone = true;
        html.classList.remove('hero-loading');
        html.classList.add('hero-done');
        window.dispatchEvent(new CustomEvent('hero:complete'));
      }

      // Hold the still + content for 20 s, then hide content and restart.
      await new Promise((r) => setTimeout(r, CONTENT_VISIBLE_MS));
      hideContent();
      // Reverse-stagger fade-out: last delay (0.60s) + transition (1.1s) ≈ 1.8s
      // Add a small visual breath before videos restart.
      await new Promise((r) => setTimeout(r, 2000));
    }
  }

  // ---------- Orchestrate ----------
  async function run() {
    initSectionReveal();
    initAboutVideo();
    initNavbarScroll();

    preloadAll();

    if (REDUCED_MOTION) {
      // Skip videos entirely.
      still.classList.add('is-active');
      revealContent();
      html.classList.remove('hero-loading');
      html.classList.add('hero-done');
      window.dispatchEvent(new CustomEvent('hero:complete'));
      return;
    }

    const startLoop = () => {
      loopForever();
    };

    const splashContainer = document.getElementById('hero-preloader');
    if (splashContainer && !splashContainer.classList.contains('is-hidden')) {
      window.addEventListener('splash:complete', startLoop, { once: true });
    } else {
      startLoop();
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', run, { once: true });
  } else {
    run();
  }
})();
