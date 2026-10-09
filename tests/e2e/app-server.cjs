/*
 * Starts the app for the E2E run: N `php artisan serve` workers behind a tiny round-robin proxy.
 *
 * Why: PHP's built-in server is single-threaded, so one idle / half-open connection from the
 * browser (exactly what request interception and network cuts create) stalls EVERY other request
 * and turns a perfectly healthy app into a flaky test. The proxy opens a fresh upstream
 * connection per request, so no worker ever waits on an idle socket.
 */
const http = require('node:http');
const { spawn, spawnSync } = require('node:child_process');
const path = require('node:path');

const ROOT = path.join(__dirname, '../..');
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

async function ready(port, timeout = 30000) {
    const t0 = Date.now();
    for (;;) {
        const ok = await new Promise((res) => {
            const req = http.get({ host: '127.0.0.1', port, path: '/student/login', agent: false, timeout: 3000 }, (r) => { r.resume(); res(r.statusCode < 500); });
            req.on('error', () => res(false)); req.on('timeout', () => { req.destroy(); res(false); });
        });
        if (ok) return;
        if (Date.now() - t0 > timeout) throw new Error(`php server on ${port} did not start`);
        await sleep(300);
    }
}

async function startApp({ port = 8123, workers = 4 } = {}) {
    const ports = Array.from({ length: workers }, (_, i) => port + 1 + i);
    const procs = ports.map((p) => spawn('php', ['artisan', 'serve', '--host=127.0.0.1', `--port=${p}`, '--no-reload'], { cwd: ROOT, stdio: 'ignore' }));
    await Promise.all(ports.map((p) => ready(p)));

    let next = 0;
    const proxy = http.createServer((req, res) => {
        const target = ports[next++ % ports.length];
        const up = http.request({ host: '127.0.0.1', port: target, method: req.method, path: req.url, headers: { ...req.headers, connection: 'close' }, agent: false }, (r) => {
            res.writeHead(r.statusCode, r.headers);
            r.pipe(res);
        });
        up.on('error', () => { try { res.writeHead(502); res.end('proxy error'); } catch (e) { /* client gone */ } });
        res.on('close', () => up.destroy());
        req.pipe(up);
    });
    await new Promise((r) => proxy.listen(port, '127.0.0.1', r));

    return {
        url: `http://127.0.0.1:${port}`,
        stop() {
            proxy.close();
            procs.forEach((p) => { try { spawnSync('taskkill', ['/pid', String(p.pid), '/T', '/F'], { stdio: 'ignore' }); } catch (e) { p.kill(); } });
        },
    };
}

module.exports = { startApp };
