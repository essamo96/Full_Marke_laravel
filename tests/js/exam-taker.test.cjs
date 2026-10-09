/*
 * Unit / scenario tests for public/student/js/exam-taker.js (the online exam engine).
 *
 *   node --test tests/js/exam-taker.test.cjs
 *
 * jsdom provides the DOM, a fake clock + scheduler and a fake server provide the
 * "flaky internet": every scenario below is a way the connection used to make
 * students lose or flip their answers.
 */
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { JSDOM } = require('jsdom');

const src = fs.readFileSync(path.join(__dirname, '../../public/student/js/exam-taker.js'), 'utf8');
const mod = { exports: {} };
new Function('module', 'exports', src)(mod, mod.exports);
const ExamTaker = mod.exports;

const HTML = `
<meta name="csrf-token" content="tok-1">
<form id="examForm" action="/submit" method="POST">
  <input type="hidden" name="_token" value="tok-1">
  <input type="hidden" name="auto_submitted" id="autoSubmittedField" value="0">
  <label class="card"><input type="radio" name="answers[1]" data-qid="1" value="11"></label>
  <label class="card"><input type="radio" name="answers[1]" data-qid="1" value="12"></label>
  <label class="card"><input type="radio" name="answers[2]" data-qid="2" value="21"></label>
  <label class="card"><input type="radio" name="answers[2]" data-qid="2" value="22"></label>
  <textarea name="answers[3]" data-qid="3"></textarea>
</form>
<i id="examSaveIcon"></i><span id="examSaveStatus"></span><span id="examProgress"></span>
<div id="examConnBanner" class="d-none"></div>
<div id="examSubmitOverlay" class="d-none"><p id="examSubmitOverlayText"></p></div>
<span id="countdownTimer" class="text-white"></span>
`;

/** Deterministic timers + clock so back-off and countdowns can be fast-forwarded. */
function makeScheduler() {
    let now = 1_700_000_000_000;
    let seq = 0;
    const timers = new Map();
    const api = {
        now: () => now,
        setTimeout: (fn, ms) => { const id = ++seq; timers.set(id, { fn, at: now + (ms || 0), every: null }); return id; },
        setInterval: (fn, ms) => { const id = ++seq; timers.set(id, { fn, at: now + ms, every: ms }); return id; },
        clearTimeout: (id) => timers.delete(id),
        clearInterval: (id) => timers.delete(id),
        async advance(ms) {
            const end = now + ms;
            for (;;) {
                const due = [...timers.entries()].filter(([, t]) => t.at <= end).sort((a, b) => a[1].at - b[1].at)[0];
                if (!due) break;
                const [id, t] = due;
                now = Math.max(now, t.at);
                if (t.every) t.at += t.every; else timers.delete(id);
                t.fn();
                await settle();
            }
            now = end;
            await settle();
        },
    };
    return api;
}

const settle = async () => { for (let i = 0; i < 8; i++) await new Promise((r) => setImmediate(r)); };

function respond(status, body, extra = {}) {
    return { status, ok: status >= 200 && status < 300, redirected: false, json: async () => body, ...extra };
}

function setup({ answers = {}, started = false, remainingSeconds = 3600, storageSeed = null, hooks = {} } = {}) {
    const dom = new JSDOM(HTML, { url: 'https://academy.test/student/exams/1/take' });
    const doc = dom.window.document;
    const sched = makeScheduler();
    const listeners = {};
    const store = new Map();
    if (storageSeed) store.set('fm_exam_1_7_5', JSON.stringify(storageSeed));

    const win = {
        navigator: { onLine: true },
        setTimeout: sched.setTimeout, clearTimeout: sched.clearTimeout,
        setInterval: sched.setInterval, clearInterval: sched.clearInterval,
        addEventListener: (type, fn) => { (listeners[type] ||= []).push(fn); },
        removeEventListener: (type, fn) => { listeners[type] = (listeners[type] || []).filter((f) => f !== fn); },
        FormData: dom.window.FormData,
        location: { href: '' },
        dispatch: (type) => (listeners[type] || []).slice().forEach((fn) => fn({})),
    };

    const server = { state: {}, remaining: remainingSeconds, started, calls: [], submits: [], mode: 'ok', submitMode: 'ok', holds: [] };
    Object.keys(answers).forEach((k) => { server.state[k] = answers[k]; });

    const fetchImpl = async (url, opts) => {
        if (url.endsWith('/draft')) {
            const body = JSON.parse(opts.body);
            server.calls.push({ url, body });
            if (server.mode === 'offline') throw new TypeError('Failed to fetch');
            if (server.mode === 'expired') return respond(419, {});
            if (server.mode === 'submitted') return respond(409, { submitted: true, redirect: '/results/9' });
            if (server.mode === 'hold') {
                return new Promise((resolve) => server.holds.push(() => {
                    server.state = ExamTaker.mergeStates(server.state, body.answers);
                    resolve(respond(200, { answers: server.state, started: true, remainingSeconds: server.remaining, csrf: 'tok-2' }));
                }));
            }
            server.state = ExamTaker.mergeStates(server.state, body.answers);
            if (body.begin) server.started = true;
            return respond(200, { answers: server.state, started: server.started, remainingSeconds: server.remaining, csrf: 'tok-2' });
        }
        // exam submit
        server.submits.push({ url, body: opts.body, headers: opts.headers });
        if (server.submitMode === 'offline') throw new TypeError('Failed to fetch');
        if (server.submitMode === 'error') { server.submitMode = 'ok'; return respond(503, {}); }
        if (server.submitMode === 'expired') return respond(419, {});
        return respond(200, { submitted: true, redirect: '/results/1' });
    };

    const cfg = {
        examId: 1, studentId: 7, attemptId: 5, draftUrl: '/exams/1/draft', submitUrl: '/exams/1/submit',
        questionIds: [1, 2, 3], answers, started, remainingSeconds, csrf: 'tok-1',
    };
    const storage = { getItem: (k) => (store.has(k) ? store.get(k) : null), setItem: (k, v) => store.set(k, String(v)), removeItem: (k) => store.delete(k) };
    const engine = ExamTaker.init(cfg, { window: win, document: doc, fetch: fetchImpl, storage, now: sched.now }, hooks);

    const pick = (qid, value) => {
        const radio = doc.querySelector(`input[data-qid="${qid}"][value="${value}"]`);
        radio.checked = true;
        radio.dispatchEvent(new dom.window.Event('change', { bubbles: true }));
    };
    const type = (qid, text) => {
        const ta = doc.querySelector(`textarea[data-qid="${qid}"]`);
        ta.value = text;
        ta.dispatchEvent(new dom.window.Event('input', { bubbles: true }));
    };
    const checked = (qid) => doc.querySelector(`input[data-qid="${qid}"]:checked`)?.value ?? null;
    const saved = () => JSON.parse(store.get('fm_exam_1_7_5') || 'null');
    const banner = () => doc.getElementById('examConnBanner');

    return { engine, doc, win, sched, server, pick, type, checked, saved, banner, store, hooks };
}

// ---------------------------------------------------------------------------

test('mergeStates: newest timestamp wins per question, incoming wins ties, garbage ignored', () => {
    const merged = ExamTaker.mergeStates(
        { 1: { v: 'A', t: 100 }, 2: { v: 'X', t: 50 } },
        { 1: { v: 'B', t: 90 }, 2: { v: 'Y', t: 50 }, 3: { v: 'Z', t: 5 }, 4: 'nope', 5: { v: 1 } },
    );
    assert.deepEqual(merged, { 1: { v: 'A', t: 100 }, 2: { v: 'Y', t: 50 }, 3: { v: 'Z', t: 5 } });
});

test('mergeStates does not mutate its inputs and handles empty / null', () => {
    const a = { 1: { v: 'A', t: 1 } };
    const out = ExamTaker.mergeStates(a, null);
    assert.deepEqual(out, a);
    assert.notEqual(out, a);
    assert.deepEqual(ExamTaker.mergeStates(null, undefined), {});
});

test('backoffDelay doubles, caps at 30s and adds bounded jitter', () => {
    assert.equal(ExamTaker.backoffDelay(0, 0), 2000);
    assert.equal(ExamTaker.backoffDelay(1, 0), 4000);
    assert.equal(ExamTaker.backoffDelay(2, 0), 8000);
    assert.equal(ExamTaker.backoffDelay(20, 0), 30000);
    assert.ok(ExamTaker.backoffDelay(3, 1) <= 16000 * 1.25 + 1);
});

test('formatClock / countAnswered / statesEqual', () => {
    assert.equal(ExamTaker.formatClock(3725), '01:02:05');
    assert.equal(ExamTaker.formatClock(-4), '00:00:00');
    assert.equal(ExamTaker.countAnswered({ 1: { v: '11', t: 1 }, 2: { v: '', t: 1 }, 3: { v: null, t: 1 } }, [1, 2, 3, 4]), 1);
    assert.ok(ExamTaker.statesEqual({ 1: { v: 1, t: 2 } }, { 1: { v: 1, t: 2 } }));
    assert.ok(!ExamTaker.statesEqual({ 1: { v: 1, t: 2 } }, { 1: { v: 1, t: 3 } }));
});

test('an answer is saved locally at once and pushed to the server shortly after', async () => {
    const t = setup();
    t.engine.start();
    await t.sched.advance(10);
    t.pick(1, 11);

    assert.equal(t.saved().answers[1].v, '11', 'persisted to localStorage immediately');
    assert.equal(t.server.state[1], undefined, 'not on the server yet (debounced)');

    await t.sched.advance(400);
    assert.equal(t.server.state[1].v, '11');
    assert.equal(t.doc.getElementById('examProgress').textContent, '1 / 3');
    assert.ok(t.banner().classList.contains('d-none'));
});

test('internet drops while answering: nothing is lost and the final choice is sent once it is back', async () => {
    const t = setup();
    t.engine.start();
    await t.sched.advance(10);
    t.pick(1, 11);
    await t.sched.advance(400);

    // connection lost
    t.server.mode = 'offline';
    t.win.navigator.onLine = false;
    t.win.dispatch('offline');
    t.pick(1, 12);              // student changes their mind while offline
    t.type(3, 'essay while offline');
    await t.sched.advance(60_000);

    assert.ok(!t.banner().classList.contains('d-none'), 'student sees that they are offline');
    assert.match(t.banner().textContent, /انقطع الاتصال/);
    assert.equal(t.checked(1), '12', 'the screen still shows what they chose');
    assert.equal(t.saved().answers[1].v, '12', 'and so does the device copy');
    assert.equal(t.server.state[1].v, '11', 'server still has the older choice');

    // connection back
    t.server.mode = 'ok';
    t.win.navigator.onLine = true;
    t.win.dispatch('online');
    await t.sched.advance(50);

    assert.equal(t.server.state[1].v, '12', 'final choice reached the server');
    assert.equal(t.server.state[3].v, 'essay while offline');
    assert.equal(t.checked(1), '12', 'and the screen did not change under the student');
    assert.ok(t.banner().classList.contains('d-none'));
});

test('flaky server (errors, not offline): retries with back-off until the answer is saved', async () => {
    const t = setup();
    t.engine.start();
    await t.sched.advance(10);
    t.server.mode = 'offline'; // fetch rejects but the browser still claims to be online
    t.pick(2, 22);
    await t.sched.advance(20_000);
    assert.ok(t.server.calls.length >= 3, 'kept retrying');
    assert.equal(t.server.state[2], undefined);

    t.server.mode = 'ok';
    await t.sched.advance(40_000);
    assert.equal(t.server.state[2].v, '22');
});

test('a delayed response carrying an older choice can never flip the answer on screen', async () => {
    const t = setup();
    t.engine.start();
    await t.sched.advance(10);

    t.server.mode = 'hold';          // the request for A hangs on a bad connection
    t.pick(1, 11);
    await t.sched.advance(400);
    assert.equal(t.server.holds.length, 1);

    t.pick(1, 12);                   // meanwhile the student switches to B
    t.server.holds.shift()();        // the old request finally completes (server only knew A)
    await t.sched.advance(10);

    assert.equal(t.checked(1), '12', 'still B on screen');
    assert.equal(t.saved().answers[1].v, '12');

    t.server.mode = 'ok';
    await t.sched.advance(2000);
    assert.equal(t.server.state[1].v, '12', 'B is what ends up saved');
});

test('after a reload the newest copy wins: device draft vs server draft', async () => {
    const serverOlder = setup({
        answers: { 1: { v: '11', t: 100 }, 2: { v: '21', t: 500 } },
        storageSeed: { answers: { 1: { v: '12', t: 900 }, 3: { v: 'typed', t: 800 } }, savedAt: 1 },
    });
    await serverOlder.sched.advance(10);
    assert.equal(serverOlder.checked(1), '12', 'device copy is newer for Q1');
    assert.equal(serverOlder.checked(2), '21', 'server copy is the only one for Q2');
    assert.equal(serverOlder.doc.querySelector('textarea').value, 'typed');
    await serverOlder.sched.advance(500);
    assert.equal(serverOlder.server.state[1].v, '12', 'the newer device copy is pushed up straight away');

    const serverNewer = setup({
        answers: { 1: { v: '11', t: 2000 } },
        storageSeed: { answers: { 1: { v: '12', t: 900 } }, savedAt: 1 },
    });
    await serverNewer.sched.advance(10);
    assert.equal(serverNewer.checked(1), '11', 'server copy is newer');
});

test('countdown follows the server clock and fires time-up exactly once', async () => {
    let timeUps = 0;
    const t = setup({ remainingSeconds: 60, hooks: { onTimeUp: () => { timeUps++; } } });
    const clock = () => t.doc.getElementById('countdownTimer').textContent;

    t.engine.start();
    await t.sched.advance(1000);
    assert.equal(clock(), '00:00:59');

    // the server says there are only 10 seconds left (e.g. the student was offline a while)
    t.server.remaining = 10;
    t.pick(1, 11);
    await t.sched.advance(1100);
    assert.ok(clock() <= '00:00:10', `jumped from ~58s to the server value (got ${clock()})`);

    await t.sched.advance(11_000);
    assert.equal(timeUps, 1);
    assert.equal(clock(), '00:00:00');
});

test('a resumed attempt shows the already-running countdown without pressing start', async () => {
    const t = setup({ started: true, remainingSeconds: 1500 });
    await t.sched.advance(10);
    assert.equal(t.doc.getElementById('countdownTimer').textContent, '00:25:00');
});

test('begin is sent with the first sync so the server can start the clock', async () => {
    const t = setup();
    t.engine.start();
    await t.sched.advance(10);
    assert.equal(t.server.calls[0].body.begin, true);
    assert.equal(t.server.started, true);
    await t.sched.advance(30_000);
    assert.ok(t.server.calls.slice(1).every((c) => c.body.begin === false), 'begin is not repeated once acknowledged');
});

test('heartbeat does not resend answers when nothing changed', async () => {
    const t = setup();
    t.engine.start();
    await t.sched.advance(10);
    t.pick(1, 11);
    await t.sched.advance(400);
    const before = t.server.calls.length;
    await t.sched.advance(26_000);
    const heartbeat = t.server.calls[before];
    assert.ok(heartbeat, 'a heartbeat was sent');
    assert.deepEqual(heartbeat.body.answers, {});
});

test('submitting offline waits for the connection and then hands the exam in once, with the final answers', async () => {
    const t = setup();
    t.engine.start();
    await t.sched.advance(10);
    t.pick(1, 11);
    t.pick(1, 12);
    t.type(3, 'final essay');

    t.win.navigator.onLine = false;
    t.server.submitMode = 'offline';
    const done = t.engine.submit({});
    await t.sched.advance(100);

    assert.equal(t.server.submits.length, 0, 'nothing is posted while offline');
    const overlay = t.doc.getElementById('examSubmitOverlay');
    assert.ok(!overlay.classList.contains('d-none'));
    assert.match(t.doc.getElementById('examSubmitOverlayText').textContent, /تلقائياً فور عودة الاتصال/);

    t.win.navigator.onLine = true;
    t.server.submitMode = 'ok';
    t.win.dispatch('online');
    await t.sched.advance(50);
    assert.equal(await done, true);

    assert.equal(t.server.submits.length, 1, 'submitted exactly once');
    const body = t.server.submits[0].body;
    const state = JSON.parse(body.get('answers_state'));
    assert.equal(state[1].v, '12');
    assert.equal(state[3].v, 'final essay');
    assert.equal(body.get('answers[1]'), '12');
    assert.equal(body.get('_token'), 'tok-2', 'the token refreshed by the last autosave is used');
    assert.equal(t.win.location.href, '/results/1');
    assert.equal(t.saved(), null, 'device copy is wiped after a successful hand-in');
});

test('submit retries after a server error and a second call while submitting is ignored', async () => {
    const t = setup();
    t.engine.start();
    await t.sched.advance(10);
    t.pick(2, 21);
    t.server.submitMode = 'error';

    const first = t.engine.submit({});
    const again = t.engine.submit({});
    assert.equal(first, again, 'double click / timer + violation firing together share one submission');
    await t.sched.advance(100);
    assert.equal(t.server.submits.length, 1);

    await t.sched.advance(3000);
    assert.equal(await first, true);
    assert.equal(t.server.submits.length, 2);
    assert.equal(t.win.location.href, '/results/1');
});

test('expired session on autosave stops retrying, keeps answers on the device and tells the student', async () => {
    let expired = 0;
    const t = setup({ hooks: { onSessionExpired: () => { expired++; } } });
    t.engine.start();
    await t.sched.advance(10);
    t.server.mode = 'expired';
    t.pick(1, 11);
    await t.sched.advance(500);

    const calls = t.server.calls.length;
    await t.sched.advance(120_000);
    assert.equal(t.server.calls.length, calls, 'no hammering of the server');
    assert.equal(expired, 1);
    assert.equal(t.saved().answers[1].v, '11');
    assert.match(t.banner().textContent, /انتهت صلاحية جلستك/);
});

test('autosave learning that the exam was already submitted leaves the page', async () => {
    const t = setup();
    t.engine.start();
    await t.sched.advance(10);
    t.server.mode = 'submitted';
    t.pick(1, 11);
    await t.sched.advance(500);
    assert.equal(t.win.location.href, '/results/9');
});

test('a fresh csrf token from the server replaces the stale one used for submitting', async () => {
    const t = setup();
    t.engine.start();
    await t.sched.advance(10);
    t.pick(1, 11);
    await t.sched.advance(500);
    assert.equal(t.doc.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'tok-2');

    const done = t.engine.submit({});
    await t.sched.advance(50);
    await done;
    assert.equal(t.server.submits[0].body.get('_token'), 'tok-2');
    assert.equal(t.server.submits[0].headers['X-CSRF-TOKEN'], 'tok-2');
});

test('selected option cards are highlighted from the saved state', async () => {
    const t = setup({ answers: { 2: { v: '22', t: 10 } } });
    await t.sched.advance(10);
    const labels = [...t.doc.querySelectorAll('label.card')];
    assert.deepEqual(labels.map((l) => l.classList.contains('is-selected')), [false, false, false, true]);
});

test('session expiring during the final submit releases the screen and asks the student to log in again', async () => {
    let expired = 0;
    const t = setup({ hooks: { onSessionExpired: () => { expired++; } } });
    t.engine.start();
    await t.sched.advance(10);
    t.pick(1, 11);
    t.server.submitMode = 'expired';
    const done = t.engine.submit({});
    await t.sched.advance(50);
    assert.equal(await done, false);
    assert.equal(expired, 1);
    assert.ok(t.doc.getElementById('examSubmitOverlay').classList.contains('d-none'), 'overlay is gone so the login prompt is reachable');
    assert.equal(t.saved().answers[1].v, '11');
});
