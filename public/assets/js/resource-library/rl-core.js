/**
 * Resource library workspace - core.
 *
 * Shared by the admin and the teacher screens: the same JSON API, the same components.
 * Everything lives under window.RL. No build step, no framework; Bootstrap 5 modals only.
 *
 *   RL.http()      fetch wrapper (CSRF, JSON, errors as RL.Error)
 *   RL.bus         tiny event emitter ("changed" -> every open view refreshes itself)
 *   RL.toast()     toast, with an Undo button + countdown when the action can be reverted
 *   RL.confirm()   promise based confirm dialog
 *   RL.modal()     Bootstrap modal factory
 *   RL.picker()    multi-select with tags (groups / lessons / students)
 */
(function (window, document) {
  'use strict';

  const RL = (window.RL = window.RL || {});
  const cfg = (RL.config = window.RL_CONFIG || {});

  // ------------------------------------------------------------------ helpers

  RL.esc = function (value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  };

  RL.$ = function (selector, root) { return (root || document).querySelector(selector); };
  RL.$$ = function (selector, root) { return Array.prototype.slice.call((root || document).querySelectorAll(selector)); };

  RL.debounce = function (fn, wait) {
    let timer = null;
    return function () {
      const args = arguments;
      const self = this;
      clearTimeout(timer);
      timer = setTimeout(function () { fn.apply(self, args); }, wait);
    };
  };

  RL.html = function (markup) {
    const template = document.createElement('template');
    template.innerHTML = markup.trim();
    return template.content.firstElementChild;
  };

  /** Arabic-insensitive text for client side searching. */
  RL.norm = function (text) {
    return String(text || '').toLowerCase()
      .replace(/[ً-ٰٟـ]/g, '')
      .replace(/[أإآ]/g, 'ا')
      .replace(/ى/g, 'ي')
      .replace(/ة/g, 'ه')
      .replace(/\s+/g, ' ').trim();
  };

  RL.qs = function (params) {
    const parts = [];
    Object.keys(params || {}).forEach(function (key) {
      const value = params[key];
      if (value === null || value === undefined || value === '' || value === false) return;
      parts.push(encodeURIComponent(key) + '=' + encodeURIComponent(value === true ? 1 : value));
    });
    return parts.length ? '?' + parts.join('&') : '';
  };

  RL.url = function (path) {
    return String(cfg.api || '').replace(/\/+$/, '') + '/' + String(path).replace(/^\/+/, '');
  };

  /** Plural segment used by the API for a model kind. */
  RL.plural = function (kind) { return { resource: 'resources', unit: 'units', lesson: 'lessons' }[kind] || kind; };

  /**
   * Browser-side memory. Key it by numeric ids only: route keys are encrypted with a random IV,
   * so the same row gets a different key on every page load.
   */
  RL.store = {
    get: function (key, fallback) {
      try { const v = window.localStorage.getItem('rl:' + key); return v === null ? fallback : JSON.parse(v); } catch (e) { return fallback; }
    },
    set: function (key, value) {
      try { window.localStorage.setItem('rl:' + key, JSON.stringify(value)); } catch (e) { /* private mode */ }
    },
  };

  // entries an older build keyed by route key ("rl:open:<encrypted>"): one per page load, never read again
  try {
    for (let i = window.localStorage.length - 1; i >= 0; i--) {
      const name = window.localStorage.key(i);
      if (name && name.indexOf('rl:open:') === 0 && !/^rl:open:\d+$/.test(name)) window.localStorage.removeItem(name);
    }
  } catch (e) { /* private mode */ }

  // --------------------------------------------------------------------- http

  function RLError(message, status, data) {
    this.name = 'RLError';
    this.message = message;
    this.status = status;
    this.data = data || {};
  }
  RLError.prototype = Object.create(Error.prototype);
  RL.Error = RLError;

  /**
   * A role that may read the library but not change it (admin with "view" only) gets a notice
   * above the workspace. The server refuses every change anyway; this just explains why.
   */
  RL.readOnlyNotice = function (root) {
    if (cfg.canEdit !== false || !root || !root.parentNode || document.getElementById('rl-readonly')) return;
    root.parentNode.insertBefore(
      RL.html('<div class="rl-readonly-note" id="rl-readonly" dir="rtl"><i class="bi bi-eye"></i> عرض فقط — ليس لديك صلاحية تعديل محتوى المواد، وسيُرفض أي تغيير.</div>'),
      root
    );
  };

  /**
   * @param {string} method
   * @param {string} path   relative to the API base
   * @param {object|FormData|null} body
   */
  RL.http = function (method, path, body, retried) {
    const init = {
      method: method,
      credentials: 'same-origin',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': cfg.csrf || '',
      },
    };

    let url = RL.url(path);

    if (body instanceof FormData) {
      init.body = body;
    } else if (body && method === 'GET') {
      url += RL.qs(body);
    } else if (body) {
      init.headers['Content-Type'] = 'application/json';
      init.body = JSON.stringify(body);
    }

    return fetch(url, init).then(function (response) {
      return response.text().then(function (text) {
        let data = {};
        try { data = text ? JSON.parse(text) : {}; } catch (e) { data = { message: '' }; }

        if (response.ok && data.success !== false) return data;

        // the CSRF token went stale (session regenerated, page left open for hours): renew it once and replay
        if (response.status === 419 && !retried && path !== 'ping') {
          return RL.refreshCsrf().then(function () { return RL.http(method, path, body, true); });
        }

        let message = data.message || '';
        if (response.status === 419) message = 'انتهت صلاحية الجلسة، حدّث الصفحة ثم أعد المحاولة.';
        else if (response.status === 401) message = 'انتهت جلسة الدخول، سجّل الدخول من جديد.';
        else if (response.status === 413) message = 'حجم الملف أكبر من المسموح.';
        else if (response.status === 422 && data.errors) message = Object.keys(data.errors).map(function (k) { return data.errors[k][0]; }).join(' — ');
        else if (!message) message = 'تعذّر تنفيذ العملية (' + response.status + ').';

        throw new RLError(message, response.status, data);
      });
    });
  };

  /** Touch the session (keeps it alive during a long upload) and pick up its current CSRF token. */
  RL.refreshCsrf = function () {
    return RL.http('GET', 'ping', null, true).then(function (res) {
      if (res.csrf) {
        cfg.csrf = res.csrf;
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) meta.setAttribute('content', res.csrf);
      }
      return cfg.csrf;
    });
  };

  RL.get = function (path, params) { return RL.http('GET', path, params || null); };
  RL.post = function (path, body) { return RL.http('POST', path, body || {}); };
  RL.put = function (path, body) { return RL.http('PUT', path, body || {}); };
  RL.del = function (path, body) { return RL.http('DELETE', path, body || {}); };

  // ---------------------------------------------------------------------- bus

  const listeners = {};
  RL.bus = {
    on: function (event, fn) { (listeners[event] = listeners[event] || []).push(fn); },
    off: function (event, fn) { listeners[event] = (listeners[event] || []).filter(function (f) { return f !== fn; }); },
    emit: function (event, payload) { (listeners[event] || []).slice().forEach(function (fn) { fn(payload); }); },
  };

  // -------------------------------------------------------------------- toast

  let toastHost = null;
  function host() {
    if (!toastHost) {
      toastHost = document.createElement('div');
      toastHost.className = 'rl-toasts rl-theme-' + (cfg.role === 'admin' ? 'admin' : 'teacher');
      toastHost.setAttribute('aria-live', 'polite');
      toastHost.setAttribute('dir', 'rtl');
      document.body.appendChild(toastHost);
    }
    return toastHost;
  }

  /**
   * @param {{message: string, type?: 'success'|'error'|'info', undo?: {token: string, seconds: number}|null, duration?: number}} options
   */
  RL.toast = function (options) {
    const opts = Object.assign({ type: 'success', undo: null, duration: 4000 }, options);
    const seconds = opts.undo ? (opts.undo.seconds || 5) : opts.duration / 1000;

    const el = RL.html(
      '<div class="rl-toast is-' + RL.esc(opts.type) + '" role="status">' +
        '<span class="rl-toast-msg">' + RL.esc(opts.message) + '</span>' +
        (opts.undo ? '<button type="button" class="rl-toast-undo">تراجع</button>' : '') +
        '<button type="button" class="rl-toast-close" aria-label="إغلاق"><i class="bi bi-x-lg"></i></button>' +
        '<span class="rl-toast-bar" style="animation-duration:' + seconds + 's"></span>' +
      '</div>'
    );

    let done = false;
    const close = function () {
      if (done) return;
      done = true;
      el.classList.add('is-leaving');
      setTimeout(function () { el.remove(); }, 220);
    };

    el.querySelector('.rl-toast-close').addEventListener('click', close);

    if (opts.undo) {
      el.querySelector('.rl-toast-undo').addEventListener('click', function () {
        this.disabled = true;
        RL.undo(opts.undo.token).then(close, close);
      });
    }

    host().appendChild(el);
    setTimeout(close, seconds * 1000 + 150);

    return { close: close };
  };

  RL.undo = function (token) {
    return RL.post('undo', { token: token })
      .then(function (res) {
        RL.toast({ message: res.message || 'تم التراجع.', type: 'info' });
        RL.bus.emit('changed', { undo: true });
        return res;
      })
      .catch(function (err) {
        RL.toast({ message: err.message, type: 'error', duration: 6000 });
      });
  };

  /** Show the result of an action: message + Undo when the server gave a token. */
  RL.done = function (res) {
    RL.toast({ message: res.message || 'تم.', type: 'success', undo: res.undo || null });
    RL.bus.emit('changed', res);
    return res;
  };

  RL.fail = function (err) {
    RL.toast({ message: (err && err.message) || 'حدث خطأ.', type: 'error', duration: 6500 });
    return Promise.reject(err);
  };

  /**
   * Run an API call and report it (toast, Undo, refresh). Never rejects: it resolves with the
   * response, or with null after showing the error - so callers can simply `.then(res => res && ...)`.
   */
  RL.act = function (promise) {
    return promise.then(RL.done, function (err) {
      RL.toast({ message: (err && err.message) || 'حدث خطأ.', type: 'error', duration: 6500 });
      return null;
    });
  };

  // ------------------------------------------------------------------- modals

  let modalCount = 0;

  /**
   * @param {{title: string, body?: string, footer?: string, size?: 'sm'|'lg'|'xl', onHidden?: Function, scrollable?: boolean}} options
   */
  RL.modal = function (options) {
    const id = 'rl-modal-' + (++modalCount);
    const size = options.size ? ' modal-' + options.size : '';
    const el = RL.html(
      '<div class="modal fade rl-modal rl-theme-' + (cfg.role === 'admin' ? 'admin' : 'teacher') + '" id="' + id + '" tabindex="-1" aria-hidden="true">' +
        '<div class="modal-dialog modal-dialog-centered' + size + (options.scrollable === false ? '' : ' modal-dialog-scrollable') + '">' +
          '<div class="modal-content">' +
            '<div class="modal-header"><h5 class="modal-title">' + (options.title || '') + '</h5>' +
              '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button></div>' +
            '<div class="modal-body">' + (options.body || '') + '</div>' +
            (options.footer === null ? '' : '<div class="modal-footer">' + (options.footer || '') + '</div>') +
          '</div>' +
        '</div>' +
      '</div>'
    );
    el.setAttribute('dir', 'rtl');
    document.body.appendChild(el);

    const bs = new window.bootstrap.Modal(el, { backdrop: options.backdrop === undefined ? true : options.backdrop });
    const api = {
      el: el,
      bs: bs,
      body: el.querySelector('.modal-body'),
      footer: el.querySelector('.modal-footer'),
      title: el.querySelector('.modal-title'),
      show: function () { bs.show(); return api; },
      hide: function () { bs.hide(); return api; },
    };

    // Bootstrap only hears Escape while focus is inside the dialog. Clicking a button that then
    // disables itself ("restore", "undo") drops focus to <body>, so Escape would do nothing.
    const onEscape = function (event) {
      if (event.key !== 'Escape' || event.defaultPrevented || !el.classList.contains('show')) return;
      const shown = document.querySelectorAll('.rl-modal.show');
      if (shown[shown.length - 1] === el && !el.contains(document.activeElement)) bs.hide();
    };
    document.addEventListener('keydown', onEscape);

    el.addEventListener('hidden.bs.modal', function () {
      document.removeEventListener('keydown', onEscape);
      if (options.onHidden) options.onHidden(api);
      bs.dispose();
      el.remove();
    });

    return api;
  };

  /** @returns {Promise<boolean>} */
  RL.confirm = function (options) {
    const opts = Object.assign({ title: 'تأكيد', text: '', confirmText: 'تأكيد', cancelText: 'إلغاء', danger: false }, options);

    return new Promise(function (resolve) {
      let answered = false;
      const modal = RL.modal({
        title: RL.esc(opts.title),
        body: '<p class="mb-0" style="line-height:1.8">' + opts.text + '</p>',
        footer:
          '<button type="button" class="rl-btn ' + (opts.danger ? 'rl-btn--solid-danger' : 'rl-btn--primary') + '" data-act="yes">' + RL.esc(opts.confirmText) + '</button>' +
          '<button type="button" class="rl-btn" data-act="no">' + RL.esc(opts.cancelText) + '</button>',
        size: 'sm',
        scrollable: false,
        onHidden: function () { if (!answered) resolve(false); },
      });

      modal.el.addEventListener('click', function (event) {
        const button = event.target.closest('[data-act]');
        if (!button) return;
        answered = true;
        resolve(button.getAttribute('data-act') === 'yes');
        modal.hide();
      });

      modal.show();
    });
  };

  // ------------------------------------------------------------------- picker

  /**
   * Multi-select with tags. Options may carry a `group` (rendered as a heading).
   *
   * @param {HTMLElement} container
   * @param {{options: Array<{value: string, label: string, group?: string, disabled?: boolean, hint?: string}>, selected?: string[], placeholder?: string, onChange?: Function, single?: boolean}} options
   */
  RL.picker = function (container, options) {
    let opts = options.options || [];
    let selected = (options.selected || []).map(String);

    container.innerHTML =
      '<div class="rl-picker">' +
        '<div class="rl-picker-box"><span class="rl-picker-tags" style="display:contents"></span>' +
          '<input type="text" class="rl-picker-input" autocomplete="off" placeholder="' + RL.esc(options.placeholder || 'اختر…') + '"></div>' +
        '<div class="rl-picker-list" role="listbox"></div>' +
      '</div>';

    const root = container.firstElementChild;
    const tagsEl = root.querySelector('.rl-picker-tags');
    const input = root.querySelector('.rl-picker-input');
    const list = root.querySelector('.rl-picker-list');

    function labelOf(value) {
      const found = opts.filter(function (o) { return String(o.value) === String(value); })[0];
      return found ? found.label : value;
    }

    function renderTags() {
      tagsEl.innerHTML = selected.map(function (value) {
        return '<span class="rl-chip rl-chip--group">' + RL.esc(labelOf(value)) +
          '<button type="button" class="rl-x" data-remove="' + RL.esc(value) + '" aria-label="إزالة"><i class="bi bi-x"></i></button></span>';
      }).join('');
      input.placeholder = selected.length ? '' : (options.placeholder || 'اختر…');
    }

    function renderList() {
      const query = RL.norm(input.value);
      let lastGroup = null;
      let html = '';
      let shown = 0;

      opts.forEach(function (o) {
        if (query && RL.norm(o.label + ' ' + (o.group || '') + ' ' + (o.hint || '')).indexOf(query) === -1) return;

        if (o.group && o.group !== lastGroup) {
          html += '<div class="rl-picker-group">' + RL.esc(o.group) + '</div>';
          lastGroup = o.group;
        }
        const on = selected.indexOf(String(o.value)) !== -1;
        html += '<div class="rl-picker-opt' + (on ? ' is-selected' : '') + (o.disabled ? ' is-disabled' : '') + '" data-value="' + RL.esc(o.value) + '" role="option">' +
          '<i class="bi ' + (on ? 'bi-check-square-fill' : 'bi-square') + '"></i><span>' + RL.esc(o.label) + '</span>' +
          (o.hint ? '<small class="text-muted ms-auto">' + RL.esc(o.hint) + '</small>' : '') + '</div>';
        shown++;
      });

      list.innerHTML = shown ? html : '<div class="rl-picker-none">لا توجد نتائج</div>';
    }

    function change() {
      renderTags();
      renderList();
      if (options.onChange) options.onChange(selected.slice());
    }

    function open() { root.classList.add('is-open'); renderList(); }
    function close() { root.classList.remove('is-open'); input.value = ''; }

    root.querySelector('.rl-picker-box').addEventListener('click', function (event) {
      if (event.target.closest('[data-remove]')) return;
      input.focus();
      open();
    });
    input.addEventListener('focus', open);
    input.addEventListener('input', renderList);
    input.addEventListener('keydown', function (event) {
      if (event.key === 'Backspace' && !input.value && selected.length) { selected.pop(); change(); }
      if (event.key === 'Escape' && root.classList.contains('is-open')) {
        // closes the list only - without this Bootstrap would also dismiss the whole modal
        event.stopPropagation();
        close();
        input.blur();
      }
    });

    root.addEventListener('click', function (event) {
      const remove = event.target.closest('[data-remove]');
      if (remove) {
        selected = selected.filter(function (v) { return v !== remove.getAttribute('data-remove'); });
        change();
        return;
      }

      const opt = event.target.closest('.rl-picker-opt');
      if (opt && !opt.classList.contains('is-disabled')) {
        const value = opt.getAttribute('data-value');
        if (options.single) {
          selected = [value];
          close();
        } else {
          selected = selected.indexOf(value) === -1 ? selected.concat([value]) : selected.filter(function (v) { return v !== value; });
        }
        change();
        if (!options.single) input.focus();
      }
    });

    const outside = function (event) { if (!root.contains(event.target)) close(); };
    document.addEventListener('mousedown', outside);

    renderTags();

    return {
      get: function () { return selected.slice(); },
      set: function (values) { selected = (values || []).map(String); renderTags(); renderList(); },
      setOptions: function (next) { opts = next || []; selected = selected.filter(function (v) { return opts.some(function (o) { return String(o.value) === v; }); }); renderTags(); renderList(); },
      destroy: function () { document.removeEventListener('mousedown', outside); },
    };
  };

  // -------------------------------------------------------------- global menus

  document.addEventListener('click', function (event) {
    const toggle = event.target.closest('.rl-menu > [data-menu]');
    RL.$$('.rl-menu.is-open').forEach(function (menu) {
      if (!toggle || toggle.parentNode !== menu) menu.classList.remove('is-open');
    });
    if (toggle) toggle.parentNode.classList.toggle('is-open');
  });

  // ------------------------------------------------------------ small widgets

  RL.chip = function (text, tone, extra) {
    return '<span class="rl-chip rl-chip--' + (tone || 'secondary') + '"' + (extra || '') + '>' + RL.esc(text) + '</span>';
  };

  /** "file missing" tag of a resource row; clickable (opens "replace file") for whoever may edit it. */
  RL.missingFileChip = function (r) {
    if (!r.file_missing) return '';
    const label = '<i class="bi bi-exclamation-octagon"></i> الملف مفقود';
    return r.can && r.can.manage
      ? '<button type="button" class="rl-chip rl-chip--danger" data-act="edit" title="الملف غير موجود على السيرفر — اضغط لرفع ملف بديل">' + label + ' — استبدال</button>'
      : '<span class="rl-chip rl-chip--danger" title="الملف غير موجود على السيرفر — اطلب من الإدارة استبداله">' + label + '</span>';
  };

  /** Tooltip of a resource's kill switch: a teacher who cannot edit it only pauses it for their own groups. */
  RL.powerTitle = function (r, on) {
    if (!on) return 'تشغيل عرضه للطلاب';
    return r.can && r.can.manage ? 'إيقاف عرضه للطلاب' : 'إيقاف عن مجموعاتي (مجموعات زملائك تبقى تراه)';
  };

  RL.switchEl = function (checked, attrs, extraClass) {
    return '<label class="rl-switch ' + (extraClass || '') + '"' + (attrs && attrs.title ? ' title="' + RL.esc(attrs.title) + '"' : '') + '>' +
      '<input type="checkbox"' + (checked ? ' checked' : '') + (attrs && attrs.disabled ? ' disabled' : '') + ' ' + (attrs && attrs.raw ? attrs.raw : '') + '><i></i></label>';
  };

  RL.loading = function (text) {
    return '<div class="rl-loading"><div class="rl-spinner"></div><span>' + RL.esc(text || 'جارٍ التحميل…') + '</span></div>';
  };

  RL.empty = function (icon, text) {
    return '<div class="rl-empty"><i class="bi ' + icon + '"></i>' + RL.esc(text) + '</div>';
  };

  RL.formatBytes = function (bytes) {
    if (!bytes || bytes < 0) return '0 B';
    const units = ['B', 'KB', 'MB', 'GB'];
    const i = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
    const v = bytes / Math.pow(1024, i);
    return v.toFixed(v >= 10 || i === 0 ? 0 : 1) + ' ' + units[i];
  };

  RL.formatDuration = function (seconds) {
    if (!isFinite(seconds) || seconds < 0) return '—';
    seconds = Math.round(seconds);
    if (seconds < 60) return seconds + ' ث';
    const m = Math.floor(seconds / 60);
    if (m < 60) return m + ' د ' + (seconds % 60) + ' ث';
    return Math.floor(m / 60) + ' س ' + (m % 60) + ' د';
  };

  /** Order a node list drag-and-drop with SortableJS when the library is present. */
  RL.sortable = function (el, options) {
    if (!window.Sortable || !el || el.__rlSortable) return;
    el.__rlSortable = new window.Sortable(el, Object.assign({ animation: 150, ghostClass: 'rl-sortable-ghost' }, options));
  };

})(window, document);
