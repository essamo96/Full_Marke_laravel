
document.addEventListener('DOMContentLoaded', () => {
  const triggers = document.querySelectorAll('.sidebar-nav-item.accordion-trigger');
  
  triggers.forEach(trigger => {
    trigger.addEventListener('click', (e) => {
      e.preventDefault(); // prevent jumping
      const targetId = trigger.getAttribute('href').substring(1);
      const targetMenu = document.getElementById(targetId);
      const isExpanded = trigger.getAttribute('aria-expanded') === 'true';

      // Close all others
      triggers.forEach(other => {
        if (other !== trigger) {
          other.setAttribute('aria-expanded', 'false');
          const otherTargetId = other.getAttribute('href').substring(1);
          const otherMenu = document.getElementById(otherTargetId);
          if (otherMenu) otherMenu.classList.remove('expanded');
        }
      });

      // Toggle current
      if (isExpanded) {
        trigger.setAttribute('aria-expanded', 'false');
        targetMenu.classList.remove('expanded');
      } else {
        trigger.setAttribute('aria-expanded', 'true');
        targetMenu.classList.add('expanded');
      }
    });
  });

  // Active state handling
  const currentPath = window.location.pathname.split('/').pop() || 'dashboard.html';
  const activeSubLink = document.querySelector(`.sidebar-submenu-item[href="${currentPath}"]`) || document.querySelector(`.sidebar-submenu-item[href^="${currentPath}#"]`);
  
  if (activeSubLink) {
    activeSubLink.classList.add('active');
    const parentMenu = activeSubLink.closest('.sidebar-submenu-wrapper');
    if (parentMenu) {
      parentMenu.classList.add('expanded');
      const triggerId = parentMenu.getAttribute('id');
      const trigger = document.querySelector(`.sidebar-nav-item[href="#${triggerId}"]`);
      if (trigger) trigger.setAttribute('aria-expanded', 'true');
    }
  } else {
    const activeMainLink = document.querySelector(`.sidebar-nav-item[href="${currentPath}"]`);
    if (activeMainLink) activeMainLink.classList.add('active');
  }
  
  // STITCH MODAL LOGIC
  window.openStitchModal = function(title, content) {
    let modal = document.getElementById('stitchModal');
    if (!modal) {
      modal = document.createElement('div');
      modal.id = 'stitchModal';
      modal.className = 'stitch-modal-overlay';
      modal.innerHTML = `
        <div class="stitch-modal-content glass-panel tilt-card">
          <div class="stitch-modal-header d-flex justify-content-between align-items-center">
            <h5 class="fw-bold m-0" style="color: var(--accent-color);" id="stitchModalTitle"></h5>
            <button class="btn btn-glass icon-btn rounded-circle" onclick="closeStitchModal()"><i class="bi bi-x-lg"></i></button>
          </div>
          <div class="stitch-modal-body" id="stitchModalBody"></div>
        </div>
      `;
      document.body.appendChild(modal);
    }
    document.getElementById('stitchModalTitle').innerHTML = title;
    document.getElementById('stitchModalBody').innerHTML = content;
    
    // Animate in
    requestAnimationFrame(() => {
      modal.classList.add('active');
    });
  };

  window.closeStitchModal = function() {
    const modal = document.getElementById('stitchModal');
    if (modal) {
      modal.classList.remove('active');
      setTimeout(() => { modal.remove(); }, 300); // Wait for CSS transition
    }
  };

  // SWEETALERT2 WELCOME NOTIFICATION
  if (!sessionStorage.getItem('welcomeAlertShown')) {
    sessionStorage.setItem('welcomeAlertShown', 'true');
    
    // Dynamically load SweetAlert2
    const swalScript = document.createElement('script');
    swalScript.src = "https://cdn.jsdelivr.net/npm/sweetalert2@11";
    swalScript.onload = () => {
      // Determine user type based on URL path
      const isTeacher = window.location.pathname.includes('/teacher/');
      const userName = document.querySelector('.dashboard-header span.fw-semibold')?.textContent || (isTeacher ? 'Dr. Sarah' : 'Ahmad Ali');
      const role = isTeacher ? 'Teacher' : 'Student';
      const roleAr = isTeacher ? 'المعلم' : 'الطالب';
      const titleEn = `Welcome back, ${userName}!`;
      const titleAr = `مرحباً بعودتك، ${userName}!`;
      
      const isRtl = document.documentElement.dir === 'rtl';
      const title = isRtl ? titleAr : titleEn;
      const text = isRtl ? `تم تسجيل الدخول بنجاح إلى بوابة ${roleAr}` : `Successfully logged into the ${role} portal`;

      // Play Sound (using a subtle, open-source notification sound URL)
      const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
      audio.volume = 0.5;
      
      // We must catch play errors since browsers block auto-play without user interaction
      audio.play().catch(e => console.log('Audio autoplay blocked until user interacts.'));

      Swal.fire({
        toast: true,
        position: isRtl ? 'top-start' : 'top-end',
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true,
        title: title,
        text: text,
        icon: 'success',
        background: 'var(--bg-secondary)',
        color: 'var(--text-primary)',
        customClass: {
          popup: 'glass-panel border border-accent',
          title: 'fw-bold',
        }
      });
    };
    document.body.appendChild(swalScript);
  }

  // DYNAMIC STITCH MODALS BINDING FOR ACTION BUTTONS
  // This targets buttons that look like actions but don't have hrefs
  const actionButtons = document.querySelectorAll('.dashboard-main button.btn, .dashboard-main .dropdown-item');
  actionButtons.forEach(btn => {
    // Only bind if it doesn't already have an onclick or data-bs-toggle and isn't a form submit
    if (!btn.hasAttribute('onclick') && !btn.hasAttribute('data-bs-toggle') && btn.type !== 'submit') {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        
        const btnText = btn.textContent.trim();
        if (!btnText) return; // Skip icon-only buttons
        
        // Find parent context
        const parentCard = btn.closest('.glass-panel');
        let contextTitle = '';
        if (parentCard) {
          const heading = parentCard.querySelector('h5, h6');
          if (heading) contextTitle = heading.textContent.trim();
        }
        
        const modalTitle = btnText;
        const modalContent = `
          <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 60px; height: 60px; background: rgba(197, 168, 128, 0.1); color: var(--accent-color);">
              <i class="bi bi-layers fs-3"></i>
            </div>
            <h5 class="fw-bold mb-2" style="color: var(--text-primary);">${btnText}</h5>
            ${contextTitle ? `<p class="text-sm opacity-75">Context: <strong style="color: var(--accent-color);">${contextTitle}</strong></p>` : ''}
          </div>
          <form onsubmit="event.preventDefault(); closeStitchModal();">
            <div class="mb-3">
              <label class="form-label text-sm opacity-75">Additional Notes</label>
              <textarea class="form-control bg-transparent border text-white p-3" style="border-color: var(--separator-color);" rows="3" placeholder="Enter details here..."></textarea>
            </div>
            <button type="submit" class="btn btn-luxury w-100 py-2 fw-bold">Confirm Action</button>
          </form>
        `;
        
        openStitchModal(modalTitle, modalContent);
      });
    }
  });
});


// ─── "Show more" for the sidebar nav ──────────────────────────────────────────
// If the number of menu items exceeds the space allotted to the nav (on any
// screen size), clip the list and reveal a "Show more" toggle instead of
// silently overflowing.
document.addEventListener('DOMContentLoaded', () => {
  const sidebar = document.getElementById('dashboardSidebar');
  const nav = sidebar ? sidebar.querySelector('nav') : null;
  if (!sidebar || !nav) return;

  const isRtl = document.documentElement.dir === 'rtl';
  const btn = document.createElement('button');
  btn.type = 'button';
  btn.className = 'sidebar-more-btn';
  btn.style.display = 'none';
  nav.insertAdjacentElement('afterend', btn);

  let expanded = false;

  function setLabel() {
    btn.innerHTML = expanded
      ? '<i class="bi bi-chevron-double-up"></i><span data-en="Show less" data-ar="عرض أقل">' + (isRtl ? 'عرض أقل' : 'Show less') + '</span>'
      : '<i class="bi bi-chevron-double-down"></i><span data-en="Show more" data-ar="عرض المزيد">' + (isRtl ? 'عرض المزيد' : 'Show more') + '</span>';
  }

  function fit() {
    nav.style.maxHeight = '';
    nav.classList.remove('nav-clipped');

    const sbRect = sidebar.getBoundingClientRect();
    const navRect = nav.getBoundingClientRect();
    const footer = nav.nextElementSibling === btn ? btn.nextElementSibling : nav.nextElementSibling;
    const footerH = footer && footer !== btn ? footer.offsetHeight : 0;
    const btnH = 54; // reserved space for the toggle itself
    const avail = sidebar.clientHeight - (navRect.top - sbRect.top) - footerH - btnH;

    const overflowing = nav.scrollHeight > avail + 10;

    if (!overflowing) {
      btn.style.display = 'none';
      expanded = false;
      return;
    }

    btn.style.display = 'flex';
    setLabel();
    if (!expanded) {
      nav.style.maxHeight = Math.max(avail, 140) + 'px';
      nav.classList.add('nav-clipped');
    }
  }

  btn.addEventListener('click', () => {
    expanded = !expanded;
    fit();
    if (expanded) setLabel();
  });

  // Re-measure when accordions open/close, on resize, and on language flip
  nav.addEventListener('click', () => setTimeout(fit, 350));
  window.addEventListener('resize', () => setTimeout(fit, 100));
  document.addEventListener('languageChanged', () => setTimeout(fit, 100));
  setTimeout(fit, 50);
});
