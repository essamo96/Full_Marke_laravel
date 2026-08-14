/**
 * Shared dashboard sidebar: accordion menus + active route highlight.
 * Height comes from the inner list's scrollHeight so a collapsed flex
 * parent (max-height: 0) cannot report 0 and hide child items.
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

  function wrapBareMenus(sidebar) {
    sidebar.querySelectorAll('ul.sidebar-submenu-wrapper').forEach(function (ul) {
      var wrap = document.createElement('div');
      wrap.className = 'sidebar-submenu-wrapper';
      if (ul.classList.contains('expanded')) {
        wrap.classList.add('expanded');
      }
      wrap.id = ul.id;
      ul.removeAttribute('id');
      ul.classList.remove('sidebar-submenu-wrapper', 'expanded');
      if (!ul.classList.contains('sidebar-submenu')) {
        ul.classList.add('sidebar-submenu');
      }
      ul.parentNode.insertBefore(wrap, ul);
      wrap.appendChild(ul);
    });
  }

  function contentHeight(menu) {
    var inner = menu.querySelector('.sidebar-submenu') || menu;
    var height = inner.scrollHeight;
    if (height > 0) return height;
    var sum = 0;
    var children = inner.children;
    for (var i = 0; i < children.length; i++) {
      sum += children[i].offsetHeight;
    }
    return sum;
  }

  function setExpanded(trigger, menu, open) {
    if (!trigger || !menu) return;
    trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
    menu.classList.toggle('expanded', open);
    if (open) {
      var height = contentHeight(menu);
      menu.style.maxHeight = (height > 0 ? height : 800) + 'px';
    } else {
      menu.style.maxHeight = '0px';
    }
  }

  function refreshOpenMenus(sidebar) {
    sidebar.querySelectorAll('.sidebar-submenu-wrapper.expanded').forEach(function (menu) {
      var height = contentHeight(menu);
      menu.style.maxHeight = (height > 0 ? height : 800) + 'px';
    });
  }

  function initAccordion(sidebar) {
    var triggers = Array.from(sidebar.querySelectorAll('.sidebar-nav-item.accordion-trigger'));

    triggers.forEach(function (trigger) {
      var menu = menuForTrigger(trigger);
      if (!menu) return;

      if (trigger.getAttribute('aria-expanded') === 'true' || menu.classList.contains('expanded')) {
        setExpanded(trigger, menu, true);
      } else {
        setExpanded(trigger, menu, false);
      }
    });

    sidebar.addEventListener('click', function (e) {
      var trigger = e.target.closest('.sidebar-nav-item.accordion-trigger');
      if (!trigger || !sidebar.contains(trigger)) return;
      e.preventDefault();
      e.stopPropagation();

      var menu = menuForTrigger(trigger);
      if (!menu) return;

      var isOpen = trigger.getAttribute('aria-expanded') === 'true';
      setExpanded(trigger, menu, !isOpen);
    });

    window.addEventListener('resize', function () {
      refreshOpenMenus(sidebar);
    });
    window.addEventListener('languageChanged', function () {
      refreshOpenMenus(sidebar);
    });
    window.addEventListener('load', function () {
      refreshOpenMenus(sidebar);
    });
    if (document.fonts && document.fonts.ready) {
      document.fonts.ready.then(function () {
        refreshOpenMenus(sidebar);
      });
    }
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

    wrapBareMenus(sidebar);
    markActiveLinks(sidebar);
    initAccordion(sidebar);
    initMobileSidebar();
  });
})();
