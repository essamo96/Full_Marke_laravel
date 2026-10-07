/**
 * Resource library workspace - the library: every resource of every subject the user manages,
 * as one table (DataTables). Two views over the same rows:
 *
 *   list     title, state, audience, places, exclusions, kill switch, actions
 *   matrix   one column per group: a click shows / stops the resource for that group
 *
 *   RL.Library.mount(rootElement)
 */
(function (window, document) {
  'use strict';

  const RL = window.RL;
  const esc = RL.esc;

  RL.Library = {};

  RL.Library.mount = function (root) {
    const cfg = RL.config;
    RL.readOnlyNotice(root);
    const query = new URL(window.location.href).searchParams;

    const S = {
      subject: query.get('subject') || cfg.subject || '',
      group: '',
      type: '',
      state: '',
      trashed: false,
      view: query.get('view') === 'matrix' ? 'matrix' : 'list',
      rows: [],
      subjects: cfg.subjects || [],
      groups: [],
      stats: {},
      table: null,
      byKey: {},
    };

    // -------------------------------------------------------------- fetching

    function load() {
      return RL.get('rows', { subject: S.subject, group: S.group, type: S.type, state: S.state, trashed: S.trashed ? 1 : 0 }).then(function (res) {
        S.rows = res.rows;
        S.subjects = res.subjects;
        S.groups = res.groups || [];
        S.stats = res.stats;
        S.byKey = {};
        S.rows.forEach(function (r) { S.byKey[r.key] = r; });
        paint();
      }).catch(function (err) {
        root.innerHTML = '<div class="rl-note rl-note--bad"><i class="bi bi-x-octagon"></i><div>' + esc(err.message) + '</div></div>';
      });
    }

    // ---------------------------------------------------------------- layout

    function shell() {
      root.innerHTML =
        '<div class="rl-head"><h1 class="rl-title-main"><i class="bi bi-archive me-2" style="color:var(--rl-accent)"></i>مكتبة الموارد</h1><span class="rl-spacer"></span>' +
          '<div class="rl-flex" id="rl-view-switch"></div></div>' +
        '<div class="rl-stats" id="rl-lib-stats"></div>' +
        '<div class="rl-toolbar" id="rl-lib-toolbar"></div>' +
        '<div class="rl-table-wrap rl-table-wrap--cards"><table class="rl-table" id="rl-lib-table"></table></div>';

      paintToolbar();
    }

    function paintToolbar() {
      const bar = RL.$('#rl-lib-toolbar', root);

      bar.innerHTML =
        '<select class="rl-select" id="rl-f-subject"><option value="">كل المواد</option>' +
          S.subjects.map(function (s) { return '<option value="' + s.id + '"' + (String(S.subject) === String(s.id) ? ' selected' : '') + '>' + esc(s.name) + '</option>'; }).join('') + '</select>' +
        '<select class="rl-select" id="rl-f-group"' + (S.subject ? '' : ' disabled') + '><option value="">كل المجموعات</option>' +
          S.groups.map(function (g) { return '<option value="' + g.id + '"' + (String(S.group) === String(g.id) ? ' selected' : '') + '>' + esc(g.name) + '</option>'; }).join('') + '</select>' +
        '<select class="rl-select" id="rl-f-type"><option value="">كل الأنواع</option>' +
          Object.keys(cfg.types).map(function (k) { return '<option value="' + k + '"' + (S.type === k ? ' selected' : '') + '>' + esc(cfg.types[k]) + '</option>'; }).join('') + '</select>' +
        '<select class="rl-select" id="rl-f-state"><option value="">كل الحالات</option>' +
          [['visible', 'ظاهر للطلاب'], ['hidden', 'موقوف'], ['draft', 'مسودة / بلا مكان'], ['blocked', 'مخفي بسبب درس/وحدة'], ['excluded', 'فيه استثناءات'], ['issues', 'فيه مشاكل']]
            .map(function (o) { return '<option value="' + o[0] + '"' + (S.state === o[0] ? ' selected' : '') + '>' + o[1] + '</option>'; }).join('') + '</select>' +
        '<label class="rl-flex" style="cursor:pointer"><input type="checkbox" id="rl-f-trashed"' + (S.trashed ? ' checked' : '') + '> المحذوفات فقط</label>' +
        '<span class="rl-sep"></span>' +
        (S.subject
          ? '<button type="button" class="rl-btn" data-act="manage"><i class="bi bi-diagram-3"></i> فتح شاشة المحتوى</button>' +
            '<button type="button" class="rl-btn" data-act="health"><i class="bi bi-heart-pulse"></i> فحص الجودة</button>'
          : '<span class="rl-help mb-0">اختر مادة لعرض مصفوفة التوزيع على المجموعات وفحص الجودة.</span>') +
        '<button type="button" class="rl-btn" data-act="uploads" title="ملفات رُفعت ولم يُحفظ لها مورد"><i class="bi bi-cloud-upload"></i> الملفات المرفوعة</button>';

      RL.$('#rl-view-switch', root).innerHTML =
        '<button type="button" class="rl-btn' + (S.view === 'list' ? ' rl-btn--primary' : '') + '" data-act="view" data-view="list"><i class="bi bi-list-ul"></i> قائمة</button>' +
        '<button type="button" class="rl-btn' + (S.view === 'matrix' ? ' rl-btn--primary' : '') + '" data-act="view" data-view="matrix"' + (S.subject ? '' : ' disabled title="اختر مادة أولاً"') + '><i class="bi bi-grid-3x3-gap"></i> مصفوفة التوزيع</button>';
    }

    function paintStats() {
      const s = S.stats;
      RL.$('#rl-lib-stats', root).innerHTML =
        RL.chip(s.resources + ' مورد', 'info') + ' ' + RL.chip(s.videos + ' فيديو', 'secondary') + ' ' + RL.chip(s.visible + ' ظاهر', 'success') + ' ' +
        RL.chip(s.hidden + ' موقوف', s.hidden ? 'danger' : 'secondary') + ' ' + RL.chip(s.draft + ' مسودة/بلا مكان', s.draft ? 'warning' : 'secondary') + ' ' +
        RL.chip(s.excluded + ' فيها استثناءات', 'secondary') + ' ' + RL.chip(s.issues + ' فيها مشاكل', s.issues ? 'warning' : 'secondary');
    }

    // ----------------------------------------------------------------- cells

    function resourceCell(r) {
      return '<div class="rl-flex" style="flex-wrap:nowrap"><span class="rl-icon t-' + esc(r.type) + '"><i class="bi ' + esc(r.icon) + '"></i></span>' +
        '<div style="min-width:0"><div class="rl-title rl-title--clamp" title="' + esc(r.title) + '">' + esc(r.title) + '</div>' +
        '<div class="rl-sub">' + esc(r.type_label) + (r.size_label ? ' · ' + r.size_label : '') + (r.created_at ? ' · ' + r.created_at : '') + '</div>' +
        (r.file_missing && !S.trashed ? '<div class="rl-tags">' + RL.missingFileChip(r) + '</div>' : '') + '</div></div>';
    }

    function audienceCell(r) {
      let html = '';
      if (r.shared) html += '<span class="rl-chip rl-chip--all"><i class="bi bi-globe2"></i> كل المجموعات</span> ';
      r.groups.forEach(function (g) {
        if (r.shared && !g.paused) return;
        html += '<span class="rl-chip ' + (g.paused ? 'rl-chip--paused' : 'rl-chip--group') + '">' + esc(g.name) + (g.paused ? ' ⏸' : '') + '</span> ';
      });
      if (r.other_groups > 0 && !r.shared) html += '<span class="rl-chip rl-chip--secondary">+' + r.other_groups + ' أخرى</span>';
      return html || '<span class="rl-chip rl-chip--secondary">بلا جمهور</span>';
    }

    function placesCell(r) {
      if (!r.placements.length) return '<span class="rl-chip rl-chip--secondary">بلا مكان</span>';
      const first = r.placements.slice(0, 2).map(function (p) { return '<span class="rl-chip rl-chip--place" title="' + esc(p.label) + '">' + esc(p.label.length > 34 ? p.label.slice(0, 33) + '…' : p.label) + '</span>'; }).join(' ');
      return first + (r.placements.length > 2 ? ' <span class="rl-chip rl-chip--secondary">+' + (r.placements.length - 2) + '</span>' : '');
    }

    function actionsCell(r) {
      if (S.trashed) {
        return '<button type="button" class="rl-btn rl-btn--sm rl-btn--primary" data-act="restore"><i class="bi bi-arrow-counterclockwise"></i> استرجاع</button>';
      }

      const on = r.state !== 'hidden';
      return '<div class="rl-row-actions">' +
        RL.switchEl(on, { title: RL.powerTitle(r, on), disabled: !r.can.toggle, raw: 'data-act="power"' }, r.state === 'partial' ? 'is-partial' : '') +
        '<button type="button" class="rl-btn rl-btn--icon rl-btn--ghost" data-act="preview" title="معاينة"><i class="bi bi-eye"></i></button>' +
        '<button type="button" class="rl-btn rl-btn--icon rl-btn--ghost" data-act="students" title="الطلاب والاستثناءات"><i class="bi bi-person-x"></i></button>' +
        '<button type="button" class="rl-btn rl-btn--icon rl-btn--ghost" data-act="scope" title="المشاركة والأماكن"><i class="bi bi-share"></i></button>' +
        '<button type="button" class="rl-btn rl-btn--icon rl-btn--ghost" data-act="open" title="فتحه في شاشة المحتوى"><i class="bi bi-box-arrow-up-left"></i></button>' +
        '<button type="button" class="rl-btn rl-btn--icon rl-btn--ghost" data-act="delete" title="' + (r.can.manage ? 'حذف' : 'إيقاف عن مجموعاتي') + '"><i class="bi bi-trash"></i></button></div>';
    }

    /** matrix cell: does group g see the resource, and what does a click do? */
    function cellState(r, group) {
      const link = r.groups.filter(function (g) { return g.id === group.id; })[0];

      if (link && link.paused) return { cls: 'is-paused', icon: 'bi-pause-fill', title: 'موقوف مؤقتاً — اضغط لإعادة التشغيل', act: 'resume' };
      if (r.shared) return { cls: 'is-all', icon: 'bi-check2-all', title: 'يراه ضمن «كل المجموعات» — اضغط لإيقافه عنها', act: 'pause' };
      if (link) return { cls: 'is-on', icon: 'bi-check-lg', title: 'تراه هذه المجموعة — اضغط لإزالتها', act: 'detach' };
      return { cls: '', icon: 'bi-plus-lg', title: 'اضغط لإظهاره لهذه المجموعة', act: 'attach' };
    }

    // ----------------------------------------------------------------- table

    // `label` names the cell when a narrow screen shows each row as a card (data-label)
    function columns() {
      const cols = [
        { title: 'المورد', data: 'title', className: 'rl-col-main', render: function (d, t, r) { return t === 'display' ? resourceCell(r) : d; } },
      ];

      if (!S.subject) cols.push({ title: 'المادة', label: 'المادة', data: 'subject', render: function (d) { return esc(d); } });

      cols.push({
        title: 'الحالة', label: 'الحالة', data: 'state_label', className: 'rl-nowrap',
        render: function (d, t, r) { return t === 'display' ? '<span class="rl-chip rl-chip--' + esc(r.state_tone) + '">' + esc(d) + '</span>' : d; },
      });

      if (S.view === 'matrix') {
        S.groups.filter(function (g) { return cfg.isAdmin || g.mine; }).forEach(function (group) {
          cols.push({
            title: esc(group.name), label: group.name, data: null, orderable: false, className: 'rl-matrix-cell',
            render: function (d, t, r) {
              if (t !== 'display') return '';
              const c = cellState(r, group);
              return '<button type="button" class="rl-cell-btn ' + c.cls + '" data-act="cell" data-cell="' + c.act + '" data-group="' + group.id + '" title="' + esc(c.title) + '"' + (S.trashed ? ' disabled' : '') + '><i class="bi ' + c.icon + '"></i></button>';
            },
          });
        });
        cols.push({ title: 'الأماكن', label: 'الأماكن', data: null, orderable: false, render: function (d, t, r) { return t === 'display' ? placesCell(r) : ''; } });
      } else {
        cols.push({ title: 'الجمهور', label: 'الجمهور', data: null, orderable: false, render: function (d, t, r) { return t === 'display' ? audienceCell(r) : ''; } });
        cols.push({ title: 'الأماكن', label: 'الأماكن', data: null, orderable: false, render: function (d, t, r) { return t === 'display' ? placesCell(r) : ''; } });
        cols.push({ title: 'استثناءات', label: 'استثناءات', data: 'exclusions', className: 'text-center', render: function (d, t, r) { return t === 'display' ? (d ? '<button type="button" class="rl-chip rl-chip--excl" data-act="students"><i class="bi bi-person-x"></i> ' + d + '</button>' : '—') : d; } });
      }

      cols.push({ title: '', data: null, orderable: false, className: 'text-end rl-col-actions', render: function (d, t, r) { return t === 'display' ? actionsCell(r) : ''; } });

      cols.forEach(function (col) {
        if (!col.label) return;
        const render = col.render;
        col.render = function (d, t, r, meta) { const out = render(d, t, r, meta); return t === 'display' ? '<div class="rl-cell-val">' + out + '</div>' : out; };
      });

      return cols;
    }

    function labelCells(cells, cols) {
      cells.forEach(function (td, i) { if (cols[i] && cols[i].label) td.setAttribute('data-label', cols[i].label); });
    }

    function paint() {
      paintToolbar();
      paintStats();

      const jq = window.jQuery;
      const tableEl = RL.$('#rl-lib-table', root);
      tableEl.classList.toggle('rl-is-matrix', S.view === 'matrix');

      if (jq && jq.fn && jq.fn.DataTable) {
        // columns differ between views, so the table is rebuilt (the page is kept when only the rows change)
        const signature = S.view + '|' + S.subject + '|' + S.trashed + '|' + S.groups.map(function (g) { return g.id; }).join(',');

        if (S.table && S.table.__signature === signature) {
          S.table.clear().rows.add(S.rows).draw(false);
          return;
        }

        if (S.table) { S.table.destroy(); tableEl.innerHTML = ''; S.table = null; }

        const tableCols = columns();
        S.table = jq(tableEl).DataTable({
          data: S.rows,
          columns: tableCols,
          autoWidth: false,
          pageLength: 25,
          lengthMenu: [10, 25, 50, 100],
          order: [],
          language: RL.AR_LANG,
          createdRow: function (tr, data, index, cells) {
            tr.className += ' rl-s-' + data.state;
            labelCells(Array.prototype.slice.call(cells), tableCols);
          },
        });
        S.table.__signature = signature;
        return;
      }

      // no DataTables on the page: plain table
      const cols = columns();
      tableEl.innerHTML = '<thead><tr>' + cols.map(function (c) { return '<th>' + c.title + '</th>'; }).join('') + '</tr></thead><tbody>' +
        S.rows.map(function (r) {
          return '<tr class="rl-s-' + esc(r.state) + '" data-key="' + esc(r.key) + '">' + cols.map(function (c) {
            return '<td class="' + (c.className || '') + '"' + (c.label ? ' data-label="' + esc(c.label) + '"' : '') + '>' + (c.render ? c.render(r[c.data], 'display', r) : esc(r[c.data])) + '</td>';
          }).join('') + '</tr>';
        }).join('') + '</tbody>';
    }

    // ----------------------------------------------------------------- events

    function rowOf(element) {
      const tr = element.closest('tr');
      if (!tr) return null;

      if (S.table) {
        const data = S.table.row(window.jQuery(tr)).data();
        return data || null;
      }

      return S.byKey[tr.getAttribute('data-key')] || null;
    }

    root.addEventListener('click', function (event) {
      const button = event.target.closest('[data-act]');
      if (!button || !root.contains(button) || button.tagName === 'INPUT') return;

      const act = button.getAttribute('data-act');

      if (act === 'view') { S.view = button.getAttribute('data-view'); if (S.table) { S.table.destroy(); RL.$('#rl-lib-table', root).innerHTML = ''; S.table = null; } paint(); return; }
      if (act === 'manage') { window.location.href = manageUrl(subjectKeyOf(S.subject)); return; }
      if (act === 'health') { RL.state.subjectKey = subjectKeyOf(S.subject); RL.Panels.health(); return; }
      if (act === 'uploads') { RL.Panels.uploads(); return; }

      const r = rowOf(button);
      if (!r) return;

      switch (act) {
        case 'preview': RL.Panels.media(r); break;
        case 'edit': RL.Forms.resource({ resource: r }); break;
        case 'students': RL.Panels.students('resource', r.key, r.title); break;
        case 'scope': RL.Panels.scope('resource', r.key, r.title); break;
        case 'open': window.location.href = manageUrl(r.subject_key, r.id); break;
        case 'restore': RL.act(RL.post('resources/' + r.key + '/restore')); break;
        case 'delete':
          RL.confirm({
            title: r.can.manage ? 'حذف المورد' : 'إيقاف عن مجموعاتك',
            text: r.can.manage ? 'سيُحذف «<b>' + esc(r.title) + '</b>» ويبقى في المحذوفات. يمكنك التراجع الآن.' : 'سيتوقف المورد عن مجموعاتك فقط لأنه يخدم مجموعات أخرى.',
            confirmText: r.can.manage ? 'حذف' : 'إيقاف', danger: true,
          }).then(function (ok) { if (ok) RL.act(RL.del('resources/' + r.key)); });
          break;
        case 'cell': {
          const groupId = Number(button.getAttribute('data-group'));
          const what = button.getAttribute('data-cell');
          button.disabled = true;

          if (what === 'attach') RL.act(RL.post('resources/' + r.key + '/groups', { group_ids: [groupId] }));
          else if (what === 'detach') RL.act(RL.del('resources/' + r.key + '/groups', { group_ids: [groupId] }));
          else RL.act(RL.post('resources/' + r.key + '/groups/' + groupId + '/pause', { paused: what === 'pause' }));
          break;
        }
        default:
      }
    });

    root.addEventListener('change', function (event) {
      const t = event.target;

      if (t.id === 'rl-f-subject') { S.subject = t.value; S.group = ''; if (!S.subject) S.view = 'list'; S.groups = []; syncUrl(); return load(); }
      if (t.id === 'rl-f-group') { S.group = t.value; return load(); }
      if (t.id === 'rl-f-type') { S.type = t.value; return load(); }
      if (t.id === 'rl-f-state') { S.state = t.value; return load(); }
      if (t.id === 'rl-f-trashed') { S.trashed = t.checked; return load(); }

      // the kill switch of a row
      if (t.matches('input[data-act="power"]')) {
        const r = rowOf(t);
        if (!r) return;
        const wanted = t.checked;
        t.disabled = true;
        RL.act(RL.post('resources/' + r.key + '/active', { active: wanted })).then(function (res) {
          if (!res) { t.checked = !wanted; t.disabled = false; }
        });
      }
    });

    function subjectKeyOf(id) {
      const s = S.subjects.filter(function (x) { return String(x.id) === String(id); })[0];
      return s ? s.key : '';
    }

    function manageUrl(subjectKey, focusId) {
      return cfg.manageUrl.replace('__SUBJECT__', encodeURIComponent(subjectKey)) + (focusId ? '?focus=' + encodeURIComponent(focusId) : '');
    }

    function syncUrl() {
      const url = new URL(window.location.href);
      S.subject ? url.searchParams.set('subject', S.subject) : url.searchParams.delete('subject');
      S.view === 'matrix' ? url.searchParams.set('view', 'matrix') : url.searchParams.delete('view');
      window.history.replaceState({}, '', url);
    }

    RL.bus.on('changed', function () { load(); });

    shell();
    load();
  };

})(window, document);
