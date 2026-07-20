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
                                $resUrl = $isUrl ? $resource->url : asset('storage/'.$resource->url);
                            @endphp
                            <a href="javascript:void(0)" class="resource-item" onclick="loadResource({{ json_encode(['id' => $resource->getRouteKey(), 'title' => $resource->title, 'type' => $resource->type, 'url' => $resUrl, 'description' => $resource->description]) }})">
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
                                                      $resUrl = $isUrl ? $resource->url : asset('storage/'.$resource->url);
                                                    @endphp
                                                    <a href="javascript:void(0)" class="resource-item ps-4" onclick="loadResource({{ json_encode(['id' => $resource->getRouteKey(), 'title' => $resource->title, 'type' => $resource->type, 'url' => $resUrl, 'description' => $resource->description]) }})">
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

                <div id="documentWrapper" class="d-none mb-4 text-center py-5 rounded-4" style="background: rgba(255,255,255,0.02); border: 1px dashed rgba(197,168,128,0.3);">
                    <i class="bi bi-file-earmark-pdf-fill fs-1 d-block mb-3 text-info"></i>
                    <h4 class="fw-bold mb-3 text-white" data-en="Document Ready" data-ar="الملف جاهز للعرض">الملف جاهز للعرض</h4>
                    <a id="documentLink" href="#" target="_blank" class="btn btn-info px-5 py-3 text-white rounded-pill">
                        <i class="bi bi-box-arrow-up-right me-2"></i> <span data-en="Open Document" data-ar="فتح الملف">فتح الملف</span>
                    </a>
                </div>

                <div id="zoomWrapper" class="d-none mb-4 text-center py-5 rounded-4" style="background: rgba(255,255,255,0.02); border: 1px dashed rgba(197,168,128,0.3);">
                    <i class="bi bi-camera-video-fill fs-1 d-block mb-3 text-primary"></i>
                    <h4 class="fw-bold mb-3 text-white" data-en="External Link / Live Session" data-ar="رابط خارجي / جلسة مباشرة">رابط خارجي / جلسة مباشرة</h4>
                    <a id="zoomLink" href="#" target="_blank" class="btn btn-primary px-5 py-3 rounded-pill">
                        <i class="bi bi-box-arrow-up-right me-2"></i> <span data-en="Open Link" data-ar="فتح الرابط">فتح الرابط</span>
                    </a>
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
<script>
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
        document.getElementById('zoomWrapper').classList.add('d-none');

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
            
        } else if (resource.type === 'zoom' || resource.type === 'link') {
            badge.innerText = 'رابط خارجي';
            badge.className = 'badge bg-primary text-white px-3 py-2 rounded-pill';
            document.getElementById('zoomWrapper').classList.remove('d-none');
            document.getElementById('zoomLink').href = resource.url;
        } else {
            badge.innerText = 'ملف مقروء';
            badge.className = 'badge bg-info text-dark px-3 py-2 rounded-pill';
            document.getElementById('documentWrapper').classList.remove('d-none');
            document.getElementById('documentLink').href = resource.url;
        }
    }
</script>
@endpush
@endsection
