/**
 * Resource library workspace - the manage screen of one subject (units > lessons > resources).
 *
 * The page is rendered from JSON by the shared API, so the admin and the teacher get the very
 * same screen. Every change refetches the tree - what you see is always what the server holds.
 *
 *   RL.Manager.mount(rootElement)
 */
(function (window, document) {
  'use strict';

  const RL = window.RL;
  const esc = RL.esc;

  RL.Manager = {};

  RL.Manager.mount = function (root) {
    const cfg = RL.config;
    RL.readOnlyNotice(root);
    const S = {
      tree: null,
      group: cfg.group || null,
      filters: { q: '', type: '', status: '' },
      open: RL.store.get('open:' + cfg.subject.id, {}),
      index: {},        // key -> resource state
      containers: {},   // key -> unit / lesson state
      loading: false,
      requestId: 0,
    };

    RL.state = { subjectKey: cfg.subject.key, groups: [], tree: null, groupTab: S.group };

    // ------------------------------------------------------------------ fetch

    function fetchTree(options) {
      const id = ++S.requestId;
      S.loading = true;

      return RL.get('subjects/' + cfg.subject.key + '/tree', {
        group: S.group,
        q: S.filters.q,
        type: S.filters.type,
        status: S.filters.status,
      }).then(function (res) {
        if (id !== S.requestId) return;
        S.loading = false;
        setTree(res.tree);
        render(options);
      }).catch(function (err) {
        S.loading = false;
        root.innerHTML = '<div class="rl-note rl-note--bad"><i class="bi bi-x-octagon"></i><div>' + esc(err.message) + '</div></div>';
      });
    }

    function setTree(tree) {
      S.tree = tree;
      S.index = {};
      S.containers = {};

      const addResources = function (list) { (list || []).forEach(function (r) { S.index[r.key] = r; }); };
      addResources(tree.general);
      addResources(tree.unplaced);
      tree.units.forEach(function (unit) {
        S.containers[unit.key] = unit;
        addResources(unit.resources);
        unit.lessons.forEach(function (lesson) { S.containers[lesson.key] = lesson; addResources(lesson.resources); });
      });

      RL.state.tree = tree;
      RL.state.groups = tree.groups;
      RL.state.groupTab = S.group;
    }

    // ----------------------------------------------------------------- render

    function isFiltering() { return !!(S.filters.q || S.filters.type || S.filters.status); }

    function render(options) {
      const keepFocus = document.activeElement && document.activeElement.id === 'rl-q';
      const scrollY = window.scrollY;

      root.innerHTML = headHtml() + statsHtml() + toolbarHtml() + tabsHtml() + '<div class="rl-body">' + bodyHtml() + '</div>';

      if (keepFocus) {
        const input = RL.$('#rl-q', root);
        input.focus();
        input.setSelectionRange(input.value.length, input.value.length);
      }
      window.scrollTo(0, scrollY);

      initSortables();
      if (options && options.flash) flash(options.flash);
    }

    function headHtml() {
      const t = S.tree;
      return '<div class="rl-head"><h1 class="rl-title-main"><i class="bi bi-collection-play me-2" style="color:var(--rl-accent)"></i>' + esc(t.subject.name) + '</h1>' +
        '<span class="rl-spacer"></span>' +
        '<button type="button" class="rl-btn rl-btn--primary" data-act="add-unit"><i class="bi bi-folder-plus"></i> إضافة وحدة</button>' +
        '<button type="button" class="rl-btn rl-btn--primary" data-act="add-resource" data-place="general"><i class="bi bi-cloud-arrow-up"></i> إضافة مورد</button></div>';
    }

    function statsHtml() {
      const s = S.tree.stats;
      const stat = function (label, value, status, cls) {
        return '<button type="button" class="rl-stat ' + (cls || '') + (S.filters.status === status ? ' is-active' : '') + '" data-act="filter-status" data-status="' + status + '"><b>' + value + '</b> ' + label + '</button>';
      };

      return '<div class="rl-stats">' +
        stat('مورد', s.resources, '') +
        stat('فيديو', s.videos, '') +
        stat('ظاهر للطلاب', s.visible, 'visible') +
        stat('موقوف', s.hidden, 'hidden', s.hidden ? 'rl-stat--bad' : '') +
        stat('مسودة / بلا مكان', s.draft, 'draft', s.draft ? 'rl-stat--warn' : '') +
        stat('فيها استثناءات', s.excluded, 'excluded') +
        stat('مشاكل', s.issues, 'issues', s.issues ? 'rl-stat--warn' : '') +
        '</div>';
    }

    function toolbarHtml() {
      const scopeLabel = S.group ? 'مجموعة «' + groupName(S.group) + '»' : 'كل ما يظهر هنا';

      return '<div class="rl-toolbar">' +
        '<div class="rl-search"><i class="bi bi-search"></i><input type="search" id="rl-q" class="rl-input" placeholder="ابحث باسم المورد أو الدرس…" value="' + esc(S.filters.q) + '" autocomplete="off"></div>' +
        '<select class="rl-select" id="rl-type"><option value="">كل الأنواع</option>' +
          Object.keys(cfg.types).map(function (k) { return '<option value="' + k + '"' + (S.filters.type === k ? ' selected' : '') + '>' + esc(cfg.types[k]) + '</option>'; }).join('') + '</select>' +
        '<select class="rl-select" id="rl-status"><option value="">كل الحالات</option>' +
          [['visible', 'ظاهر للطلاب'], ['hidden', 'موقوف'], ['draft', 'مسودة / بلا مكان'], ['blocked', 'مخفي بسبب درس/وحدة'], ['excluded', 'فيه استثناءات'], ['issues', 'فيه مشاكل']]
            .map(function (o) { return '<option value="' + o[0] + '"' + (S.filters.status === o[0] ? ' selected' : '') + '>' + o[1] + '</option>'; }).join('') + '</select>' +
        (isFiltering() ? '<button type="button" class="rl-btn rl-btn--ghost" data-act="clear-filters"><i class="bi bi-x-circle"></i> مسح التصفية</button>' : '') +
        '<span class="rl-sep"></span>' +
        '<button type="button" class="rl-btn rl-btn--danger" data-act="bulk-videos-off" title="يوقف عرض كل الفيديوهات عن الطلاب نهائياً (يمكنك إعادة تشغيلها)"><i class="bi bi-camera-video-off"></i> إيقاف كل الفيديوهات</button>' +
        '<button type="button" class="rl-btn" data-act="bulk-videos-on"><i class="bi bi-camera-video"></i> تشغيلها</button>' +
        '<span class="rl-sep"></span>' +
        '<button type="button" class="rl-btn" data-act="as-student" title="شاهد ما يراه الطلاب بالضبط"><i class="bi bi-person-video3"></i> كما يراها الطالب</button>' +
        '<button type="button" class="rl-btn" data-act="health" title="استخراج الأخطاء"><i class="bi bi-heart-pulse"></i> فحص الجودة' + (S.tree.stats.issues ? ' <span class="rl-chip rl-chip--warning">' + S.tree.stats.issues + '</span>' : '') + '</button>' +
        '<button type="button" class="rl-btn" data-act="trash"><i class="bi bi-trash3"></i> المحذوفات</button>' +
        '<button type="button" class="rl-btn" data-act="activity"><i class="bi bi-clock-history"></i> سجل النشاط</button>' +
        '<span class="rl-help mb-0 w-100" style="margin-top:.1rem">إيقاف الفيديوهات يطبَّق على: ' + esc(scopeLabel) + ' — ولا يحذف أي ملف.</span>' +
        '</div>';
    }

    function groupName(id) {
      const g = S.tree.groups.filter(function (x) { return x.id === Number(id); })[0];
      return g ? g.name : '';
    }

    function tabsHtml() {
      const groups = S.tree.groups.filter(function (g) { return cfg.isAdmin || g.mine; });
      if (!groups.length) return '';

      return '<nav class="rl-tabs" aria-label="التبويبات">' +
        '<button type="button" class="rl-tab' + (!S.group ? ' is-active' : '') + '" data-act="tab" data-group=""><i class="bi bi-collection"></i> ' +
          (cfg.isAdmin ? 'المحتوى المشترك (كل المجموعات)' : 'المحتوى المشترك (كل مجموعاتي)') + '</button>' +
        groups.map(function (g) {
          return '<button type="button" class="rl-tab' + (S.group === g.id ? ' is-active' : '') + '" data-act="tab" data-group="' + g.id + '" title="' + esc(g.name) + '"><i class="bi bi-people"></i> ' + esc(g.name) + ' <small>(' + g.students + ')</small></button>';
        }).join('') + '</nav>';
    }

    // ---- body

    function bodyHtml() {
      const t = S.tree;
      let html = '';

      if (!t.units.length && !t.general.length && !t.unplaced.length) {
        return '<div class="rl-section"><div class="rl-empty"><i class="bi ' + (isFiltering() ? 'bi-funnel' : 'bi-inbox') + '"></i>' +
          (isFiltering() ? 'لا توجد نتائج مطابقة للتصفية.' : 'لا يوجد محتوى بعد. ابدأ بإضافة وحدة ثم دروس ثم موارد.') + '</div></div>';
      }

      html += '<div class="rl-units" data-sort="units">' + t.units.map(function (unit, index) { return unitHtml(unit, index); }).join('') + '</div>';

      html += sectionHtml('general', 'مرفقات عامة (بدون وحدة)', 'bi-collection', t.general, { kind: 'general' }, '<button type="button" class="rl-btn rl-btn--sm" data-act="add-resource" data-place="general"><i class="bi bi-plus-lg"></i> مورد</button>');

      if (t.unplaced.length) {
        html += sectionHtml('unplaced', 'موارد بلا مكان — لا يراها أي طالب', 'bi-exclamation-triangle', t.unplaced, { kind: 'none' }, '', 'rl-section--warn');
      }

      return html;
    }

    function isOpen(id, fallback) {
      return Object.prototype.hasOwnProperty.call(S.open, id) ? S.open[id] : fallback;
    }

    function sectionHtml(id, title, icon, resources, container, actions, extraClass) {
      const open = isOpen(id, resources.length > 0 && resources.length < 12);
      return '<div class="rl-section ' + (extraClass || '') + (open ? ' is-open' : '') + '" data-id="' + id + '">' +
        '<div class="rl-section-head" data-act="toggle" data-id="' + id + '"><i class="bi bi-chevron-left rl-caret"></i><i class="bi ' + icon + '" style="color:var(--rl-accent)"></i>' +
        '<div class="rl-name"><span class="rl-name-text">' + esc(title) + '</span><span class="rl-meta">' + resources.length + ' مورد</span></div>' +
        '<div class="rl-head-actions">' + (actions || '') + '</div></div>' +
        '<div class="rl-section-body">' + resourceList(resources, container) + '</div></div>';
    }

    function resourceList(resources, container) {
      if (!resources.length) return '<div class="rl-empty">' + (isFiltering() ? 'لا توجد موارد مطابقة.' : 'لا توجد موارد هنا.') + '</div>';

      return '<div class="rl-res-list" data-sort="resources" data-kind="' + container.kind + '"' + (container.key ? ' data-key="' + esc(container.key) + '"' : '') + '>' +
        resources.map(function (r) { return resHtml(r); }).join('') + '</div>';
    }

    function audienceTags(r) {
      let html = '';

      if (r.shared) html += '<span class="rl-chip rl-chip--all"><i class="bi bi-globe2"></i> كل المجموعات</span>';
      r.groups.forEach(function (g) {
        if (r.shared && !g.paused) return;
        html += '<span class="rl-chip ' + (g.paused ? 'rl-chip--paused' : 'rl-chip--group') + '" title="' + (g.paused ? 'موقوف مؤقتاً لهذه المجموعة' : 'تراه هذه المجموعة') + '">' + esc(g.name) + (g.paused ? ' ⏸' : '') + '</span>';
      });
      if (r.other_groups > 0 && !r.shared) html += '<span class="rl-chip rl-chip--secondary" title="مجموعات تتبع معلمين آخرين">+' + r.other_groups + ' أخرى</span>';
      if (!r.shared && !r.groups.length) html += '<span class="rl-chip rl-chip--secondary">بلا جمهور</span>';

      return html;
    }

    function resHtml(r) {
      const sizeLabel = r.size_label ? ' · ' + r.size_label : '';
      const on = r.state !== 'hidden';
      const places = r.placements.length;
      const offHere = r.placement_active === false;

      return '<div class="rl-res rl-s-' + r.state + (on ? '' : ' is-off') + '" data-key="' + esc(r.key) + '" data-id="' + r.id + '" data-kind="resource">' +
        '<span class="rl-handle" title="اسحب لإعادة الترتيب"><i class="bi bi-grip-vertical"></i></span>' +
        '<span class="rl-icon t-' + esc(r.type) + '"><i class="bi ' + esc(r.icon) + '"></i></span>' +
        '<div class="rl-main"><div class="rl-title" title="' + esc(r.title) + '">' + esc(r.title) + '</div>' +
          '<div class="rl-sub">' + esc(r.type_label) + sizeLabel + (r.created_by ? ' · ' + esc(r.created_by) : '') + '</div>' +
          '<div class="rl-tags">' +
            '<span class="rl-chip rl-chip--' + esc(r.state_tone) + '">' + esc(r.state_label) + '</span>' +
            (offHere ? '<span class="rl-chip rl-chip--warning">مخفي في هذا المكان</span>' : '') +
            RL.missingFileChip(r) +
            audienceTags(r) +
            (places > 1 ? '<span class="rl-chip rl-chip--place" title="' + esc(r.placements.map(function (p) { return p.label; }).join(' | ')) + '"><i class="bi bi-diagram-3"></i> ' + places + ' أماكن</span>' : '') +
            (r.exclusions ? '<button type="button" class="rl-chip rl-chip--excl" data-act="students" title="الطلاب المستثنون"><i class="bi bi-person-x"></i> ' + r.exclusions + ' مستثنى</button>' : '') +
          '</div></div>' +
        '<div class="rl-actions">' +
          RL.switchEl(on, { title: RL.powerTitle(r, on), disabled: !r.can.toggle, raw: 'data-act="power"' }, r.state === 'partial' ? 'is-partial' : '') +
          '<button type="button" class="rl-btn rl-btn--icon rl-btn--ghost" data-act="preview" title="معاينة"><i class="bi bi-eye"></i></button>' +
          '<button type="button" class="rl-btn rl-btn--icon rl-btn--ghost" data-act="students" title="الطلاب والاستثناءات"><i class="bi bi-person-x"></i></button>' +
          '<button type="button" class="rl-btn rl-btn--icon rl-btn--ghost" data-act="scope" title="المشاركة والأماكن"><i class="bi bi-share"></i></button>' +
          '<div class="rl-menu"><button type="button" class="rl-btn rl-btn--icon rl-btn--ghost" data-menu title="المزيد"><i class="bi bi-three-dots-vertical"></i></button><div class="rl-menu-list">' +
            (r.can.manage ? '<button type="button" data-act="edit"><i class="bi bi-pencil"></i> تعديل</button>' : '') +
            (r.is_external ? '<button type="button" data-act="copy-link"><i class="bi bi-clipboard"></i> نسخ الرابط</button>' : '') +
            '<hr><button type="button" class="is-danger" data-act="delete"><i class="bi bi-trash"></i> ' + (r.can.manage ? 'حذف' : 'إيقاف عن مجموعاتي') + '</button>' +
          '</div></div>' +
        '</div></div>';
    }

    function containerChips(c) {
      if (c.audience_shared) return '<span class="rl-chip rl-chip--all"><i class="bi bi-globe2"></i> كل المجموعات</span>';
      const groups = c.audience_groups;
      if (!groups.length) return '';

      return groups.slice(0, 3).map(function (g) { return '<span class="rl-chip rl-chip--group">' + esc(g.name) + '</span>'; }).join('') +
        (groups.length > 3 ? '<span class="rl-chip rl-chip--secondary">+' + (groups.length - 3) + '</span>' : '');
    }

    function unitHtml(unit, index) {
      const open = isOpen('u' + unit.id, index === 0);
      const lessonsCount = unit.lessons.length;
      const total = unit.resources_count + unit.lessons.reduce(function (sum, l) { return sum + l.resources_count; }, 0);
      const hidden = unit.hidden_count + unit.lessons.reduce(function (sum, l) { return sum + l.hidden_count; }, 0);

      return '<div class="rl-unit' + (open ? ' is-open' : '') + (unit.is_active ? '' : ' is-off') + '" data-key="' + esc(unit.key) + '" data-kind="unit">' +
        '<div class="rl-unit-head" data-act="toggle" data-id="u' + unit.id + '">' +
          '<i class="bi bi-chevron-left rl-caret"></i><span class="rl-num">' + (index + 1) + '</span>' +
          '<div class="rl-name"><span class="rl-name-text">' + esc(unit.name) + '</span>' +
            '<span class="rl-meta">' + lessonsCount + ' دروس · ' + total + ' مورد' + (hidden ? ' · ' + hidden + ' موقوف' : '') + (unit.exclusions ? ' · ' + unit.exclusions + ' طالب مستثنى' : '') + (unit.is_active ? '' : ' · <b style="color:var(--rl-danger)">الوحدة موقوفة</b>') + '</span></div>' +
          '<span class="d-none d-lg-inline-flex gap-1">' + containerChips(unit) + '</span>' +
          '<div class="rl-head-actions" data-stop>' + containerActions('unit', unit) + '</div></div>' +
        '<div class="rl-unit-body">' +
          (unit.resources.length ? '<div class="rl-help mb-0 mt-2"><i class="bi bi-folder2-open"></i> موارد الوحدة مباشرة</div>' + resourceList(unit.resources, { kind: 'unit', key: unit.key }) : '') +
          '<div class="rl-lessons" data-sort="lessons" data-unit="' + esc(unit.key) + '">' + unit.lessons.map(lessonHtml).join('') + '</div>' +
          (lessonsCount ? '' : '<div class="rl-empty">لا توجد دروس في هذه الوحدة.</div>') +
        '</div></div>';
    }

    function lessonHtml(lesson) {
      const open = isOpen('l' + lesson.id, false);

      return '<div class="rl-lesson' + (open ? ' is-open' : '') + (lesson.is_active ? '' : ' is-off') + '" data-key="' + esc(lesson.key) + '" data-kind="lesson">' +
        '<div class="rl-lesson-head" data-act="toggle" data-id="l' + lesson.id + '">' +
          '<i class="bi bi-chevron-left rl-caret"></i><i class="bi bi-journal-text" style="color:var(--rl-accent)"></i>' +
          '<div class="rl-name"><span class="rl-name-text">' + esc(lesson.name) + '</span>' +
            '<span class="rl-meta">' + lesson.resources_count + ' مورد' + (lesson.hidden_count ? ' · ' + lesson.hidden_count + ' موقوف' : '') + (lesson.exclusions ? ' · ' + lesson.exclusions + ' طالب مستثنى' : '') + (lesson.is_active ? '' : ' · <b style="color:var(--rl-danger)">الدرس موقوف</b>') + '</span></div>' +
          '<span class="d-none d-xl-inline-flex gap-1">' + containerChips(lesson) + '</span>' +
          '<div class="rl-head-actions" data-stop>' + containerActions('lesson', lesson) + '</div></div>' +
        '<div class="rl-lesson-body">' + resourceList(lesson.resources, { kind: 'lesson', key: lesson.key }) +
          '<div class="mt-2"><button type="button" class="rl-btn rl-btn--sm" data-act="add-resource" data-place="lesson:' + esc(lesson.key) + '"><i class="bi bi-plus-lg"></i> إضافة مورد / رفع فيديو لهذا الدرس</button></div></div></div>';
    }

    function containerActions(kind, c) {
      const label = kind === 'unit' ? 'الوحدة' : 'الدرس';

      return RL.switchEl(c.is_active, { title: (c.is_active ? 'إيقاف ' : 'تشغيل ') + label + ' للطلاب', raw: 'data-act="power"' }) +
        '<button type="button" class="rl-btn rl-btn--icon rl-btn--ghost" data-act="students" title="استثناء طلاب من ' + label + '"><i class="bi bi-person-x"></i></button>' +
        '<button type="button" class="rl-btn rl-btn--icon rl-btn--ghost" data-act="scope" title="مشاركة ' + label + ' مع مجموعات"><i class="bi bi-share"></i></button>' +
        '<div class="rl-menu"><button type="button" class="rl-btn rl-btn--icon rl-btn--ghost" data-menu title="المزيد"><i class="bi bi-three-dots-vertical"></i></button><div class="rl-menu-list">' +
          (kind === 'unit' ? '<button type="button" data-act="add-lesson"><i class="bi bi-journal-plus"></i> إضافة درس</button><button type="button" data-act="add-resource" data-place="unit:' + esc(c.key) + '"><i class="bi bi-cloud-arrow-up"></i> إضافة مورد للوحدة مباشرة</button>'
            : '<button type="button" data-act="add-resource" data-place="lesson:' + esc(c.key) + '"><i class="bi bi-cloud-arrow-up"></i> إضافة مورد</button>') +
          (c.can_manage ? '<button type="button" data-act="rename"><i class="bi bi-pencil"></i> تعديل الاسم</button><hr><button type="button" class="is-danger" data-act="delete"><i class="bi bi-trash"></i> حذف ' + label + ' (يمكن التراجع)</button>' : '') +
        '</div></div>';
    }

    // ----------------------------------------------------------------- sorting

    function initSortables() {
      if (isFiltering()) return;

      RL.$$('[data-sort="units"]', root).forEach(function (el) {
        RL.sortable(el, {
          handle: '.rl-unit-head', filter: '[data-stop], button, input, label', preventOnFilter: false,
          onEnd: function () {
            RL.post('subjects/' + cfg.subject.key + '/reorder/units', { order: RL.$$(':scope > .rl-unit', el).map(function (u) { return u.getAttribute('data-key'); }) }).catch(RL.fail);
          },
        });
      });

      RL.$$('[data-sort="lessons"]', root).forEach(function (el) {
        RL.sortable(el, {
          handle: '.rl-lesson-head', filter: '[data-stop], button, input, label', preventOnFilter: false,
          onEnd: function () {
            RL.post('subjects/' + cfg.subject.key + '/reorder/lessons', { order: RL.$$(':scope > .rl-lesson', el).map(function (u) { return u.getAttribute('data-key'); }) }).catch(RL.fail);
          },
        });
      });

      RL.$$('[data-sort="resources"]', root).forEach(function (el) {
        const kind = el.getAttribute('data-kind');
        if (kind === 'none') return;

        RL.sortable(el, {
          handle: '.rl-handle',
          onEnd: function () {
            const container = kind === 'general' ? { type: 'general' } : { type: kind, key: el.getAttribute('data-key') };
            RL.post('subjects/' + cfg.subject.key + '/reorder/resources', {
              container: container,
              order: RL.$$(':scope > .rl-res', el).map(function (r) { return r.getAttribute('data-key'); }),
            }).catch(RL.fail);
          },
        });
      });
    }

    function flash(id) {
      const el = RL.$$('.rl-res', root).filter(function (e) { return e.getAttribute('data-id') === String(id); })[0];
      if (!el) return;
      for (let p = el.parentNode; p && p !== root; p = p.parentNode) if (p.classList && /rl-(unit|lesson|section)/.test(p.className)) p.classList.add('is-open');
      el.classList.add('is-flash');
      el.scrollIntoView({ block: 'center', behavior: 'smooth' });
    }

    // ----------------------------------------------------------------- events

    function target(event) {
      const button = event.target.closest('[data-act]');
      if (!button || !root.contains(button)) return null;

      const node = button.closest('[data-kind]');
      const key = node ? node.getAttribute('data-key') : null;
      const kind = node ? node.getAttribute('data-kind') : null;

      return {
        button: button,
        act: button.getAttribute('data-act'),
        kind: kind,
        key: key,
        data: kind === 'resource' ? S.index[key] : (S.containers[key] || null),
      };
    }

    root.addEventListener('click', function (event) {
      if (event.target.closest('label.rl-switch')) return;      // the switch is handled on change
      if (event.target.closest('[data-menu]')) return;          // the menu opens in rl-core

      const t = target(event);
      if (!t) return;

      // clicks on the action area of a header must not fold / unfold it
      if (t.act === 'toggle' && event.target.closest('[data-stop]')) return;

      switch (t.act) {
        case 'toggle': {
          const id = t.button.getAttribute('data-id');
          const node = t.button.parentNode;
          const open = !node.classList.contains('is-open');
          node.classList.toggle('is-open', open);
          S.open[id] = open;
          RL.store.set('open:' + cfg.subject.id, S.open);
          break;
        }
        case 'tab':
          S.group = t.button.getAttribute('data-group') ? Number(t.button.getAttribute('data-group')) : null;
          pushState();
          fetchTree();
          break;
        case 'filter-status':
          S.filters.status = t.button.getAttribute('data-status') === S.filters.status ? '' : t.button.getAttribute('data-status');
          fetchTree();
          break;
        case 'clear-filters':
          S.filters = { q: '', type: '', status: '' };
          fetchTree();
          break;

        case 'add-unit': RL.Forms.unit(); break;
        case 'add-lesson': RL.Forms.lesson({ unitKey: t.key }); break;
        case 'add-resource': {
          const place = t.button.getAttribute('data-place');
          RL.Forms.resource({ places: place ? [place] : [] });
          break;
        }
        case 'rename':
          t.kind === 'unit'
            ? RL.Forms.unit({ unit: { key: t.key, name: t.data.name, name_en: t.data.name_en } })
            : RL.Forms.lesson({ lesson: { key: t.key, name: t.data.name, name_en: t.data.name_en } });
          break;
        case 'edit': RL.Forms.resource({ resource: t.data }); break;

        case 'scope': RL.Panels.scope(t.kind, t.key, t.kind === 'resource' ? t.data.title : t.data.name); break;
        case 'students': RL.Panels.students(t.kind, t.key, t.kind === 'resource' ? t.data.title : t.data.name); break;
        case 'preview': RL.Panels.media(t.data); break;
        case 'copy-link':
          if (navigator.clipboard) navigator.clipboard.writeText(t.data.url).then(function () { RL.toast({ message: 'تم نسخ الرابط.', type: 'info' }); });
          break;

        case 'delete': deleteTarget(t); break;

        case 'bulk-videos-off': bulkVideos(false); break;
        case 'bulk-videos-on': bulkVideos(true); break;

        case 'as-student': RL.Panels.asStudent(); break;
        case 'health': RL.Panels.health(); break;
        case 'trash': RL.Panels.trash(); break;
        case 'activity': RL.Panels.activity(); break;
        default:
      }
    });

    // the big switch: stop / resume showing a resource, lesson or unit to students
    root.addEventListener('change', function (event) {
      const input = event.target.closest('input[type="checkbox"]');
      const label = input && input.closest('label.rl-switch');
      if (!input || !label || label.querySelector('input').getAttribute('data-act') !== 'power') return;

      const node = input.closest('[data-kind]');
      const kind = node.getAttribute('data-kind');
      const key = node.getAttribute('data-key');
      const wanted = input.checked;

      input.disabled = true;
      RL.act(RL.post(RL.plural(kind) + '/' + key + '/active', { active: wanted })).then(function (res) {
        if (!res) input.checked = !wanted;
        input.disabled = false;
      });
    });

    function deleteTarget(t) {
      const isResource = t.kind === 'resource';
      const name = isResource ? t.data.title : t.data.name;
      const manage = isResource ? t.data.can.manage : true;
      const what = isResource ? 'المورد' : (t.kind === 'unit' ? 'الوحدة' : 'الدرس');

      RL.confirm({
        title: manage ? 'حذف ' + what : 'إيقاف عن مجموعاتك',
        text: manage
          ? 'سيُحذف ' + what + ' «<b>' + esc(name) + '</b>»' + (isResource ? '' : ' مع ما يخصّه من موارد') + ' ويختفي عن الطلاب. <br>يبقى في «المحذوفات» ويمكنك التراجع الآن بزر <b>تراجع</b>.'
          : 'هذا المورد يخدم مجموعات لا تتبعك، لذلك لن يُحذف؛ سيتوقف فقط عن مجموعاتك. يمكنك التراجع.',
        confirmText: manage ? 'حذف' : 'إيقاف', danger: true,
      }).then(function (ok) {
        if (ok) RL.act(RL.del(RL.plural(t.kind) + '/' + t.key));
      });
    }

    function bulkVideos(active) {
      const count = S.tree.stats.videos;
      if (!count) return RL.toast({ message: 'لا توجد فيديوهات في هذا العرض.', type: 'info' });

      const scope = S.group ? 'مجموعة «' + groupName(S.group) + '»' : (cfg.isAdmin ? 'كل المجموعات' : 'كل مجموعاتك');

      RL.confirm({
        title: active ? 'تشغيل كل الفيديوهات' : 'إيقاف كل الفيديوهات',
        text: active
          ? 'سيعود عرض <b>' + count + '</b> فيديو لـ ' + esc(scope) + ' حسب جمهور كل فيديو.'
          : 'سيتوقف عرض <b>' + count + '</b> فيديو عن طلاب ' + esc(scope) + ' <b>فوراً</b>. لا يُحذف أي شيء، ويمكنك إعادة التشغيل أو التراجع.',
        confirmText: active ? 'تشغيل' : 'إيقاف الكل', danger: !active,
      }).then(function (ok) {
        if (ok) RL.act(RL.post('subjects/' + cfg.subject.key + '/bulk/active', { active: active, type: 'video', group: S.group }));
      });
    }

    // filters
    root.addEventListener('input', RL.debounce(function (event) {
      if (event.target.id !== 'rl-q') return;
      S.filters.q = event.target.value.trim();
      fetchTree();
    }, 280));

    root.addEventListener('change', function (event) {
      if (event.target.id === 'rl-type') { S.filters.type = event.target.value; fetchTree(); }
      if (event.target.id === 'rl-status') { S.filters.status = event.target.value; fetchTree(); }
    });

    function pushState() {
      const url = new URL(window.location.href);
      S.group ? url.searchParams.set('group', S.group) : url.searchParams.delete('group');
      window.history.replaceState({}, '', url);
    }

    // anything that changed data anywhere (toast undo, panels, forms...) refreshes the tree
    RL.bus.on('changed', function () { fetchTree(); });

    // first paint comes from the server so there is no empty flash
    if (cfg.tree) {
      setTree(cfg.tree);
      render();
    } else {
      root.innerHTML = '<div class="rl-skeleton"></div><div class="rl-skeleton"></div><div class="rl-skeleton"></div>';
      fetchTree();
    }

    // deep link from the library: ?focus=<resource id> arrives as cfg.focusId
    if (cfg.focusId) setTimeout(function () { flash(cfg.focusId); }, 400);
  };

})(window, document);
