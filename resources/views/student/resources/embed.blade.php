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
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.15;
            pointer-events: none;
            font-size: 5vw;
            color: #ffffff;
            z-index: 9999;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            user-select: none;
            white-space: nowrap;
            font-family: sans-serif;
            font-weight: bold;
        }
        /* Moving watermark animation to make it harder to remove statically */
        @keyframes drift {
            0% { transform: translate(-50%, -50%) rotate(-5deg) scale(1); }
            50% { transform: translate(-50%, -60%) rotate(5deg) scale(1.1); }
            100% { transform: translate(-50%, -50%) rotate(-5deg) scale(1); }
        }
        .watermark {
            animation: drift 20s infinite ease-in-out;
        }
    </style>
</head>
<body oncontextmenu="return false;">
    <div class="watermark">
        {{ $student->full_name_ar ?? $student->full_name_en }}<br>
        {{ $student->phone ?? $student->id }}
    </div>

    @php
        $embedUrl = $resource->url;
        // Fix Google Drive links for iframe embedding (Extract ID and force /preview)
        if (preg_match('#drive\.google\.com/file/d/([a-zA-Z0-9_-]+)#', $embedUrl, $matches)) {
            $embedUrl = 'https://drive.google.com/file/d/' . $matches[1] . '/preview';
        } elseif (preg_match('#drive\.google\.com/open\?id=([a-zA-Z0-9_-]+)#', $embedUrl, $matches)) {
            $embedUrl = 'https://drive.google.com/file/d/' . $matches[1] . '/preview';
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
        <div style="position: relative; width: 100%; height: 100%;">
            <iframe src="{{ $embedUrl }}" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe>
            {{-- Shield to block the 'Open in new window' button in Google Drive (top right) --}}
            <div style="position: absolute; top: 0; right: 0; width: 80px; height: 80px; z-index: 9999; background: transparent; cursor: not-allowed;" title="{{ __('app.protected') }}"></div>
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
