/**
 * Resource library workspace - panels (modals).
 *
 *   RL.Panels.scope()     who sees it + where it is shown (multi-select with tags, pause, undo)
 *   RL.Panels.students()  DataTable of students with an AJAX exclusion switch per row
 *   RL.Panels.health()    "extract the errors": what silently hides or breaks content, one-click fixes
 *   RL.Panels.trash()     deleted resources / lessons / units, restorable
 *   RL.Panels.activity()  journal of recent actions with Undo
 *   RL.Panels.asStudent() exactly what a group / a student sees
 *   RL.Panels.explain()   why a given student does (not) see a resource
 *   RL.Panels.media()     preview of a resource (video / pdf / image / link)
 */
(function (window, document) {
  'use strict';

  const RL = window.RL;
  const esc = RL.esc;
  const Panels = (RL.Panels = {});

  RL.state = RL.state || { subjectKey: (RL.config.subject || {}).key || null, groups: [] };

  const AR_LANG = {
    search: 'بحث:', lengthMenu: 'عرض _MENU_', info: 'عرض _START_ إلى _END_ من _TOTAL_', infoEmpty: 'لا توجد بيانات',
    infoFiltered: '(من أصل _MAX_)', zeroRecords: 'لا توجد نتائج مطابقة', emptyTable: 'لا توجد بيانات',
    paginate: { first: 'الأول', previous: 'السابق', next: 'التالي', last: 'الأخير' },
  };
  RL.AR_LANG = AR_LANG;

  /** "lesson:ENC" | "unit:ENC" | "general"  ->  {type, key} for the API */
  function parseTarget(value) {
    if (value === 'general') return { type: 'general' };
    const index = value.indexOf(':');
    return { type: value.slice(0, index), key: value.slice(index + 1) };
  }

  const KIND_LABEL = { resource: 'المورد', lesson: 'الدرس', unit: 'الوحدة' };

  // ================================================================== SCOPE

  Panels.scope = function (kind, key, title) {
    const plural = RL.plural(kind);
    const modal = RL.modal({
      title: '<i class="bi bi-share me-2"></i>' + esc(title || 'المشاركة والعرض'),
      body: RL.loading(),
      footer: '<span class="rl-help me-auto">كل تغيير يُحفظ فوراً، ويظهر لك زر «تراجع» لـ 5 ثوانٍ.</span><button type="button" class="rl-btn" data-bs-dismiss="modal">إغلاق</button>',
      size: 'lg',
    });
    modal.show();

    let pickers = [];
    let scope = null;

    function load() {
      return RL.get(plural + '/' + key + '/scope').then(function (res) {
        scope = res.scope;
        render();
      }).catch(function (err) {
        modal.body.innerHTML = '<div class="rl-note rl-note--bad"><i class="bi bi-x-octagon"></i><div>' + esc(err.message) + '</div></div>';
      });
    }

    function run(promise) { return RL.act(promise).then(load); }

    function render() {
      pickers.forEach(function (p) { p.destroy(); });
      pickers = [];
      modal.body.innerHTML = kind === 'resource' ? resourceHtml(scope) : containerHtml(scope);
      mountPickers();
    }

    // ---- resource

    function resourceHtml(s) {
      const on = s.state !== 'hidden';
      const can = s.can || {};
      let html = '';

      html += '<div class="rl-power ' + (on ? 'is-on' : 'is-off') + '">' +
        RL.switchEl(on, { raw: 'data-act="power"', disabled: !can.toggle, title: RL.powerTitle(s, on) }, 'rl-switch--lg') +
        '<div class="rl-grow"><b>' + (on ? 'ظاهر للطلاب' : 'موقوف عن الطلاب') + '</b>' +
        '<span class="rl-help">' + (on
          ? (can.manage
            ? 'يراه الطلاب حسب المجموعات والأماكن أدناه.'
            : 'يراه الطلاب حسب المجموعات والأماكن أدناه. المورد مشترك مع مجموعات لا تتبعك، لذا يوقفه هذا المفتاح عن مجموعاتك فقط وتبقى مجموعات زملائك تراه.')
          : (s.is_active ? 'موقوف لمجموعاتك فقط — لن يراه طلابك حتى تعيد تشغيله.' : 'لن يراه أي طالب حتى تعيد تشغيله — دون حذف أي شيء.')) + '</span></div>' +
        '<span class="rl-chip rl-chip--' + esc(s.state_tone) + '">' + esc(s.state_label) + '</span></div>';

      if (s.file_missing) {
        html += '<div class="rl-note rl-note--bad"><i class="bi bi-exclamation-octagon"></i><div><b>الملف مفقود على السيرفر</b> — الطالب يرى «هذا الملف غير متاح حالياً». ' +
          (can.manage ? 'ارفع ملفاً بديلاً من «تعديل» (أو اربطه بملف من «الملفات المرفوعة»).' : 'اطلب من الإدارة استبداله.') + '</div></div>';
      }

      // ---- groups
      html += '<div class="rl-card"><div class="rl-section-title"><i class="bi bi-people-fill"></i> المجموعات التي ترى المورد</div>';

      if (s.shared) {
        html += '<div class="rl-note rl-note--good mb-2"><i class="bi bi-globe2"></i><div>مشترك مع <b>كل مجموعات المادة</b> — وأي مجموعة تُضاف لاحقاً سترى المورد تلقائياً. يمكنك إيقافه مؤقتاً عن مجموعة محددة.</div></div>';
      }

      let shown = 0;
      s.groups.forEach(function (g) {
        if (!s.shared && !g.linked && !g.paused) return;
        shown++;
        html += '<div class="rl-group-row ' + (g.paused ? 'is-paused' : 'is-on') + '"><i class="bi bi-people"></i><span class="rl-grow">' + esc(g.name) + '</span>' +
          (g.paused ? RL.chip('موقوف مؤقتاً', 'warning') : RL.chip('يرى المورد', 'success')) +
          '<button type="button" class="rl-btn rl-btn--sm" data-act="' + (g.paused ? 'resume' : 'pause') + '" data-group="' + g.id + '">' +
          '<i class="bi ' + (g.paused ? 'bi-play-fill' : 'bi-pause-fill') + '"></i> ' + (g.paused ? 'تشغيل' : 'إيقاف مؤقت') + '</button>' +
          (!s.shared ? '<button type="button" class="rl-btn rl-btn--sm rl-btn--danger rl-btn--icon" title="إزالة المجموعة" data-act="detach" data-group="' + g.id + '"><i class="bi bi-x-lg"></i></button>' : '') +
          '</div>';
      });

      if (!s.shared && shown === 0) {
        html += '<div class="rl-note rl-note--warn mb-2"><i class="bi bi-eye-slash"></i><div><b>مسودة:</b> المورد غير موجّه لأي مجموعة، لذلك لا يراه أحد. أضف مجموعة بالأسفل.</div></div>';
      }

      const addable = s.groups.filter(function (g) { return !s.shared && !g.linked && !g.paused; });
      if (addable.length) {
        html += '<div class="mt-3"><label class="rl-label">أظهره لمجموعات أخرى (دون إعادة رفع)</label>' +
          '<div class="rl-flex"><div class="rl-grow" data-picker="groups"></div><button type="button" class="rl-btn rl-btn--primary" data-act="attach"><i class="bi bi-plus-lg"></i> إضافة</button></div></div>';
      }

      if (can.manage) {
        if (s.shared) {
          html += '<div class="mt-3"><button type="button" class="rl-btn rl-btn--sm" data-act="unshare-all"><i class="bi bi-list-check"></i> تحويل إلى قائمة مجموعات محددة</button></div>';
        } else if (can.share_all) {
          html += '<div class="mt-3"><button type="button" class="rl-btn rl-btn--sm" data-act="share-all"><i class="bi bi-globe2"></i> مشاركة مع كل مجموعات المادة</button></div>';
        }
      }

      if (s.other_groups > 0) {
        html += '<div class="rl-help mt-2"><i class="bi bi-info-circle"></i> يخدم المورد أيضاً ' + s.other_groups + ' مجموعة تتبع معلمين آخرين؛ ما تفعله هنا لا يؤثر عليها.</div>';
      }
      html += '</div>';

      // ---- placements
      html += '<div class="rl-card"><div class="rl-section-title"><i class="bi bi-diagram-3-fill"></i> أماكن الظهور في المنهج</div>';

      if (!s.placements.length) {
        html += '<div class="rl-note rl-note--warn mb-2"><i class="bi bi-geo-alt"></i><div><b>بلا مكان:</b> المورد غير موضوع في أي وحدة أو درس، لذلك لا يراه أحد.</div></div>';
      }

      s.placements.forEach(function (p) {
        html += '<div class="rl-group-row ' + (p.active && p.container_active ? 'is-on' : 'is-paused') + '"><i class="bi ' + (p.type === 'general' ? 'bi-collection' : (p.type === 'unit' ? 'bi-folder2-open' : 'bi-journal-text')) + '"></i>' +
          '<span class="rl-grow">' + esc(p.label) + '</span>' +
          (!p.container_active ? RL.chip('الدرس/الوحدة موقوف', 'warning') : (p.active ? '' : RL.chip('مخفي هنا', 'warning'))) +
          (can.manage
            ? '<button type="button" class="rl-btn rl-btn--sm rl-btn--icon" title="' + (p.active ? 'إخفاء في هذا المكان فقط' : 'إظهار في هذا المكان') + '" data-act="place-toggle" data-place="' + esc(p.key) + '" data-active="' + (p.active ? 1 : 0) + '"><i class="bi ' + (p.active ? 'bi-eye' : 'bi-eye-slash') + '"></i></button>' +
              '<button type="button" class="rl-btn rl-btn--sm rl-btn--danger rl-btn--icon" title="إزالة من هذا المكان" data-act="place-remove" data-place="' + esc(p.key) + '"><i class="bi bi-x-lg"></i></button>'
            : '') +
          '</div>';
      });

      if (can.manage) {
        html += '<div class="mt-3"><label class="rl-label">أظهره في أماكن أخرى (نفس الملف، بلا تكرار)</label>' +
          '<div class="rl-flex"><div class="rl-grow" data-picker="places"></div><button type="button" class="rl-btn rl-btn--primary" data-act="place-add"><i class="bi bi-plus-lg"></i> إضافة</button></div></div>';
      } else {
        html += '<div class="rl-help mt-2"><i class="bi bi-lock"></i> لا يمكنك تغيير أماكن الظهور لأن المورد مشترك مع مجموعات لا تتبعك.</div>';
      }
      html += '</div>';

      return html;
    }

    // ---- unit / lesson

    function containerHtml(s) {
      const label = KIND_LABEL[kind];
      let html = '<div class="rl-power ' + (s.is_active ? 'is-on' : 'is-off') + '">' +
        RL.switchEl(s.is_active, { raw: 'data-act="power"' }, 'rl-switch--lg') +
        '<div class="rl-grow"><b>' + (s.is_active ? label + ' ظاهر للطلاب' : label + ' موقوف عن الطلاب') + '</b>' +
        '<span class="rl-help">' + (s.is_active ? 'يظهر بما فيه حسب جمهور كل مورد.' : 'كل ما بداخله مخفي عن الطلاب حتى تعيد تشغيله — دون حذف.') + '</span></div>' +
        '<span class="rl-chip rl-chip--info">' + s.resources_count + ' مورد</span></div>';

      html += '<div class="rl-card"><div class="rl-section-title"><i class="bi bi-people-fill"></i> ماذا تشاهد كل مجموعة</div>';
      html += '<div class="rl-help mb-2">أظهر محتوى ' + label + ' كاملاً لمجموعة، أو أوقفه عنها. لا يُنسخ أي ملف — تُضاف المجموعة إلى موارده فقط.</div>' +
        '<div class="rl-note mb-2"><i class="bi bi-info-circle"></i><div>«إظهار الكل» يضيف المجموعة إلى <b>الموارد الموجودة الآن</b> في ' + label + '، ويحفظها كجمهور افتراضي: أي مورد جديد يُضاف هنا بخيار «حسب المكان» يظهر لها تلقائياً. ' +
        'الجمهور يُحفظ على كل مورد، لذلك هذا هو المكان الذي تُظهر منه موارد ' + label + ' الموجودة لمجموعة جديدة.</div></div>';

      if (!s.groups.length) html += '<div class="rl-note rl-note--warn">لا توجد مجموعات في نطاقك لهذه المادة.</div>';

      s.groups.forEach(function (g) {
        const full = g.total > 0 && g.sees_count === g.total;
        html += '<div class="rl-group-row ' + (full ? 'is-on' : (g.sees_count ? 'is-paused' : '')) + '"><i class="bi bi-people"></i><span class="rl-grow">' + esc(g.name) + '</span>' +
          RL.chip('ترى ' + g.sees_count + ' من ' + g.total, full ? 'success' : (g.sees_count ? 'warning' : 'secondary')) +
          (g.sees_count < g.total ? '<button type="button" class="rl-btn rl-btn--sm rl-btn--primary" data-act="c-attach" data-group="' + g.id + '"><i class="bi bi-plus-lg"></i> إظهار الكل</button>' : '') +
          (g.sees_count > 0 ? '<button type="button" class="rl-btn rl-btn--sm rl-btn--danger" data-act="c-detach" data-group="' + g.id + '"><i class="bi bi-slash-circle"></i> إيقاف</button>' : '') +
          '</div>';
      });
      html += '</div>';

      return html;
    }

    function mountPickers() {
      const groupsHost = modal.body.querySelector('[data-picker="groups"]');
      if (groupsHost && scope.kind === 'resource') {
        pickers.push(RL.picker(groupsHost, {
          placeholder: 'اختر مجموعة أو أكثر…',
          options: scope.groups.filter(function (g) { return !scope.shared && !g.linked && !g.paused; })
            .map(function (g) { return { value: String(g.id), label: g.name }; }),
        }));
        groupsHost.__picker = pickers[pickers.length - 1];
      }

      const placesHost = modal.body.querySelector('[data-picker="places"]');
      if (placesHost && scope.kind === 'resource') {
        const d = scope.destinations;
        const opts = [];
        if (!d.general.placed) opts.push({ value: 'general', label: 'المرفقات العامة (بدون وحدة)', group: 'عام' });
        d.units.forEach(function (u) {
          if (!u.placed) opts.push({ value: 'unit:' + u.key, label: 'الوحدة نفسها (بدون درس)', group: u.name });
          u.lessons.forEach(function (l) { if (!l.placed) opts.push({ value: 'lesson:' + l.key, label: l.name, group: u.name }); });
        });
        pickers.push(RL.picker(placesHost, { placeholder: 'اختر وحدة أو درساً…', options: opts }));
        placesHost.__picker = pickers[pickers.length - 1];
      }
    }

    // ---- actions

    modal.body.addEventListener('click', function (event) {
      const button = event.target.closest('[data-act]');
      if (!button || button.tagName === 'INPUT') return;

      const act = button.getAttribute('data-act');
      const group = button.getAttribute('data-group');
      const place = button.getAttribute('data-place');
      const base = plural + '/' + key;

      switch (act) {
        case 'pause': run(RL.post('resources/' + key + '/groups/' + group + '/pause', { paused: true })); break;
        case 'resume': run(RL.post('resources/' + key + '/groups/' + group + '/pause', { paused: false })); break;
        case 'detach': run(RL.del(base + '/groups', { group_ids: [Number(group)] })); break;
        case 'attach': {
          const ids = (modal.body.querySelector('[data-picker="groups"]').__picker.get() || []).map(Number);
          if (!ids.length) return RL.toast({ message: 'اختر مجموعة واحدة على الأقل.', type: 'info' });
          run(RL.post(base + '/groups', { group_ids: ids }));
          break;
        }
        case 'share-all': run(RL.post('resources/' + key + '/shared', { shared: true })); break;
        case 'unshare-all':
          RL.confirm({
            title: 'تحديد المجموعات',
            text: 'سيتحول المورد من «كل المجموعات» إلى قائمة بكل المجموعات الحالية، فتستطيع إزالة ما لا تريده. لن يتغير ما يراه الطلاب الآن.',
            confirmText: 'متابعة',
          }).then(function (ok) { if (ok) run(RL.post('resources/' + key + '/shared', { shared: false })); });
          break;
        case 'c-attach': run(RL.post(base + '/groups', { group_ids: [Number(group)] })); break;
        case 'c-detach':
          RL.confirm({
            title: 'إيقاف عن المجموعة',
            text: 'ستتوقف المجموعة عن رؤية موارد ' + KIND_LABEL[kind] + ' كلها. يمكنك التراجع بعدها مباشرة.',
            confirmText: 'إيقاف', danger: true,
          }).then(function (ok) { if (ok) run(RL.del(base + '/groups', { group_ids: [Number(group)] })); });
          break;
        case 'place-add': {
          const values = modal.body.querySelector('[data-picker="places"]').__picker.get();
          if (!values.length) return RL.toast({ message: 'اختر مكاناً واحداً على الأقل.', type: 'info' });
          run(RL.post('resources/' + key + '/placements', { targets: values.map(parseTarget) }));
          break;
        }
        case 'place-toggle':
          run(RL.post('resources/' + key + '/placements/active', { target: parseTarget(place), active: button.getAttribute('data-active') !== '1' }));
          break;
        case 'place-remove':
          RL.http('DELETE', 'resources/' + key + '/placements', { targets: [parseTarget(place)] }).then(RL.done, function (err) {
            if (err.data && err.data.code === 'last_placement') {
              RL.confirm({
                title: 'آخر مكان للمورد',
                text: 'هذا هو المكان الوحيد الذي يظهر فيه المورد. هل تريد نقله إلى «المرفقات العامة» بدلاً من إزالته؟',
                confirmText: 'انقله إلى العامة',
              }).then(function (ok) {
                if (ok) run(RL.http('DELETE', 'resources/' + key + '/placements', { targets: [parseTarget(place)], move_to_general: true }));
              });
              return;
            }
            RL.toast({ message: err.message, type: 'error' });
          }).then(load);
          break;
        default:
      }
    });

    modal.body.addEventListener('change', function (event) {
      const input = event.target.closest('input[data-act="power"]');
      if (!input) return;
      run(RL.post(plural + '/' + key + '/active', { active: input.checked }));
    });

    // keep the panel in sync when something else changes (undo from a toast, another panel...)
    const onChange = function () { if (document.body.contains(modal.el)) load(); };
    RL.bus.on('changed', onChange);
    modal.el.addEventListener('hidden.bs.modal', function () {
      RL.bus.off('changed', onChange);
      pickers.forEach(function (p) { p.destroy(); });
    });

    load();
  };

  // ================================================================ STUDENTS

  Panels.students = function (kind, key, title) {
    const plural = RL.plural(kind);
    const label = KIND_LABEL[kind];
    const modal = RL.modal({
      title: '<i class="bi bi-person-x me-2"></i>الطلاب والاستثناءات — ' + esc(title || ''),
      size: 'xl',
      body:
        '<div class="rl-note rl-note--info mb-3"><i class="bi bi-info-circle"></i><div>فعّل مفتاح <b>«استثناء»</b> لأي طالب فيختفي عنه ' + label +
        (kind === 'resource' ? '' : ' <b>وكل ما بداخله</b>') + ' فوراً، ولا يستطيع فتحه حتى برابط قديم. الاستثناء يتفوق على مشاركة المجموعة، ويمكنك التراجع.</div></div>' +
        '<div class="rl-flex mb-2">' +
          '<label class="rl-flex" style="cursor:pointer"><input type="checkbox" id="rl-st-all"> عرض كل طلاب المادة (وليس طلاب المجموعات التي ترى هذا فقط)</label>' +
          '<span class="rl-grow"></span>' +
          '<button type="button" class="rl-btn rl-btn--sm rl-btn--danger" data-act="bulk-exclude" disabled><i class="bi bi-person-x"></i> استثناء المحدّدين</button>' +
          '<button type="button" class="rl-btn rl-btn--sm" data-act="bulk-include" disabled><i class="bi bi-person-check"></i> إلغاء استثناء المحدّدين</button>' +
        '</div>' +
        '<div class="rl-table-wrap"><table class="rl-table" id="rl-st-table"><thead></thead><tbody></tbody></table></div>',
      footer: '<span class="rl-help me-auto" id="rl-st-count"></span><button type="button" class="rl-btn" data-bs-dismiss="modal">إغلاق</button>',
    });
    modal.show();

    const tableEl = modal.body.querySelector('#rl-st-table');
    const everyone = modal.body.querySelector('#rl-st-all');
    let rows = [];
    let table = null;

    function statusHtml(row) {
      if (row.excluded) {
        return RL.chip('مستثنى', 'danger') + (row.excluded_by ? ' <small class="text-muted">بواسطة ' + esc(row.excluded_by) + '</small>' : '');
      }
      if (row.inherited) return RL.chip('مستثنى عبر ' + row.inherited, 'warning');
      return RL.chip('يرى ' + label, 'success');
    }

    function switchHtml(row) {
      return '<label class="rl-switch rl-switch--danger" title="استثناء هذا الطالب"><input type="checkbox" class="rl-excl" data-id="' + row.student_id + '"' + (row.excluded ? ' checked' : '') + '><i></i></label>' +
        (kind === 'resource' ? ' <button type="button" class="rl-btn rl-btn--sm rl-btn--ghost rl-btn--icon" data-explain="' + row.student_id + '" title="لماذا يرى / لا يرى؟"><i class="bi bi-question-circle"></i></button>' : '');
    }

    function selectedIds() {
      return RL.$$('.rl-pick:checked', tableEl).map(function (c) { return Number(c.getAttribute('data-id')); });
    }

    function syncBulk() {
      const n = selectedIds().length;
      modal.el.querySelectorAll('[data-act="bulk-exclude"],[data-act="bulk-include"]').forEach(function (b) { b.disabled = n === 0; });
    }

    function paint() {
      const jq = window.jQuery;
      modal.el.querySelector('#rl-st-count').textContent = rows.length + ' طالب · ' + rows.filter(function (r) { return r.excluded; }).length + ' مستثنى';

      if (jq && jq.fn && jq.fn.DataTable) {
        if (table) {
          table.clear().rows.add(rows).draw(false);
          syncBulk();
          return;
        }
        table = jq(tableEl).DataTable({
          data: rows,
          autoWidth: false,
          pageLength: 10,
          lengthMenu: [10, 25, 50, 100],
          order: [[1, 'asc']],
          language: AR_LANG,
          columns: [
            { data: null, orderable: false, searchable: false, width: '30px', title: '', render: function (d, t, r) { return '<input type="checkbox" class="rl-pick" data-id="' + r.student_id + '">'; } },
            { data: 'name', title: 'الطالب', render: function (d) { return '<b>' + esc(d) + '</b>'; } },
            { data: 'group', title: 'المجموعة', render: function (d) { return esc(d); } },
            { data: null, title: 'الحالة', orderable: false, render: function (d, t, r) { return t === 'display' ? statusHtml(r) : (r.excluded ? 'مستثنى' : (r.inherited ? 'مستثنى عبر' : 'يرى')); } },
            { data: 'excluded', title: 'استثناء', orderable: true, className: 'text-center', render: function (d, t, r) { return t === 'display' ? switchHtml(r) : (d ? 1 : 0); } },
          ],
          createdRow: function (tr, data) { tr.classList.toggle('is-excluded', !!data.excluded); },
          drawCallback: syncBulk,
        });
        return;
      }

      // fallback without DataTables: a plain table
      tableEl.innerHTML = '<thead><tr><th></th><th>الطالب</th><th>المجموعة</th><th>الحالة</th><th>استثناء</th></tr></thead><tbody>' +
        rows.map(function (r) {
          return '<tr class="' + (r.excluded ? 'is-excluded' : '') + '"><td><input type="checkbox" class="rl-pick" data-id="' + r.student_id + '"></td><td><b>' + esc(r.name) + '</b></td><td>' + esc(r.group) + '</td><td>' + statusHtml(r) + '</td><td class="text-center">' + switchHtml(r) + '</td></tr>';
        }).join('') + '</tbody>';
      syncBulk();
    }

    function load() {
      return RL.get(plural + '/' + key + '/students', { everyone: everyone.checked ? 1 : 0 }).then(function (res) {
        rows = res.students;
        paint();
      }).catch(function (err) {
        modal.body.insertAdjacentHTML('afterbegin', '<div class="rl-note rl-note--bad mb-2">' + esc(err.message) + '</div>');
      });
    }

    function setExclusion(ids, excluded, revert) {
      return RL.act(RL.post(plural + '/' + key + '/exclusions', { student_ids: ids, excluded: excluded })).then(function (res) {
        if (!res && revert) revert();
      });
    }

    tableEl.addEventListener('change', function (event) {
      const input = event.target;
      if (input.classList.contains('rl-excl')) {
        const id = Number(input.getAttribute('data-id'));
        const wanted = input.checked;
        input.disabled = true;
        setExclusion([id], wanted, function () { input.checked = !wanted; }).then(function () { input.disabled = false; });
      } else if (input.classList.contains('rl-pick')) {
        syncBulk();
      }
    });

    tableEl.addEventListener('click', function (event) {
      const button = event.target.closest('[data-explain]');
      if (button) {
        const id = Number(button.getAttribute('data-explain'));
        const row = rows.filter(function (r) { return r.student_id === id; })[0];
        Panels.explain(key, id, row ? row.name : '');
      }
    });

    modal.el.addEventListener('click', function (event) {
      const button = event.target.closest('[data-act="bulk-exclude"],[data-act="bulk-include"]');
      if (!button) return;
      const ids = selectedIds();
      if (ids.length) setExclusion(ids, button.getAttribute('data-act') === 'bulk-exclude');
    });

    everyone.addEventListener('change', function () { load(); });

    const onChange = function () { if (document.body.contains(modal.el)) load(); };
    RL.bus.on('changed', onChange);
    modal.el.addEventListener('hidden.bs.modal', function () {
      RL.bus.off('changed', onChange);
      if (table) { table.destroy(); table = null; }
    });

    load();
  };

  // ================================================================= EXPLAIN

  Panels.explain = function (resourceKey, studentId, studentName) {
    const modal = RL.modal({
      title: '<i class="bi bi-question-circle me-2"></i>لماذا يرى / لا يرى؟ ' + (studentName ? '— ' + esc(studentName) : ''),
      body: RL.loading(),
      footer: '<button type="button" class="rl-btn" data-bs-dismiss="modal">إغلاق</button>',
    });
    modal.show();

    RL.get('explain', { resource: resourceKey, student_id: studentId }).then(function (res) {
      modal.body.innerHTML =
        '<div class="rl-note ' + (res.visible ? 'rl-note--good' : 'rl-note--bad') + ' mb-3"><i class="bi ' + (res.visible ? 'bi-check-circle-fill' : 'bi-x-octagon-fill') + '"></i><div><b>' + esc(res.summary) + '</b></div></div>' +
        res.steps.map(function (s) {
          return '<div class="rl-group-row ' + (s.ok ? 'is-on' : 'is-paused') + '"><i class="bi ' + (s.ok ? 'bi-check-circle text-success' : 'bi-x-circle text-danger') + '"></i>' +
            '<div class="rl-grow"><div>' + esc(s.label) + '</div>' + (s.detail ? '<small class="text-muted">' + esc(s.detail) + '</small>' : '') + '</div></div>';
        }).join('');
    }).catch(function (err) {
      modal.body.innerHTML = '<div class="rl-note rl-note--bad">' + esc(err.message) + '</div>';
    });
  };

  // ================================================================== HEALTH

  Panels.health = function () {
    const modal = RL.modal({
      title: '<i class="bi bi-heart-pulse me-2"></i>فحص جودة المكتبة — استخراج الأخطاء',
      body: RL.loading('جارٍ فحص المادة…'),
      footer: '<span class="rl-help me-auto">الإصلاحات التلقائية قابلة للتراجع.</span><button type="button" class="rl-btn" data-bs-dismiss="modal">إغلاق</button>',
      size: 'xl',
    });
    modal.show();

    const sk = RL.state.subjectKey;
    const ICON = { error: 'bi-x-octagon-fill', warning: 'bi-exclamation-triangle-fill', info: 'bi-info-circle-fill' };

    function itemHtml(code, item) {
      let extra = '';
      if (code === 'declared_empty') {
        extra = ' <button type="button" class="rl-btn rl-btn--sm rl-btn--primary" data-fix="share_container_with_group" data-key="' + esc(item.key) + '">إظهار محتواها للمجموعة</button>';
      } else if (item.kind === 'duplicate_set') {
        extra = ' <button type="button" class="rl-btn rl-btn--sm rl-btn--primary" data-fix="merge_duplicates" data-keys="' + esc(item.keys.join(',')) + '">دمج</button>';
      }
      return '<li><b>' + esc(item.label) + '</b>' + (item.detail ? ' <small>— ' + esc(item.detail) + '</small>' : '') + extra +
        (item.resources ? '<ul class="mt-1">' + item.resources.map(function (r) { return '<li><small>• ' + esc(r.label) + '</small></li>'; }).join('') + '</ul>' : '') + '</li>';
    }

    function load() {
      return RL.get('subjects/' + sk + '/health').then(function (res) {
        if (!res.issues.length) {
          modal.body.innerHTML = '<div class="rl-note rl-note--good"><i class="bi bi-check-circle-fill"></i><div><b>لا توجد مشاكل.</b> كل الموارد موضوعة وموجّهة وتعمل كما ينبغي.</div></div>';
          return;
        }

        modal.body.innerHTML =
          '<div class="rl-flex mb-3">' +
            RL.chip(res.summary.error + ' خطأ', res.summary.error ? 'danger' : 'secondary') +
            RL.chip(res.summary.warning + ' تنبيه', res.summary.warning ? 'warning' : 'secondary') +
            RL.chip(res.summary.info + ' ملاحظة', 'info') +
          '</div>' +
          res.issues.map(function (issue) {
            const limit = 12;
            return '<div class="rl-issue sev-' + issue.severity + '">' +
              '<div class="rl-issue-head"><i class="bi sev ' + ICON[issue.severity] + '"></i><div class="rl-grow"><b>' + esc(issue.title) + '</b> ' + RL.chip(String(issue.count), 'secondary') +
              '<div class="rl-help mb-0">' + esc(issue.detail) + '</div></div>' +
              (issue.fix && issue.code !== 'declared_empty' && issue.code !== 'duplicates'
                ? '<button type="button" class="rl-btn rl-btn--primary rl-btn--sm" data-fix="' + esc(issue.fix) + '">' + esc(issue.fix_label || 'إصلاح') + '</button>' : '') +
              ((issue.code === 'declared_empty' || issue.code === 'duplicates') && issue.fix
                ? '<button type="button" class="rl-btn rl-btn--primary rl-btn--sm" data-fix="' + esc(issue.fix) + '">' + esc((issue.fix_label || 'إصلاح') + ' (الكل)') + '</button>' : '') +
              (issue.code === 'orphan_uploads'
                ? '<button type="button" class="rl-btn rl-btn--primary rl-btn--sm" data-open-uploads><i class="bi bi-cloud-upload"></i> إدارة الملفات المرفوعة</button>' : '') +
              '</div><ul class="rl-issue-items list-unstyled mb-0">' +
              issue.items.slice(0, limit).map(function (item) { return itemHtml(issue.code, item); }).join('') +
              (issue.items.length > limit ? '<li><small>… و ' + (issue.items.length - limit) + ' آخرين</small></li>' : '') +
              '</ul></div>';
          }).join('');
      }).catch(function (err) {
        modal.body.innerHTML = '<div class="rl-note rl-note--bad">' + esc(err.message) + '</div>';
      });
    }

    modal.body.addEventListener('click', function (event) {
      if (event.target.closest('[data-open-uploads]')) { modal.hide(); Panels.uploads(); return; }

      const button = event.target.closest('[data-fix]');
      if (!button) return;

      const params = {};
      if (button.getAttribute('data-key')) params.key = button.getAttribute('data-key');
      if (button.getAttribute('data-keys')) params.keys = button.getAttribute('data-keys').split(',');

      button.disabled = true;
      RL.act(RL.post('subjects/' + sk + '/health/fix', { code: button.getAttribute('data-fix'), params: params })).then(load);
    });

    const onChange = function () { if (document.body.contains(modal.el)) load(); };
    RL.bus.on('changed', onChange);
    modal.el.addEventListener('hidden.bs.modal', function () { RL.bus.off('changed', onChange); });

    load();
  };

  // ========================================================= ORPHAN UPLOADS

  /**
   * Files uploaded through a resource form that was never saved: give them back to a resource whose
   * file is missing, turn them into a new resource (manage screen), or delete them.
   */
  Panels.uploads = function () {
    const modal = RL.modal({
      title: '<i class="bi bi-cloud-upload me-2"></i>ملفات مرفوعة غير مربوطة بأي مورد',
      body: RL.loading(),
      footer: '<span class="rl-help me-auto" data-grace></span><button type="button" class="rl-btn" data-bs-dismiss="modal">إغلاق</button>',
      size: 'lg',
    });
    modal.show();

    const VIDEO = ['mp4', 'mov', 'avi', 'webm', 'mkv'];
    const canAdopt = !!(RL.state && RL.state.tree && RL.Forms && RL.Forms.resource);
    let uploads = [];

    function typeOf(path) {
      const ext = String(path).split('.').pop().toLowerCase();
      return VIDEO.indexOf(ext) >= 0 ? 'video' : 'document';
    }

    function fileUrl(path) { return RL.url('uploads/orphans/file') + RL.qs({ path: path }); }

    function row(u, i, missing) {
      const suggested = u.suggestions.map(function (s) { return s.key; });
      const option = function (r, star) {
        return '<option value="' + esc(r.key) + '">' + (star ? '★ ' : '') + esc(r.title) + (r.subject ? ' — ' + esc(r.subject) : '') + (r.is_active ? '' : ' (موقوف)') + '</option>';
      };
      const others = missing.filter(function (r) { return u.suggestions.every(function (s) { return s.id !== r.id; }); });
      const options = u.suggestions.map(function (r) { return option(r, true); }).join('') + others.map(function (r) { return option(r, false); }).join('');

      return '<div class="rl-issue" data-i="' + i + '">' +
        '<div class="rl-issue-head"><i class="bi ' + (u.is_video ? 'bi-film' : 'bi-file-earmark') + ' sev"></i><div class="rl-grow">' +
          '<b>' + esc(u.original_filename || u.path.split('/').pop()) + '</b> ' + (u.size_bytes ? RL.chip(RL.formatBytes(u.size_bytes), 'secondary') : '') +
          '<div class="rl-help mb-0">' + esc(u.modified_at) + (u.uploaded_by ? ' · رفعه ' + esc(u.uploaded_by) : ' · رافع غير معروف (قبل تسجيل البيانات)') +
          (suggested.length ? ' · <span class="text-success">يطابق مورداً ملفه مفقود</span>' : '') + '</div></div>' +
          '<button type="button" class="rl-btn rl-btn--sm" data-up="preview"><i class="bi bi-eye"></i> معاينة</button>' +
          '<button type="button" class="rl-btn rl-btn--sm rl-btn--danger rl-btn--icon" data-up="delete" title="حذف الملف"><i class="bi bi-trash"></i></button>' +
        '</div>' +
        '<div data-preview></div>' +
        '<div class="rl-flex mt-2">' +
          (options
            ? '<select class="rl-select rl-grow" data-target>' + options + '</select><button type="button" class="rl-btn rl-btn--sm rl-btn--primary" data-up="relink"><i class="bi bi-link-45deg"></i> ربطه بهذا المورد</button>'
            : '<span class="rl-help mb-0 rl-grow">لا توجد موارد ملفها مفقود لربطه بها.</span>') +
          '<button type="button" class="rl-btn rl-btn--sm" data-up="adopt"' + (canAdopt ? '' : ' disabled title="افتح شاشة محتوى المادة لإنشاء مورد من هذا الملف"') + '><i class="bi bi-plus-circle"></i> إنشاء مورد منه</button>' +
        '</div></div>';
    }

    function load() {
      return RL.get('uploads/orphans').then(function (res) {
        uploads = res.uploads || [];
        modal.el.querySelector('[data-grace]').textContent = 'تُحذف الملفات غير المربوطة تلقائياً بعد ' + res.grace_hours + ' ساعة من رفعها.';
        modal.body.innerHTML = uploads.length
          ? uploads.map(function (u, i) { return row(u, i, res.missing || []); }).join('')
          : RL.empty('bi-inbox', 'لا توجد ملفات مرفوعة غير مربوطة.');
      }).catch(function (err) {
        modal.body.innerHTML = '<div class="rl-note rl-note--bad">' + esc(err.message) + '</div>';
      });
    }

    modal.body.addEventListener('click', function (event) {
      const button = event.target.closest('[data-up]');
      if (!button) return;
      const box = button.closest('[data-i]');
      const u = uploads[Number(box.getAttribute('data-i'))];
      const act = button.getAttribute('data-up');

      if (act === 'preview') {
        const host = box.querySelector('[data-preview]');
        if (host.innerHTML) { host.innerHTML = ''; return; }
        host.innerHTML = u.is_video
          ? '<video class="w-100 mt-2 rounded" style="max-height:340px;background:#000" controls preload="metadata" src="' + esc(fileUrl(u.path)) + '"></video>'
          : '<a class="rl-btn rl-btn--sm mt-2" target="_blank" rel="noopener" href="' + esc(fileUrl(u.path)) + '"><i class="bi bi-box-arrow-up-left"></i> فتح الملف</a>';
        return;
      }

      if (act === 'relink') {
        const select = box.querySelector('[data-target]');
        button.disabled = true;
        RL.act(RL.post('uploads/orphans/relink', { path: u.path, resource: select.value })).then(load);
        return;
      }

      if (act === 'adopt') {
        modal.hide();
        RL.Forms.resource({ upload: { path: u.path, name: u.original_filename || u.path.split('/').pop(), size: u.size_bytes, type: typeOf(u.path) } });
        return;
      }

      if (act === 'delete') {
        RL.confirm({
          title: 'حذف الملف المرفوع',
          text: 'سيُحذف «<b>' + esc(u.original_filename || u.path) + '</b>» من السيرفر نهائياً.',
          confirmText: 'احذف', danger: true,
        }).then(function (ok) { if (ok) RL.act(RL.del('uploads/orphans', { path: u.path })).then(load); });
      }
    });

    load();
  };

  // =================================================================== TRASH

  Panels.trash = function () {
    const modal = RL.modal({
      title: '<i class="bi bi-trash3 me-2"></i>المحذوفات — يمكن استرجاعها',
      body: RL.loading(),
      footer: '<button type="button" class="rl-btn" data-bs-dismiss="modal">إغلاق</button>',
      size: 'lg',
    });
    modal.show();

    const sk = RL.state.subjectKey;

    function row(kind, item, title, extra) {
      return '<div class="rl-group-row"><i class="bi ' + (kind === 'resource' ? (item.icon || 'bi-file-earmark') : (kind === 'unit' ? 'bi-folder2' : 'bi-journal-text')) + '"></i>' +
        '<div class="rl-grow">' + esc(title) + '<div class="rl-help mb-0">' + KIND_LABEL[kind] + ' · حُذف ' + esc(item.deleted_at || '') + '</div></div>' +
        '<button type="button" class="rl-btn rl-btn--sm rl-btn--primary" data-restore="' + kind + '" data-key="' + esc(item.key) + '"><i class="bi bi-arrow-counterclockwise"></i> استرجاع</button>' +
        (extra || '') + '</div>';
    }

    function load() {
      return RL.get('subjects/' + sk + '/trash').then(function (res) {
        const total = res.resources.length + res.units.length + res.lessons.length;
        if (!total) {
          modal.body.innerHTML = RL.empty('bi-inbox', 'سلة المحذوفات فارغة.');
          return;
        }

        modal.body.innerHTML =
          res.units.map(function (u) { return row('unit', u, u.name); }).join('') +
          res.lessons.map(function (l) { return row('lesson', l, l.name); }).join('') +
          res.resources.map(function (r) {
            return row('resource', r, r.title, cfg().isAdmin
              ? '<button type="button" class="rl-btn rl-btn--sm rl-btn--danger rl-btn--icon" title="حذف نهائي" data-force="' + esc(r.key) + '"><i class="bi bi-x-octagon"></i></button>' : '');
          }).join('');
      }).catch(function (err) {
        modal.body.innerHTML = '<div class="rl-note rl-note--bad">' + esc(err.message) + '</div>';
      });
    }

    function cfg() { return RL.config; }

    modal.body.addEventListener('click', function (event) {
      const restore = event.target.closest('[data-restore]');
      if (restore) {
        restore.disabled = true;
        RL.act(RL.post(RL.plural(restore.getAttribute('data-restore')) + '/' + restore.getAttribute('data-key') + '/restore')).then(load);
        return;
      }

      const force = event.target.closest('[data-force]');
      if (force) {
        RL.confirm({
          title: 'حذف نهائي',
          text: 'سيُحذف المورد وملفه من السيرفر <b>نهائياً</b> ولا يمكن استرجاعه.',
          confirmText: 'احذف نهائياً', danger: true,
        }).then(function (ok) {
          if (ok) RL.act(RL.del('resources/' + force.getAttribute('data-force') + '/force')).then(load);
        });
      }
    });

    load();
  };

  // ================================================================ ACTIVITY

  Panels.activity = function () {
    const modal = RL.modal({
      title: '<i class="bi bi-clock-history me-2"></i>سجل النشاط',
      body: RL.loading(),
      footer: '<span class="rl-help me-auto">يمكن التراجع عن الإجراءات خلال 15 دقيقة من تنفيذها.</span><button type="button" class="rl-btn" data-bs-dismiss="modal">إغلاق</button>',
      size: 'lg',
    });
    modal.show();

    const sk = RL.state.subjectKey;

    function load() {
      return RL.get('subjects/' + sk + '/activity').then(function (res) {
        if (!res.entries.length) { modal.body.innerHTML = RL.empty('bi-clock-history', 'لا توجد إجراءات مسجلة بعد.'); return; }

        modal.body.innerHTML = res.entries.map(function (e) {
          return '<div class="rl-group-row ' + (e.undone ? '' : 'is-on') + '"><i class="bi ' + (e.undone ? 'bi-arrow-counterclockwise' : 'bi-check2-circle') + '"></i>' +
            '<div class="rl-grow">' + esc(e.summary) + '<div class="rl-help mb-0">' + esc(e.actor) + ' · ' + esc(e.at) + (e.undone ? ' · تم التراجع' : '') + '</div></div>' +
            (e.undoable ? '<button type="button" class="rl-btn rl-btn--sm" data-undo="' + esc(e.token) + '"><i class="bi bi-arrow-counterclockwise"></i> تراجع</button>' : '') + '</div>';
        }).join('');
      }).catch(function (err) {
        modal.body.innerHTML = '<div class="rl-note rl-note--bad">' + esc(err.message) + '</div>';
      });
    }

    modal.body.addEventListener('click', function (event) {
      const button = event.target.closest('[data-undo]');
      if (button) { button.disabled = true; RL.undo(button.getAttribute('data-undo')).then(load); }
    });

    load();
  };

  // ============================================================ AS A STUDENT

  Panels.asStudent = function () {
    const sk = RL.state.subjectKey;
    const groups = (RL.state.groups || []).filter(function (g) { return g.mine; });

    const modal = RL.modal({
      title: '<i class="bi bi-person-video3 me-2"></i>معاينة كما يراها الطالب',
      body:
        '<div class="rl-flex mb-3"><div class="rl-grow"><label class="rl-label">معاينة</label>' +
        '<select class="rl-select w-100" id="rl-as-kind"><option value="group">مجموعة كاملة</option><option value="student">طالب محدد</option></select></div>' +
        '<div class="rl-grow" style="flex-basis:60%"><label class="rl-label" id="rl-as-label">المجموعة</label><div id="rl-as-pick"></div></div></div>' +
        '<div id="rl-as-result">' + RL.empty('bi-eye', 'اختر مجموعة أو طالباً لترى ما يراه بالضبط.') + '</div>',
      footer: '<button type="button" class="rl-btn" data-bs-dismiss="modal">إغلاق</button>',
      size: 'lg',
    });
    modal.show();

    const kindSel = modal.body.querySelector('#rl-as-kind');
    const pickHost = modal.body.querySelector('#rl-as-pick');
    const result = modal.body.querySelector('#rl-as-result');
    let students = null;
    let picker = null;

    function renderPicker() {
      const isGroup = kindSel.value === 'group';
      modal.body.querySelector('#rl-as-label').textContent = isGroup ? 'المجموعة' : 'الطالب';
      result.innerHTML = RL.empty('bi-eye', 'اختر ' + (isGroup ? 'مجموعة' : 'طالباً') + ' لترى ما يراه بالضبط.');

      const mount = function (options) {
        if (picker) picker.destroy();
        picker = RL.picker(pickHost, { options: options, single: true, placeholder: isGroup ? 'اختر مجموعة…' : 'ابحث عن طالب…', onChange: function (v) { if (v.length) show(isGroup, v[0]); } });
      };

      if (isGroup) {
        mount(groups.map(function (g) { return { value: String(g.id), label: g.name }; }));
      } else if (students) {
        mount(students.map(function (s) { return { value: String(s.id), label: s.name, hint: s.group }; }));
      } else {
        pickHost.innerHTML = RL.loading();
        RL.get('subjects/' + sk + '/students').then(function (res) {
          students = res.students;
          mount(students.map(function (s) { return { value: String(s.id), label: s.name, hint: s.group }; }));
        });
      }
    }

    function resourcesHtml(list) {
      return list.map(function (r) { return '<div class="rl-res rl-s-visible" style="margin-top:.3rem"><span class="rl-icon t-' + esc(r.type) + '"><i class="bi ' + esc(r.icon) + '"></i></span><div class="rl-main"><div class="rl-title">' + esc(r.title) + '</div></div></div>'; }).join('');
    }

    function show(isGroup, id) {
      result.innerHTML = RL.loading();
      RL.get('subjects/' + sk + '/preview', isGroup ? { group_id: id } : { student_id: id }).then(function (res) {
        let html = '<div class="rl-note rl-note--info mb-3"><i class="bi bi-eye"></i><div>هكذا يرى <b>' + esc(res.who) + '</b> المحتوى الآن (بعد المجموعات والإيقاف والاستثناءات).</div></div>';

        if (!res.general.length && !res.units.length) {
          html += '<div class="rl-note rl-note--warn"><i class="bi bi-eye-slash"></i><div><b>لا يرى شيئاً.</b> لو كان هذا غير متوقع، افتح «فحص الجودة» لمعرفة السبب.</div></div>';
        }

        if (res.general.length) html += '<div class="rl-section-title mt-2"><i class="bi bi-collection"></i> مرفقات عامة</div>' + resourcesHtml(res.general);

        res.units.forEach(function (u) {
          html += '<div class="rl-section-title mt-3"><i class="bi bi-folder2-open"></i> ' + esc(u.name) + '</div>' + resourcesHtml(u.resources);
          u.lessons.forEach(function (l) {
            html += '<div class="rl-help mt-2 mb-0"><i class="bi bi-journal-text"></i> ' + esc(l.name) + '</div>' + resourcesHtml(l.resources);
          });
        });

        result.innerHTML = html;
      }).catch(function (err) {
        result.innerHTML = '<div class="rl-note rl-note--bad">' + esc(err.message) + '</div>';
      });
    }

    kindSel.addEventListener('change', renderPicker);
    modal.el.addEventListener('hidden.bs.modal', function () { if (picker) picker.destroy(); });
    renderPicker();
  };

  // =================================================================== MEDIA

  /** Embeddable form of Drive / YouTube links (no player chrome, no raw watch URL). */
  function embeddable(url) {
    let m;
    if ((m = url.match(/drive\.google\.com\/file\/d\/([\w-]+)/))) return 'https://drive.google.com/file/d/' + m[1] + '/preview';
    if ((m = url.match(/drive\.google\.com\/(?:open|uc)\?(?:export=\w+&)?id=([\w-]+)/))) return 'https://drive.google.com/file/d/' + m[1] + '/preview';
    if ((m = url.match(/youtube\.com\/watch\?(?:.*&)?v=([\w-]+)/))) return 'https://www.youtube-nocookie.com/embed/' + m[1] + '?rel=0&modestbranding=1&playsinline=1';
    if ((m = url.match(/youtu\.be\/([\w-]+)/))) return 'https://www.youtube-nocookie.com/embed/' + m[1] + '?rel=0&modestbranding=1&playsinline=1';
    if ((m = url.match(/youtube\.com\/(?:shorts|live|embed)\/([\w-]+)/))) return 'https://www.youtube-nocookie.com/embed/' + m[1] + '?rel=0&modestbranding=1&playsinline=1';
    return null;
  }

  /** Preview a resource the way a student would open it (protected: no download control, no context menu). */
  Panels.media = function (resource) {
    const modal = RL.modal({
      title: '<i class="bi bi-eye me-2"></i>' + esc(resource.title),
      body: '<div class="rl-viewer" id="rl-viewer"></div>',
      footer: '<span class="rl-help me-auto"><i class="bi bi-shield-lock"></i> معاينة محمية — لا تحميل ولا نسخ.</span><button type="button" class="rl-btn" data-bs-dismiss="modal">إغلاق</button>',
      size: 'xl',
      scrollable: false,
    });

    const stage = modal.body.querySelector('#rl-viewer');
    let node = null;

    if (resource.is_external) {
      const embed = embeddable(resource.url || '');
      if (!embed) {
        stage.style.aspectRatio = 'auto';
        stage.style.background = 'transparent';
        stage.innerHTML = '<div class="rl-note rl-note--info"><i class="bi bi-box-arrow-up-right"></i><div>هذا رابط خارجي لا يمكن تضمينه هنا. ' +
          '<a href="' + esc(resource.url) + '" target="_blank" rel="noopener noreferrer">افتح الرابط في صفحة جديدة</a>.</div></div>';
      } else {
        node = document.createElement('iframe');
        node.src = embed;
        node.setAttribute('allow', 'autoplay; encrypted-media; picture-in-picture');
        node.setAttribute('allowfullscreen', '');
        node.setAttribute('referrerpolicy', 'no-referrer');
        node.setAttribute('sandbox', 'allow-scripts allow-same-origin allow-presentation');
      }
    } else {
      const src = RL.url('resources/' + resource.key + '/file');
      if (resource.type === 'video') {
        node = document.createElement('video');
        node.src = src;
        node.controls = true;
        node.playsInline = true;
        node.setAttribute('controlsList', 'nodownload noplaybackrate noremoteplayback');
        node.disablePictureInPicture = true;
      } else if (resource.type === 'image') {
        node = document.createElement('img');
        node.src = src;
        node.alt = resource.title;
      } else {
        node = document.createElement('iframe');
        node.src = src;
      }
    }

    if (node) {
      node.addEventListener('contextmenu', function (e) { e.preventDefault(); });
      stage.appendChild(node);
    }

    let destroyWatermark = null;
    modal.el.addEventListener('shown.bs.modal', function () {
      if (typeof window.mountSecureWatermark === 'function') {
        try { destroyWatermark = window.mountSecureWatermark(stage, RL.config.actorName || 'معاينة', null); } catch (e) { /* optional */ }
      }
    });

    modal.el.addEventListener('hidden.bs.modal', function () {
      if (node && node.tagName === 'VIDEO') { node.pause(); node.removeAttribute('src'); node.load(); }
      if (typeof destroyWatermark === 'function') destroyWatermark();
    });

    modal.show();
  };

})(window, document);
