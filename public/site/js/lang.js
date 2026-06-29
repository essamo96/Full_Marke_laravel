// js/lang.js

function toggleLanguage() {
  const html = document.documentElement;
  const isRtl = html.dir === 'rtl';
  
  html.dir = isRtl ? 'ltr' : 'rtl';
  html.lang = isRtl ? 'en' : 'ar';
  
  // Translate all elements with data-en and data-ar attributes
  document.querySelectorAll('[data-en][data-ar]').forEach(el => {
    // If it's an input placeholder
    if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
        if (el.hasAttribute('placeholder')) {
            el.setAttribute('placeholder', isRtl ? el.getAttribute('data-en') : el.getAttribute('data-ar'));
        }
    } else {
        el.textContent = isRtl ? el.getAttribute('data-en') : el.getAttribute('data-ar');
    }
  });

  // Dispatch custom event for other scripts to respond
  window.dispatchEvent(new CustomEvent('languageChanged', { 
    detail: { dir: html.dir, lang: html.lang } 
  }));
}

// Optionally initialize if the HTML is set to something specific
document.addEventListener('DOMContentLoaded', () => {
    const html = document.documentElement;
    if (html.lang === 'ar' || html.dir === 'rtl') {
        html.dir = 'rtl';
        html.lang = 'ar';
        document.querySelectorAll('[data-en][data-ar]').forEach(el => {
            if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
                if (el.hasAttribute('placeholder')) {
                    el.setAttribute('placeholder', el.getAttribute('data-ar'));
                }
            } else {
                el.textContent = el.getAttribute('data-ar');
            }
        });
    } else {
        html.dir = 'ltr';
        html.lang = 'en';
        document.querySelectorAll('[data-en][data-ar]').forEach(el => {
            if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
                if (el.hasAttribute('placeholder')) {
                    el.setAttribute('placeholder', el.getAttribute('data-en'));
                }
            } else {
                el.textContent = el.getAttribute('data-en');
            }
        });
    }
});
