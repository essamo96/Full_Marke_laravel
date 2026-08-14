/**
 * Secure lesson video player: starts an exclusive playback session, then
 * streams the MP4 progressively via <video src> (Range requests) — the same
 * approach teachers use. A moving watermark of the student's name/photo is
 * overlaid so any screen-recorded leak can be traced. Session token + auth +
 * referer locks on the stream endpoint still gate access; this cannot stop
 * screen capture itself.
 */
(function (global) {
  'use strict';

  var LOAD_TIME_STORAGE_KEY = 'lessonVideoAvgLoadMs';

  function mountWatermark(container, studentName, studentPhotoUrl) {
    return global.mountSecureWatermark(container, studentName, studentPhotoUrl, { className: 'video-watermark-overlay' });
  }

  function getEstimatedLoadMs() {
    var stored = Number(localStorage.getItem(LOAD_TIME_STORAGE_KEY));
    return stored > 0 ? stored : 2500;
  }

  function recordLoadMs(ms) {
    var previous = Number(localStorage.getItem(LOAD_TIME_STORAGE_KEY));
    var next = previous > 0 ? Math.round(previous * 0.7 + ms * 0.3) : ms;
    localStorage.setItem(LOAD_TIME_STORAGE_KEY, String(next));
  }

  /**
   * Native video fullscreen makes the <video> itself the fullscreen element,
   * which leaves sibling overlays (watermark canvas) behind. Redirect
   * fullscreen to the wrapping container instead.
   *
   * Also hide the modal header while fullscreen: backdrop-filter ancestors
   * can keep painting it over the fullscreen stage in Chromium.
   */
  function keepOverlayInFullscreen(container, videoEl) {
    var modal = container.closest('.modal');

    function onFullscreenChange() {
      var fsEl = document.fullscreenElement || document.webkitFullscreenElement;

      if (modal) {
        modal.classList.toggle('video-is-fullscreen', fsEl === container);
      }

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
      if (modal) modal.classList.remove('video-is-fullscreen');
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
      var remainingSeconds = Math.max(0, (estimatedMs - elapsed) / 1000);
      timeEl.textContent = remainingSeconds.toFixed(1) + ' ثانية متبقية تقريبًا';
      textEl.textContent = elapsed > estimatedMs
        ? 'يستغرق وقتًا أطول من المعتاد، لا يزال التحميل مستمرًا...'
        : 'جاري تجهيز الفيديو الآمن...';

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
   * @param {number|string} opts.resourceId
   * @param {HTMLElement} opts.container
   * @param {HTMLVideoElement} opts.videoEl
   * @param {string} opts.startUrl
   * @param {string} opts.studentName
   * @param {string} [opts.studentPhotoUrl]
   * @param {string} opts.csrfToken
   * @param {function(string):void} [opts.onError]
   */
  function mountSecureVideoPlayer(opts) {
    var cleanupFns = [];
    var loader = mountLoadingOverlay(opts.container);
    var firstPlayHandled = false;
    var destroyed = false;

    function handleFirstFrame() {
      if (firstPlayHandled) return;
      firstPlayHandled = true;
      loader.finish();
    }

    function handleMediaError() {
      if (destroyed || firstPlayHandled) return;
      loader.destroy();
      if (opts.onError) opts.onError('تعذّر تشغيل الفيديو');
    }

    opts.videoEl.addEventListener('canplay', handleFirstFrame);
    opts.videoEl.addEventListener('error', handleMediaError);
    cleanupFns.push(function () {
      opts.videoEl.removeEventListener('canplay', handleFirstFrame);
      opts.videoEl.removeEventListener('error', handleMediaError);
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
        if (destroyed) return;
        if (!data || !data.stream_url) throw new Error('تعذّر بدء التشغيل');

        // Progressive Range streaming (same model as the teacher viewer).
        // Full-file blob download was causing long countdowns then failure on
        // larger lessons; seeking and play-while-buffering need a real src URL.
        opts.videoEl.src = data.stream_url;
        opts.videoEl.load();

        cleanupFns.push(mountWatermark(opts.container, opts.studentName, opts.studentPhotoUrl));
        cleanupFns.push(applyDeterrents(opts.videoEl));
        cleanupFns.push(keepOverlayInFullscreen(opts.container, opts.videoEl));
      })
      .catch(function (err) {
        if (destroyed) return;
        loader.destroy();
        if (opts.onError) opts.onError(err.message || 'حدث خطأ أثناء تشغيل الفيديو');
      });

    return function destroy() {
      destroyed = true;
      loader.destroy();
      cleanupFns.forEach(function (fn) { fn(); });
      opts.videoEl.pause();
      opts.videoEl.removeAttribute('src');
      opts.videoEl.load();
    };
  }

  global.mountSecureVideoPlayer = mountSecureVideoPlayer;
})(window);
