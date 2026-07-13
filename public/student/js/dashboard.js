
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
