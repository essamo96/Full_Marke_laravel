/**
 * Shared dashboard sidebar: accordion menus + active route highlight.
 * Used by student and teacher portals.
 */
(function () {
  'use strict';

  function normalizePath(pathname) {
    return (pathname || '/').replace(/\/+$/, '') || '/';
  }

  function setExpanded(trigger, menu, open) {
    if (!trigger || !menu) return;
    trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
    menu.classList.toggle('expanded', open);
    if (open) {
      menu.style.maxHeight = menu.scrollHeight + 'px';
    } else {
      menu.style.maxHeight = '0px';
    }
  }

  function refreshOpenMenus(nav) {
    nav.querySelectorAll('.sidebar-submenu-wrapper.expanded').forEach(function (menu) {
      menu.style.maxHeight = menu.scrollHeight + 'px';
    });
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
      var href = trigger.getAttribute('href') || '';
      if (href.charAt(0) !== '#') return;
      var menu = document.getElementById(href.slice(1));
      if (!menu) return;

      // Honour server-rendered open state
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
          var otherHref = other.getAttribute('href') || '';
          if (otherHref.charAt(0) !== '#') return;
          var otherMenu = document.getElementById(otherHref.slice(1));
          setExpanded(other, otherMenu, false);
        });

        setExpanded(trigger, menu, !isOpen);
        if (!isOpen) {
          window.setTimeout(function () {
            refreshOpenMenus(nav);
            scrollMenuIntoView(nav, trigger, menu);
          }, 40);
        }
      });
    });

    window.addEventListener('resize', function () {
      refreshOpenMenus(nav);
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

      try {
        var linkPath = normalizePath(new URL(href, window.location.origin).pathname);
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
    var trigger = sidebar.querySelector('.sidebar-nav-item.accordion-trigger[href="#' + parentMenu.id + '"]');
    setExpanded(trigger, parentMenu, true);
  }

  function initMobileSidebar() {
    // Ensure overlay closes consistently
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
