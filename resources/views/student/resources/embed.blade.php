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
        .watermark {
            position: absolute;
            top: 15%;
            left: 5%;
            opacity: 0.25;
            pointer-events: none;
            font-size: 4vw;
            color: #ffffff;
            z-index: 9999;
            /* إضافة Stroke أسود حول النص */
            -webkit-text-stroke: 1.5px #000;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.8);
            user-select: none;
            white-space: nowrap;
            font-family: sans-serif;
            font-weight: bold;
            /* تتحرك مرة واحدة خلال أول 4 ثوانٍ فقط، ثم تثبت (انظر watermark.static أدناه) */
            animation: moveSideToSide 4s ease-in-out 1;
        }

        /* تحريك من اليسار لليمين والعكس دون الخروج من الشاشة */
        @keyframes moveSideToSide {
            0%, 100% {
                left: 5%;
                transform: translateX(0);
            }
            50% {
                left: 95%;
                transform: translateX(-100%);
            }
        }

        /* الحالة الثابتة: تُضاف بعد انتهاء الحركة عبر JS - حجم مصغر، خط نقي، الزاوية العلوية اليسرى */
        .watermark.static {
            animation: none;
            top: 12px;
            left: 12px;
            transform: none;
            opacity: 0.5;
            font-size: 13px;
            font-weight: 400;
            -webkit-text-stroke: 0.5px #000;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.7);
            font-family: 'Segoe UI', Tahoma, sans-serif;
        }
    </style>
</head>
<body oncontextmenu="return false;">
    <div class="watermark" id="videoWatermark">
        {{ $student->full_name_ar ?? $student->full_name_en }}<br>
        {{ $student->phone ?? $student->id }}
    </div>
    <script>
        // بعد انتهاء الحركة (4 ثوانٍ) تتحول العلامة المائية لحالة ثابتة مصغرة في الزاوية العلوية اليسرى
        setTimeout(function () {
            var wm = document.getElementById('videoWatermark');
            if (wm) wm.classList.add('static');
        }, 4000);
    </script>

    @php
        $embedUrl = $resource->url;
        
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

        // Fix YouTube links for iframe embedding
        elseif (str_contains($embedUrl, 'youtube.com/watch')) {
            parse_str(parse_url($embedUrl, PHP_URL_QUERY), $vars);
            if (isset($vars['v'])) {
                $embedUrl = 'https://www.youtube.com/embed/' . $vars['v'] . '?rel=0';
            }
        } elseif (str_contains($embedUrl, 'youtu.be/')) {
            $path = parse_url($embedUrl, PHP_URL_PATH);
            $embedUrl = 'https://www.youtube.com/embed' . $path . '?rel=0';
        }
    @endphp

    @if($resource->type === 'link' || $resource->isExternalLink())
        <div style="position: relative; width: 100%; height: 100%;" oncontextmenu="return false;">
            <iframe src="{{ $embedUrl }}" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen oncontextmenu="return false;"></iframe>
            {{-- Shield to block the 'Open in new window' button in Google Drive (top right) --}}
            <div style="position: absolute; top: 0; right: 0; width: 80px; height: 80px; z-index: 9999; background: transparent; cursor: not-allowed;" title="{{ __('app.protected') }}" oncontextmenu="return false;"></div>
        </div>
    @elseif($resource->type === 'video' && $resource->url)
        <video controls controlsList="{{ $resource->allow_download ? '' : 'nodownload' }}" playsinline>
            <source src="{{ route('student.resources.file', $resource) }}" type="video/mp4">
        </video>
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
