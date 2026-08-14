/**
 * Shared dashboard sidebar: accordion menus + active route highlight.
 * Used by student and teacher portals.
 */
(function () {
  'use strict';

  function normalizePath(pathname) {
    return (pathname || '/').replace(/\/+$/, '') || '/';
  }

  function menuForTrigger(trigger) {
    var id = trigger.getAttribute('aria-controls') || '';
    if (!id) {
      var href = trigger.getAttribute('href') || '';
      if (href.charAt(0) === '#') {
        id = href.slice(1);
      }
    }
    return id ? document.getElementById(id) : null;
  }

  // Purely class-driven: the CSS transitions max-height between 0 and a
  // large fixed cap (see .sidebar-submenu-wrapper.expanded), so there is no
  // need to measure scrollHeight in JS. An earlier version computed pixel
  // heights here, but scrollHeight/offsetHeight reads are sensitive to
  // layout timing (e.g. reads happening before the browser has laid out the
  // just-unhidden content) and could silently resolve to 0, leaving the
  // menu's aria-expanded/chevron toggled correctly while the list itself
  // never actually became visible. Toggling the class alone removes that
  // whole failure mode.
  function setExpanded(trigger, menu, open) {
    if (!trigger || !menu) return;
    trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
    menu.classList.toggle('expanded', open);
  }

  function scrollMenuIntoView(nav, trigger, menu) {
    if (!nav || !trigger || !menu) return;
    window.requestAnimationFrame(function () {
      var navRect = nav.getBoundingClientRect();
      var menuRect = menu.getBoundingClientRect();
      if (menuRect.bottom > navRect.bottom - 8) {
        nav.scrollTop += menuRect.bottom - navRect.bottom + 16;
      } else if (trigger.getBoundingClientRect().top < navRect.top + 8) {
        nav.scrollTop -= navRect.top - trigger.getBoundingClientRect().top + 8;
      }
    });
  }

  function initAccordion(sidebar) {
    var nav = sidebar.querySelector('nav');
    if (!nav) return;

    var triggers = Array.from(nav.querySelectorAll('.sidebar-nav-item.accordion-trigger'));

    triggers.forEach(function (trigger) {
      var menu = menuForTrigger(trigger);
      if (!menu) return;

      if (trigger.getAttribute('aria-expanded') === 'true' || menu.classList.contains('expanded')) {
        setExpanded(trigger, menu, true);
      } else {
        setExpanded(trigger, menu, false);
      }

      trigger.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var isOpen = trigger.getAttribute('aria-expanded') === 'true';

        triggers.forEach(function (other) {
          if (other === trigger) return;
          setExpanded(other, menuForTrigger(other), false);
        });

        setExpanded(trigger, menu, !isOpen);
        if (!isOpen) {
          window.setTimeout(function () {
            scrollMenuIntoView(nav, trigger, menu);
          }, 40);
        }
      });
    });
  }

  function markActiveLinks(sidebar) {
    var currentPath = normalizePath(window.location.pathname);
    var best = null;
    var bestLen = -1;

    sidebar.querySelectorAll('.sidebar-submenu-item[href], .sidebar-nav-item[href]').forEach(function (link) {
      if (link.classList.contains('accordion-trigger')) return;
      var href = link.getAttribute('href');
      if (!href || href === '#' || href.charAt(0) === '#') return;

      var linkPath;
      try {
        linkPath = normalizePath(new URL(href, window.location.origin).pathname);
      } catch (err) {
        return;
      }

      if (currentPath === linkPath || currentPath.indexOf(linkPath + '/') === 0) {
        if (linkPath.length > bestLen) {
          best = link;
          bestLen = linkPath.length;
        }
      }
    });

    if (!best) return;

    best.classList.add('active');

    var parentMenu = best.closest('.sidebar-submenu-wrapper');
    if (!parentMenu) return;

    parentMenu.classList.add('expanded');
    var trigger = sidebar.querySelector('.sidebar-nav-item.accordion-trigger[aria-controls="' + parentMenu.id + '"]');
    setExpanded(trigger, parentMenu, true);
  }

  function initMobileSidebar() {
    var overlay = document.getElementById('sidebarOverlay');
    var sidebar = document.getElementById('dashboardSidebar');
    if (!overlay || !sidebar) return;

    overlay.addEventListener('click', function () {
      sidebar.classList.remove('mobile-open');
      overlay.classList.remove('active');
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    var sidebar = document.getElementById('dashboardSidebar');
    if (!sidebar) return;

    markActiveLinks(sidebar);
    initAccordion(sidebar);
    initMobileSidebar();
  });
})();
