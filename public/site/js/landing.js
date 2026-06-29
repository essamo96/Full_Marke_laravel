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

