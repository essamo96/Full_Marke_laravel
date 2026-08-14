<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $resource->title }}</title>
    <style>
        html, body { margin: 0; padding: 0; width: 100%; height: 100%; overflow: hidden; background: #000; }
        #embedPlayerRoot { position: absolute; inset: 0; }
    </style>
</head>
<body oncontextmenu="return false;" ondragstart="return false;">
    <div id="embedPlayerRoot"></div>
    <script src="{{ asset_ver('assets/js/secure-watermark.js') }}"></script>
    <script src="{{ asset_ver('assets/js/student-secure-media-player.js') }}"></script>
    <script>
        (function () {
            var payload = @json($payload);
            var opts = {
                container: document.getElementById('embedPlayerRoot'),
                resourceId: @json($resource->getRouteKey()),
                kind: payload.kind,
                youtubeId: payload.youtube_id || null,
                embedUrl: payload.embed_url || null,
                openUrl: payload.open_url || null,
                startUrl: '{{ route('student.video.start', $resource) }}',
                studentName: @json($student->full_name_ar ?? $student->full_name_en ?? $student->name),
                studentPhotoUrl: @json($student->photo_url),
                csrfToken: document.querySelector('meta[name="csrf-token"]').content,
                onError: function (message) {
                    document.getElementById('embedPlayerRoot').innerHTML =
                        '<div style="color:#fff;display:flex;align-items:center;justify-content:center;height:100%;font-family:system-ui,sans-serif;">' +
                        (message || 'تعذّر التشغيل') + '</div>';
                },
            };
            mountSecureMediaPlayer(opts);

            document.addEventListener('keydown', function (e) {
                if (e.keyCode === 123) e.preventDefault();
                if (e.ctrlKey && e.shiftKey && (e.keyCode === 73 || e.keyCode === 74 || e.keyCode === 67)) e.preventDefault();
                if (e.ctrlKey && (e.keyCode === 85 || e.keyCode === 83)) e.preventDefault();
            });
        })();
    </script>
</body>
</html>
