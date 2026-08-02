/**
 * Secure lesson video player: fetches a single-use, single-session stream
 * URL, downloads it as a blob and plays it from a blob: object URL (rather
 * than pointing <video src> at the real network endpoint), and overlays a
 * moving watermark of the current student's name and photo so any
 * screen-recorded leak can be traced back to its source. The blob: URL only
 * resolves inside this tab for this page load, so a browser's native
 * "download"/"copy video address" affordance yields nothing usable outside
 * this session. None of this can stop screen capture itself — see the
 * protection study for why that boundary is technical, not a bug here.
 */
(function (global) {
  'use strict';

  var LOAD_TIME_STORAGE_KEY = 'lessonVideoAvgLoadMs';

  function mountWatermark(container, studentName, studentPhotoUrl) {
    return global.mountSecureWatermark(container, studentName, studentPhotoUrl, { className: 'video-watermark-overlay' });
  }

  function getEstimatedLoadMs() {
    var stored = Number(localStorage.getItem(LOAD_TIME_STORAGE_KEY));
    return stored > 0 ? stored : 6000; // first-ever load: reasonable generic guess
  }

  function recordLoadMs(ms) {
    var previous = Number(localStorage.getItem(LOAD_TIME_STORAGE_KEY));
    // Exponential moving average so one slow/fast network blip doesn't swing the estimate wildly.
    var next = previous > 0 ? Math.round(previous * 0.7 + ms * 0.3) : ms;
    localStorage.setItem(LOAD_TIME_STORAGE_KEY, String(next));
  }

  /**
   * Native video fullscreen (the button in the browser's own <video controls>)
   * makes the <video> itself the fullscreen element, which leaves sibling
   * overlays — like the watermark canvas — behind on the non-fullscreen page,
   * so the watermark visually "disappears" while zoomed. Redirect fullscreen
   * to the wrapping container instead, which keeps the canvas layered on top.
   */
  function keepOverlayInFullscreen(container, videoEl) {
    function onFullscreenChange() {
      var fsEl = document.fullscreenElement || document.webkitFullscreenElement;
      if (fsEl !== videoEl) return;

      var exit = document.exitFullscreen
        ? document.exitFullscreen()
        : (document.webkitExitFullscreen ? Promise.resolve(document.webkitExitFullscreen()) : Promise.resolve());

      Promise.resolve(exit).catch(function () {}).then(function () {
        var request = container.requestFullscreen || container.webkitRequestFullscreen;
        if (request) request.call(container).catch(function () {});
      });
    }
    document.addEventListener('fullscreenchange', onFullscreenChange);
    document.addEventListener('webkitfullscreenchange', onFullscreenChange);
    return function destroy() {
      document.removeEventListener('fullscreenchange', onFullscreenChange);
      document.removeEventListener('webkitfullscreenchange', onFullscreenChange);
    };
  }

  function applyDeterrents(videoEl) {
    videoEl.setAttribute('controlsList', 'nodownload noremoteplayback');
    videoEl.setAttribute('disablePictureInPicture', 'true');
    videoEl.oncontextmenu = function () { return false; };

    function onVisibilityChange() {
      if (document.hidden) videoEl.pause();
    }
    document.addEventListener('visibilitychange', onVisibilityChange);

    return function destroy() {
      document.removeEventListener('visibilitychange', onVisibilityChange);
    };
  }

  /**
   * Shows a spinner with a live elapsed-time counter and a progress bar
   * that fills toward this browser's own historical average load time, so
   * the wait (mostly HLS decryption + first-segment buffering) reads as
   * "loading, ~N seconds left" instead of a frozen screen.
   */
  function mountLoadingOverlay(container) {
    var overlay = document.createElement('div');
    overlay.className = 'video-loading-overlay';
    overlay.innerHTML =
      '<div class="video-loading-spinner" role="status"></div>' +
      '<div class="video-loading-text">جاري تجهيز الفيديو الآمن...</div>' +
      '<div class="video-loading-time">0.0 ثانية متبقية تقريبًا</div>' +
      '<div class="video-loading-bar-track"><div class="video-loading-bar-fill"></div></div>';
    container.appendChild(overlay);

    if (!document.getElementById('video-loading-overlay-styles')) {
      var style = document.createElement('style');
      style.id = 'video-loading-overlay-styles';
      style.textContent =
        '.video-loading-overlay{position:absolute;inset:0;display:flex;flex-direction:column;' +
        'align-items:center;justify-content:center;gap:.6rem;background:rgba(10,10,14,0.85);' +
        'color:#fff;z-index:5;transition:opacity .35s ease;}' +
        '.video-loading-spinner{width:44px;height:44px;border-radius:50%;' +
        'border:3px solid rgba(255,255,255,0.25);border-top-color:#e6231e;' +
        'animation:videoLoadingSpin 0.9s linear infinite;}' +
        '.video-loading-text{font-size:.95rem;opacity:.9;}' +
        '.video-loading-time{font-size:1.4rem;font-weight:700;font-variant-numeric:tabular-nums;}' +
        '.video-loading-bar-track{width:60%;max-width:260px;height:6px;border-radius:999px;' +
        'background:rgba(255,255,255,0.18);overflow:hidden;}' +
        '.video-loading-bar-fill{height:100%;width:0%;background:#e6231e;border-radius:999px;' +
        'transition:width .2s linear;}' +
        '@keyframes videoLoadingSpin{to{transform:rotate(360deg);}}' +
        '@media (prefers-reduced-motion: reduce){.video-loading-spinner{animation:none;border-top-color:rgba(255,255,255,0.25);}}';
      document.head.appendChild(style);
    }

    var startedAt = Date.now();
    var estimatedMs = getEstimatedLoadMs();
    var timeEl = overlay.querySelector('.video-loading-time');
    var fillEl = overlay.querySelector('.video-loading-bar-fill');

    var textEl = overlay.querySelector('.video-loading-text');

    function tick() {
      var elapsed = Date.now() - startedAt;
      // Countdown and bar are driven by the same ratio, so they always finish together:
      // the number counts down to ~0 exactly as the bar reaches its (capped) fill.
      var remainingSeconds = Math.max(0, (estimatedMs - elapsed) / 1000);
      timeEl.textContent = remainingSeconds.toFixed(1) + ' ثانية متبقية تقريبًا';
      textEl.textContent = elapsed > estimatedMs
        ? 'يستغرق وقتًا أطول من المعتاد، لا يزال التحميل مستمرًا...'
        : 'جاري تجهيز الفيديو الآمن...';

      // Approach — never fully reach — 92% while still waiting, so the bar
      // never lies about being done before the video actually plays.
      var pct = Math.min(92, (elapsed / estimatedMs) * 100);
      fillEl.style.width = pct + '%';
    }

    tick();
    var interval = setInterval(tick, 100);

    return {
      finish: function () {
        clearInterval(interval);
        recordLoadMs(Date.now() - startedAt);
        fillEl.style.width = '100%';
        overlay.style.opacity = '0';
        setTimeout(function () { overlay.remove(); }, 350);
      },
      destroy: function () {
        clearInterval(interval);
        overlay.remove();
      },
    };
  }

  /**
   * @param {Object} opts
   * @param {number} opts.resourceId
   * @param {HTMLElement} opts.container - wrapper the <video> and watermark canvas mount into (must be position:relative)
   * @param {HTMLVideoElement} opts.videoEl
   * @param {string} opts.startUrl - route('student.video.start', resource)
   * @param {string} opts.studentName
   * @param {string} [opts.studentPhotoUrl]
   * @param {string} opts.csrfToken
   * @param {function(string):void} [opts.onError]
   */
  function mountSecureVideoPlayer(opts) {
    var cleanupFns = [];
    var loader = mountLoadingOverlay(opts.container);
    var firstPlayHandled = false;
    var objectUrl = null;

    function handleFirstFrame() {
      if (firstPlayHandled) return;
      firstPlayHandled = true;
      loader.finish();
    }
    opts.videoEl.addEventListener('canplay', handleFirstFrame);
    cleanupFns.push(function () {
      opts.videoEl.removeEventListener('canplay', handleFirstFrame);
    });

    fetch(opts.startUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'X-CSRF-TOKEN': opts.csrfToken,
        'Accept': 'application/json',
      },
    })
      .then(function (res) {
        if (!res.ok) throw new Error('تعذّر بدء التشغيل');
        return res.json();
      })
      .then(function (data) {
        // Fetch the video into memory and play it from a blob: object URL
        // instead of pointing <video src> at the real network endpoint.
        // A blob: URL only exists inside this tab's memory for this page
        // load — copying "video address" from the browser's native menu
        // yields a URL that is meaningless anywhere else (a different
        // browser, a new tab, or even this same tab after a reload), which
        // closes off the native "download"/"copy link" escape hatch that
        // controlsList/contextmenu blocking can't reach. Trade-off: the
        // whole file must download before playback starts — no more
        // progressive play-while-buffering — which is why the loading
        // overlay above gives an honest estimate rather than pretending
        // this is instant.
        return fetch(data.stream_url, { credentials: 'same-origin' })
          .then(function (res) {
            if (!res.ok) throw new Error('تعذّر تحميل الفيديو');
            return res.blob();
          })
          .then(function (blob) {
            objectUrl = URL.createObjectURL(blob);
            opts.videoEl.src = objectUrl;
            opts.videoEl.load();

            cleanupFns.push(mountWatermark(opts.container, opts.studentName, opts.studentPhotoUrl));
            cleanupFns.push(applyDeterrents(opts.videoEl));
            cleanupFns.push(keepOverlayInFullscreen(opts.container, opts.videoEl));
          });
      })
      .catch(function (err) {
        loader.destroy();
        if (opts.onError) opts.onError(err.message || 'حدث خطأ أثناء تشغيل الفيديو');
      });

    return function destroy() {
      loader.destroy();
      cleanupFns.forEach(function (fn) { fn(); });
      opts.videoEl.pause();
      opts.videoEl.removeAttribute('src');
      opts.videoEl.load();
      if (objectUrl) {
        URL.revokeObjectURL(objectUrl);
        objectUrl = null;
      }
    };
  }

  global.mountSecureVideoPlayer = mountSecureVideoPlayer;
})(window);
