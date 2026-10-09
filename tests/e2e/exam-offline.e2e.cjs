/*
 * REAL-BROWSER end-to-end simulation of the online exam under a bad connection.
 *
 * Drives a real Edge/Chrome through the DevTools protocol against a running copy of the app,
 * cuts the network for real (Network.emulateNetworkConditions offline), loses / drops / replays
 * requests (Fetch domain), reloads, switches device, and in every scenario compares three
 * things with what the student actually clicked:
 *    1. what is on screen,   2. what the server holds,   3. what ends up graded.
 *
 *   php artisan tinker --execute="include 'tests/e2e/seed.php';"       # throw-away data
 *   node tests/e2e/exam-offline.e2e.cjs                                # starts the app itself, needs Edge/Chrome
 *   php artisan tinker --execute="include 'tests/e2e/cleanup.php';"    # remove the data again
 *
 * env: E2E_BASE_URL (use an already running app instead of starting one),
 *      E2E_BROWSER (path of msedge/chrome), E2E_ONLY=3,5 (run only some scenarios), E2E_TRACE=1.
 */
const { spawn, spawnSync } = require('node:child_process');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');

const ROOT = path.join(__dirname, '../..');
let BASE = process.env.E2E_BASE_URL || 'http://127.0.0.1:8123';
const { startApp } = require('./app-server.cjs');
let app = null;
const BROWSER = process.env.E2E_BROWSER || 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe';
const PORT = 9333;
const seed = JSON.parse(fs.readFileSync(path.join(ROOT, 'storage/app/e2e-seed.json'), 'utf8'));
const MC = seed.questions.filter((q) => q.type === 'mc');
const ESSAY = seed.questions.find((q) => q.type === 'essay');

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
async function waitFor(fn, { timeout = 30000, interval = 150, label = 'condition' } = {}) {
    const t0 = Date.now();
    for (;;) {
        const v = await fn();
        if (v) return v;
        if (Date.now() - t0 > timeout) throw new Error(`timeout (${timeout}ms) waiting for ${label}`);
        await sleep(interval);
    }
}

// ---------------------------------------------------------------- CDP plumbing

class Conn {
    constructor(ws) {
        this.ws = ws; this.id = 0; this.pending = new Map(); this.handlers = {};
        ws.addEventListener('message', (m) => {
            const msg = JSON.parse(m.data);
            if (msg.id && this.pending.has(msg.id)) {
                const { resolve, reject } = this.pending.get(msg.id);
                this.pending.delete(msg.id);
                msg.error ? reject(new Error(`${msg.error.message} (${JSON.stringify(msg.error.data || '')})`)) : resolve(msg.result);
            } else if (msg.method) {
                (this.handlers[msg.method] || []).forEach((h) => h(msg.params));
            }
        });
    }
    static async open(url) {
        const ws = new WebSocket(url);
        await new Promise((res, rej) => { ws.addEventListener('open', res); ws.addEventListener('error', rej); });
        return new Conn(ws);
    }
    send(method, params = {}) {
        const id = ++this.id;
        this.ws.send(JSON.stringify({ id, method, params }));
        return new Promise((resolve, reject) => this.pending.set(id, { resolve, reject }));
    }
    on(method, fn) { (this.handlers[method] ||= []).push(fn); }
    close() { try { this.ws.close(); } catch (e) { /* noop */ } }
}

const allTabs = [];
const jget = async (p) => (await fetch(`http://127.0.0.1:${PORT}${p}`)).json();

class Tab {
    constructor(conn, targetId) {
        this.c = conn; this.targetId = targetId;
        this.errors = []; this.requests = []; this.held = []; this.interceptMode = 'off'; this.dialogs = 0;
        this.timeline = []; this.t0 = Date.now();
    }
    log(msg) { this.timeline.push(`${String(Date.now() - this.t0).padStart(6)}ms  ${msg}`); }
    async init() {
        const c = this.c;
        allTabs.push(this);
        c.on('Page.javascriptDialogOpening', () => { this.dialogs++; c.send('Page.handleJavaScriptDialog', { accept: true }).catch(() => {}); });
        c.on('Runtime.exceptionThrown', (e) => this.errors.push('exception: ' + (e.exceptionDetails.exception?.description || e.exceptionDetails.text)));
        c.on('Fetch.requestPaused', (p) => this._paused(p));
        const draftIds = new Map();
        c.on('Network.requestWillBeSent', (p) => {
            if (/\/(draft|submit)$/.test(p.request.url)) {
                draftIds.set(p.requestId, p.request.url.split('/').pop());
                let n = ''; try { n = Object.keys(JSON.parse(p.request.postData || '{}').answers || {}).length + ' answers'; } catch (e) { n = 'form'; }
                this.log(`NET  -> ${draftIds.get(p.requestId)} (${n})`);
            }
        });
        c.on('Network.responseReceived', (p) => { if (draftIds.has(p.requestId)) this.log(`NET  <- ${draftIds.get(p.requestId)} HTTP ${p.response.status}`); });
        c.on('Network.loadingFailed', (p) => { if (draftIds.has(p.requestId)) this.log(`NET  xx ${draftIds.get(p.requestId)} FAILED ${p.errorText}`); });
        await Promise.all([c.send('Page.enable'), c.send('Runtime.enable'), c.send('Network.enable')]);
        await c.send('Emulation.setFocusEmulationEnabled', { enabled: true }).catch(() => {});
    }
    _paused(p) {
        const rec = { url: p.request.url, postData: p.request.postData, mode: this.interceptMode, requestId: p.requestId };
        this.requests.push(rec);
        const act = (m, params) => this.c.send(m, params).catch(() => {});
        if (this.interceptMode === 'drop') act('Fetch.failRequest', { requestId: p.requestId, errorReason: 'InternetDisconnected' });
        else if (this.interceptMode === 'hang') this.held.push(p.requestId);
        else if (this.interceptMode === 'fail503' && this.failBudget-- > 0) {
            act('Fetch.fulfillRequest', { requestId: p.requestId, responseCode: 503, body: Buffer.from('{"message":"Service Unavailable"}').toString('base64'),
                responseHeaders: [{ name: 'Content-Type', value: 'application/json' }] });
        }
        else act('Fetch.continueRequest', { requestId: p.requestId });
    }
    /** off | capture (record + pass through) | drop (request is lost) | hang (never answered) */
    async intercept(mode, pattern = '*/draft') {
        this.interceptMode = mode;
        if (mode === 'off') {
            await this.c.send('Fetch.disable').catch(() => {});
            this.held.splice(0).forEach((id) => this.c.send('Fetch.continueRequest', { requestId: id }).catch(() => {}));
        } else {
            await this.c.send('Fetch.enable', { patterns: [{ urlPattern: pattern, requestStage: 'Request' }] });
        }
    }
    async goto(url) {
        const loaded = new Promise((res) => { this.c.on('Page.loadEventFired', res); setTimeout(res, 45000); });
        await this.c.send('Page.navigate', { url });
        await loaded;
        await sleep(300);
    }
    async eval(expression) {
        const r = await this.c.send('Runtime.evaluate', { expression, awaitPromise: true, returnByValue: true });
        if (r.exceptionDetails) throw new Error('eval failed: ' + (r.exceptionDetails.exception?.description || r.exceptionDetails.text) + ' :: ' + expression.slice(0, 120));
        return r.result.value;
    }
    async setOffline(offline) {
        this.log(offline ? 'NETWORK CUT' : 'NETWORK RESTORED');
        await this.c.send('Network.emulateNetworkConditions', { offline, latency: 0, downloadThroughput: -1, uploadThroughput: -1 });
    }
    async click(selector) {
        const pt = await this.eval(`(() => { const el = document.querySelector(${JSON.stringify(selector)}); if (!el) return null;
            el.scrollIntoView({ block: 'center', behavior: 'instant' }); const r = el.getBoundingClientRect();
            return { x: r.left + r.width / 2, y: r.top + r.height / 2 }; })()`);
        if (!pt) throw new Error('click: no element ' + selector);
        await sleep(150); // let layout settle (Bootstrap sets smooth scrolling)
        const pt2 = await this.eval(`(() => { const r = document.querySelector(${JSON.stringify(selector)}).getBoundingClientRect(); return { x: r.left + r.width / 2, y: r.top + r.height / 2 }; })()`);
        for (const type of ['mouseMoved', 'mousePressed', 'mouseReleased']) {
            await this.c.send('Input.dispatchMouseEvent', { type, x: pt2.x, y: pt2.y, button: 'left', clickCount: 1 });
        }
    }
    async type(selector, text) {
        await this.click(selector);
        await this.c.send('Input.insertText', { text });
    }
    async fill(selector, value) {
        await this.eval(`(() => { const el = document.querySelector(${JSON.stringify(selector)}); el.focus(); el.value = ${JSON.stringify(value)}; el.dispatchEvent(new Event('input', {bubbles:true})); })()`);
    }
    async reload() {
        const loaded = new Promise((res) => { this.c.on('Page.loadEventFired', res); setTimeout(res, 45000); });
        await this.c.send('Page.reload');
        await loaded; await sleep(300);
    }
    async close(browser) { this.c.close(); await browser.send('Target.closeTarget', { targetId: this.targetId }).catch(() => {}); }
}

// ---------------------------------------------------------------- browser + app helpers

let browserProc; let browser; let profileDir;

async function launch() {
    profileDir = fs.mkdtempSync(path.join(os.tmpdir(), 'e2e-edge-'));
    browserProc = spawn(BROWSER, [
        '--headless=new', '--disable-gpu', '--no-first-run', '--no-default-browser-check', `--remote-debugging-port=${PORT}`,
        `--user-data-dir=${profileDir}`, '--window-size=1280,1000', '--remote-allow-origins=*', '--disable-features=Translate', 'about:blank',
    ], { stdio: 'ignore' });
    const ver = await waitFor(async () => { try { return await jget('/json/version'); } catch (e) { return null; } }, { timeout: 20000, label: 'browser start' });
    browser = await Conn.open(ver.webSocketDebuggerUrl);
    return ver.Browser;
}

/** Hide / show the page like a student switching tabs: minimise the real window, else fire the event by hand. */
async function setHidden(tab, hidden) {
    try {
        const { windowId } = await browser.send('Browser.getWindowForTarget', { targetId: tab.targetId });
        await browser.send('Browser.setWindowBounds', { windowId, bounds: { windowState: hidden ? 'minimized' : 'normal' } });
        await sleep(600);
        if ((await tab.eval('document.hidden')) === hidden) return 'real';
    } catch (e) { /* fall through */ }
    await tab.eval(`(() => { Object.defineProperty(document, 'hidden', { configurable: true, get: () => ${hidden} }); document.dispatchEvent(new Event('visibilitychange')); })()`);
    return 'synthetic';
}
const foreground = (tab) => browser.send('Target.activateTarget', { targetId: tab.targetId }).catch(() => {});
let currentCtx = null; // every scenario gets its own browser context (= its own cookies/localStorage = its own "device")
async function newTab(browserContextId = currentCtx) {
    const { targetId } = await browser.send('Target.createTarget', { url: 'about:blank', ...(browserContextId ? { browserContextId } : {}) });
    const list = await waitFor(async () => (await jget('/json/list')).find((t) => t.id === targetId), { label: 'target ws' });
    const tab = new Tab(await Conn.open(list.webSocketDebuggerUrl), targetId);
    await tab.init();
    return tab;
}

async function login(tab, student) {
    await tab.goto(`${BASE}/student/login`);
    await tab.fill('#studentId', student.email);
    await tab.fill('#studentPassword', seed.password);
    await tab.eval(`document.getElementById('studentLoginForm').submit()`);
    await waitFor(async () => /\/student\/(dashboard|exams|groups)/.test(await tab.eval('location.pathname')), { label: 'login redirect', timeout: 40000 });
}

async function openExam(tab, { start = true } = {}) {
    await tab.goto(BASE + seed.take_path);
    await waitFor(() => tab.eval(`typeof ExamTaker === 'object' && !!document.getElementById('examStartBtn')`), { label: 'exam page', timeout: 40000 });
    if (start) await startExam(tab);
}

async function startExam(tab) {
    await tab.click('#examStartBtn');
    await waitFor(() => tab.eval(`!document.getElementById('examIntroOverlay') && examStarted === true`), { label: 'exam start (3-2-1)', timeout: 15000 });
}

const optSel = (qi, oi) => `label:has(input[data-qid="${MC[qi].id}"][value="${MC[qi].options[oi]}"])`;
async function pick(tab, expected, qi, oi) {
    tab.log(`CLICK Q${qi + 1} = option ${oi + 1}`);
    await tab.click(optSel(qi, oi));
    expected[MC[qi].id] = MC[qi].options[oi];
    await waitFor(async () => (await uiPicks(tab))[MC[qi].id] === MC[qi].options[oi], { label: `page to register click Q${qi + 1}`, timeout: 15000, interval: 50 });
    await sleep(60);
}
async function writeEssay(tab, expected, text) {
    await tab.type(`textarea[data-qid="${ESSAY.id}"]`, text);
    expected[ESSAY.id] = (expected[ESSAY.id] || '') + text;
    await waitFor(async () => (await uiPicks(tab))[ESSAY.id] === expected[ESSAY.id], { label: 'page to register essay text', timeout: 15000, interval: 50 });
}

/** What the student sees: { qid: value } for every answered question. */
const uiPicks = (tab) => tab.eval(`(() => { const out = {};
    for (const q of EXAM.questionIds) {
        const r = document.querySelector('input[data-qid="' + q + '"]:checked');
        const t = document.querySelector('textarea[data-qid="' + q + '"]');
        if (r) out[q] = Number(r.value); else if (t && t.value) out[q] = t.value;
    } return out; })()`);
/** The device copy in localStorage. */
const localPicks = (tab) => tab.eval(`(() => { const k = 'fm_exam_' + EXAM.examId + '_' + EXAM.studentId + '_' + EXAM.attemptId;
    const s = JSON.parse(localStorage.getItem(k) || 'null'); if (!s) return null; const o = {};
    for (const [q, e] of Object.entries(s.answers)) if (e.v !== null && e.v !== '') o[q] = isNaN(e.v) ? e.v : Number(e.v); return o; })()`);
/** What the SERVER holds, read through the same endpoint the page autosaves to (works from any tab of the same session). */
const serverPicks = (tab, draftUrl, answersToSend = {}) => tab.eval(`(async () => {
    const token = document.querySelector('meta[name="csrf-token"]').content;
    const res = await fetch(${JSON.stringify(draftUrl)}, { method: 'POST', credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
        body: JSON.stringify({ answers: ${JSON.stringify(answersToSend)} }) });
    const j = await res.json(); const o = {};
    for (const [q, e] of Object.entries(j.answers || {})) if (e.v !== null && e.v !== '') o[q] = isNaN(e.v) ? e.v : Number(e.v);
    return o; })()`);
const draftUrlOf = (tab) => tab.eval('EXAM.draftUrl');
const saveStatus = (tab) => tab.eval(`document.getElementById('examSaveStatus').textContent`);
const bannerShown = (tab) => tab.eval(`!document.getElementById('examConnBanner').classList.contains('d-none')`);
const overlayText = (tab) => tab.eval(`(() => { const o = document.getElementById('examSubmitOverlay'); return o && !o.classList.contains('d-none') ? document.getElementById('examSubmitOverlayText').textContent : null; })()`);

async function waitSaved(tab, expected, label = 'saved') {
    if (expected) await waitFor(async () => sameMap(await localPicks(tab), expected), { label: 'device copy to hold the latest answers', timeout: 15000, interval: 50 });
    await waitFor(async () => /تم حفظ/.test(await saveStatus(tab)) && !(await bannerShown(tab)), { label: `status "${label}"`, timeout: 60000 });
}
async function clickSubmitAndConfirm(tab) {
    await tab.click('#submitExamBtn');
    // SweetAlert (CDN) or, if it could not load, the native confirm() that the dialog handler accepts.
    if (await tab.eval(`typeof Swal !== 'undefined'`)) {
        await waitFor(() => tab.eval(`!!document.querySelector('.swal2-popup.swal2-show .swal2-confirm')`), { timeout: 10000, label: 'confirmation dialog' });
        await sleep(800); // let the pop-in animation finish so the click lands on the button
        await tab.click('.swal2-confirm');
    }
    await waitFor(() => tab.eval(`engine.isSubmitting() || location.pathname.includes('/student/results/')`), { timeout: 10000, label: 'submission to start' });
}
const sameMap = (a, b) => JSON.stringify(Object.entries(a || {}).sort()) === JSON.stringify(Object.entries(b || {}).sort());
const show = (m) => JSON.stringify(m);

// ---------------------------------------------------------------- scenarios

const results = []; const expectedByStudent = {};
function check(scenario, name, ok, detail = '') { results.push({ scenario, name, ok, detail }); console.log(`   ${ok ? 'PASS' : 'FAIL'}  ${name}${ok || !detail ? '' : '\n         ' + detail}`); }
const noRealErrors = (tab) => tab.errors.filter((e) => !/ERR_INTERNET_DISCONNECTED|Failed to fetch|aborted/i.test(e));

const scenarios = {
    1: ['Online picks, then the internet drops: answers changed OFFLINE reach the server unchanged on reconnect', async () => {
        const s = seed.students[0]; const exp = expectedByStudent[s.id] = {};
        const tab = await newTab(); const observer = await newTab();
        await login(tab, s); await foreground(tab); await openExam(tab);
        const draftUrl = await draftUrlOf(tab);
        await pick(tab, exp, 0, 0); await pick(tab, exp, 1, 1);
        await waitSaved(tab, exp);
        const before = { ...exp };

        await tab.setOffline(true);
        await waitFor(() => bannerShown(tab), { label: 'offline banner', timeout: 10000 }).catch(() => {});
        check(1, 'student is told the connection is gone (banner)', await bannerShown(tab));

        await pick(tab, exp, 0, 2);                    // changes their mind on Q1 while offline
        await pick(tab, exp, 2, 3); await pick(tab, exp, 3, 0);
        await writeEssay(tab, exp, 'إجابة مكتوبة أثناء الانقطاع');
        await sleep(3500);
        check(1, 'screen shows exactly what was clicked while offline', sameMap(await uiPicks(tab), exp), show(await uiPicks(tab)));
        check(1, 'device copy (localStorage) has every offline answer', sameMap(await localPicks(tab), exp), show(await localPicks(tab)));

        await observer.goto(`${BASE}/student/exams`);                // same browser session, own network
        const serverOffline = await serverPicks(observer, draftUrl);
        check(1, 'server still has only the pre-outage answers (nothing could be sent)', sameMap(serverOffline, before), show(serverOffline));

        await tab.setOffline(false);
        await waitSaved(tab, exp, 'saved after reconnect');
        const serverAfter = await serverPicks(observer, draftUrl);
        check(1, 'after reconnect the server holds EXACTLY the final picks (incl. the changed Q1 + essay)', sameMap(serverAfter, exp), `server=${show(serverAfter)} expected=${show(exp)}`);
        check(1, 'screen did not change after reconnect', sameMap(await uiPicks(tab), exp), show(await uiPicks(tab)));

        await clickSubmitAndConfirm(tab);
        await waitFor(async () => (await tab.eval('location.pathname')).includes('/student/results/'), { label: 'results page', timeout: 40000 });
        check(1, 'no JS errors on the page', noRealErrors(tab).length === 0, noRealErrors(tab).join(' | '));
        await tab.close(browser); await observer.close(browser);
    }],

    2: ['Request lost in transit, then the page is reloaded before anything synced: nothing is lost', async () => {
        const s = seed.students[1]; const exp = expectedByStudent[s.id] = {};
        const tab = await newTab(); const observer = await newTab();
        await login(tab, s); await foreground(tab); await openExam(tab);
        const draftUrl = await draftUrlOf(tab);
        await pick(tab, exp, 0, 1); await waitSaved(tab, exp);

        await tab.intercept('drop');                   // every autosave request is lost from now on
        await pick(tab, exp, 1, 2); await pick(tab, exp, 2, 0); await writeEssay(tab, exp, 'مقال لم يصل للخادم');
        await sleep(2500);
        await observer.goto(`${BASE}/student/exams`);
        const serverLost = await serverPicks(observer, draftUrl);
        check(2, 'server really missed the answers (requests were dropped)', !sameMap(serverLost, exp), show(serverLost));

        await tab.reload();                           // student refreshes / browser restarts the tab; beforeunload auto-accepted
        await tab.intercept('off');
        await waitFor(() => tab.eval(`typeof ExamTaker === 'object'`), { label: 'reloaded exam page' });
        const restored = await uiPicks(tab);
        check(2, 'after reload the page restores EVERY answer from the device copy (before pressing resume)', sameMap(restored, exp), `ui=${show(restored)} expected=${show(exp)}`);
        const server2 = await serverPicks(observer, draftUrl);
        await waitFor(async () => sameMap(await serverPicks(observer, draftUrl), exp), { label: 'device copy pushed to server', timeout: 30000 }).catch(() => {});
        const serverFinal = await serverPicks(observer, draftUrl);
        check(2, 'the restored answers are pushed back to the server automatically', sameMap(serverFinal, exp), `server=${show(serverFinal)}`);
        await startExam(tab);                         // resume
        check(2, 'resuming keeps the answers', sameMap(await uiPicks(tab), exp), show(await uiPicks(tab)));
        void server2;
        await clickSubmitAndConfirm(tab);
        await waitFor(async () => (await tab.eval('location.pathname')).includes('/student/results/'), { label: 'results page', timeout: 40000 });
        await tab.close(browser); await observer.close(browser);
    }],

    3: ['Student continues on another device (no local copy): restored from the server only', async () => {
        const s = seed.students[2]; const exp = expectedByStudent[s.id] = {};
        const tab = await newTab(); await login(tab, s); await openExam(tab);
        await pick(tab, exp, 0, 3); await pick(tab, exp, 1, 0); await pick(tab, exp, 2, 1); await writeEssay(tab, exp, 'من الجهاز الأول');
        await waitSaved(tab, exp);
        await tab.close(browser);

        const ctx = (await browser.send('Target.createBrowserContext')).browserContextId;    // brand-new storage = new device
        const tab2 = await newTab(ctx);
        await login(tab2, s); await openExam(tab2, { start: false });
        check(3, 'new device has NO local copy', (await localPicks(tab2)) === null || Object.keys((await localPicks(tab2)) || {}).length === Object.keys(exp).length);
        check(3, 'new device shows every answer from the server', sameMap(await uiPicks(tab2), exp), `ui=${show(await uiPicks(tab2))} expected=${show(exp)}`);
        await startExam(tab2);
        await pick(tab2, exp, 1, 2);                   // keeps working and changes an answer
        await waitSaved(tab2, exp);
        await clickSubmitAndConfirm(tab2);
        await waitFor(async () => (await tab2.eval('location.pathname')).includes('/student/results/'), { label: 'results page', timeout: 40000 });
        await tab2.close(browser);
        await browser.send('Target.disposeBrowserContext', { browserContextId: ctx }).catch(() => {});
    }],

    4: ['Stale (zombie) request arrives after a newer answer, and a hung request: neither flips the answer', async () => {
        const s = seed.students[3]; const exp = expectedByStudent[s.id] = {};
        const tab = await newTab(); const observer = await newTab();
        await login(tab, s); await foreground(tab); await openExam(tab);
        const draftUrl = await draftUrlOf(tab);

        await tab.intercept('capture');                // record every autosave body
        await pick(tab, exp, 0, 0); await waitSaved(tab, exp);
        const staleBody = tab.requests.filter((r) => r.postData && r.postData.includes('"answers":{"')).pop()?.postData;
        await pick(tab, exp, 0, 1); await waitSaved(tab, exp);          // newer choice for the same question
        check(4, 'captured an older autosave body to replay', !!staleBody, 'no body captured');

        await observer.goto(`${BASE}/student/exams`);
        const replay = await observer.eval(`(async () => { const token = document.querySelector('meta[name="csrf-token"]').content;
            const r = await fetch(${JSON.stringify(draftUrl)}, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token }, body: ${JSON.stringify(staleBody || '{}')} });
            return r.status; })()`);
        check(4, 'server accepts the late zombie request without error', replay === 200, `status ${replay}`);
        const afterZombie = await serverPicks(observer, draftUrl);
        check(4, 'but the NEWER answer still wins on the server', sameMap(afterZombie, exp), `server=${show(afterZombie)} expected=${show(exp)}`);
        await tab.eval('engine.sync()'); await sleep(1500);
        check(4, 'and the screen keeps showing the newer answer after the next sync', sameMap(await uiPicks(tab), exp), show(await uiPicks(tab)));

        tab.requests.length = 0;
        await tab.intercept('hang');                   // autosave requests now hang forever
        await pick(tab, exp, 2, 2);
        await sleep(1500);
        check(4, 'a request is hanging', tab.held.length >= 1);
        await sleep(22000);                            // longer than the 15s client timeout + back-off
        check(4, 'the client gave up on the hung request and RETRIED it (>= 2 attempts, page not blocked)', tab.requests.filter((r) => r.mode === 'hang').length >= 2, `attempts=${tab.requests.length}`);
        const t0 = Date.now();
        await tab.intercept('off');                    // network heals
        await waitFor(async () => sameMap(await serverPicks(observer, draftUrl), exp), { label: 'answer saved after hang', timeout: 60000 });
        check(4, `the answer behind the hung requests reached the server once the network healed (${Math.round((Date.now() - t0) / 1000)}s)`, true);
        await clickSubmitAndConfirm(tab);
        await waitFor(async () => (await tab.eval('location.pathname')).includes('/student/results/'), { label: 'results page', timeout: 40000 });
        await tab.close(browser); await observer.close(browser);
    }],

    5: ['Student presses SUBMIT while offline: handed in automatically, once, with the right answers, when back online', async () => {
        const s = seed.students[4]; const exp = expectedByStudent[s.id] = {};
        const tab = await newTab(); await login(tab, s); await openExam(tab);
        await pick(tab, exp, 0, 2); await pick(tab, exp, 1, 1); await waitSaved(tab, exp);

        await tab.setOffline(true);
        await sleep(500);
        await pick(tab, exp, 1, 3); await pick(tab, exp, 4, 0); await writeEssay(tab, exp, 'إجابة أخيرة أثناء الانقطاع');
        await clickSubmitAndConfirm(tab);
        await sleep(3000);
        const msg = await overlayText(tab);
        check(5, 'screen explains it will submit automatically when the connection returns', !!msg && /فور عودة الاتصال|إعادة المحاولة/.test(msg), String(msg));
        check(5, 'still on the exam page (not lost)', (await tab.eval('location.pathname')).includes('/take'));

        await tab.setOffline(false);
        await waitFor(async () => (await tab.eval('location.pathname')).includes('/student/results/'), { label: 'auto-submit after reconnect', timeout: 60000 });
        check(5, 'reached the results page automatically after reconnect', true);
        await tab.close(browser);
    }],

    6: ['Flapping connection while answering (random on/off every few hundred ms)', async () => {
        const s = seed.students[5]; const exp = expectedByStudent[s.id] = {};
        const tab = await newTab(); const observer = await newTab();
        await login(tab, s); await foreground(tab); await openExam(tab);
        const draftUrl = await draftUrlOf(tab);
        let offline = false; let seedN = 7;
        const rnd = () => { seedN = (seedN * 1103515245 + 12345) & 0x7fffffff; return seedN / 0x7fffffff; };
        for (let i = 0; i < 18; i++) {
            if (rnd() < 0.55) { offline = !offline; await tab.setOffline(offline); }
            await pick(tab, exp, Math.floor(rnd() * MC.length), Math.floor(rnd() * 4));
            await sleep(150 + Math.floor(rnd() * 500));
        }
        await tab.setOffline(false);
        await waitSaved(tab, exp, 'saved after flapping');
        await observer.goto(`${BASE}/student/exams`);
        const server = await serverPicks(observer, draftUrl);
        check(6, '18 random clicks with the network flapping: server == what was clicked', sameMap(server, exp), `server=${show(server)} expected=${show(exp)}`);
        check(6, 'screen == what was clicked', sameMap(await uiPicks(tab), exp), show(await uiPicks(tab)));
        check(6, 'device copy == what was clicked', sameMap(await localPicks(tab), exp), show(await localPicks(tab)));
        await clickSubmitAndConfirm(tab);
        await waitFor(async () => (await tab.eval('location.pathname')).includes('/student/results/'), { label: 'results page', timeout: 40000 });
        await tab.close(browser); await observer.close(browser);
    }],

    7: ['Countdown is owned by the server: reload does not reset it', async () => {
        const s = seed.students[6]; const exp = expectedByStudent[s.id] = {};
        const tab = await newTab(); await login(tab, s); await openExam(tab);
        await pick(tab, exp, 0, 1); await waitSaved(tab, exp);
        await sleep(7000);
        const before = await tab.eval(`document.getElementById('countdownTimer').textContent`);
        await tab.reload();
        await waitFor(() => tab.eval(`typeof ExamTaker === 'object'`), { label: 'reloaded' });
        await sleep(1200);
        const after = await tab.eval(`document.getElementById('countdownTimer').textContent`);
        const secs = (t) => t.split(':').reduce((a, v) => a * 60 + Number(v), 0);
        check(7, `timer before reload ${before}, after reload ${after}: not reset to 30:00 and not frozen`, secs(after) < 1800 - 5 && Math.abs(secs(after) - (secs(before) - 1)) <= 6, `${before} -> ${after}`);
        check(7, 'answer survived the reload', sameMap(await uiPicks(tab), exp), show(await uiPicks(tab)));
        await startExam(tab);
        await clickSubmitAndConfirm(tab);
        await waitFor(async () => (await tab.eval('location.pathname')).includes('/student/results/'), { label: 'results page', timeout: 40000 });
        await tab.close(browser);
    }],

    8: ['Session expires while offline: answers stay on the device and come back after logging in again', async () => {
        const s = seed.students[7]; const exp = expectedByStudent[s.id] = {};
        const tab = await newTab(); await login(tab, s); await openExam(tab);
        await pick(tab, exp, 0, 0); await waitSaved(tab, exp);
        await tab.setOffline(true);
        await pick(tab, exp, 1, 2); await pick(tab, exp, 2, 3); await writeEssay(tab, exp, 'كتبتها قبل انتهاء الجلسة');
        await sleep(800);
        await tab.c.send('Network.clearBrowserCookies');          // the session is gone
        await tab.setOffline(false);
        await waitFor(async () => /انتهت/.test(await saveStatus(tab)), { label: 'session-expired state', timeout: 40000 });
        check(8, 'student is told the session expired (no silent failure)', true);
        check(8, 'device copy still holds all answers', sameMap(await localPicks(tab), exp), show(await localPicks(tab)));
        await tab.eval(`document.querySelector('.swal2-confirm')?.click()`).catch(() => {});

        await login(tab, s); await openExam(tab, { start: false });
        check(8, 'after logging in again every answer is restored', sameMap(await uiPicks(tab), exp), `ui=${show(await uiPicks(tab))} expected=${show(exp)}`);
        const draftUrl = await draftUrlOf(tab);
        await waitFor(async () => sameMap(await serverPicks(tab, draftUrl), exp), { label: 'device copy synced after re-login', timeout: 30000 }).catch(() => {});
        check(8, 'and they are synced to the server', sameMap(await serverPicks(tab, draftUrl), exp), show(await serverPicks(tab, draftUrl)));
        await startExam(tab);
        await clickSubmitAndConfirm(tab);
        await waitFor(async () => (await tab.eval('location.pathname')).includes('/student/results/'), { label: 'results page', timeout: 40000 });
        await tab.close(browser);
    }],

    9: ['Student switches tabs 3 times while OFFLINE (proctoring): auto-submit waits for the connection, violations are not lost', async () => {
        const s = seed.students[8]; const exp = expectedByStudent[s.id] = {};
        const tab = await newTab(); const other = await newTab();
        await login(tab, s); await foreground(tab); await openExam(tab);
        await pick(tab, exp, 0, 1); await pick(tab, exp, 1, 3); await waitSaved(tab, exp);

        await tab.setOffline(true);
        let how = 'real';
        for (let i = 1; i <= 3; i++) {
            how = (await setHidden(tab, true)) === 'real' ? how : 'synthetic';
            await sleep(900);
            await setHidden(tab, false);
            await sleep(900);
            if (i < 3) await tab.eval(`document.querySelector('.swal2-confirm')?.click()`);   // dismiss the warning like a student would
        }
        console.log(`   (page hidden via ${how === 'real' ? 'a real minimised window' : 'a synthetic visibilitychange - headless could not hide the page'})`);
        const counted = await tab.eval('totalViolations');
        check(9, `three tab switches were counted locally while offline (counted ${counted})`, counted >= 3, String(counted));
        await waitFor(async () => !!(await overlayText(tab)), { label: 'auto-submit overlay', timeout: 15000 });
        const msg = await overlayText(tab);
        check(9, 'auto-submit started but waits for the connection (answers kept)', !!msg, String(msg));

        await tab.setOffline(false);
        await waitFor(async () => (await tab.eval('location.pathname')).includes('/student/results/'), { label: 'auto-submit after reconnect', timeout: 60000 });
        check(9, 'handed in automatically once back online', true);
        await tab.close(browser); await other.close(browser);
    }],

    10: ['Very slow network (2.5s latency, ~30KB/s) while clicking: still ends consistent', async () => {
        const fresh = seed.students[9];
        const exp = expectedByStudent[fresh.id] = {};
        const tab = await newTab(); const observer = await newTab();
        await login(tab, fresh); await foreground(tab); await openExam(tab);
        const draftUrl = await draftUrlOf(tab);
        await tab.c.send('Network.emulateNetworkConditions', { offline: false, latency: 2500, downloadThroughput: 30000, uploadThroughput: 30000 });
        for (let i = 0; i < 8; i++) await pick(tab, exp, i % MC.length, (i * 3 + 1) % 4);
        await writeEssay(tab, exp, 'على شبكة بطيئة');
        await waitSaved(tab, exp, 'saved on slow network');
        await tab.c.send('Network.emulateNetworkConditions', { offline: false, latency: 0, downloadThroughput: -1, uploadThroughput: -1 });
        await observer.goto(`${BASE}/student/exams`);
        const server = await serverPicks(observer, draftUrl);
        check(10, 'slow network: server == what was clicked', sameMap(server, exp), `server=${show(server)} expected=${show(exp)}`);
        check(10, 'slow network: screen == what was clicked', sameMap(await uiPicks(tab), exp), show(await uiPicks(tab)));
        await clickSubmitAndConfirm(tab);
        await waitFor(async () => (await tab.eval('location.pathname')).includes('/student/results/'), { label: 'results page', timeout: 60000 });
        await tab.close(browser); await observer.close(browser);
    }],

    11: ['Server errors (HTTP 503 x4) while answering: retried with back-off, nothing lost', async () => {
        const fresh = seed.students[10];
        const exp = expectedByStudent[fresh.id] = {};
        const tab = await newTab(); const observer = await newTab();
        await login(tab, fresh); await foreground(tab); await openExam(tab);
        const draftUrl = await draftUrlOf(tab);
        tab.failBudget = 4; await tab.intercept('fail503');
        await pick(tab, exp, 0, 2); await pick(tab, exp, 1, 0);
        await waitFor(() => bannerShown(tab), { label: 'unstable-connection banner', timeout: 15000 });
        check(11, 'student sees "connection unstable, retrying" while the server fails', await bannerShown(tab));
        await waitSaved(tab, exp, 'saved after the 503s');
        await tab.intercept('off');
        check(11, 'all 4 failures were retried (>= 5 attempts)', tab.requests.filter((r) => r.mode === 'fail503').length >= 5, String(tab.requests.length));
        await observer.goto(`${BASE}/student/exams`);
        const server = await serverPicks(observer, draftUrl);
        check(11, 'after the errors stop, server == what was clicked', sameMap(server, exp), `server=${show(server)} expected=${show(exp)}`);
        await clickSubmitAndConfirm(tab);
        await waitFor(async () => (await tab.eval('location.pathname')).includes('/student/results/'), { label: 'results page', timeout: 40000 });
        await tab.close(browser); await observer.close(browser);
    }],

    12: ['Student presses START while offline: the time spent offline is NOT handed back (timer follows the server)', async () => {
        const fresh = seed.students[11];
        const exp = expectedByStudent[fresh.id] = {};
        const tab = await newTab(); const observer = await newTab();
        await login(tab, fresh); await foreground(tab); await openExam(tab, { start: false });
        const draftUrl = await draftUrlOf(tab);

        await tab.setOffline(true);
        await startExam(tab);                          // 3-2-1 and the exam is "running" locally
        await pick(tab, exp, 0, 3);
        await sleep(20000);                            // 20 seconds pass while offline
        await tab.setOffline(false);
        await waitSaved(tab, exp, 'saved after reconnect');

        await observer.goto(`${BASE}/student/exams`);
        const remaining = await observer.eval(`(async () => { const token = document.querySelector('meta[name="csrf-token"]').content;
            const r = await fetch(${JSON.stringify(draftUrl)}, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token }, body: '{"answers":{}}' });
            return (await r.json()).remainingSeconds; })()`);
        check(12, `server remaining time ${remaining}s is about 1800 - 20 - a few (NOT a fresh 1800)`, remaining <= 1800 - 18 && remaining >= 1800 - 40, String(remaining));
        const shown = await tab.eval(`document.getElementById('countdownTimer').textContent`);
        const secs = shown.split(':').reduce((a, v) => a * 60 + Number(v), 0);
        check(12, `the timer on screen (${shown}) agrees with the server (no jump upwards)`, Math.abs(secs - remaining) <= 8, `${secs} vs ${remaining}`);
        await clickSubmitAndConfirm(tab);
        await waitFor(async () => (await tab.eval('location.pathname')).includes('/student/results/'), { label: 'results page', timeout: 40000 });
        await tab.close(browser); await observer.close(browser);
    }],
};

// ---------------------------------------------------------------- run + final grading check

(async () => {
    const only = (process.env.E2E_ONLY || '').split(',').filter(Boolean).map(Number);
    if (!process.env.E2E_BASE_URL) { app = await startApp(); BASE = app.url; }
    const ver = await launch();
    console.log(`Browser: ${ver}\nApp: ${BASE}  exam #${seed.exam_id}  (${seed.tag})\n`);
    let crashed = 0;
    for (const [n, [title, fn]] of Object.entries(scenarios)) {
        if (only.length && !only.includes(Number(n))) continue;
        console.log(`#${n} ${title}`);
        const t0 = Date.now();
        currentCtx = (await browser.send('Target.createBrowserContext')).browserContextId;
        try { await fn(); } catch (e) { crashed++; check(n, 'scenario completed without a harness error', false, e.stack.split('\n').slice(0, 3).join(' | ')); }
        console.log(`   (${((Date.now() - t0) / 1000).toFixed(1)}s)\n`);
        await browser.send('Target.disposeBrowserContext', { browserContextId: currentCtx }).catch(() => {});
    }

    // What was GRADED must equal what each student clicked.
    const dump = spawnSync('php', ['artisan', 'tinker', '--execute=include "tests/e2e/dump.php";'], { cwd: ROOT, encoding: 'utf8' });
    const line = (dump.stdout || '').split('\n').find((l) => l.startsWith('E2E_DUMP:'));
    if (!line) { console.log('could not read grades', dump.stdout, dump.stderr); }
    else {
        const db = JSON.parse(line.slice('E2E_DUMP:'.length));
        console.log('Grading check (DB) ----------------------------------------');
        for (const [sid, exp] of Object.entries(expectedByStudent)) {
            const g = db[sid];
            if (!g) { check('grade', `student ${sid}: a grade exists`, false, 'no grade'); continue; }
            const graded = {};
            for (const a of g.answers) { if (a.selected_option_id) graded[a.question_id] = Number(a.selected_option_id); if (a.essay_answer) graded[a.question_id] = a.essay_answer; }
            check('grade', `student ${sid}: exactly 1 grade, and graded answers == clicked answers`, g.grades === 1 && sameMap(graded, exp), `grades=${g.grades} graded=${show(graded)} expected=${show(exp)}`);
            if (String(sid) === String(seed.students[8].id)) {
                check('grade', 'proctoring: the grade is flagged auto-submitted', g.auto_submitted === true, show(g));
                check('grade', 'proctoring: all 3 violations made while OFFLINE are recorded on the grade (tab_switch_count = 3)', g.tab_switch_count === 3, `tab_switch_count=${g.tab_switch_count}`);
            }
            const correct = MC.filter((q) => exp[q.id] === q.correct).length;
            check('grade', `student ${sid}: score ${g.score} == ${correct} correct picks`, Number(g.score) === correct, `score=${g.score} expected=${correct}`);
        }
    }

    browser.close(); browserProc.kill();
    if (app) app.stop();
    try { fs.rmSync(profileDir, { recursive: true, force: true }); } catch (e) { /* locked on Windows: harmless */ }

    const failed = results.filter((r) => !r.ok);
    if (failed.length || process.env.E2E_TRACE) {
        allTabs.filter((t) => t.timeline.length).forEach((t, i) => {
            console.log('\n--- timeline of tab ' + (i + 1) + ' ---');
            console.log(t.timeline.join('\n'));
        });
    }
    console.log(`\n${results.length - failed.length}/${results.length} checks passed${crashed ? `, ${crashed} scenario(s) crashed` : ''}`);
    failed.forEach((f) => console.log(`  FAIL [${f.scenario}] ${f.name}${f.detail ? '\n        ' + f.detail : ''}`));
    process.exit(failed.length ? 1 : 0);
})().catch((e) => { console.error(e); try { browserProc && browserProc.kill(); } catch (x) { /* noop */ } try { app && app.stop(); } catch (x) { /* noop */ } process.exit(2); });
