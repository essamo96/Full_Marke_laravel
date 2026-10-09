# Real-browser E2E: online exam under a bad connection

`exam-offline.e2e.cjs` drives a **real Edge/Chrome** (DevTools protocol, no extra npm packages) through
the online exam and abuses the connection for real. In every scenario it compares three things with
what the student actually clicked: **the screen**, **the server's saved draft**, **the graded result**.

| # | Scenario |
|---|----------|
| 1 | Online picks, internet cut, answers (incl. a *changed* one + an essay) made offline → reconnect |
| 2 | Autosave requests lost in transit, page reloaded before anything synced |
| 3 | Student continues on another device (no local copy) |
| 4 | A stale "zombie" request arrives after a newer answer; a request hangs (client aborts at 15 s and retries) |
| 5 | Student presses *submit* while offline → handed in automatically, once, on reconnect |
| 6 | Flapping connection (random on/off) while clicking 18 times |
| 7 | Reload does not reset the server-owned countdown |
| 8 | Session expires while offline → log in again, everything restored |
| 9 | Three tab switches (proctoring) while offline → violations are all recorded |
| 10 | Very slow network (2.5 s latency) |
| 11 | HTTP 503 bursts from the server |
| 12 | *Start* pressed while offline → no free time is handed back |

## Run

```bash
php artisan tinker --execute="include 'tests/e2e/seed.php';"      # throw-away data (committed rows)
node tests/e2e/exam-offline.e2e.cjs                               # starts 4 php workers + a proxy itself
php artisan tinker --execute="include 'tests/e2e/cleanup.php';"   # remove the data again
```

Environment: `E2E_BROWSER` (path to msedge/chrome), `E2E_ONLY=3,5`, `E2E_TRACE=1` (print request
timelines), `E2E_BASE_URL` (test an already running app instead of starting one).

The seeded data lives in the **local dev database** and is removed by `cleanup.php`. The unit-level
counterparts are `npm run test:js` (engine logic in jsdom) and `tests/Feature/StudentExamAutosaveTest.php`.

Why 4 workers behind a proxy: PHP's built-in server is single-threaded, so one idle browser socket
(which request interception and network cuts create) stalls every other request and makes the run flaky.
