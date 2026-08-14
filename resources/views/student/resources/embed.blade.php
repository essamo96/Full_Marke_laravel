<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $resource->title }}</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            background-color: #000;
            -webkit-user-select: none;
            user-select: none;
        }
        iframe, video {
            width: 100%;
            height: 100%;
            border: none;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 1;
        }
        .embed-stage {
            position: relative;
            width: 100%;
            height: 100%;
            background: #000;
        }
        .embed-shield {
            position: absolute;
            z-index: 20;
            background: transparent;
            cursor: not-allowed;
        }
        .embed-shield-drive-tr {
            top: 0;
            right: 0;
            width: 96px;
            height: 96px;
        }

        /* ── Protected YouTube player (custom chrome, no native YT UI clicks) ── */
        #ytPlayerHost {
            position: absolute;
            inset: 0;
            z-index: 1;
        }
        #ytPlayerHost iframe,
        #ytPlayerFrame {
            width: 100% !important;
            height: 100% !important;
            position: absolute;
            inset: 0;
            border: 0;
            /* Clicks never reach YouTube title / logo / share / open-in-YT */
            pointer-events: none !important;
        }
        .yt-click-blocker {
            position: absolute;
            inset: 0;
            z-index: 5;
            background: transparent;
        }
        .yt-controls {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 30;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            background: linear-gradient(transparent, rgba(0,0,0,0.85));
            color: #fff;
            font-family: system-ui, sans-serif;
            font-size: 13px;
        }
        .yt-controls button {
            appearance: none;
            border: 0;
            background: rgba(255,255,255,0.12);
            color: #fff;
            width: 36px;
            height: 36px;
            border-radius: 999px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .yt-controls button:hover {
            background: rgba(230, 35, 30, 0.85);
        }
        .yt-progress {
            flex: 1;
            height: 6px;
            border-radius: 999px;
            accent-color: #e6231e;
            cursor: pointer;
        }
        .yt-time {
            min-width: 92px;
            text-align: center;
            opacity: 0.9;
            font-variant-numeric: tabular-nums;
            flex-shrink: 0;
        }
        .yt-big-play {
            position: absolute;
            inset: 0;
            z-index: 15;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0,0,0,0.25);
            cursor: pointer;
        }
        .yt-big-play.hidden { display: none; }
        .yt-big-play span {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: rgba(230, 35, 30, 0.92);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 28px rgba(0,0,0,0.45);
            font-size: 28px;
            color: #fff;
            padding-inline-start: 4px;
        }
        .yt-loading {
            position: absolute;
            inset: 0;
            z-index: 40;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: #000;
            font-family: system-ui, sans-serif;
        }
        .yt-loading.hidden { display: none; }
    </style>
</head>
<body oncontextmenu="return false;" ondragstart="return false;">
    <script src="{{ asset('assets/js/secure-watermark.js') }}"></script>
    <script>
        mountSecureWatermark(
            document.body,
            @json($student->full_name_ar ?? $student->full_name_en),
            @json($student->photo_url)
        );
    </script>

    @php
        $embedUrl = $resource->url;
        $isYoutube = false;
        $isDrive = false;
        $youtubeId = null;

        if ($embedUrl && !preg_match('#^https?://#i', $embedUrl)) {
            $embedUrl = 'https://' . ltrim($embedUrl, '/');
        }

        if (preg_match('#drive\.google\.com/file/d/([a-zA-Z0-9_-]+)#', $embedUrl, $matches)) {
            $embedUrl = 'https://drive.google.com/file/d/' . $matches[1] . '/preview';
            $isDrive = true;
        } elseif (preg_match('#drive\.google\.com/(?:open|uc)\?(?:export=\w+&)?id=([a-zA-Z0-9_-]+)#', $embedUrl, $matches)) {
            $embedUrl = 'https://drive.google.com/file/d/' . $matches[1] . '/preview';
            $isDrive = true;
        } elseif (preg_match('#docs\.google\.com/(?:document|spreadsheets|presentation)/d/([a-zA-Z0-9_-]+)#', $embedUrl, $matches)) {
            $parsed = parse_url($embedUrl);
            $embedUrl = $parsed['scheme'] . '://' . $parsed['host'] . $parsed['path'];
            $embedUrl = preg_replace('#/(edit|view).*$#', '/preview', $embedUrl);
            if (!str_ends_with($embedUrl, '/preview')) {
                $embedUrl = rtrim($embedUrl, '/') . '/preview';
            }
            $isDrive = true;
        } elseif (preg_match('#drive\.google\.com/drive/(?:u/\d+/)?folders/([a-zA-Z0-9_-]+)#', $embedUrl, $matches)) {
            $embedUrl = 'https://drive.google.com/embeddedfolderview?id=' . $matches[1] . '#list';
            $isDrive = true;
        } elseif (preg_match('#youtu(?:be\.com|\.be)#i', $embedUrl)) {
            if (preg_match('#[?&]v=([a-zA-Z0-9_-]{6,})#', $embedUrl, $matches)) {
                $youtubeId = $matches[1];
            } elseif (preg_match('#youtu\.be/([a-zA-Z0-9_-]{6,})#', $embedUrl, $matches)) {
                $youtubeId = $matches[1];
            } elseif (preg_match('#youtube\.com/(?:shorts|live|embed)/([a-zA-Z0-9_-]{6,})#', $embedUrl, $matches)) {
                $youtubeId = $matches[1];
            }

            if ($youtubeId) {
                $isYoutube = true;
            }
        }
    @endphp

    @if(($resource->type === 'link' || $resource->isExternalLink()) && $isYoutube && $youtubeId)
        <div class="embed-stage" id="ytProtectedStage" oncontextmenu="return false;">
            <div id="ytPlayerHost"><div id="ytPlayerFrame"></div></div>
            <div class="yt-click-blocker" aria-hidden="true"></div>
            <div class="yt-loading" id="ytLoading">جاري تجهيز المشغّل الآمن...</div>
            <div class="yt-big-play" id="ytBigPlay" title="تشغيل">
                <span>▶</span>
            </div>
            <div class="yt-controls" id="ytControls">
                <button type="button" id="ytPlayBtn" title="تشغيل / إيقاف" aria-label="Play">▶</button>
                <input type="range" id="ytProgress" class="yt-progress" min="0" max="1000" value="0" step="1" aria-label="Seek">
                <div class="yt-time" id="ytTime">0:00 / 0:00</div>
                <button type="button" id="ytMuteBtn" title="كتم الصوت" aria-label="Mute">🔊</button>
                <button type="button" id="ytFsBtn" title="ملء الشاشة داخل المنصة" aria-label="Fullscreen">⛶</button>
            </div>
        </div>

        <script>
        (function () {
            var VIDEO_ID = @json($youtubeId);
            var player = null;
            var pollTimer = null;
            var seeking = false;

            var loadingEl = document.getElementById('ytLoading');
            var bigPlay = document.getElementById('ytBigPlay');
            var playBtn = document.getElementById('ytPlayBtn');
            var progress = document.getElementById('ytProgress');
            var timeEl = document.getElementById('ytTime');
            var muteBtn = document.getElementById('ytMuteBtn');
            var fsBtn = document.getElementById('ytFsBtn');
            var stage = document.getElementById('ytProtectedStage');

            function formatTime(totalSeconds) {
                totalSeconds = Math.max(0, Math.floor(totalSeconds || 0));
                var h = Math.floor(totalSeconds / 3600);
                var m = Math.floor((totalSeconds % 3600) / 60);
                var s = totalSeconds % 60;
                var mm = (h > 0 ? String(m).padStart(2, '0') : String(m));
                var ss = String(s).padStart(2, '0');
                return h > 0 ? (h + ':' + mm + ':' + ss) : (mm + ':' + ss);
            }

            function hardenIframe() {
                var iframe = document.querySelector('#ytPlayerHost iframe') || document.getElementById('ytPlayerFrame');
                if (!iframe || iframe.tagName !== 'IFRAME') return;
                iframe.style.pointerEvents = 'none';
                iframe.setAttribute('tabindex', '-1');
                iframe.removeAttribute('allowfullscreen');
            }

            function setPlayingUi(isPlaying) {
                playBtn.textContent = isPlaying ? '❚❚' : '▶';
                bigPlay.classList.toggle('hidden', isPlaying);
            }

            function togglePlay() {
                if (!player || typeof player.getPlayerState !== 'function') return;
                var state = player.getPlayerState();
                if (state === YT.PlayerState.PLAYING) {
                    player.pauseVideo();
                } else {
                    player.playVideo();
                }
            }

            function updateProgress() {
                if (!player || seeking || typeof player.getCurrentTime !== 'function') return;
                var current = player.getCurrentTime() || 0;
                var duration = player.getDuration() || 0;
                if (duration > 0) {
                    progress.value = String(Math.round((current / duration) * 1000));
                }
                timeEl.textContent = formatTime(current) + ' / ' + formatTime(duration);
            }

            function requestStageFullscreen() {
                var el = stage;
                var req = el.requestFullscreen || el.webkitRequestFullscreen || el.msRequestFullscreen;
                if (req) req.call(el).catch(function () {});
            }

            function exitFullscreen() {
                var exit = document.exitFullscreen || document.webkitExitFullscreen || document.msExitFullscreen;
                if (exit) exit.call(document);
            }

            function toggleFullscreen() {
                var fs = document.fullscreenElement || document.webkitFullscreenElement;
                if (fs) exitFullscreen();
                else requestStageFullscreen();
            }

            window.onYouTubeIframeAPIReady = function () {
                player = new YT.Player('ytPlayerFrame', {
                    width: '100%',
                    height: '100%',
                    videoId: VIDEO_ID,
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
                            loadingEl.classList.add('hidden');
                            hardenIframe();
                            setInterval(hardenIframe, 1000);
                            pollTimer = setInterval(updateProgress, 400);
                            setPlayingUi(false);
                        },
                        onStateChange: function (event) {
                            hardenIframe();
                            if (event.data === YT.PlayerState.PLAYING) setPlayingUi(true);
                            if (event.data === YT.PlayerState.PAUSED || event.data === YT.PlayerState.ENDED) {
                                setPlayingUi(false);
                            }
                        },
                        onError: function () {
                            loadingEl.textContent = 'تعذّر تشغيل الفيديو داخل المنصة';
                            loadingEl.classList.remove('hidden');
                        },
                    },
                });
            };

            bigPlay.addEventListener('click', togglePlay);
            playBtn.addEventListener('click', togglePlay);
            muteBtn.addEventListener('click', function () {
                if (!player) return;
                if (player.isMuted()) {
                    player.unMute();
                    muteBtn.textContent = '🔊';
                } else {
                    player.mute();
                    muteBtn.textContent = '🔇';
                }
            });
            fsBtn.addEventListener('click', toggleFullscreen);

            progress.addEventListener('pointerdown', function () { seeking = true; });
            progress.addEventListener('pointerup', function () { seeking = false; });
            progress.addEventListener('change', function () {
                if (!player || typeof player.getDuration !== 'function') return;
                var duration = player.getDuration() || 0;
                var next = (Number(progress.value) / 1000) * duration;
                player.seekTo(next, true);
                seeking = false;
            });

            // Space / arrows stay inside our controls — never forwarded to YT chrome.
            document.addEventListener('keydown', function (e) {
                if (e.code === 'Space') {
                    e.preventDefault();
                    togglePlay();
                } else if (e.code === 'KeyF') {
                    e.preventDefault();
                    toggleFullscreen();
                } else if (e.code === 'ArrowRight' && player) {
                    e.preventDefault();
                    player.seekTo((player.getCurrentTime() || 0) + 5, true);
                } else if (e.code === 'ArrowLeft' && player) {
                    e.preventDefault();
                    player.seekTo(Math.max(0, (player.getCurrentTime() || 0) - 5), true);
                }
            });

            var tag = document.createElement('script');
            tag.src = 'https://www.youtube.com/iframe_api';
            document.head.appendChild(tag);
        })();
        </script>

    @elseif($resource->type === 'link' || $resource->isExternalLink())
        <div class="embed-stage" oncontextmenu="return false;">
            <iframe
                id="secureEmbedFrame"
                src="{{ $embedUrl }}"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                referrerpolicy="strict-origin-when-cross-origin"
                oncontextmenu="return false;"
            ></iframe>
            <div class="embed-shield embed-shield-drive-tr" title="{{ __('app.protected') }}" oncontextmenu="return false;"></div>
        </div>
    @elseif($resource->type === 'video' && $resource->url)
        <video id="embedVideoEl" controls controlsList="{{ $resource->allow_download ? '' : 'nodownload' }}" playsinline>
            <source src="{{ route('student.resources.file', $resource) }}" type="video/mp4">
        </video>
        <script>
            (function () {
                var videoEl = document.getElementById('embedVideoEl');
                function onFullscreenChange() {
                    var fsEl = document.fullscreenElement || document.webkitFullscreenElement;
                    if (fsEl !== videoEl) return;
                    var exit = document.exitFullscreen
                        ? document.exitFullscreen()
                        : (document.webkitExitFullscreen ? Promise.resolve(document.webkitExitFullscreen()) : Promise.resolve());
                    Promise.resolve(exit).catch(function () {}).then(function () {
                        var request = document.body.requestFullscreen || document.body.webkitRequestFullscreen;
                        if (request) request.call(document.body).catch(function () {});
                    });
                }
                document.addEventListener('fullscreenchange', onFullscreenChange);
                document.addEventListener('webkitfullscreenchange', onFullscreenChange);
            })();
        </script>
    @else
        <div style="color: white; text-align: center; padding-top: 20%;">
            <h3>{{ __('app.not_found') }}</h3>
        </div>
    @endif

    <script>
        document.addEventListener('contextmenu', function (e) {
            e.preventDefault();
            return false;
        });

        document.addEventListener('dragstart', function (e) {
            e.preventDefault();
            return false;
        });

        document.addEventListener('keydown', function (e) {
            if (e.keyCode === 123) {
                e.preventDefault();
                return false;
            }
            if (e.ctrlKey && e.shiftKey && (e.keyCode === 73 || e.keyCode === 74 || e.keyCode === 67)) {
                e.preventDefault();
                return false;
            }
            if (e.ctrlKey && (e.keyCode === 85 || e.keyCode === 83)) {
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>
