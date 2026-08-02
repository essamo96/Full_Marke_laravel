/* language-manager.js */
(function() {
  function applyLanguage(lang) {
    const htmlElement = document.documentElement;
    if (lang === 'ar') {
      htmlElement.setAttribute('dir', 'rtl');
      htmlElement.setAttribute('lang', 'ar');
      document.body.style.fontFamily = 'var(--font-ar)';
    } else {
      htmlElement.setAttribute('dir', 'ltr');
      htmlElement.setAttribute('lang', 'en');
      document.body.style.fontFamily = 'var(--font-en)';
    }

    document.querySelectorAll('[data-en][data-ar]').forEach(element => {
      const textVal = element.getAttribute(`data-${lang}`);
      if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
        element.setAttribute('placeholder', textVal);
      } else if (element.tagName === 'OPTION') {
        element.textContent = textVal;
      } else {
        element.textContent = textVal;
      }
      // Reveal — see the matching visibility:hidden rule in layouts/site.blade.php.
      element.style.visibility = 'visible';
    });

    window.dispatchEvent(new CustomEvent('languageChanged', { detail: { lang } }));
  }

  window.LanguageManager = {
    applyLanguage: applyLanguage
  };

  document.addEventListener('DOMContentLoaded', () => {
    if (window.currentLang) {
      applyLanguage(window.currentLang);
    }
  });
})();
