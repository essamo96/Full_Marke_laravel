/* ============================================================
   Global site splash — shown once per browser session (sessionStorage),
   Sequentially animates motivational words then reveals the logo.
   ============================================================ */
(() => {
  'use strict';

  const SESSION_KEY = 'fma_splash_shown';
  const TOTAL_SPLASH_TIME_MS = 2500; // total duration of splash before fading out
  const WORD_DELAY_MS = 300;         // delay between each word appearing
  const LOGO_APPEAR_MS = 1300;       // when to hide words and show logo

  const splashContainer = document.getElementById('hero-preloader');
  if (!splashContainer) return;

  // Instead of sessionStorage, check if the user navigated from within our site.
  // If they came from another page on this site, skip the splash screen.
  const isInternal = document.referrer && document.referrer.includes(window.location.hostname);
  
  if (isInternal) {
    splashContainer.remove();
    setTimeout(() => window.dispatchEvent(new CustomEvent('splash:complete')), 50);
    return;
  }

  const words = splashContainer.querySelectorAll('.splash-word');
  const logoWrap = splashContainer.querySelector('.hero-splash__logo-wrap');

  // Timings: Each word appears and disappears individually
  const WORD_DURATION_MS = 800; // Time each word stays visible
  const LOGO_DURATION_MS = 4000; // Logo stays for 4 seconds

  function startAnimationSequence() {
    let currentDelay = 0;

    // 1. Show words sequentially (one by one in the same spot)
    words.forEach((word) => {
      // Show word
      setTimeout(() => {
        word.classList.add('is-active');
        word.classList.remove('is-hidden');
      }, currentDelay);

      // Hide word
      setTimeout(() => {
        word.classList.remove('is-active');
        word.classList.add('is-hidden');
      }, currentDelay + WORD_DURATION_MS - 100);

      currentDelay += WORD_DURATION_MS;
    });

    // 2. Hide all words (just in case) and show logo
    setTimeout(() => {
      words.forEach(word => {
        word.classList.remove('is-active');
        word.classList.add('is-hidden');
      });
      if (logoWrap) {
        logoWrap.classList.add('is-visible');
      }
    }, currentDelay);

    // 3. Hide entire splash screen after 4 seconds of logo
    const TOTAL_SPLASH_TIME_MS = currentDelay + LOGO_DURATION_MS;
    setTimeout(() => {
      splashContainer.classList.add('is-hidden');
      
      // Remove from DOM after fade out transition completes
      setTimeout(() => {
        splashContainer.remove();
        window.dispatchEvent(new CustomEvent('splash:complete'));
      }, 900);
    }, TOTAL_SPLASH_TIME_MS);
  }

  // Ensure DOM is ready, then wait a tiny bit to let CSS load properly before starting
  if (document.readyState === 'complete') {
    requestAnimationFrame(startAnimationSequence);
  } else {
    window.addEventListener('load', () => requestAnimationFrame(startAnimationSequence), { once: true });
    // Safety net
    setTimeout(() => {
      if (!splashContainer.classList.contains('is-hidden')) {
        splashContainer.classList.add('is-hidden');
        setTimeout(() => {
            splashContainer.remove();
            window.dispatchEvent(new CustomEvent('splash:complete'));
        }, 900);
      }
    }, (3 * WORD_DURATION_MS) + LOGO_DURATION_MS + 1000);
  }
})();
