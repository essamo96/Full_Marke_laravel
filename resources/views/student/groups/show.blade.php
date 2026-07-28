@extends('layouts.student')

@section('title', $group->name . ' | FULL MARK ACADEMY')
@section('page_title_en', $subject->name)
@section('page_title_ar', $subject->name)

@push('styles')
<style>
/* Custom styles for the course viewer */
.curriculum-accordion .accordion-item {
    background: transparent;
    border: none;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
.curriculum-accordion .accordion-button {
    background: transparent;
    color: var(--text-primary);
    box-shadow: none;
    padding: 1rem;
}
.curriculum-accordion .accordion-button:not(.collapsed) {
    color: var(--accent-color);
    background: rgba(255,255,255,0.02);
}
.curriculum-accordion .accordion-button::after {
    filter: invert(1) grayscale(100%) brightness(200%);
}
.resource-item {
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: var(--text-primary);
    text-decoration: none;
}
.resource-item:hover, .resource-item.active {
    background: rgba(212, 175, 55, 0.1);
    color: var(--accent-color);
}
.video-player-container {
    width: 100%;
    aspect-ratio: 16 / 9;
    background: #000;
    border-radius: 1rem;
    overflow: hidden;
    position: relative;
    border: 1px solid rgba(255,255,255,0.1);
}
/* Tailwind's CDN build also ships a `.collapse { visibility: collapse }` utility
   (for table rows), which collides with Bootstrap's `.collapse` accordion class
   and hides the panel body even after Bootstrap adds `.show`. Force it back. */
.curriculum-accordion .accordion-collapse {
    visibility: visible !important;
}
</style>
@endpush

@section('content')

@if($notes->isNotEmpty())
<div class="glass-panel rounded-4 p-4 mb-4">
    <h5 class="fw-bold mb-3" style="color: var(--text-primary);" data-en="Group Notes" data-ar="ملاحظات المجموعة">ملاحظات المجموعة</h5>
    <div class="d-flex flex-column gap-2">
        @foreach($notes as $note)
            @if($note->is_alert)
                <div class="rounded-3 p-3" style="background: rgba(220,53,69,0.12); border-inline-start: 3px solid #dc3545;">
                    <div class="fw-bold fs-7 mb-1 text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $note->title }}</div>
                    <div class="text-sm text-white">{{ $note->content }}</div>
                    <div class="text-muted fs-7 mt-1">{{ $note->created_at->diffForHumans() }}</div>
                </div>
            @else
                <div class="rounded-3 p-3" style="background: var(--bg-secondary);">
                    <div class="fw-bold fs-7 mb-1" style="color: var(--accent-color);">{{ $note->title }}</div>
                    <div class="text-sm text-white">{{ $note->content }}</div>
                    <div class="text-muted fs-7 mt-1">{{ $note->created_at->diffForHumans() }}</div>
                </div>
            @endif
        @endforeach
    </div>
</div>
@endif

<div class="row g-4 h-100 fade-in-up">
    <!-- Left Sidebar: Curriculum -->
    <div class="col-lg-4 col-xl-3 order-2 order-lg-1">
        <div class="glass-panel rounded-4 h-100 p-0 overflow-hidden d-flex flex-column" style="max-height: calc(100vh - 120px);">
            <div class="p-4 border-bottom border-white/10 bg-pattern-gold position-relative">
                <div class="position-absolute top-0 end-0 w-100 h-100 bg-gold/10 blur-[80px]"></div>
                <h5 class="fw-bold mb-1 position-relative z-1" style="color: var(--accent-color);">{{ $subject->name }}</h5>
                <p class="text-sm opacity-75 mb-0 position-relative z-1 text-white">{{ $group->name }}</p>
            </div>
            
            <div class="overflow-auto flex-grow-1 p-2" id="curriculumContainer">
                @if($generalResources->isNotEmpty())
                    <div class="mb-3">
                        <div class="px-3 py-2 text-xs fw-bold text-uppercase opacity-50" style="color: var(--text-primary);" data-en="General Resources" data-ar="موارد عامة">موارد عامة</div>
                        @foreach($generalResources as $resource)
                            @php
                                $isUrl = \Illuminate\Support\Str::startsWith($resource->url, ['http://', 'https://']);
                                $isPdf = $resource->type === 'document' && !$isUrl && strtolower(pathinfo($resource->url ?? '', PATHINFO_EXTENSION)) === 'pdf';
                            @endphp
                            <a href="javascript:void(0)" class="resource-item" onclick="loadResource({{ json_encode(['id' => $resource->getRouteKey(), 'title' => $resource->title, 'type' => $resource->type, 'is_pdf' => $isPdf, 'is_image' => $resource->isImage(), 'is_external' => $isUrl, 'url' => $isUrl ? $resource->url : null, 'description' => $resource->description]) }})">
                                <i class="bi bi-{{ $resource->type === 'video' ? 'play-circle-fill text-danger' : ($resource->type === 'zoom' ? 'camera-video-fill text-primary' : 'file-earmark-text-fill text-info') }}"></i>
                                <span class="text-sm">{{ $resource->title }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif

                <div class="accordion curriculum-accordion" id="stagesAccordion">
                    @forelse($subject->stages as $stageIndex => $stage)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingStage{{ $stage->id }}">
                                <button class="accordion-button {{ $stageIndex === 0 ? '' : 'collapsed' }} fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseStage{{ $stage->id }}" aria-expanded="{{ $stageIndex === 0 ? 'true' : 'false' }}">
                                    {{ $stage->name }}
                                </button>
                            </h2>
                            <div id="collapseStage{{ $stage->id }}" class="accordion-collapse collapse {{ $stageIndex === 0 ? 'show' : '' }}">
                                <div class="accordion-body p-0 pb-2">
                                    @foreach($stage->units as $unit)
                                        <div class="px-3 py-2 text-xs fw-bold text-uppercase opacity-50 mt-2" style="color: var(--text-primary);">{{ $unit->name }}</div>
                                        @foreach($unit->lessons as $lesson)
                                            <div class="ps-3 pe-2 py-1">
                                                <div class="text-sm fw-medium mb-1" style="color: var(--text-primary);">{{ $lesson->name }}</div>
                                                @foreach($lesson->resources as $resource)
                                                    @php
                                                      $isUrl = \Illuminate\Support\Str::startsWith($resource->url, ['http://', 'https://']);
                                                      $isPdf = $resource->type === 'document' && !$isUrl && strtolower(pathinfo($resource->url ?? '', PATHINFO_EXTENSION)) === 'pdf';
                                                    @endphp
                                                    <a href="javascript:void(0)" class="resource-item ps-4" onclick="loadResource({{ json_encode(['id' => $resource->getRouteKey(), 'title' => $resource->title, 'type' => $resource->type, 'is_pdf' => $isPdf, 'is_image' => $resource->isImage(), 'is_external' => $isUrl, 'url' => $isUrl ? $resource->url : null, 'description' => $resource->description]) }})">
                                                        <i class="bi bi-{{ $resource->type === 'video' ? 'play-circle-fill text-danger' : ($resource->type === 'zoom' ? 'camera-video-fill text-primary' : 'file-earmark-text-fill text-info') }}"></i>
                                                        <span class="text-sm">{{ $resource->title }}</span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @empty
                        @if($generalResources->isEmpty())
                            <div class="p-4 text-center opacity-50 text-white">
                                <i class="bi bi-journal-x fs-2 d-block mb-2"></i>
                                <span data-en="No materials available" data-ar="لا يوجد مواد دراسية">لا يوجد مواد دراسية</span>
                            </div>
                        @endif
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Right Content: Player -->
    <div class="col-lg-8 col-xl-9 order-1 order-lg-2 mb-4 mb-lg-0">
        <div class="glass-panel rounded-4 h-100 p-4 p-md-5 d-flex flex-column align-items-center justify-content-center text-center glow-card" id="playerContainer">
            <div id="emptyState">
                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px; background: rgba(197, 168, 128, 0.1); color: var(--accent-color);">
                    <i class="bi bi-play-btn-fill fs-1"></i>
                </div>
                <h3 class="fw-bold mb-2" style="color: var(--text-primary);" data-en="Select a lesson to start" data-ar="اختر درساً للبدء">اختر درساً للبدء</h3>
                <p class="opacity-75 text-white" data-en="Choose a video or document from the curriculum sidebar." data-ar="اختر فيديو أو ملفاً من القائمة الجانبية.">اختر فيديو أو ملفاً من القائمة الجانبية.</p>
            </div>
            
            <div id="contentViewer" class="w-100 d-none text-start">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold mb-0" id="contentTitle" style="color: var(--text-primary);"></h2>
                    <span id="contentTypeBadge" class="badge bg-gold text-dark px-3 py-2 rounded-pill"></span>
                </div>
                
                <div id="videoWrapper" class="video-player-container d-none mb-4 shadow-lg">
                    <video id="videoPlayer" controls controlsList="nodownload" oncontextmenu="return false;" class="w-100 h-100" style="object-fit: contain;">
                        <source src="" type="video/mp4">
                        Your browser does not support HTML video.
                    </video>
                </div>

                <div id="documentWrapper" class="d-none mb-4 rounded-4" style="background: #1a1a1a; min-height: 60vh; padding: 10px; overflow: auto;">
                    <div id="documentContainer" style="position: relative; width: 100%; min-height: 55vh;">
                        <div class="text-center text-white-50 py-5" id="documentLoading">جاري تحميل الملف الآمن...</div>
                    </div>
                    <p id="documentError" class="text-danger mt-3 mb-0 d-none px-3"></p>
                </div>

                <div id="imageWrapper" class="d-none mb-4 rounded-4 d-flex align-items-center justify-content-center" style="background: #1a1a1a; min-height: 40vh; padding: 10px; overflow: auto;">
                    <div id="imageContainer" style="position: relative; width: 100%; min-height: 35vh; display: flex; align-items: center; justify-content: center;">
                        <div class="text-center text-white-50 py-5" id="imageLoading">جاري تحميل الصورة الآمنة...</div>
                    </div>
                </div>

                <div id="otherFileWrapper" class="d-none mb-4 text-center py-5 rounded-4" style="background: rgba(255,255,255,0.02); border: 1px dashed rgba(197,168,128,0.3);">
                    <i class="bi bi-file-earmark-text-fill fs-1 d-block mb-3 text-info"></i>
                    <h4 class="fw-bold mb-3 text-white" data-en="Document Ready" data-ar="الملف جاهز للعرض">الملف جاهز للعرض</h4>
                    <a id="otherFileLink" href="#" target="_blank" rel="noopener" class="btn btn-info px-5 py-3 text-white rounded-pill">
                        <i class="bi bi-box-arrow-up-right me-2"></i> <span data-en="Open Document" data-ar="فتح الملف">فتح الملف</span>
                    </a>
                </div>

                <div id="zoomWrapper" class="d-none mb-4 text-center py-5 rounded-4" style="background: rgba(255,255,255,0.02); border: 1px dashed rgba(197,168,128,0.3);">
                    <i class="bi bi-camera-video-fill fs-1 d-block mb-3 text-primary"></i>
                    <h4 class="fw-bold mb-3 text-white" data-en="Live Session" data-ar="جلسة مباشرة">جلسة مباشرة</h4>
                    <a id="zoomLink" href="#" target="_blank" class="btn btn-primary px-5 py-3 rounded-pill">
                        <i class="bi bi-box-arrow-up-right me-2"></i> <span data-en="Open Link" data-ar="فتح الرابط">فتح الرابط</span>
                    </a>
                </div>

                <div id="iframeWrapper" class="d-none mb-4 shadow-lg" style="position: relative; width: 100%; aspect-ratio: 16/9; background: #000; border-radius: 12px; overflow: hidden;">
                    <iframe id="iframePlayer" style="width: 100%; height: 100%; border: 0;" allow="encrypted-media; picture-in-picture" allowfullscreen></iframe>
                </div>

                <div class="glass-panel rounded-4 p-4 mt-4 bg-pattern-gold" style="border: 1px solid rgba(255,255,255,0.1);">
                    <h5 class="fw-bold mb-2 text-white" data-en="Description" data-ar="الوصف">الوصف</h5>
                    <p id="contentDescription" class="opacity-75 mb-0 text-sm lh-lg text-white"></p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/vendor/pdfjs/pdf.min.js') }}"></script>
<script src="{{ asset('assets/js/student-document-viewer.js') }}"></script>
<script src="{{ asset('assets/js/student-image-viewer.js') }}"></script>
<script>
    var groupDocumentDestroy = null;
    var groupImageDestroy = null;

    function loadResource(resource) {
        // Highlight active item
        document.querySelectorAll('.resource-item').forEach(el => el.classList.remove('active'));
        if(event && event.currentTarget) {
            event.currentTarget.classList.add('active');
        }

        // Toggle visibility
        document.getElementById('emptyState').classList.add('d-none');
        document.getElementById('contentViewer').classList.remove('d-none');

        document.getElementById('videoWrapper').classList.add('d-none');
        document.getElementById('documentWrapper').classList.add('d-none');
        document.getElementById('imageWrapper').classList.add('d-none');
        document.getElementById('otherFileWrapper').classList.add('d-none');
        document.getElementById('zoomWrapper').classList.add('d-none');
        document.getElementById('iframeWrapper').classList.add('d-none');

        if (groupDocumentDestroy) { groupDocumentDestroy(); groupDocumentDestroy = null; }
        if (groupImageDestroy) { groupImageDestroy(); groupImageDestroy = null; }

        // Set Metadata
        document.getElementById('contentTitle').innerText = resource.title;
        const plainDescription = (resource.description || '').replace(/<[^>]*>/g, '').trim();
        document.getElementById('contentDescription').innerText = plainDescription || 'لا يوجد وصف متاح.';

        let badge = document.getElementById('contentTypeBadge');
        let playerContainer = document.getElementById('playerContainer');
        playerContainer.classList.remove('align-items-center', 'justify-content-center', 'text-center');
        playerContainer.classList.add('align-items-start');

        const videoPlayer = document.getElementById('videoPlayer');
        videoPlayer.pause();

        const iframePlayer = document.getElementById('iframePlayer');
        iframePlayer.src = 'about:blank';

        if (resource.type === 'video') {
            badge.innerText = 'فيديو';
            badge.className = 'badge bg-danger text-white px-3 py-2 rounded-pill';
            document.getElementById('videoWrapper').classList.remove('d-none');

            // Fetch secure stream URL
            fetch('{{ url("student/videos") }}/' + resource.id + '/start', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    videoPlayer.src = data.stream_url;
                    videoPlayer.load();
                }
            })
            .catch(err => console.error(err));

        } else if (resource.type === 'zoom') {
            badge.innerText = 'رابط خارجي';
            badge.className = 'badge bg-primary text-white px-3 py-2 rounded-pill';
            document.getElementById('zoomWrapper').classList.remove('d-none');
            document.getElementById('zoomLink').href = resource.url;
        } else if (resource.type === 'link') {
            badge.innerText = 'رابط محمي';
            badge.className = 'badge bg-primary text-white px-3 py-2 rounded-pill';
            document.getElementById('iframeWrapper').classList.remove('d-none');
            // Load the secure embed route
            iframePlayer.src = '{{ url("student/secure-embed") }}/' + resource.id;
        } else if (resource.is_pdf) {
            // Rendered in-page via the watermarked pdf.js canvas viewer — same
            // one used on the "Learning Resources" page — instead of opening
            // the raw file in a new browser tab.
            badge.innerText = 'ملف PDF';
            badge.className = 'badge bg-info text-dark px-3 py-2 rounded-pill';
            document.getElementById('documentWrapper').classList.remove('d-none');

            const container = document.getElementById('documentContainer');
            const errorEl = document.getElementById('documentError');
            container.innerHTML = '<div class="text-center text-white-50 py-5" id="documentLoading">جاري تحميل الملف الآمن...</div>';
            errorEl.classList.add('d-none');

            groupDocumentDestroy = mountSecureDocumentViewer({
                container: container,
                fileUrl: '{{ url('student/resources') }}/' + resource.id + '/file',
                studentName: @json(auth()->guard('student')->user()->name),
                studentPhotoUrl: @json(auth()->guard('student')->user()->image ? asset('storage/' . auth()->guard('student')->user()->image) : null),
                onLoaded: function () {
                    const loadingEl = document.getElementById('documentLoading');
                    if (loadingEl) loadingEl.remove();
                },
                onError: function (message) {
                    errorEl.textContent = message;
                    errorEl.classList.remove('d-none');
                },
            });
        } else if (resource.is_image) {
            badge.innerText = 'صورة';
            badge.className = 'badge bg-info text-dark px-3 py-2 rounded-pill';
            document.getElementById('imageWrapper').classList.remove('d-none');

            const container = document.getElementById('imageContainer');
            container.innerHTML = '<div class="text-center text-white-50 py-5" id="imageLoading">جاري تحميل الصورة الآمنة...</div>';

            groupImageDestroy = mountSecureImageViewer({
                container: container,
                fileUrl: '{{ url('student/resources') }}/' + resource.id + '/file',
                studentName: @json(auth()->guard('student')->user()->name),
                studentPhotoUrl: @json(auth()->guard('student')->user()->image ? asset('storage/' . auth()->guard('student')->user()->image) : null),
                onLoaded: function () {
                    const loadingEl = document.getElementById('imageLoading');
                    if (loadingEl) loadingEl.remove();
                },
                onError: function () {},
            });
        } else {
            // Office formats (doc/xlsx/ppt/...) have no in-page viewer, so they
            // still open via the authenticated file route in a new tab.
            badge.innerText = 'ملف مقروء';
            badge.className = 'badge bg-info text-dark px-3 py-2 rounded-pill';
            document.getElementById('otherFileWrapper').classList.remove('d-none');
            document.getElementById('otherFileLink').href = resource.is_external
                ? resource.url
                : '{{ url("student/resources") }}/' + resource.id + '/file';
        }
    }
</script>
@endpush
@endsection
