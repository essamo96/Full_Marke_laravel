/**
 * Unified protected media player for student portal.
 * Engines: mp4 (session-token stream), youtube (IFrame API + custom chrome),
 * drive (preview iframe + shields), external (gated open).
 */
(function (global) {
  'use strict';

  var LOAD_TIME_STORAGE_KEY = 'lessonVideoAvgLoadMs';
  var ytApiLoading = null;

  function ensureStyles() {
    if (document.getElementById('secure-media-player-styles')) return;
    var style = document.createElement('style');
    style.id = 'secure-media-player-styles';
    style.textContent =
      '.smp-root{position:relative;width:100%;height:100%;min-height:220px;background:#000;overflow:hidden;user-select:none;-webkit-user-select:none;}' +
      '.smp-stage{position:absolute;inset:0;}' +
      '.smp-stage video,.smp-stage iframe,#smpYtHost iframe{position:absolute;inset:0;width:100%!important;height:100%!important;border:0;object-fit:contain;}' +
      '#smpYtHost iframe{pointer-events:none!important;}' +
      '.smp-blocker{position:absolute;inset:0;z-index:6;background:transparent;}' +
      '.smp-shield{position:absolute;z-index:8;background:transparent;cursor:not-allowed;}' +
      '.smp-shield-top{top:0;left:0;right:0;height:64px;}' +
      '.smp-shield-tr{top:0;right:0;width:120px;height:80px;}' +
      '.smp-shield-br{right:0;bottom:0;width:150px;height:58px;}' +
      '.smp-shield-bottom{left:0;right:150px;bottom:0;height:14px;}' +
      '.smp-controls{position:absolute;left:0;right:0;bottom:0;z-index:20;display:flex;align-items:center;gap:10px;padding:10px 12px;background:linear-gradient(transparent,rgba(0,0,0,.88));color:#fff;font:13px/1.2 system-ui,sans-serif;}' +
      '.smp-controls button{appearance:none;border:0;background:rgba(255,255,255,.12);color:#fff;width:36px;height:36px;border-radius:999px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;}' +
      '.smp-controls button:hover{background:rgba(230,35,30,.85);}' +
      '.smp-progress{flex:1;height:6px;border-radius:999px;accent-color:#e6231e;cursor:pointer;}' +
      '.smp-time{min-width:92px;text-align:center;opacity:.9;font-variant-numeric:tabular-nums;flex-shrink:0;}' +
      '.smp-big-play{position:absolute;inset:0;z-index:15;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.22);cursor:pointer;}' +
      '.smp-big-play.hidden{display:none;}' +
      '.smp-big-play span{width:72px;height:72px;border-radius:50%;background:rgba(230,35,30,.92);display:flex;align-items:center;justify-content:center;color:#fff;font-size:28px;padding-inline-start:4px;box-shadow:0 8px 28px rgba(0,0,0,.45);}' +
      '.smp-loading{position:absolute;inset:0;z-index:40;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.6rem;background:rgba(10,10,14,.88);color:#fff;}' +
      '.smp-loading.hidden{display:none;}' +
      '.smp-spinner{width:44px;height:44px;border-radius:50%;border:3px solid rgba(255,255,255,.25);border-top-color:#e6231e;animation:smpSpin .9s linear infinite;}' +
      '.smp-load-time{font-size:1.25rem;font-weight:700;font-variant-numeric:tabular-nums;}' +
      '.smp-load-bar{width:60%;max-width:260px;height:6px;border-radius:999px;background:rgba(255,255,255,.18);overflow:hidden;}' +
      '.smp-load-fill{height:100%;width:0;background:#e6231e;border-radius:999px;transition:width .2s linear;}' +
      '.smp-external{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:12px;color:#fff;padding:24px;text-align:center;}' +
      '.smp-external button{appearance:none;border:0;background:linear-gradient(135deg,#c5a880,#a8895f);color:#111;font-weight:700;padding:10px 22px;border-radius:999px;cursor:pointer;}' +
      '@keyframes smpSpin{to{transform:rotate(360deg);}}' +
      '.smp-root:fullscreen,.smp-root:-webkit-full-screen{width:100vw;height:100vh;border-radius:0;}' +
      '.modal.video-is-fullscreen .modal-header{display:none!important;}';
    document.head.appendChild(style);
  }

  function formatTime(totalSeconds) {
    totalSeconds = Math.max(0, Math.floor(totalSeconds || 0));
    var h = Math.floor(totalSeconds / 3600);
    var m = Math.floor((totalSeconds % 3600) / 60);
    var s = totalSeconds % 60;
    var mm = h > 0 ? String(m).padStart(2, '0') : String(m);
    var ss = String(s).padStart(2, '0');
    return h > 0 ? (h + ':' + mm + ':' + ss) : (mm + ':' + ss);
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

  function mountLoading(container) {
    var el = document.createElement('div');
    el.className = 'smp-loading';
    el.innerHTML =
      '<div class="smp-spinner"></div>' +
      '<div class="smp-load-text">جاري تجهيز المشغّل الآمن...</div>' +
      '<div class="smp-load-time">0.0 ثانية متبقية تقريبًا</div>' +
      '<div class="smp-load-bar"><div class="smp-load-fill"></div></div>';
    container.appendChild(el);
    var startedAt = Date.now();
    var estimatedMs = getEstimatedLoadMs();
    var timeEl = el.querySelector('.smp-load-time');
    var fillEl = el.querySelector('.smp-load-fill');
    var textEl = el.querySelector('.smp-load-text');
    var interval = setInterval(function () {
      var elapsed = Date.now() - startedAt;
      timeEl.textContent = Math.max(0, (estimatedMs - elapsed) / 1000).toFixed(1) + ' ثانية متبقية تقريبًا';
      textEl.textContent = elapsed > estimatedMs
        ? 'يستغرق وقتًا أطول من المعتاد...'
        : 'جاري تجهيز المشغّل الآمن...';
      fillEl.style.width = Math.min(92, (elapsed / estimatedMs) * 100) + '%';
    }, 100);
    return {
      finish: function () {
        clearInterval(interval);
        recordLoadMs(Date.now() - startedAt);
        fillEl.style.width = '100%';
        el.classList.add('hidden');
        setTimeout(function () { el.remove(); }, 250);
      },
      destroy: function () {
        clearInterval(interval);
        el.remove();
      },
    };
  }

  function mountControls(root, handlers) {
    var big = document.createElement('div');
    big.className = 'smp-big-play';
    big.innerHTML = '<span>▶</span>';
    root.appendChild(big);

    var bar = document.createElement('div');
    bar.className = 'smp-controls';
    bar.innerHTML =
      '<button type="button" data-act="play" title="تشغيل / إيقاف">▶</button>' +
      '<button type="button" data-act="rewind" title="تأخير 10 ثوان" style="font-size:14px;line-height:1;">⏪</button>' +
      '<button type="button" data-act="forward" title="تقديم 10 ثوان" style="font-size:14px;line-height:1;">⏩</button>' +
      '<input type="range" class="smp-progress" data-act="seek" min="0" max="1000" value="0" step="1">' +
      '<div class="smp-time" data-act="time">0:00 / 0:00</div>' +
      '<button type="button" data-act="speed" title="سرعة التشغيل" style="font-size:13px;font-weight:bold;">1x</button>' +
      '<button type="button" data-act="mute" title="كتم">🔊</button>' +
      '<button type="button" data-act="fs" title="ملء الشاشة داخل المنصة">⛶</button>';
    root.appendChild(bar);

    var playBtn = bar.querySelector('[data-act="play"]');
    var rewindBtn = bar.querySelector('[data-act="rewind"]');
    var forwardBtn = bar.querySelector('[data-act="forward"]');
    var seek = bar.querySelector('[data-act="seek"]');
    var timeEl = bar.querySelector('[data-act="time"]');
    var speedBtn = bar.querySelector('[data-act="speed"]');
    var muteBtn = bar.querySelector('[data-act="mute"]');
    var fsBtn = bar.querySelector('[data-act="fs"]');
    var seeking = false;
    var speeds = [1, 1.25, 1.5, 2, 0.5, 0.75];
    var currentSpeedIdx = 0;

    function setPlaying(isPlaying) {
      playBtn.textContent = isPlaying ? '❚❚' : '▶';
      big.classList.toggle('hidden', isPlaying);
    }

    function setTime(current, duration) {
      if (!seeking && duration > 0) seek.value = String(Math.round((current / duration) * 1000));
      timeEl.textContent = formatTime(current) + ' / ' + formatTime(duration);
    }

    big.addEventListener('click', function () { handlers.togglePlay(); });
    playBtn.addEventListener('click', function () { handlers.togglePlay(); });
    rewindBtn.addEventListener('click', function () { handlers.seekOffset(-10); });
    forwardBtn.addEventListener('click', function () { handlers.seekOffset(10); });
    speedBtn.addEventListener('click', function () {
      currentSpeedIdx = (currentSpeedIdx + 1) % speeds.length;
      var rate = speeds[currentSpeedIdx];
      speedBtn.textContent = rate + 'x';
      handlers.setSpeed(rate);
    });
    muteBtn.addEventListener('click', function () {
      var muted = handlers.toggleMute();
      muteBtn.textContent = muted ? '🔇' : '🔊';
    });
    fsBtn.addEventListener('click', function () { handlers.toggleFullscreen(); });
    seek.addEventListener('pointerdown', function () { seeking = true; });
    seek.addEventListener('pointerup', function () { seeking = false; });
    seek.addEventListener('change', function () {
      handlers.seek(Number(seek.value) / 1000);
      seeking = false;
    });

    return {
      setPlaying: setPlaying,
      setTime: setTime,
      setMuted: function (muted) { muteBtn.textContent = muted ? '🔇' : '🔊'; },
      destroy: function () { big.remove(); bar.remove(); },
    };
  }

  function bindStageFullscreen(root) {
    var modal = root.closest('.modal');
    function onFsChange() {
      var fsEl = document.fullscreenElement || document.webkitFullscreenElement;
      if (modal) modal.classList.toggle('video-is-fullscreen', fsEl === root);
    }
    document.addEventListener('fullscreenchange', onFsChange);
    document.addEventListener('webkitfullscreenchange', onFsChange);
    return {
      toggle: function () {
        var fsEl = document.fullscreenElement || document.webkitFullscreenElement;
        if (fsEl) {
          var exit = document.exitFullscreen || document.webkitExitFullscreen;
          if (exit) exit.call(document);
          return;
        }
        var req = root.requestFullscreen || root.webkitRequestFullscreen;
        if (req) req.call(root).catch(function () {});
      },
      destroy: function () {
        document.removeEventListener('fullscreenchange', onFsChange);
        document.removeEventListener('webkitfullscreenchange', onFsChange);
        if (modal) modal.classList.remove('video-is-fullscreen');
      },
    };
  }

  function applyPageDeterrents(root) {
    function onCtx(e) { e.preventDefault(); }
    function onDrag(e) { e.preventDefault(); }
    function onVis() {
      if (!document.hidden) return;
      root.dispatchEvent(new CustomEvent('smp:visibility-hide'));
    }
    root.addEventListener('contextmenu', onCtx);
    root.addEventListener('dragstart', onDrag);
    document.addEventListener('visibilitychange', onVis);
    return function () {
      root.removeEventListener('contextmenu', onCtx);
      root.removeEventListener('dragstart', onDrag);
      document.removeEventListener('visibilitychange', onVis);
    };
  }

  function loadYoutubeApi() {
    if (global.YT && global.YT.Player) return Promise.resolve();
    if (ytApiLoading) return ytApiLoading;
    ytApiLoading = new Promise(function (resolve) {
      var prev = global.onYouTubeIframeAPIReady;
      global.onYouTubeIframeAPIReady = function () {
        if (typeof prev === 'function') prev();
        resolve();
      };
      var tag = document.createElement('script');
      tag.src = 'https://www.youtube.com/iframe_api';
      document.head.appendChild(tag);
    });
    return ytApiLoading;
  }

  function engineMp4(root, opts, ui) {
    var video = document.createElement('video');
    video.playsInline = true;
    video.setAttribute('playsinline', '');
    video.setAttribute('controlsList', 'nodownload noremoteplayback');
    video.setAttribute('disablePictureInPicture', 'true');
    video.disablePictureInPicture = true;
    // Custom chrome only — never expose native download/share UI.
    video.removeAttribute('controls');
    opts.stage.appendChild(video);

    var poll = null;
    var destroyed = false;

    return fetch(opts.startUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'X-CSRF-TOKEN': opts.csrfToken, 'Accept': 'application/json' },
    })
      .then(function (res) {
        if (!res.ok) throw new Error('تعذّر بدء التشغيل');
        return res.json();
      })
      .then(function (data) {
        if (destroyed) return null;
        if (!data || !data.stream_url) throw new Error('تعذّر بدء التشغيل');
        video.src = data.stream_url;
        video.load();

        video.addEventListener('canplay', function onReady() {
          video.removeEventListener('canplay', onReady);
          ui.loader.finish();
        });
        video.addEventListener('play', function () { ui.controls.setPlaying(true); });
        video.addEventListener('pause', function () { ui.controls.setPlaying(false); });
        video.addEventListener('ended', function () { ui.controls.setPlaying(false); });
        video.addEventListener('error', function () {
          if (opts.onError) opts.onError('تعذّر تشغيل الفيديو');
        });

        poll = setInterval(function () {
          ui.controls.setTime(video.currentTime || 0, video.duration || 0);
        }, 400);

        root.addEventListener('smp:visibility-hide', function () { video.pause(); });

        return {
          togglePlay: function () {
            if (video.paused) video.play().catch(function () {});
            else video.pause();
          },
          toggleMute: function () {
            video.muted = !video.muted;
            return video.muted;
          },
          seek: function (ratio) {
            if (!video.duration) return;
            video.currentTime = ratio * video.duration;
          },
          seekOffset: function (seconds) {
            if (!video.duration) return;
            video.currentTime = Math.max(0, Math.min(video.duration, video.currentTime + seconds));
          },
          setSpeed: function (rate) {
            video.playbackRate = rate;
          },
          destroy: function () {
            destroyed = true;
            if (poll) clearInterval(poll);
            video.pause();
            video.removeAttribute('src');
            video.load();
            video.remove();
          },
        };
      });
  }

  function engineYoutube(root, opts, ui, youtubeId) {
    var host = document.createElement('div');
    host.id = 'smpYtHost';
    host.style.cssText = 'position:absolute;inset:0;';
    var frame = document.createElement('div');
    frame.id = 'smpYtFrame_' + Math.random().toString(36).slice(2);
    host.appendChild(frame);
    opts.stage.appendChild(host);

    var blocker = document.createElement('div');
    blocker.className = 'smp-blocker';
    opts.stage.appendChild(blocker);

    ['smp-shield-top', 'smp-shield-tr', 'smp-shield-br', 'smp-shield-bottom'].forEach(function (cls) {
      var s = document.createElement('div');
      s.className = 'smp-shield ' + cls;
      opts.stage.appendChild(s);
    });

    var player = null;
    var poll = null;
    var hardenTimer = null;

    function harden() {
      var iframe = host.querySelector('iframe');
      if (!iframe) return;
      iframe.style.pointerEvents = 'none';
      iframe.setAttribute('tabindex', '-1');
      iframe.removeAttribute('allowfullscreen');
    }

    return loadYoutubeApi().then(function () {
      return new Promise(function (resolve, reject) {
        player = new YT.Player(frame.id, {
          width: '100%',
          height: '100%',
          videoId: youtubeId,
          host: 'https://www.youtube-nocookie.com',
          playerVars: {
            autoplay: 0,
            controls: 0,
            disablekb: 1,
            fs: 0,
            iv_load_policy: 3,
            modestbranding: 1,
            playsinline: 1,
            rel: 0,
            cc_load_policy: 0,
            enablejsapi: 1,
            origin: window.location.origin,
          },
          events: {
            onReady: function () {
              ui.loader.finish();
              harden();
              hardenTimer = setInterval(harden, 1000);
              poll = setInterval(function () {
                if (!player || typeof player.getCurrentTime !== 'function') return;
                ui.controls.setTime(player.getCurrentTime() || 0, player.getDuration() || 0);
              }, 400);
              ui.controls.setPlaying(false);
              resolve({
                togglePlay: function () {
                  var state = player.getPlayerState();
                  if (state === YT.PlayerState.PLAYING) player.pauseVideo();
                  else player.playVideo();
                },
                toggleMute: function () {
                  if (player.isMuted()) { player.unMute(); return false; }
                  player.mute();
                  return true;
                },
                seek: function (ratio) {
                  var d = player.getDuration() || 0;
                  player.seekTo(ratio * d, true);
                },
                seekOffset: function (seconds) {
                  var d = player.getDuration() || 0;
                  var c = player.getCurrentTime() || 0;
                  player.seekTo(Math.max(0, Math.min(d, c + seconds)), true);
                },
                setSpeed: function (rate) {
                  player.setPlaybackRate(rate);
                },
                destroy: function () {
                  if (poll) clearInterval(poll);
                  if (hardenTimer) clearInterval(hardenTimer);
                  try { player.destroy(); } catch (e) {}
                  host.remove();
                  blocker.remove();
                },
              });
            },
            onStateChange: function (event) {
              harden();
              if (event.data === YT.PlayerState.PLAYING) ui.controls.setPlaying(true);
              if (event.data === YT.PlayerState.PAUSED || event.data === YT.PlayerState.ENDED) {
                ui.controls.setPlaying(false);
              }
            },
            onError: function () {
              reject(new Error('تعذّر تشغيل فيديو يوتيوب داخل المنصة'));
            },
          },
        });
      });
    });
  }

  function engineDrive(root, opts, ui, embedUrl) {
    var iframe = document.createElement('iframe');
    iframe.src = embedUrl;
    iframe.referrerPolicy = 'strict-origin-when-cross-origin';
    iframe.setAttribute('allow', 'autoplay; encrypted-media; fullscreen');
    iframe.setAttribute('sandbox', 'allow-scripts allow-same-origin allow-presentation allow-forms');
    opts.stage.appendChild(iframe);

    ['smp-shield-top', 'smp-shield-tr', 'smp-shield-br'].forEach(function (cls) {
      var s = document.createElement('div');
      s.className = 'smp-shield ' + cls;
      opts.stage.appendChild(s);
    });

    // Drive has no public seek API — keep only platform fullscreen control.
    ui.controls.destroy();
    var fsOnly = document.createElement('div');
    fsOnly.className = 'smp-controls';
    fsOnly.innerHTML = '<div style="flex:1"></div><button type="button" data-act="fs" title="ملء الشاشة داخل المنصة">⛶</button>';
    root.appendChild(fsOnly);
    fsOnly.querySelector('[data-act="fs"]').addEventListener('click', function () { ui.fullscreen.toggle(); });

    iframe.addEventListener('load', function () { ui.loader.finish(); });
    setTimeout(function () { ui.loader.finish(); }, 1800);

    return Promise.resolve({
      togglePlay: function () {},
      toggleMute: function () { return false; },
      seek: function () {},
      destroy: function () {
        iframe.remove();
        fsOnly.remove();
      },
    });
  }

  function engineExternal(root, opts, ui, openUrl) {
    ui.loader.finish();
    ui.controls.destroy();
    var box = document.createElement('div');
    box.className = 'smp-external';
    box.innerHTML = '<p>هذا الرابط يُفتح خارجيًا بعد التحقق من صلاحيتك.</p><button type="button">فتح الرابط الآمن</button>';
    root.appendChild(box);
    box.querySelector('button').addEventListener('click', function () {
      window.open(openUrl, '_blank', 'noopener,noreferrer');
    });
    return Promise.resolve({
      togglePlay: function () {},
      toggleMute: function () { return false; },
      seek: function () {},
      destroy: function () { box.remove(); },
    });
  }

  /**
   * @param {Object} opts
   * @param {HTMLElement} opts.container
   * @param {string} opts.resourceId encrypted route key
   * @param {string} [opts.kind] mp4|youtube|drive|external — if omitted, resolve API is called
   * @param {string} [opts.resolveUrl]
   * @param {string} [opts.startUrl] required for mp4
   * @param {string} [opts.youtubeId]
   * @param {string} [opts.embedUrl]
   * @param {string} [opts.openUrl]
   * @param {string} opts.studentName
   * @param {string} [opts.studentPhotoUrl]
   * @param {string} opts.csrfToken
   * @param {function(string):void} [opts.onError]
   */
  function mountSecureMediaPlayer(opts) {
    ensureStyles();
    var container = opts.container;
    container.innerHTML = '';
    container.style.position = container.style.position || 'relative';

    var root = document.createElement('div');
    root.className = 'smp-root';
    container.appendChild(root);

    var stage = document.createElement('div');
    stage.className = 'smp-stage';
    root.appendChild(stage);

    var cleanup = [];
    var engineApi = null;
    var destroyed = false;

    var loader = mountLoading(root);
    var fullscreen = bindStageFullscreen(root);
    var controls = mountControls(root, {
      togglePlay: function () { if (engineApi) engineApi.togglePlay(); },
      toggleMute: function () { return engineApi ? engineApi.toggleMute() : false; },
      seek: function (ratio) { if (engineApi) engineApi.seek(ratio); },
      seekOffset: function (seconds) { if (engineApi && engineApi.seekOffset) engineApi.seekOffset(seconds); },
      setSpeed: function (rate) { if (engineApi && engineApi.setSpeed) engineApi.setSpeed(rate); },
      toggleFullscreen: function () { fullscreen.toggle(); },
    });

    cleanup.push(applyPageDeterrents(root));
    if (global.mountSecureWatermark) {
      cleanup.push(global.mountSecureWatermark(root, opts.studentName, opts.studentPhotoUrl, { className: 'video-watermark-overlay' }));
    }
    cleanup.push(function () { controls.destroy(); });
    cleanup.push(function () { fullscreen.destroy(); });
    cleanup.push(function () { loader.destroy(); });

    var ui = { loader: loader, controls: controls, fullscreen: fullscreen };
    var localOpts = Object.assign({}, opts, { stage: stage });

    function fail(message) {
      loader.destroy();
      if (opts.onError) opts.onError(message || 'تعذّر تشغيل الوسائط');
    }

    function boot(payload) {
      var kind = payload.kind;
      var starter;
      if (kind === 'mp4') {
        if (!opts.startUrl) throw new Error('مسار تشغيل الفيديو غير متوفر');
        starter = engineMp4(root, localOpts, ui);
      } else if (kind === 'youtube') {
        starter = engineYoutube(root, localOpts, ui, payload.youtube_id || opts.youtubeId);
      } else if (kind === 'drive') {
        starter = engineDrive(root, localOpts, ui, payload.embed_url || opts.embedUrl);
      } else if (kind === 'external') {
        starter = engineExternal(root, localOpts, ui, payload.open_url || opts.openUrl);
      } else {
        throw new Error('نوع الوسائط غير مدعوم');
      }

      return Promise.resolve(starter).then(function (api) {
        if (destroyed) {
          if (api && api.destroy) api.destroy();
          return;
        }
        engineApi = api;
        cleanup.push(function () { if (api && api.destroy) api.destroy(); });
      });
    }

    var ready = Promise.resolve()
      .then(function () {
        if (opts.kind) {
          return {
            kind: opts.kind,
            youtube_id: opts.youtubeId,
            embed_url: opts.embedUrl,
            open_url: opts.openUrl,
          };
        }
        if (!opts.resolveUrl) throw new Error('تعذّر تحديد نوع الوسائط');
        return fetch(opts.resolveUrl, {
          credentials: 'same-origin',
          headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': opts.csrfToken },
        }).then(function (res) {
          if (!res.ok) throw new Error('غير مصرح بتشغيل هذا المحتوى');
          return res.json();
        });
      })
      .then(boot)
      .catch(function (err) {
        if (!destroyed) fail(err.message || 'حدث خطأ أثناء التشغيل');
      });

    return function destroy() {
      destroyed = true;
      cleanup.slice().reverse().forEach(function (fn) {
        try { fn(); } catch (e) {}
      });
      container.innerHTML = '';
    };
  }

  // Backward-compatible wrapper used by older call sites.
  function mountSecureVideoPlayer(legacyOpts) {
    return mountSecureMediaPlayer({
      container: legacyOpts.container,
      resourceId: legacyOpts.resourceId,
      kind: 'mp4',
      startUrl: legacyOpts.startUrl,
      studentName: legacyOpts.studentName,
      studentPhotoUrl: legacyOpts.studentPhotoUrl,
      csrfToken: legacyOpts.csrfToken,
      onError: legacyOpts.onError,
    });
  }

  global.mountSecureMediaPlayer = mountSecureMediaPlayer;
  global.mountSecureVideoPlayer = mountSecureVideoPlayer;
})(window);
