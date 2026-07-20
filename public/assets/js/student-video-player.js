/**
 * Secure lesson video player: fetches a single-use, single-session HLS
 * stream URL, plays it through hls.js, and overlays a moving watermark of
 * the current student's name and photo so any screen-recorded leak can be
 * traced back to its source. None of this can stop screen capture itself —
 * see the protection study for why that boundary is technical, not a bug
 * here.
 */
(function (global) {
  'use strict';

  var LOAD_TIME_STORAGE_KEY = 'lessonVideoAvgLoadMs';

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

  function mountWatermark(container, studentName, studentPhotoUrl) {
    var canvas = document.createElement('canvas');
    canvas.className = 'video-watermark-overlay';
    canvas.style.position = 'absolute';
    canvas.style.inset = '0';
    canvas.style.width = '100%';
    canvas.style.height = '100%';
    canvas.style.pointerEvents = 'none';
    container.appendChild(canvas);

    var ctx = canvas.getContext('2d');
    var raf = null;
    var photo = null;

    if (studentPhotoUrl) {
      photo = new Image();
      photo.crossOrigin = 'anonymous';
      photo.src = studentPhotoUrl;
    }

    function resize() {
      var rect = container.getBoundingClientRect();
      canvas.width = rect.width;
      canvas.height = rect.height;
    }

    function draw() {
      var width = canvas.width;
      var height = canvas.height;
      if (width && height) {
        ctx.clearRect(0, 0, width, height);

        var t = Date.now() / 4500;
        var x = width * (0.5 + 0.36 * Math.cos(t));
        var y = height * (0.5 + 0.36 * Math.sin(t * 1.3));

        // Photo sized relative to the video frame so it stays legible on any player size.
        var photoSize = Math.max(28, Math.round(height * 0.09));

        ctx.globalAlpha = 0.55;
        if (photo && photo.complete && photo.naturalWidth) {
          ctx.save();
          ctx.beginPath();
          ctx.arc(x, y - photoSize * 0.75, photoSize / 2, 0, Math.PI * 2);
          ctx.clip();
          ctx.drawImage(photo, x - photoSize / 2, y - photoSize * 0.75 - photoSize / 2, photoSize, photoSize);
          ctx.restore();
        }

        var fontSize = Math.max(13, Math.round(height * 0.038));
        ctx.font = '700 ' + fontSize + 'px "Segoe UI", Tahoma, sans-serif';
        ctx.textAlign = 'center';
        ctx.lineWidth = Math.max(2, fontSize * 0.16);
        ctx.strokeStyle = '#ffffff';
        ctx.fillStyle = '#e6231e';
        ctx.shadowColor = 'rgba(0,0,0,0.45)';
        ctx.shadowBlur = 3;
        ctx.strokeText(studentName, x, y + fontSize * 0.3);
        ctx.fillText(studentName, x, y + fontSize * 0.3);
      }
      raf = requestAnimationFrame(draw);
    }

    var resizeObserver = new ResizeObserver(resize);
    resizeObserver.observe(container);
    resize();
    draw();

    return function destroy() {
      if (raf) cancelAnimationFrame(raf);
      resizeObserver.disconnect();
      canvas.remove();
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
        var streamUrl = data.stream_url;

        // Ensure browser can play mp4 directly
        opts.videoEl.src = streamUrl;
        opts.videoEl.load();

        cleanupFns.push(mountWatermark(opts.container, opts.studentName, opts.studentPhotoUrl));
        cleanupFns.push(applyDeterrents(opts.videoEl));
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
    };
  }

  global.mountSecureVideoPlayer = mountSecureVideoPlayer;
})(window);
