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

  // Check if we should skip splash
  if (sessionStorage.getItem(SESSION_KEY)) {
    splashContainer.remove();
    return;
  }

  const words = splashContainer.querySelectorAll('.splash-word');
  const logoWrap = splashContainer.querySelector('.hero-splash__logo-wrap');

  function startAnimationSequence() {
    // 1. Show words sequentially
    words.forEach((word, index) => {
      setTimeout(() => {
        word.classList.add('is-active');
      }, index * WORD_DELAY_MS);
    });

    // 2. Hide words and show logo
    setTimeout(() => {
      words.forEach(word => {
        word.classList.remove('is-active');
        word.classList.add('is-hidden');
      });
      if (logoWrap) {
        logoWrap.classList.add('is-visible');
      }
    }, LOGO_APPEAR_MS);

    // 3. Hide entire splash screen
    setTimeout(() => {
      splashContainer.classList.add('is-hidden');
      sessionStorage.setItem(SESSION_KEY, '1');
      
      // Remove from DOM after fade out transition completes
      setTimeout(() => splashContainer.remove(), 900);
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
        sessionStorage.setItem(SESSION_KEY, '1');
        setTimeout(() => splashContainer.remove(), 900);
      }
    }, 6000);
  }
})();
