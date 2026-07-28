
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

  // Active state handling — compares against the real current path so a
  // sidebar item highlights correctly on refresh/direct navigation.
  const currentPath = window.location.pathname.replace(/\/+$/, '') || '/';

  const activeSubLink = Array.from(document.querySelectorAll('.sidebar-submenu-item[href]')).find(link => {
    const linkPath = new URL(link.href, window.location.origin).pathname.replace(/\/+$/, '') || '/';
    return linkPath === currentPath;
  });

  if (activeSubLink) {
    activeSubLink.classList.add('active');
    const parentMenu = activeSubLink.closest('.sidebar-submenu-wrapper');
    if (parentMenu) {
      parentMenu.classList.add('expanded');
      const triggerId = parentMenu.getAttribute('id');
      const trigger = document.querySelector(`.sidebar-nav-item[href="#${triggerId}"]`);
      if (trigger) trigger.setAttribute('aria-expanded', 'true');
    }
  }
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
