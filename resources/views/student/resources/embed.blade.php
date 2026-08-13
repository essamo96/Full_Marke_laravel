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
    </style>
</head>
<body oncontextmenu="return false;">
    <script src="{{ asset('assets/js/secure-watermark.js') }}"></script>
    <script>
        // Same canonical roam-then-lock canvas watermark used by the video/document/image
        // viewers, so every attachment type shows an identical, consistently-styled mark.
        mountSecureWatermark(
            document.body,
            @json($student->full_name_ar ?? $student->full_name_en),
            @json($student->photo_url)
        );
    </script>

    @php
        $embedUrl = $resource->url;

        // A link pasted without a scheme (e.g. "youtube.com/watch?v=...")
        // would otherwise be treated as relative to our own domain by the
        // <iframe src="">, silently loading nothing from our own site
        // instead of the intended external page.
        if ($embedUrl && !preg_match('#^https?://#i', $embedUrl)) {
            $embedUrl = 'https://' . ltrim($embedUrl, '/');
        }

        // Fix Google Drive links for iframe embedding (Extract ID and force /preview)
        // Matches /file/d/ID
        if (preg_match('#drive\.google\.com/file/d/([a-zA-Z0-9_-]+)#', $embedUrl, $matches)) {
            $embedUrl = 'https://drive.google.com/file/d/' . $matches[1] . '/preview';
        } 
        // Matches /open?id=ID or /uc?id=ID
        elseif (preg_match('#drive\.google\.com/(?:open|uc)\?(?:export=\w+&)?id=([a-zA-Z0-9_-]+)#', $embedUrl, $matches)) {
            $embedUrl = 'https://drive.google.com/file/d/' . $matches[1] . '/preview';
        }
        // Matches Google Docs/Sheets/Slides
        elseif (preg_match('#docs\.google\.com/(?:document|spreadsheets|presentation)/d/([a-zA-Z0-9_-]+)#', $embedUrl, $matches)) {
            // keep the base url and append /preview
            $parsed = parse_url($embedUrl);
            $embedUrl = $parsed['scheme'] . '://' . $parsed['host'] . $parsed['path'];
            $embedUrl = preg_replace('#/(edit|view).*$#', '/preview', $embedUrl);
            if (!str_ends_with($embedUrl, '/preview')) {
                $embedUrl = rtrim($embedUrl, '/') . '/preview';
            }
        }
        // Matches Drive Folders
        elseif (preg_match('#drive\.google\.com/drive/(?:u/\d+/)?folders/([a-zA-Z0-9_-]+)#', $embedUrl, $matches)) {
            $embedUrl = 'https://drive.google.com/embeddedfolderview?id=' . $matches[1] . '#list';
        }

        // Fix YouTube links for iframe embedding. Covers every URL shape
        // YouTube itself hands out (watch, youtu.be share links, Shorts,
        // live, mobile m.youtube.com, and already-correct /embed/ links),
        // since a non-/embed/ URL simply refuses to load in an iframe
        // (YouTube's own watch/shorts/live pages send X-Frame-Options) and
        // shows the visitor a generic "playback error" instead of our
        // content — the symptom reported when a Shorts/live link was pasted
        // as an "external link" resource.
        elseif (preg_match('#youtu(?:be\.com|\.be)#i', $embedUrl)) {
            $videoId = null;

            if (preg_match('#[?&]v=([a-zA-Z0-9_-]{6,})#', $embedUrl, $matches)) {
                $videoId = $matches[1];
            } elseif (preg_match('#youtu\.be/([a-zA-Z0-9_-]{6,})#', $embedUrl, $matches)) {
                $videoId = $matches[1];
            } elseif (preg_match('#youtube\.com/(?:shorts|live|embed)/([a-zA-Z0-9_-]{6,})#', $embedUrl, $matches)) {
                $videoId = $matches[1];
            }

            if ($videoId) {
                $embedUrl = 'https://www.youtube.com/embed/' . $videoId . '?rel=0';
            }
        }
    @endphp

    @if($resource->type === 'link' || $resource->isExternalLink())
        <div style="position: relative; width: 100%; height: 100%;" oncontextmenu="return false;">
            <iframe src="{{ $embedUrl }}" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen oncontextmenu="return false;"></iframe>
            {{-- Shield to block the 'Open in new window' button in Google Drive (top right) --}}
            <div style="position: absolute; top: 0; right: 0; width: 80px; height: 80px; z-index: 9999; background: transparent; cursor: not-allowed;" title="{{ __('app.protected') }}" oncontextmenu="return false;"></div>
        </div>
    @elseif($resource->type === 'video' && $resource->url)
        <video id="embedVideoEl" controls controlsList="{{ $resource->allow_download ? '' : 'nodownload' }}" playsinline>
            <source src="{{ route('student.resources.file', $resource) }}" type="video/mp4">
        </video>
        <script>
            // Native video fullscreen leaves the sibling .watermark div behind (it's not
            // a child of <video>), so the watermark visually vanishes when zoomed.
            // Redirect fullscreen to <body> instead, which contains both.
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
        // Disable common developer tools shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.keyCode === 123) { // F12
                e.preventDefault();
                return false;
            }
            if (e.ctrlKey && e.shiftKey && (e.keyCode === 73 || e.keyCode === 74 || e.keyCode === 67)) { // Ctrl+Shift+I/J/C
                e.preventDefault();
                return false;
            }
            if (e.ctrlKey && e.keyCode === 85) { // Ctrl+U (view source)
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>
