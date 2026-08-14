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
        }
        /* Transparent click-catchers over vendor chrome that would escape to
           Drive / YouTube. Students can still use the play/seek controls in
           the uncovered center and lower control strip. */
        .embed-shield {
            position: absolute;
            z-index: 9999;
            background: transparent;
            cursor: not-allowed;
        }
        .embed-shield-drive-tr {
            top: 0;
            right: 0;
            width: 96px;
            height: 96px;
        }
        /* YouTube: title / channel row that links out to youtube.com */
        .embed-shield-yt-top {
            top: 0;
            left: 0;
            right: 0;
            height: 58px;
        }
        /* YouTube: share / watch-on-YouTube affordances */
        .embed-shield-yt-tr {
            top: 0;
            right: 0;
            width: 120px;
            height: 72px;
        }
        /* YouTube logo in the native control bar (bottom-right) */
        .embed-shield-yt-br {
            right: 0;
            bottom: 0;
            width: 140px;
            height: 54px;
        }
        /* Thin bottom edge catcher for right-click on the control chrome */
        .embed-shield-yt-bottom {
            left: 0;
            right: 140px;
            bottom: 0;
            height: 12px;
            cursor: default;
        }
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
            $videoId = null;

            if (preg_match('#[?&]v=([a-zA-Z0-9_-]{6,})#', $embedUrl, $matches)) {
                $videoId = $matches[1];
            } elseif (preg_match('#youtu\.be/([a-zA-Z0-9_-]{6,})#', $embedUrl, $matches)) {
                $videoId = $matches[1];
            } elseif (preg_match('#youtube\.com/(?:shorts|live|embed)/([a-zA-Z0-9_-]{6,})#', $embedUrl, $matches)) {
                $videoId = $matches[1];
            }

            if ($videoId) {
                // nocookie + modest branding; keep playback inside the platform
                // embed. Sandbox below blocks popups to youtube.com.
                $embedUrl = 'https://www.youtube-nocookie.com/embed/' . $videoId
                    . '?rel=0&modestbranding=1&iv_load_policy=3&playsinline=1&fs=1&disablekb=0';
                $isYoutube = true;
            }
        }
    @endphp

    @if($resource->type === 'link' || $resource->isExternalLink())
        <div class="embed-stage" oncontextmenu="return false;">
            <iframe
                id="secureEmbedFrame"
                src="{{ $embedUrl }}"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
                allowfullscreen
                referrerpolicy="strict-origin-when-cross-origin"
                oncontextmenu="return false;"
                @if($isYoutube)
                    sandbox="allow-scripts allow-same-origin allow-presentation allow-forms"
                @endif
            ></iframe>

            @if($isYoutube)
                <div class="embed-shield embed-shield-yt-top" title="{{ __('app.protected') }}" oncontextmenu="return false;"></div>
                <div class="embed-shield embed-shield-yt-tr" title="{{ __('app.protected') }}" oncontextmenu="return false;"></div>
                <div class="embed-shield embed-shield-yt-br" title="{{ __('app.protected') }}" oncontextmenu="return false;"></div>
                <div class="embed-shield embed-shield-yt-bottom" oncontextmenu="return false;"></div>
            @elseif($isDrive)
                <div class="embed-shield embed-shield-drive-tr" title="{{ __('app.protected') }}" oncontextmenu="return false;"></div>
            @else
                <div class="embed-shield embed-shield-drive-tr" title="{{ __('app.protected') }}" oncontextmenu="return false;"></div>
            @endif
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

        // Block common "escape hatch" shortcuts; cannot stop screen capture.
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

        // If YouTube somehow navigates the iframe to a watch/share page, pull
        // it back — sandbox already blocks most popups, this is a safety net
        // for same-frame navigations we can observe.
        (function () {
            var frame = document.getElementById('secureEmbedFrame');
            if (!frame) return;
            frame.addEventListener('load', function () {
                try {
                    var href = frame.contentWindow.location.href;
                    if (/youtube\.com\/(watch|shorts|live)/i.test(href) || /youtu\.be\//i.test(href)) {
                        frame.src = @json($embedUrl);
                    }
                } catch (err) {
                    // Cross-origin embed (expected for YouTube/Drive) — ignore.
                }
            });
        })();
    </script>
</body>
</html>
