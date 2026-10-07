/**
 * Resource library workspace - forms: units, lessons, resources (upload once).
 *
 *   RL.Forms.unit()      create / rename a unit
 *   RL.Forms.lesson()    create / rename a lesson
 *   RL.Forms.resource()  create (file / video in chunks / link) or edit a resource
 *
 * A resource form picks WHERE it is shown (any number of lessons / units) and WHO sees it
 * (inherit from the place, every group, or chosen groups) - the file is uploaded once.
 */
(function (window, document) {
  'use strict';

  const RL = window.RL;
  const esc = RL.esc;
  const Forms = (RL.Forms = {});

  // ------------------------------------------------------------ upload monitor

  const monitor = (function () {
    let el = null;
    let total = 0;
    let lastLoaded = 0;
    let lastTime = 0;
    let rate = 0;
    let onCancel = null;
    let hideTimer = null;

    function ensure() {
      if (el) return el;
      el = RL.html(
        '<div class="rl-upload rl-theme-' + (RL.config.role === 'admin' ? 'admin' : 'teacher') + '" hidden>' +
          '<div class="d-flex justify-content-between align-items-center gap-2"><div class="rl-upload-name"></div>' +
          '<button type="button" class="rl-btn rl-btn--sm rl-btn--ghost rl-btn--icon" data-cancel title="إلغاء الرفع"><i class="bi bi-x-lg"></i></button></div>' +
          '<div class="rl-help rl-upload-state mb-0"></div>' +
          '<div class="rl-upload-bar"><div class="rl-upload-fill"></div></div>' +
          '<div class="rl-upload-stats"><span class="rl-pct">0%</span><span class="rl-speed">—</span><span class="rl-eta">—</span><span class="rl-size">—</span></div>' +
        '</div>'
      );
      document.body.appendChild(el);
      el.querySelector('[data-cancel]').addEventListener('click', function () {
        if (onCancel) onCancel();
        onCancel = null;
        api.hide();
      });
      return el;
    }

    const set = function (selector, text) { ensure().querySelector(selector).textContent = text; };

    const api = {
      start: function (name, size, cancel) {
        ensure();
        clearTimeout(hideTimer);
        total = size || 0; lastLoaded = 0; lastTime = Date.now(); rate = 0; onCancel = cancel || null;
        el.classList.remove('is-done', 'is-error');
        el.hidden = false;
        set('.rl-upload-name', name || 'ملف');
        set('.rl-upload-state', 'جارٍ الرفع…');
        api.update(0, total);
      },
      update: function (loaded, size) {
        if (size) total = size;
        const now = Date.now();
        const elapsed = (now - lastTime) / 1000;
        if (elapsed >= 0.25) {
          const instant = (loaded - lastLoaded) / elapsed;
          rate = rate ? rate * 0.7 + instant * 0.3 : instant;
          lastLoaded = loaded; lastTime = now;
        }
        const pct = total ? Math.min(100, Math.floor(loaded / total * 100)) : 0;
        ensure().querySelector('.rl-upload-fill').style.width = pct + '%';
        set('.rl-pct', pct + '%');
        set('.rl-speed', rate > 0 ? RL.formatBytes(rate) + '/ث' : '—');
        set('.rl-eta', rate > 0 && total ? RL.formatDuration((total - loaded) / rate) : '—');
        set('.rl-size', total ? RL.formatBytes(loaded) + ' / ' + RL.formatBytes(total) : RL.formatBytes(loaded));
      },
      finishing: function (text) { set('.rl-upload-state', text || 'اكتمل الرفع، جارٍ الحفظ…'); ensure().querySelector('.rl-upload-fill').style.width = '100%'; },
      done: function (text) {
        el.classList.add('is-done'); set('.rl-upload-state', text || 'تم الرفع بنجاح'); set('.rl-pct', '100%');
        hideTimer = setTimeout(function () { el.hidden = true; }, 3500);
      },
      error: function (text) { ensure(); el.hidden = false; el.classList.add('is-error'); set('.rl-upload-state', text || 'فشل الرفع'); },
      hide: function () { if (el) el.hidden = true; },
    };

    return api;
  })();

  RL.uploadMonitor = monitor;

  /** multipart POST with upload progress (fetch cannot report it). */
  function xhrSend(method, path, formData, onProgress, onXhr) {
    return new Promise(function (resolve, reject) {
      const xhr = new XMLHttpRequest();
      xhr.open('POST', RL.url(path));
      xhr.setRequestHeader('Accept', 'application/json');
      xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
      xhr.setRequestHeader('X-CSRF-TOKEN', RL.config.csrf || '');
      if (method !== 'POST') formData.append('_method', method);
      if (xhr.upload && onProgress) xhr.upload.addEventListener('progress', function (e) { if (e.lengthComputable) onProgress(e.loaded, e.total); });
      if (onXhr) onXhr(xhr);

      xhr.onload = function () {
        let data = {};
        try { data = JSON.parse(xhr.responseText || '{}'); } catch (e) { /* html error page */ }
        if (xhr.status >= 200 && xhr.status < 300 && data.success !== false) return resolve(data);

        let message = data.message || 'تعذّر حفظ المورد (' + xhr.status + ').';
        if (xhr.status === 422 && data.errors) message = Object.keys(data.errors).map(function (k) { return data.errors[k][0]; }).join(' — ');
        if (xhr.status === 413) message = 'حجم الملف أكبر من المسموح.';
        reject(new RL.Error(message, xhr.status, data));
      };
      xhr.onerror = function () { reject(new RL.Error('انقطع الاتصال أثناء الرفع.', 0)); };
      xhr.onabort = function () { reject(new RL.Error('تم إلغاء الرفع.', 0, { aborted: true })); };
      xhr.send(formData);
    });
  }

  // ----------------------------------------------------------- audience field

  /**
   * Who sees new content: inherit from the place / every group / chosen groups.
   */
  function audienceField(host, options) {
    const groups = (options.groups || []).filter(function (g) { return options.isAdmin || g.mine; });
    const allLabel = options.canShareAll ? 'كل مجموعات المادة' : 'كل مجموعاتي';
    const allHelp = options.canShareAll ? 'ويظهر تلقائياً لأي مجموعة تُضاف لاحقاً.' : 'مجموعاتك الحالية فقط.';
    const id = 'aud' + Math.random().toString(36).slice(2, 8);
    let mode = options.groupTab ? 'groups' : 'inherit';
    let picker = null;

    host.innerHTML =
      '<div class="rl-card" style="margin-bottom:0">' +
        '<div class="rl-section-title"><i class="bi bi-people-fill"></i> من يرى هذا المحتوى؟</div>' +
        [
          ['inherit', 'حسب المكان (الأنسب غالباً)', 'يرث جمهور الدرس أو الوحدة المختارة.'],
          ['shared', allLabel, allHelp],
          ['groups', 'مجموعات محددة', 'اختر مجموعة أو أكثر.'],
        ].map(function (r) {
          return '<label class="rl-flex" style="align-items:flex-start;cursor:pointer;margin-bottom:.4rem"><input type="radio" name="' + id + '" value="' + r[0] + '"' + (mode === r[0] ? ' checked' : '') + ' style="margin-top:.3rem">' +
            '<span><b>' + r[1] + '</b><span class="rl-help d-block mt-0">' + r[2] + '</span></span></label>';
        }).join('') +
        '<div class="mt-2' + (mode === 'groups' ? '' : ' rl-hide') + '" data-groups></div>' +
      '</div>';

    const groupsHost = host.querySelector('[data-groups]');
    picker = RL.picker(groupsHost, {
      options: groups.map(function (g) { return { value: String(g.id), label: g.name }; }),
      selected: options.groupTab ? [String(options.groupTab)] : [],
      placeholder: 'اختر المجموعات…',
    });

    host.addEventListener('change', function (event) {
      if (event.target.name !== id) return;
      mode = event.target.value;
      groupsHost.classList.toggle('rl-hide', mode !== 'groups');
    });

    return {
      value: function () {
        const out = { audience_mode: mode };
        if (mode === 'groups') out.group_ids = picker.get().map(Number);
        return out;
      },
      valid: function () { return mode !== 'groups' || picker.get().length > 0; },
      destroy: function () { picker.destroy(); },
    };
  }

  function audienceOptions() {
    const st = RL.state || {};
    const groups = st.groups || [];
    return {
      groups: groups,
      groupTab: st.groupTab || null,
      isAdmin: !!RL.config.isAdmin,
      canShareAll: !!RL.config.isAdmin || (groups.length > 0 && groups.every(function (g) { return g.mine; })),
    };
  }

  // -------------------------------------------------------------- name modal

  function nameForm(options) {
    const editing = options.mode === 'edit';
    const modal = RL.modal({
      title: '<i class="bi ' + options.icon + ' me-2"></i>' + options.title,
      size: 'md',
      scrollable: false,
      body:
        '<div class="mb-3"><label class="rl-label">الاسم بالعربية <span class="text-danger">*</span></label><input type="text" class="rl-input" name="name_ar" maxlength="255" required></div>' +
        '<div class="mb-3"><label class="rl-label">الاسم بالإنجليزية <small class="text-muted">(اختياري)</small></label><input type="text" class="rl-input" name="name_en" maxlength="255" dir="ltr"></div>' +
        (editing ? '' : '<div data-audience></div>'),
      footer: '<button type="button" class="rl-btn rl-btn--primary" data-save><i class="bi bi-check2"></i> حفظ</button><button type="button" class="rl-btn" data-bs-dismiss="modal">إلغاء</button>',
    });

    const nameAr = modal.body.querySelector('[name="name_ar"]');
    const nameEn = modal.body.querySelector('[name="name_en"]');
    nameAr.value = (options.values && options.values.name) || '';
    nameEn.value = (options.values && options.values.name_en) || '';

    let audience = null;
    const audienceHost = modal.body.querySelector('[data-audience]');
    if (audienceHost) audience = audienceField(audienceHost, audienceOptions());

    modal.el.querySelector('[data-save]').addEventListener('click', function () {
      if (!nameAr.value.trim()) { nameAr.focus(); return RL.toast({ message: 'اكتب اسماً بالعربية.', type: 'info' }); }
      if (audience && !audience.valid()) return RL.toast({ message: 'اختر مجموعة واحدة على الأقل.', type: 'info' });

      const button = this;
      button.disabled = true;
      const payload = Object.assign({ name_ar: nameAr.value.trim(), name_en: nameEn.value.trim() || null }, audience ? audience.value() : {});

      options.submit(payload).then(function (res) {
        RL.done(res);
        modal.hide();
        if (options.onSaved) options.onSaved(res);
      }, function (err) {
        button.disabled = false;
        RL.toast({ message: err.message, type: 'error', duration: 6000 });
      });
    });

    modal.el.addEventListener('shown.bs.modal', function () { nameAr.focus(); });
    modal.el.addEventListener('hidden.bs.modal', function () { if (audience) audience.destroy(); });
    modal.show();
  }

  /** @param {{unit?: {key: string, name: string, name_en: string}}} options */
  Forms.unit = function (options) {
    const unit = (options || {}).unit;
    nameForm({
      mode: unit ? 'edit' : 'create',
      icon: 'bi-folder2-open',
      title: unit ? 'تعديل الوحدة' : 'إضافة وحدة جديدة',
      values: unit,
      submit: function (payload) {
        return unit ? RL.put('units/' + unit.key, payload) : RL.post('subjects/' + RL.state.subjectKey + '/units', payload);
      },
    });
  };

  /** @param {{unitKey?: string, lesson?: {key: string, name: string, name_en: string}}} options */
  Forms.lesson = function (options) {
    const lesson = options.lesson;
    nameForm({
      mode: lesson ? 'edit' : 'create',
      icon: 'bi-journal-text',
      title: lesson ? 'تعديل الدرس' : 'إضافة درس جديد',
      values: lesson,
      submit: function (payload) {
        return lesson ? RL.put('lessons/' + lesson.key, payload) : RL.post('units/' + options.unitKey + '/lessons', payload);
      },
    });
  };

  // ----------------------------------------------------------- resource modal

  /** Options of the "where is it shown" picker, from the loaded tree. */
  function placeOptions() {
    const tree = RL.state.tree || { units: [] };
    const options = [{ value: 'general', label: 'المرفقات العامة (بدون وحدة)', group: 'عام' }];

    tree.units.forEach(function (unit) {
      options.push({ value: 'unit:' + unit.key, label: 'الوحدة نفسها (بدون درس)', group: unit.name });
      unit.lessons.forEach(function (lesson) { options.push({ value: 'lesson:' + lesson.key, label: lesson.name, group: unit.name }); });
    });

    return options;
  }

  const TYPES = [
    ['video', 'فيديو (رفع ملف)'],
    ['document', 'ملف / PDF / مستند'],
    ['image', 'صورة'],
    ['link', 'رابط خارجي (يوتيوب، درايف…)'],
    ['zoom', 'رابط Zoom'],
  ];

  /**
   * @param {{resource?: object, places?: string[]}} options
   *   resource  edit this resource (a state from the tree)
   *   places    pre-selected placement values: 'lesson:KEY' | 'unit:KEY' | 'general'
   */
  Forms.resource = function (options) {
    options = options || {};
    const resource = options.resource || null;
    const editing = !!resource;
    let type = resource ? resource.type : 'video';

    const modal = RL.modal({
      title: '<i class="bi bi-cloud-arrow-up me-2"></i>' + (editing ? 'تعديل «' + esc(resource.title) + '»' : 'إضافة مورد (يُرفع مرة واحدة ويظهر أينما تشاء)'),
      size: 'lg',
      body:
        '<div class="row g-3">' +
          '<div class="col-md-8"><label class="rl-label">العنوان <span class="text-danger">*</span></label><input type="text" class="rl-input" name="title" maxlength="255"></div>' +
          '<div class="col-md-4"><label class="rl-label">النوع</label><select class="rl-select w-100" name="type"' + (editing ? ' disabled' : '') + '>' +
            TYPES.map(function (t) { return '<option value="' + t[0] + '"' + (t[0] === type ? ' selected' : '') + '>' + t[1] + '</option>'; }).join('') + '</select></div>' +
          '<div class="col-12" data-source></div>' +
          '<div class="col-12"><label class="rl-label">وصف مختصر</label><textarea class="rl-textarea" name="description" rows="2" maxlength="1000"></textarea></div>' +
          '<div class="col-12 rl-flex"><label class="rl-switch"><input type="checkbox" name="allow_download"><i></i></label><span>السماح للطلاب بتحميل الملف</span></div>' +
          (editing ? '' :
            '<div class="col-12"><label class="rl-label">أين يظهر؟ <small class="text-muted">(يمكن اختيار أكثر من مكان — نفس الملف دون تكرار)</small></label><div data-places></div></div>' +
            '<div class="col-12" data-audience></div>') +
        '</div>',
      footer: '<button type="button" class="rl-btn rl-btn--primary" data-save><i class="bi bi-check2"></i> حفظ</button><button type="button" class="rl-btn" data-bs-dismiss="modal">إلغاء</button>',
    });

    const body = modal.body;
    const titleEl = body.querySelector('[name="title"]');
    const typeEl = body.querySelector('[name="type"]');
    const descEl = body.querySelector('[name="description"]');
    const dlEl = body.querySelector('[name="allow_download"]');
    const sourceHost = body.querySelector('[data-source]');
    const saveBtn = modal.el.querySelector('[data-save]');

    titleEl.value = resource ? resource.title : '';
    descEl.value = resource ? (resource.description || '') : '';
    dlEl.checked = resource ? !!resource.allow_download : false;

    let picker = null;
    let audience = null;
    if (!editing) {
      picker = RL.picker(body.querySelector('[data-places]'), {
        options: placeOptions(),
        selected: options.places || [],
        placeholder: 'اختر درساً أو وحدة…',
      });
      audience = audienceField(body.querySelector('[data-audience]'), audienceOptions());
    }

    // ---- source (file / video / link)
    // `owned`: the parked file was uploaded by this form, so closing the form without saving deletes it.
    // An adopted upload (options.upload, from the "uploaded files" panel) is left alone.
    const upload = { path: null, name: null, busy: false, resumable: null, file: null, owned: false, keepalive: null };
    const adopted = options.upload || null;
    let saved = false;

    function discardParked() {
      if (upload.path && upload.owned) RL.del('upload-chunk', { path: upload.path }).catch(function () { /* the nightly cleanup gets it */ });
      upload.path = null; upload.name = null; upload.owned = false;
    }

    function stopKeepalive() {
      if (upload.keepalive) { clearInterval(upload.keepalive); upload.keepalive = null; }
    }

    function renderSource() {
      discardParked();
      upload.file = null;
      if (upload.resumable) { try { upload.resumable.cancel(); } catch (e) { /* idle */ } upload.resumable = null; }
      stopKeepalive();

      let html = '';
      if (adopted && type === adopted.type) {
        upload.path = adopted.path; upload.name = adopted.name;
        html = '<label class="rl-label">الملف</label><div class="rl-note mb-0"><i class="bi bi-file-earmark-check"></i><div>ملف مرفوع سابقاً: <b>' + esc(adopted.name || adopted.path) + '</b>' +
          (adopted.size ? ' (' + RL.formatBytes(adopted.size) + ')' : '') + ' — سيُستعمل كما هو دون إعادة رفع.</div></div>';
      } else if (type === 'video') {
        html = '<label class="rl-label">' + (editing ? 'استبدال الفيديو (اختياري)' : 'ملف الفيديو') + '</label>' +
          '<div class="rl-flex"><input type="file" class="d-none" data-file accept="video/*"><button type="button" class="rl-btn" data-browse><i class="bi bi-film"></i> اختر ملف الفيديو</button><span class="rl-help mb-0" data-fname></span></div>' +
          '<div class="rl-upload-bar d-none" data-bar><div class="rl-upload-fill"></div></div><div class="rl-help">يُرفع على أجزاء (قابل للاستئناف) ويُحفظ مرة واحدة فقط.</div>';
      } else if (type === 'document' || type === 'image') {
        html = '<label class="rl-label">' + (editing ? 'استبدال الملف (اختياري)' : (type === 'image' ? 'الصورة' : 'الملف')) + '</label>' +
          '<input type="file" class="rl-input" data-file accept="' + (type === 'image' ? '.jpg,.jpeg,.png,.webp,.gif' : '.pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx') + '">' +
          '<div class="rl-help">الحد الأقصى 50 ميجابايت.</div>';
      } else {
        html = '<label class="rl-label">الرابط</label><input type="url" class="rl-input" name="url" dir="ltr" placeholder="https://…" value="' + esc(resource && resource.url ? resource.url : '') + '">';
      }
      if (editing && resource.file_missing) {
        html = '<div class="rl-note rl-note--bad mb-2"><i class="bi bi-exclamation-octagon"></i><div>ملف هذا المورد غير موجود على السيرفر. ارفع الملف البديل هنا ثم احفظ' +
          (resource.is_active === false ? '، وبعدها أعد تشغيل المورد للطلاب.' : '.') + ' تبقى أماكن العرض والمجموعات والاستثناءات كما هي.</div></div>' + html;
      }
      sourceHost.innerHTML = html;
      wireSource();
    }

    function wireSource() {
      const fileInput = sourceHost.querySelector('[data-file]');
      if (!fileInput) return;

      if (type === 'video') {
        sourceHost.querySelector('[data-browse]').addEventListener('click', function () { fileInput.click(); });
        fileInput.addEventListener('change', function () { if (fileInput.files.length) startVideoUpload(fileInput.files[0]); });
      } else {
        fileInput.addEventListener('change', function () { upload.file = fileInput.files[0] || null; });
      }
    }

    function startVideoUpload(file) {
      if (typeof window.Resumable !== 'function') return RL.toast({ message: 'أداة الرفع المجزّأ غير محمّلة، حدّث الصفحة.', type: 'error' });

      const limits = RL.config.upload || {};
      if (limits.max_video_bytes && file.size > limits.max_video_bytes) {
        return RL.toast({ message: 'حجم الفيديو (' + RL.formatBytes(file.size) + ') أكبر من الحد المسموح (' + RL.formatBytes(limits.max_video_bytes) + ').', type: 'error', duration: 7000 });
      }

      discardParked();
      upload.busy = true;
      sourceHost.querySelector('[data-fname]').textContent = file.name;
      const bar = sourceHost.querySelector('[data-bar]');
      bar.classList.remove('d-none');
      const fill = bar.querySelector('.rl-upload-fill');
      fill.style.width = '0%';
      saveBtn.disabled = true;
      if (!titleEl.value.trim()) titleEl.value = file.name.replace(/\.[^/.]+$/, '');

      const r = new window.Resumable({
        target: RL.url('upload-chunk'),
        chunkSize: limits.chunk_bytes || 5 * 1024 * 1024,
        simultaneousUploads: 3,
        testChunks: false,
        maxChunkRetries: 8,
        chunkRetryInterval: 3000,
        // too big / refused / rejected file: retrying the same chunk cannot help
        permanentErrors: [400, 403, 404, 413, 415, 422, 500, 501],
        // read the token on every chunk: it is renewed while the upload runs
        query: function () { return { _token: RL.config.csrf }; },
        headers: function () { return { 'X-CSRF-TOKEN': RL.config.csrf, 'Accept': 'application/json' }; },
      });
      upload.resumable = r;

      // a long upload must not outlive the session: touch it regularly and pick up a renewed token
      stopKeepalive();
      upload.keepalive = setInterval(function () { RL.refreshCsrf().catch(function () { /* next tick */ }); }, (limits.keepalive_seconds || 300) * 1000);

      r.on('fileAdded', function () {
        monitor.start(file.name, file.size, function () { r.cancel(); stopKeepalive(); upload.busy = false; saveBtn.disabled = false; bar.classList.add('d-none'); });
        r.upload();
      });
      r.on('fileProgress', function () {
        const ratio = r.progress();
        fill.style.width = Math.floor(ratio * 100) + '%';
        monitor.update(Math.round(ratio * file.size), file.size);
      });
      // a chunk refused with 419 (token expired) is retried after chunkRetryInterval: renew the token meanwhile
      r.on('fileRetry', function () { RL.refreshCsrf().catch(function () { /* the retry reports it */ }); });
      r.on('fileSuccess', function (f, response) {
        let data = {};
        try { data = JSON.parse(response); } catch (e) { /* ignore */ }
        stopKeepalive();
        upload.path = data.path || null;
        upload.name = data.original_filename || file.name;
        upload.owned = !!upload.path;
        upload.busy = false;
        fill.style.width = '100%';
        saveBtn.disabled = false;
        monitor.done('تم رفع الفيديو — اضغط حفظ لإضافته');
      });
      r.on('fileError', function (f, response) {
        let data = {};
        try { data = JSON.parse(response); } catch (e) { /* html error page */ }
        stopKeepalive();
        upload.busy = false; saveBtn.disabled = false;
        const reason = data.errors ? Object.keys(data.errors).map(function (k) { return data.errors[k][0]; }).join(' — ') : data.message;
        monitor.error(reason || 'تعذّر رفع الفيديو — تحقق من الاتصال ثم أعد اختيار الملف.');
      });
      r.addFile(file);
    }

    typeEl.addEventListener('change', function () { type = typeEl.value; renderSource(); });
    if (adopted && !editing) { type = adopted.type; typeEl.value = adopted.type; }
    if (adopted && !titleEl.value) titleEl.value = (adopted.name || '').replace(/\.[^/.]+$/, '');
    renderSource();

    // ---- save
    saveBtn.addEventListener('click', function () {
      const title = titleEl.value.trim();
      if (!title) { titleEl.focus(); return RL.toast({ message: 'اكتب عنواناً للمورد.', type: 'info' }); }
      if (upload.busy) return RL.toast({ message: 'انتظر حتى ينتهي رفع الفيديو.', type: 'info' });

      const form = new FormData();
      form.append('title', title);
      form.append('description', descEl.value.trim());
      form.append('allow_download', dlEl.checked ? 1 : 0);

      if (!editing) {
        form.append('type', type);
        picker.get().forEach(function (value, i) {
          const t = value === 'general' ? { type: 'general' } : { type: value.split(':')[0], key: value.slice(value.indexOf(':') + 1) };
          form.append('targets[' + i + '][type]', t.type);
          if (t.key) form.append('targets[' + i + '][key]', t.key);
        });
        if (!audience.valid()) return RL.toast({ message: 'اختر مجموعة واحدة على الأقل.', type: 'info' });
        const a = audience.value();
        form.append('audience_mode', a.audience_mode);
        (a.group_ids || []).forEach(function (id) { form.append('group_ids[]', id); });
      }

      const urlEl = sourceHost.querySelector('[name="url"]');
      if (urlEl) form.append('url', urlEl.value.trim());
      if (upload.path) { form.append('uploaded_path', upload.path); form.append('original_filename', upload.name || ''); }
      if (upload.file) form.append('file', upload.file);

      if (!editing && type === 'video' && !upload.path) return RL.toast({ message: 'اختر ملف الفيديو أولاً.', type: 'info' });
      if (!editing && (type === 'document' || type === 'image') && !upload.file && !upload.path) return RL.toast({ message: 'اختر الملف أولاً.', type: 'info' });
      if (!editing && (type === 'link' || type === 'zoom') && !urlEl.value.trim()) { urlEl.focus(); return RL.toast({ message: 'اكتب الرابط.', type: 'info' }); }

      const path = editing ? 'resources/' + resource.key : 'subjects/' + RL.state.subjectKey + '/resources';
      saveBtn.disabled = true;

      if (upload.file) monitor.start(upload.file.name, upload.file.size, function () { /* abort handled below */ });

      let xhrRef = null;
      xhrSend(editing ? 'PUT' : 'POST', path, form, upload.file ? monitor.update : null, function (xhr) { xhrRef = xhr; }).then(function (res) {
        saved = true;
        if (upload.file) monitor.done();
        RL.done(res);
        modal.hide();
        if (options.onSaved) options.onSaved(res);
      }, function (err) {
        saveBtn.disabled = false;
        if (upload.file) monitor.error(err.message);
        RL.toast({ message: err.message, type: 'error', duration: 7000 });
      });
      void xhrRef;
    });

    modal.el.addEventListener('shown.bs.modal', function () { titleEl.focus(); });
    modal.el.addEventListener('hidden.bs.modal', function () {
      if (picker) picker.destroy();
      if (audience) audience.destroy();
      if (upload.resumable && upload.busy) { try { upload.resumable.cancel(); } catch (e) { /* idle */ } monitor.hide(); }
      stopKeepalive();
      if (!saved) discardParked();
    });

    modal.show();
  };

  Forms.audienceField = audienceField;

})(window, document);
