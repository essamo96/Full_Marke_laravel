/* landing.js */
document.addEventListener('DOMContentLoaded', () => {
  // 1. Sticky Header Scroll Effect
  const header = document.getElementById('header-nav');
  
  let isScrolling = false;
  function handleScroll() {
    if (window.scrollY > 50) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
    isScrolling = false;
  }

  window.addEventListener('scroll', () => {
    if (!isScrolling) {
      window.requestAnimationFrame(handleScroll);
      isScrolling = true;
    }
  }, { passive: true });
  handleScroll(); // Init on page load

  // 2. Mobile Menu Drawer Controls
  const menuToggleBtn = document.getElementById('mobileMenuToggle');
  const mobileNavMenu = document.getElementById('mobileNavMenu');
  const backdrop = document.getElementById('mobileMenuBackdrop');
  const closeBtn = document.getElementById('closeMobileMenu');

  function openMobileMenu() {
    mobileNavMenu.classList.add('active');
    backdrop.classList.add('active');
    document.body.style.overflow = 'hidden'; // Lock body scroll
  }

  function closeMobileMenu() {
    mobileNavMenu.classList.remove('active');
    backdrop.classList.remove('active');
    document.body.style.overflow = ''; // Unlock body scroll
  }

  if (menuToggleBtn && mobileNavMenu && backdrop) {
    menuToggleBtn.addEventListener('click', openMobileMenu);
    backdrop.addEventListener('click', closeMobileMenu);
    if (closeBtn) closeBtn.addEventListener('click', closeMobileMenu);
  }

  // Close mobile menu on clicking any link
  const mobileLinks = document.querySelectorAll('#mobileNavMenu a');
  mobileLinks.forEach(link => {
    link.addEventListener('click', closeMobileMenu);
  });

  // 3. Smooth Anchor Scrolling
  // These links (e.g. #programs) are sections that only exist on the home page.
  // When clicked from another page (login, register, ...) there's no matching
  // element here, so instead of silently doing nothing we navigate to the home
  // page with the hash attached, landing the user on that section there.
  const anchorLinks = document.querySelectorAll('a[href^="#"]');
  anchorLinks.forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const targetId = this.getAttribute('href');
      if (targetId === '#') return; // ignore top-of-page anchors

      const targetElement = document.querySelector(targetId);

      if (!targetElement) {
        if (window.SITE_HOME_URL) {
          e.preventDefault();
          window.location.href = window.SITE_HOME_URL + targetId;
        }
        return;
      }

      e.preventDefault();

      // Calculate header offset height
      const headerHeight = header.offsetHeight;
      const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - headerHeight;

      window.scrollTo({
        top: targetPosition,
        behavior: 'smooth'
      });

      // Update URL hash (without jumping)
      history.pushState(null, null, targetId);
    });
  });

  // 4. Set Active Navigation Item on Scroll
  const sections = document.querySelectorAll('section[id]');
  const navLinks = document.querySelectorAll('.desktop-nav-link');

  let isNavScrolling = false;
  function highlightNavigation() {
    const scrollPos = window.scrollY + 120; // threshold
    
    sections.forEach(section => {
      const sectionTop = section.offsetTop;
      const sectionHeight = section.offsetHeight;
      const sectionId = section.getAttribute('id');
      
      if (scrollPos >= sectionTop && scrollPos < sectionTop + sectionHeight) {
        navLinks.forEach(link => {
          link.classList.remove('active');
          if (link.getAttribute('href') === `#${sectionId}`) {
            link.classList.add('active');
          }
        });
      }
    });
    isNavScrolling = false;
  }

  window.addEventListener('scroll', () => {
    if (!isNavScrolling) {
      window.requestAnimationFrame(highlightNavigation);
      isNavScrolling = true;
    }
  }, { passive: true });
  highlightNavigation(); // Run on load

  // 5. Priority+ overflow — nav links that don't fit the available width
  //    collapse into a "More" dropdown (any screen size where the inline
  //    navbar is visible, iPad included).
  const primaryNav = document.getElementById('primary-nav');
  if (primaryNav) {
    const inlineLinks = Array.from(primaryNav.querySelectorAll(':scope > .desktop-nav-link'));

    const moreWrap = document.createElement('div');
    moreWrap.className = 'nav-more-wrap dropdown';
    moreWrap.style.display = 'none';

    const moreBtn = document.createElement('button');
    moreBtn.type = 'button';
    moreBtn.className = 'btn btn-glass nav-more-btn dropdown-toggle';
    moreBtn.setAttribute('aria-expanded', 'false');
    const isRtl = document.documentElement.dir === 'rtl';
    moreBtn.innerHTML = '<span data-en="More" data-ar="المزيد">' + (isRtl ? 'المزيد' : 'More') + '</span>';

    const moreMenu = document.createElement('div');
    moreMenu.className = 'dropdown-menu nav-more-menu';

    moreWrap.appendChild(moreBtn);
    moreWrap.appendChild(moreMenu);
    primaryNav.appendChild(moreWrap);

    // The nav uses overflow:hidden for the measurement/clipping, which would
    // also clip an absolutely-positioned dropdown — so the menu opens with
    // position:fixed, anchored to the button's on-screen position.
    function positionMoreMenu() {
      const r = moreBtn.getBoundingClientRect();
      moreMenu.style.position = 'fixed';
      moreMenu.style.top = (r.bottom + 8) + 'px';
      if (isRtl) {
        moreMenu.style.left = r.left + 'px';
        moreMenu.style.right = 'auto';
      } else {
        moreMenu.style.right = (window.innerWidth - r.right) + 'px';
        moreMenu.style.left = 'auto';
      }
    }

    function closeMoreMenu() {
      moreMenu.classList.remove('show');
      moreBtn.setAttribute('aria-expanded', 'false');
    }

    moreBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      const open = !moreMenu.classList.contains('show');
      if (open) positionMoreMenu();
      moreMenu.classList.toggle('show', open);
      moreBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    document.addEventListener('click', closeMoreMenu);
    moreMenu.addEventListener('click', closeMoreMenu);
    window.addEventListener('scroll', closeMoreMenu, { passive: true });

    let rebuilding = false;
    function rebuildNavOverflow() {
      if (rebuilding) return;
      rebuilding = true;

      // Restore every link inline, in original order, then measure
      inlineLinks.forEach(link => primaryNav.insertBefore(link, moreWrap));
      moreMenu.classList.remove('show');
      moreWrap.style.display = 'none';

      if (window.getComputedStyle(primaryNav).display !== 'none') {
        if (primaryNav.scrollWidth > primaryNav.clientWidth + 2) {
          moreWrap.style.display = '';
          // Move links into the menu from the end until the rest fits
          for (let i = inlineLinks.length - 1; i >= 1; i--) {
            moreMenu.insertBefore(inlineLinks[i], moreMenu.firstChild);
            if (primaryNav.scrollWidth <= primaryNav.clientWidth + 2) break;
          }
        }
      }

      rebuilding = false;
    }

    let resizeTimer = null;
    window.addEventListener('resize', () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(rebuildNavOverflow, 120);
    });
    window.addEventListener('load', rebuildNavOverflow);
    document.addEventListener('languageChanged', () => setTimeout(rebuildNavOverflow, 100));
    rebuildNavOverflow();
  }

  // 6. Tablet "tools" dropdown — on 768–1199px the header action buttons live
  //    inside a panel toggled by one button. The buttons are the same DOM
  //    nodes as on desktop, so all their handlers keep working.
  const toolsWrap = document.querySelector('.header-tools-wrap');
  const toolsBtn = document.getElementById('headerToolsBtn');
  if (toolsWrap && toolsBtn) {
    function closeTools() {
      toolsWrap.classList.remove('open');
      toolsBtn.setAttribute('aria-expanded', 'false');
    }

    toolsBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      const open = toolsWrap.classList.toggle('open');
      toolsBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    // Close when clicking outside the panel
    document.addEventListener('click', (e) => {
      if (!toolsWrap.contains(e.target)) closeTools();
    });

    // Close after using a button inside — except the theme cycler, so the
    // user can flip through themes and see the result live.
    toolsWrap.addEventListener('click', (e) => {
      const actionable = e.target.closest('a, button');
      if (!actionable || actionable === toolsBtn) return;
      if (actionable.id === 'themeCycleBtn') return;
      if (actionable.hasAttribute('data-bs-toggle')) return; // opening the profile submenu
      closeTools();
    });

    // Leaving the tablet range resets the state
    window.addEventListener('resize', () => {
      if (window.innerWidth >= 1200 || window.innerWidth < 768) closeTools();
    });
  }
});

// Global function to show a welcome toaster on login
window.showWelcomeToast = function(name, role) {
  const lang = document.documentElement.lang || 'ar';
  
  // Default values based on role and language
  let displayRole = '';
  if (role === 'teacher') {
    displayRole = lang === 'ar' ? 'المعلم' : 'Teacher';
  } else if (role === 'student') {
    displayRole = lang === 'ar' ? 'الطالب' : 'Student';
  } else {
    displayRole = role; // fallback
  }

  const welcomeText = lang === 'ar' ? 'أهلاً بك يا' : 'Welcome';
  
  // Create toast container
  let toast = document.createElement('div');
  toast.id = 'login-welcome-toast';
  
  // Styling the toast (from right to left sliding)
  toast.style.cssText = `
    position: fixed;
    top: 100px;
    right: -400px; /* Start off-screen right */
    background: var(--bg-secondary, rgba(13,10,6,0.95));
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--accent-color, #c5a880);
    border-left: 4px solid var(--accent-color, #c5a880);
    border-radius: 8px;
    padding: 1.25rem 1.5rem;
    box-shadow: -10px 10px 30px rgba(0,0,0,0.4), 0 0 20px rgba(197,168,128,0.1) inset;
    z-index: 9999;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: right 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275), opacity 0.5s ease;
    opacity: 0;
  `;

  // Fix border direction for LTR if needed
  if (lang === 'en') {
    toast.style.direction = 'ltr';
  } else {
    toast.style.direction = 'rtl';
    toast.style.borderLeft = '1px solid var(--accent-color, #c5a880)';
    toast.style.borderRight = '4px solid var(--accent-color, #c5a880)';
  }

  // Toast inner content
  const iconHtml = `<i class="bi bi-person-check-fill" style="font-size: 1.8rem; color: var(--accent-color, #c5a880);"></i>`;
  const textHtml = `
    <div style="display: flex; flex-direction: column;">
      <span style="font-size: 0.85rem; color: var(--text-muted, #8c8276); font-weight: 600;">${displayRole}</span>
      <span style="font-size: 1.1rem; color: var(--text-primary, #fff); font-weight: 700; font-family: var(--font-${lang}, 'Tajawal', sans-serif);">${welcomeText} ${name}</span>
    </div>
  `;

  toast.innerHTML = iconHtml + textHtml;
  document.body.appendChild(toast);

  // Trigger animation (slide in from right)
  requestAnimationFrame(() => {
    setTimeout(() => {
      toast.style.right = '20px';
      toast.style.opacity = '1';
    }, 50);
  });

  // Automatically remove after 3 seconds
  setTimeout(() => {
    toast.style.right = '-400px';
    toast.style.opacity = '0';
    setTimeout(() => {
      if (document.body.contains(toast)) {
        document.body.removeChild(toast);
      }
    }, 500);
  }, 3000);
};

