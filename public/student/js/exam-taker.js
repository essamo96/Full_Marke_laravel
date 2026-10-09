/*
 * Online exam engine: never lose, flip or duplicate a student's answers, no
 * matter how flaky the connection is.
 *
 *  - Every answer is stored as { v: value, t: timestamp } in a single in-memory
 *    state, mirrored to localStorage on each change (survives reloads / crashes)
 *    and pushed to the server (autosave) whenever a connection is available.
 *  - The state is merged per question, newest timestamp wins, both here and on
 *    the server. A request delayed by a dropped connection can therefore never
 *    overwrite a newer choice, and a reload can never bring back an old one.
 *  - Failed saves are retried with back-off and immediately when the browser
 *    reports it is online again.
 *  - The countdown is driven by the server's clock (remaining seconds), so a
 *    reload / reconnect can not reset it.
 *  - Submitting survives an outage too: the exam is handed in automatically as
 *    soon as the connection returns.
 *
 * Pure helpers are exported for the Node unit tests (tests/js/exam-taker.test.cjs).
 */
(function (root, factory) {
    if (typeof module === 'object' && module.exports) {
        module.exports = factory();
    } else {
        root.ExamTaker = factory();
    }
})(typeof self !== 'undefined' ? self : this, function () {
    'use strict';

    // ------------------------------------------------------------------
    // Pure helpers
    // ------------------------------------------------------------------

    /** Per-question merge, newest timestamp wins, `incoming` wins ties. */
    function mergeStates(base, incoming) {
        var out = {};
        var k;
        for (k in (base || {})) {
            if (Object.prototype.hasOwnProperty.call(base, k) && isEntry(base[k])) out[k] = base[k];
        }
        for (k in (incoming || {})) {
            if (!Object.prototype.hasOwnProperty.call(incoming, k) || !isEntry(incoming[k])) continue;
            var cur = out[k];
            if (!cur || Number(incoming[k].t) >= Number(cur.t)) out[k] = incoming[k];
        }
        return out;
    }

    function isEntry(e) {
        return e !== null && typeof e === 'object' && 'v' in e && isFinite(Number(e.t));
    }

    function hasValue(entry) {
        return !!entry && entry.v !== null && entry.v !== undefined && String(entry.v).trim() !== '';
    }

    function statesEqual(a, b) {
        var ka = Object.keys(a || {}), kb = Object.keys(b || {});
        if (ka.length !== kb.length) return false;
        return ka.every(function (k) {
            return b[k] && String(a[k].v) === String(b[k].v) && Number(a[k].t) === Number(b[k].t);
        });
    }

    function countAnswered(state, questionIds) {
        return (questionIds || []).filter(function (id) { return hasValue(state[id]); }).length;
    }

    /** Retry delay in ms: 2s, 4s, 8s ... capped at 30s. `random` (0..1) adds up to 25% jitter. */
    function backoffDelay(attempt, random) {
        var base = Math.min(30000, 2000 * Math.pow(2, Math.max(0, attempt)));
        return Math.round(base * (1 + 0.25 * (random === undefined ? Math.random() : random)));
    }

    function formatClock(totalSeconds) {
        var s = Math.max(0, Math.floor(totalSeconds));
        var h = Math.floor(s / 3600), m = Math.floor((s % 3600) / 60), sec = s % 60;
        var p = function (n) { return n < 10 ? '0' + n : String(n); };
        return p(h) + ':' + p(m) + ':' + p(sec);
    }

    function safeParse(json, fallback) {
        try { var v = JSON.parse(json); return v === null || v === undefined ? fallback : v; } catch (e) { return fallback; }
    }

    // ------------------------------------------------------------------
    // Engine
    // ------------------------------------------------------------------

    /**
     * @param {object} cfg   examId, studentId, attemptId, draftUrl, submitUrl, questionIds, answers,
     *                       started, remainingSeconds, csrf
     * @param {object} env   optional overrides for tests: document, window, fetch, storage, now
     * @param {object} hooks onTimeUp(), onSessionExpired(), notify(message)
     */
    function init(cfg, env, hooks) {
        env = env || {};
        hooks = hooks || {};
        var win = env.window || window;
        var doc = env.document || document;
        var doFetch = env.fetch || function () { return win.fetch.apply(win, arguments); };
        var nowFn = env.now || function () { return Date.now(); };
        var storage = env.storage !== undefined ? env.storage : (function () { try { return win.localStorage; } catch (e) { return null; } })();

        var form = doc.getElementById('examForm');
        var storageKey = 'fm_exam_' + cfg.examId + '_' + cfg.studentId + '_' + (cfg.attemptId || 0);
        var csrf = cfg.csrf;
        var questionIds = (cfg.questionIds || []).map(String);

        var state = {};          // { qid: { v, t } }  the one source of truth
        var lastT = 0;
        var version = 0;         // bumped on every local change
        var syncedVersion = 0;   // version known to be on the server
        var inFlight = false;
        var failures = 0;
        var needBegin = false;
        var startPressedAt = null; // device time of the "start" press, so a start made offline is not handed free time
        var stopped = false;
        var sessionExpired = false;
        var submitting = false;
        var syncTimer = null;
        var heartbeat = null;
        var deadline = null;     // epoch ms when time is up (timed exams)
        var timerHandle = null;
        var timeUpFired = false;
        var SYNC_TIMEOUT_MS = cfg.syncTimeoutMs || 15000;
        var SUBMIT_TIMEOUT_MS = cfg.submitTimeoutMs || 45000;

        // Answer timestamps are expressed in SERVER time: students' device clocks are often wrong, and
        // they get corrected (jump) exactly when the connection comes back. clockOffset = server - device.
        var clockOffset = cfg.serverTime ? Number(cfg.serverTime) - nowFn() : 0;

        // ---------- state ----------

        function nextT() {
            // strictly increasing on this device, whatever the clock does
            lastT = Math.max(nowFn() + clockOffset, lastT + 1);
            return lastT;
        }

        /** fetch that gives up after `ms` so one hung request on a bad connection can not block saving. */
        function timedFetch(url, opts, ms) {
            var Ctl = win.AbortController || (typeof AbortController !== 'undefined' ? AbortController : null);
            var ctl = Ctl ? new Ctl() : null;
            var timer = null;
            if (ctl) {
                opts.signal = ctl.signal;
                timer = win.setTimeout(function () { ctl.abort(); }, ms);
            }
            var done = function () { if (timer) win.clearTimeout(timer); };
            return { promise: doFetch(url, opts), done: done };
        }

        function persist() {
            if (!storage) return;
            try { storage.setItem(storageKey, JSON.stringify({ answers: state, savedAt: nowFn() })); } catch (e) { /* quota / private mode: server copy still protects us */ }
        }

        function loadLocal() {
            if (!storage) return {};
            try {
                var saved = safeParse(storage.getItem(storageKey), null);
                return saved && typeof saved.answers === 'object' ? saved.answers : {};
            } catch (e) { return {}; }
        }

        function clearLocal() {
            if (!storage) return;
            try { storage.removeItem(storageKey); } catch (e) { /* ignore */ }
        }

        function setAnswer(qid, value) {
            qid = String(qid);
            state[qid] = { v: value, t: nextT() };
            version++;
            persist();
            updateProgress();
            setStatus('pending');
        }

        // ---------- DOM <-> state ----------

        function radiosOf(qid) { return form ? form.querySelectorAll('input[type="radio"][data-qid="' + qid + '"]') : []; }
        function textareaOf(qid) { return form ? form.querySelector('textarea[data-qid="' + qid + '"]') : null; }

        function applyStateToDom() {
            questionIds.forEach(function (qid) {
                var entry = state[qid];
                var value = entry && entry.v !== null && entry.v !== undefined ? String(entry.v) : '';

                Array.prototype.forEach.call(radiosOf(qid), function (radio) {
                    var on = value !== '' && radio.value === value;
                    radio.checked = on;
                    var label = radio.closest ? radio.closest('label') : null;
                    if (label) label.classList.toggle('is-selected', on);
                });

                var ta = textareaOf(qid);
                if (ta && doc.activeElement !== ta && ta.value !== value) ta.value = value;
            });
            updateProgress();
        }

        function updateProgress() {
            var el = doc.getElementById('examProgress');
            if (el) el.textContent = countAnswered(state, questionIds) + ' / ' + questionIds.length;
        }

        function bindInputs() {
            if (!form) return;
            form.addEventListener('change', function (e) {
                var t = e.target;
                if (t && t.matches && t.matches('input[type="radio"][data-qid]')) {
                    setAnswer(t.getAttribute('data-qid'), t.value);
                    Array.prototype.forEach.call(radiosOf(t.getAttribute('data-qid')), function (r) {
                        var label = r.closest ? r.closest('label') : null;
                        if (label) label.classList.toggle('is-selected', r.checked);
                    });
                    scheduleSync(300);
                }
            });
            form.addEventListener('input', function (e) {
                var t = e.target;
                if (t && t.matches && t.matches('textarea[data-qid]')) {
                    setAnswer(t.getAttribute('data-qid'), t.value);
                    scheduleSync(1200);
                }
            });
        }

        // ---------- status UI ----------

        var statusKind = null;
        function setStatus(kind, extra) {
            statusKind = kind;
            var text = doc.getElementById('examSaveStatus');
            var icon = doc.getElementById('examSaveIcon');
            var bar = doc.getElementById('examConnBanner');
            var online = win.navigator ? win.navigator.onLine !== false : true;

            var label = {
                saved: 'تم حفظ إجاباتك' + (extra ? ' ' + extra : ''),
                pending: 'جارٍ حفظ التغييرات...',
                offline: 'لا يوجد اتصال — إجاباتك محفوظة على جهازك',
                error: 'تعذر الحفظ، سنعيد المحاولة تلقائياً',
                expired: 'انتهت الجلسة'
            }[kind] || '';
            var iconClass = {
                saved: 'bi bi-cloud-check-fill text-success',
                pending: 'bi bi-cloud-arrow-up text-warning',
                offline: 'bi bi-wifi-off text-danger',
                error: 'bi bi-exclamation-triangle-fill text-warning',
                expired: 'bi bi-lock-fill text-danger'
            }[kind] || '';

            if (text) text.textContent = label;
            if (icon) icon.className = iconClass;

            if (bar) {
                var message = null;
                if (kind === 'expired') {
                    message = 'انتهت صلاحية جلستك. سجّل الدخول مرة أخرى — إجاباتك محفوظة على جهازك وستُستعاد تلقائياً.';
                } else if (!online || kind === 'offline') {
                    message = 'انقطع الاتصال بالإنترنت. تابع الإجابة بشكل طبيعي — إجاباتك محفوظة على جهازك وسيتم إرسالها تلقائياً فور عودة الاتصال.';
                } else if (kind === 'error') {
                    message = 'الاتصال غير مستقر، جارٍ إعادة محاولة حفظ إجاباتك تلقائياً.';
                }
                bar.textContent = message || '';
                bar.classList.toggle('d-none', !message);
            }
        }

        // ---------- sync ----------

        function scheduleSync(delay) {
            if (stopped || sessionExpired) return;
            if (syncTimer) win.clearTimeout(syncTimer);
            syncTimer = win.setTimeout(function () { syncTimer = null; sync(); }, delay === undefined ? 0 : delay);
        }

        function isDirty() { return version !== syncedVersion || needBegin; }

        function sync(opts) {
            opts = opts || {};
            if (stopped || sessionExpired || submitting) return Promise.resolve(false);
            if (inFlight) { scheduleSync(500); return Promise.resolve(false); }

            // navigator.onLine is only a hint (it can be wrong behind VPNs / captive portals), so never
            // skip the attempt because of it: a truly offline fetch fails instantly and is retried.
            inFlight = true;
            var sentVersion = version;
            // Only ship the answers when something changed; otherwise this is just a cheap clock/heartbeat check.
            var payload = JSON.stringify({
                answers: version !== syncedVersion ? state : {},
                begin: needBegin,
                begin_elapsed_ms: needBegin && startPressedAt !== null ? Math.max(0, nowFn() - startPressedAt) : 0
            });

            var req = timedFetch(cfg.draftUrl, {
                method: 'POST',
                credentials: 'same-origin',
                keepalive: !!opts.keepalive && payload.length < 60000,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: payload
            }, SYNC_TIMEOUT_MS);

            return req.promise.then(function (res) {
                if (res.status === 409) {
                    return res.json().then(function (j) { stopped = true; if (j && j.redirect) navigate(j.redirect); return false; });
                }
                if (res.status === 401 || res.status === 419 || res.redirected) { return onSessionExpired(); }
                if (!res.ok) { throw new Error('http ' + res.status); }
                return res.json().then(function (json) {
                    failures = 0;
                    if (needBegin && json.started) needBegin = false;
                    if (json.csrf) setCsrf(json.csrf);
                    if (json.serverTime) clockOffset = Number(json.serverTime) - nowFn();

                    var merged = mergeStates(state, json.answers || {});
                    var changed = !statesEqual(merged, state);
                    state = merged;
                    if (changed) { persist(); applyStateToDom(); }

                    if (sentVersion === version) { syncedVersion = sentVersion; }
                    if (json.remainingSeconds !== null && json.remainingSeconds !== undefined) { adoptRemaining(json.remainingSeconds); }

                    var stamp = new Date(nowFn()).toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit' });
                    setStatus(isDirty() ? 'pending' : 'saved', isDirty() ? '' : '(' + stamp + ')');
                    if (isDirty()) scheduleSync(300);
                    return true;
                });
            }).catch(function () {
                failures++;
                var online2 = win.navigator ? win.navigator.onLine !== false : true;
                setStatus(online2 ? 'error' : 'offline');
                scheduleSync(backoffDelay(failures - 1));
                return false;
            }).then(function (ok) {
                req.done();
                inFlight = false;
                return ok;
            });
        }

        function onSessionExpired() {
            sessionExpired = true;
            setStatus('expired');
            if (hooks.onSessionExpired) hooks.onSessionExpired();
            return false;
        }

        function setCsrf(token) {
            csrf = token;
            var meta = doc.querySelector('meta[name="csrf-token"]');
            if (meta) meta.setAttribute('content', token);
            var field = form ? form.querySelector('input[name="_token"]') : null;
            if (field) field.value = token;
        }

        function navigate(url) {
            if (hooks.beforeNavigate) hooks.beforeNavigate();
            win.location.href = url;
        }

        // ---------- timer ----------

        function adoptRemaining(seconds) {
            // Before the student presses "start" the clock is not running yet: nothing to adjust.
            if (cfg.remainingSeconds === null || cfg.remainingSeconds === undefined || !timerHandle) return;
            var target = nowFn() + seconds * 1000;
            // follow the server, but ignore sub-3s jitter so the clock does not twitch
            if (deadline === null || Math.abs(target - deadline) > 3000) deadline = target;
        }

        function tick() {
            if (deadline === null) return;
            var left = Math.ceil((deadline - nowFn()) / 1000);
            var el = doc.getElementById('countdownTimer');
            if (el) {
                el.textContent = formatClock(left);
                if (left <= 300) {
                    el.classList.remove('text-white');
                    el.classList.add('text-danger', 'animate-pulse');
                }
            }
            if (left <= 0 && !timeUpFired) {
                timeUpFired = true;
                win.clearInterval(timerHandle);
                if (hooks.onTimeUp) hooks.onTimeUp();
            }
        }

        function startTimer() {
            if (cfg.remainingSeconds === null || cfg.remainingSeconds === undefined || timerHandle) return;
            if (deadline === null) deadline = nowFn() + cfg.remainingSeconds * 1000;
            tick();
            timerHandle = win.setInterval(tick, 500);
        }

        // ---------- submit ----------

        function submit(options) {
            options = options || {};
            if (submitting) return submitPromise;
            submitting = true;
            var progressMessage = options.message || 'جارٍ تسليم الامتحان...';
            if (syncTimer) { win.clearTimeout(syncTimer); syncTimer = null; }
            showOverlay(progressMessage, false);

            submitPromise = new Promise(function (resolve) {
                var attempt = 0;

                function buildBody() {
                    var fd = new win.FormData(form);
                    fd.set('answers_state', JSON.stringify(state));
                    fd.set('auto_submitted', options.auto ? '1' : (fd.get('auto_submitted') || '0'));
                    fd.set('_token', csrf);
                    var extra = hooks.extraSubmitFields ? hooks.extraSubmitFields() : null;
                    if (extra) Object.keys(extra).forEach(function (k) { fd.set(k, extra[k]); });
                    return fd;
                }

                function retryLater(offline) {
                    showOverlay(offline
                        ? 'لا يوجد اتصال بالإنترنت. إجاباتك محفوظة، وسيتم تسليم الامتحان تلقائياً فور عودة الاتصال — لا تغلق هذه الصفحة.'
                        : 'تعذر الوصول للخادم، جارٍ إعادة المحاولة تلقائياً — لا تغلق هذه الصفحة.', true);
                    var wait = backoffDelay(attempt++);
                    var done = false;
                    var go = function () { if (done) return; done = true; win.removeEventListener('online', go); win.clearTimeout(t); post(); };
                    var t = win.setTimeout(go, wait);
                    win.addEventListener('online', go);
                }

                function post() {
                    showOverlay(progressMessage, false);

                    // A retry after a lost response is safe: the server answers "already submitted" with the same redirect.
                    var req = timedFetch(cfg.submitUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                        body: buildBody()
                    }, SUBMIT_TIMEOUT_MS);

                    req.promise.then(function (res) {
                        req.done();
                        if (res.status === 419 || res.status === 401 || res.redirected) {
                            // Answers stay on the device and on the server draft; the student has to log in again.
                            hideOverlay();
                            submitting = false;
                            onSessionExpired();
                            resolve(false);
                            return;
                        }
                        if (res.status >= 500 || res.status === 429) { retryLater(false); return; }
                        return res.json().catch(function () { return null; }).then(function (json) {
                            if (res.ok && json && json.redirect) {
                                clearLocal();
                                stopped = true;
                                navigate(json.redirect);
                                resolve(true);
                            } else if (json && json.redirect) {
                                navigate(json.redirect);
                                resolve(false);
                            } else {
                                showOverlay((json && json.message) || 'تعذر تسليم الامتحان.', true);
                                submitting = false;
                                resolve(false);
                            }
                        });
                    }).catch(function () {
                        req.done();
                        retryLater(!(win.navigator ? win.navigator.onLine !== false : true));
                    });
                }

                post();
            });

            return submitPromise;
        }
        var submitPromise = null;

        function hideOverlay() {
            var overlay = doc.getElementById('examSubmitOverlay');
            if (overlay) overlay.classList.add('d-none');
        }

        function showOverlay(message, warn) {
            var overlay = doc.getElementById('examSubmitOverlay');
            if (!overlay) return;
            var text = doc.getElementById('examSubmitOverlayText');
            if (text) text.textContent = message;
            overlay.classList.remove('d-none');
            overlay.classList.toggle('is-warning', !!warn);
        }

        // ---------- boot ----------

        // Server draft + whatever this device saved: newest answer per question wins.
        state = mergeStates(cfg.answers || {}, loadLocal());
        Object.keys(state).forEach(function (k) { lastT = Math.max(lastT, Number(state[k].t)); });
        persist();

        var serverCopy = mergeStates({}, cfg.answers || {});
        syncedVersion = statesEqual(serverCopy, state) ? 0 : -1; // -1 => local is ahead of server: push it right away
        version = 0;

        applyStateToDom();
        bindInputs();
        setStatus(syncedVersion === 0 ? 'saved' : 'pending');

        win.addEventListener('online', function () { failures = 0; setStatus(isDirty() ? 'pending' : 'saved'); sync(); });
        win.addEventListener('offline', function () { setStatus('offline'); });
        doc.addEventListener('visibilitychange', function () {
            if (doc.hidden) { if (isDirty()) sync({ keepalive: true }); } else { sync(); }
        });
        win.addEventListener('pagehide', function () { if (isDirty()) sync({ keepalive: true }); });
        heartbeat = win.setInterval(function () { if (!submitting) sync(); }, 25000);

        if (cfg.started) {
            // Resumed attempt: the server countdown is already running.
            startTimer();
        }
        if (syncedVersion !== 0) scheduleSync(100);
        else if (cfg.started) scheduleSync(2000); // re-sync the clock shortly after load

        return {
            /** Student pressed "start": arm the server countdown and begin our clock. */
            start: function () {
                needBegin = !cfg.started;
                if (needBegin && startPressedAt === null) startPressedAt = nowFn();
                startTimer();
                sync();
            },
            submit: submit,
            isSubmitting: function () { return submitting; },
            getState: function () { return state; },
            setAnswer: setAnswer,
            sync: sync,
            applyStateToDom: applyStateToDom,
            destroy: function () { stopped = true; if (heartbeat) win.clearInterval(heartbeat); if (timerHandle) win.clearInterval(timerHandle); }
        };
    }

    return {
        init: init,
        mergeStates: mergeStates,
        statesEqual: statesEqual,
        countAnswered: countAnswered,
        backoffDelay: backoffDelay,
        formatClock: formatClock,
        hasValue: hasValue
    };
});
