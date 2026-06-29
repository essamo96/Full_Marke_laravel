/* theme-manager.js */
(function () {
  // Available themes
  const THEMES = ['theme-dark', 'theme-light', 'theme-gold'];
  const STORAGE_KEY = 'full-mark-academy-theme';
  const DEFAULT_THEME = 'theme-gold';

  // Apply theme to HTML tag
  function applyTheme(themeName) {
    THEMES.forEach(t => document.documentElement.classList.remove(t));
    document.documentElement.classList.add(themeName);
    localStorage.setItem(STORAGE_KEY, themeName);
    
    // Dispatch event for other scripts to listen
    const event = new CustomEvent('themechanged', { detail: { theme: themeName } });
    window.dispatchEvent(event);
    
    // Update theme switcher UI if exists
    updateThemeUI(themeName);
    updateBrandLogos(themeName);
  }

  // Update brand logos and text next to them based on active theme
  function updateBrandLogos(themeName) {
    const isLight = themeName === 'theme-light';

    // Header logo text brand swap (OXFORD vs FULL MARK)
    const navbarText = document.querySelector('.logo-text');
    if (navbarText) {
      const brandWord = isLight ? 'FULL MARK ' : 'FULL MARK ';
      if (navbarText.firstChild && navbarText.firstChild.nodeType === Node.TEXT_NODE) {
        navbarText.firstChild.textContent = brandWord;
      }
    }

    // Footer logo text brand swap
    const footerText = document.getElementById('footer-logo-text');
    if (footerText) {
      const brandWord = isLight ? 'FULL MARK ' : 'FULL MARK ';
      if (footerText.firstChild && footerText.firstChild.nodeType === Node.TEXT_NODE) {
        footerText.firstChild.textContent = brandWord;
      }
    }

    // Update logo images based on theme
    let logoSrc = '/site/images/logo_v2_gold.png';
    if (themeName === 'theme-dark' || themeName === 'theme-light') {
      logoSrc = '/site/images/logo_v2_blue.png';
    }

    const logoIds = ['navbar-logo', 'hero-splash-logo', 'footer-logo', 'footer-approved-logo'];
    logoIds.forEach(id => {
      const img = document.getElementById(id);
      if (img) img.src = logoSrc;
    });
  }

  // Get active theme
  function getActiveTheme() {
    const saved = localStorage.getItem(STORAGE_KEY);
    return saved && THEMES.includes(saved) ? saved : DEFAULT_THEME;
  }

  // Icon shown on the cycle button = the icon for the CURRENT theme.
  const THEME_ICON = {
    'theme-dark':  'bi-moon-stars-fill',
    'theme-light': 'bi-sun-fill',
    'theme-gold':  'bi-award-fill'
  };

  function updateThemeUI(activeTheme) {
    const iconClass = THEME_ICON[activeTheme] || 'bi-award-fill';
    document.querySelectorAll('#themeCycleBtn, #themeCycleBtnMobile').forEach(btn => {
      btn.innerHTML = `<i class="bi ${iconClass}"></i>`;
      btn.setAttribute('data-current-theme', activeTheme);
    });
  }

  // Cycle to the next theme in THEMES order.
  function cycleTheme() {
    const current = getActiveTheme();
    const next = THEMES[(THEMES.indexOf(current) + 1) % THEMES.length];
    applyTheme(next);
  }

  // Initialize immediately on load
  const initialTheme = getActiveTheme();
  applyTheme(initialTheme);

  // Bind events when DOM is loaded
  document.addEventListener('DOMContentLoaded', () => {
    updateThemeUI(initialTheme);
    updateBrandLogos(initialTheme);

    // Listen to language changes to update logos
    window.addEventListener('languageChanged', () => {
      updateBrandLogos(getActiveTheme());
    });

    document.querySelectorAll('#themeCycleBtn, #themeCycleBtnMobile').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        cycleTheme();
      });
    });
  });

  // Expose function globally
  window.ThemeManager = {
    applyTheme,
    cycleTheme,
    getActiveTheme
  };
})();
