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
/* Tailwind's build also ships a `.collapse { visibility: collapse }` utility
   (for table rows), which collides with Bootstrap's `.collapse` class and
   hides panels even after Bootstrap adds `.show`. Force it back for every
   collapsible on this page (units, lessons, stages). */
.collapse {
    visibility: visible !important;
}

/* Header info chips */
.group-chip {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    padding: .45rem .9rem;
    border-radius: 50rem;
    background: var(--bg-secondary);
    border: 1px solid var(--separator-color);
    color: var(--text-primary);
    font-size: .82rem;
    font-weight: 600;
}
.group-chip i { color: var(--accent-color); }

/* ═══ Units / lessons curriculum ═══ */
.unit-card {
    background: var(--bg-secondary);
    border: 1px solid var(--separator-color);
    border-radius: 14px;
    overflow: hidden;
    margin-bottom: .65rem;
    transition: border-color .25s ease;
}
.unit-card:has(.unit-toggle[aria-expanded="true"]) {
    border-color: rgba(197, 168, 128, .45);
}
.unit-toggle {
    display: flex;
    align-items: center;
    gap: .8rem;
    width: 100%;
    padding: .85rem 1rem;
    background: transparent;
    border: 0;
    text-align: start;
    color: var(--text-primary);
    cursor: pointer;
}
.unit-toggle:hover { background: rgba(197, 168, 128, .06); }
.unit-num {
    width: 34px; height: 34px;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: rgba(197, 168, 128, .14);
    color: var(--accent-color);
    font-weight: 800;
    font-size: .9rem;
}
.unit-title { font-weight: 700; font-size: .92rem; line-height: 1.3; }
.unit-meta { font-size: .72rem; opacity: .65; margin-top: 2px; }
.unit-toggle .bi-chevron-down {
    margin-inline-start: auto;
    color: var(--accent-color);
    transition: transform .3s ease;
}
.unit-toggle[aria-expanded="true"] .bi-chevron-down { transform: rotate(180deg); }

.lesson-block { border-top: 1px dashed var(--separator-color); }
.lesson-toggle {
    display: flex;
    align-items: center;
    gap: .6rem;
    width: 100%;
    padding: .6rem 1rem .6rem 1.2rem;
    background: transparent;
    border: 0;
    text-align: start;
    color: var(--text-primary);
    font-size: .85rem;
    font-weight: 600;
    cursor: pointer;
}
.lesson-toggle:hover { color: var(--accent-color); }
.lesson-toggle .lesson-count {
    margin-inline-start: auto;
    font-size: .68rem;
    padding: .15rem .55rem;
    border-radius: 50rem;
    background: rgba(197, 168, 128, .12);
    color: var(--accent-color);
    flex-shrink: 0;
}
.lesson-toggle .bi-chevron-down {
    font-size: .7rem;
    color: var(--text-muted);
    transition: transform .3s ease;
}
.lesson-toggle[aria-expanded="true"] .bi-chevron-down { transform: rotate(180deg); }
.lesson-resources { padding: 0 .9rem .6rem 1.4rem; }

/* Details cards under the player */
.detail-card { background: var(--bg-secondary); border: 1px solid var(--separator-color); }
.detail-card .card-head {
    display: flex;
    align-items: center;
    gap: .6rem;
    font-weight: 700;
    color: var(--text-primary);
    border-bottom: 1px solid var(--separator-color);
    padding-bottom: .75rem;
    margin-bottom: 1rem;
}
.detail-card .card-head i { color: var(--accent-color); font-size: 1.15rem; }
</style>
@endpush

@section('content')

@php
    $dayLabels = [
        'sun' => 'الأحد', 'mon' => 'الاثنين', 'tue' => 'الثلاثاء', 'wed' => 'الأربعاء',
        'thu' => 'الخميس', 'fri' => 'الجمعة', 'sat' => 'السبت',
    ];
    $teacher = $group->teacher;
    $teacherPhoto = $teacher && $teacher->photo
        ? (str_starts_with($teacher->photo, 'site/') ? asset($teacher->photo) : asset('storage/' . $teacher->photo))
        : asset('assets/admin/media/avatars/blank.png');
@endphp

<!-- ═══ Group Header Banner ═══ -->
<div class="glass-panel bg-pattern-gold rounded-4 p-4 p-md-5 mb-4 position-relative overflow-hidden">
    <div class="position-absolute top-0 end-0 w-50 h-100 bg-gold/10 blur-[80px]"></div>
    <div class="position-relative z-1 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4">
        <div>
            <div class="d-flex align-items-center gap-3 mb-2 flex-wrap">
                <h1 class="h3 fw-bold mb-0" style="color: var(--text-primary);">{{ $group->name }}</h1>
                <span class="badge rounded-pill px-3 py-2" style="background: rgba(16,185,129,.15); color: #10b981;">
                    <i class="bi bi-broadcast me-1"></i><span data-en="Active group" data-ar="مجموعة نشطة">مجموعة نشطة</span>
                </span>
            </div>
            <div class="fw-medium mb-3" style="color: var(--accent-color);">
                {{ $subject->name }} <span class="opacity-50 mx-1">|</span> {{ $subject->program->title ?? $subject->program->name ?? '' }}
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if(!empty($group->days))
                    <span class="group-chip"><i class="bi bi-calendar-week"></i>
                        {{ collect($group->days)->map(fn($d) => $dayLabels[$d] ?? $d)->implode(' · ') }}
                    </span>
                @endif
                @if($group->start_time)
                    <span class="group-chip"><i class="bi bi-clock"></i>
                        <span dir="ltr">{{ \Carbon\Carbon::parse($group->start_time)->format('h:i A') }} — {{ \Carbon\Carbon::parse($group->end_time)->format('h:i A') }}</span>
                    </span>
                @endif
                @if($exams->isNotEmpty())
                    <span class="group-chip"><i class="bi bi-journal-check"></i>
                        {{ $exams->count() }} <span data-en="exam(s)" data-ar="امتحان">امتحان</span>
                    </span>
                @endif
            </div>
        </div>

        @if($teacher)
            <div class="d-flex align-items-center gap-3 rounded-4 p-3 flex-shrink-0" style="background: var(--bg-secondary); border: 1px solid var(--separator-color);">
                <img src="{{ $teacherPhoto }}" alt="{{ $teacher->name }}" class="rounded-circle" style="width: 56px; height: 56px; object-fit: cover; border: 2px solid var(--accent-color);">
                <div>
                    <div class="text-xs opacity-75" style="color: var(--text-secondary);" data-en="Group teacher" data-ar="مدرس المجموعة">مدرس المجموعة</div>
                    <div class="fw-bold" style="color: var(--text-primary);">{{ $teacher->name }}</div>
                    @if($teacher->email)
                        <div class="fs-7 opacity-75" style="color: var(--text-secondary);" dir="ltr">{{ $teacher->email }}</div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

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
                            @php
                                $rIcon = match($resource->type) {
                                    'video' => 'play-circle-fill',
                                    'zoom' => 'camera-video-fill',
                                    'image' => 'image-fill',
                                    'link' => 'link-45deg',
                                    default => 'file-earmark-text-fill',
                                };
                            @endphp
                            <a href="javascript:void(0)" class="resource-item" onclick="loadResource({{ json_encode(['id' => $resource->getRouteKey(), 'title' => $resource->title, 'type' => $resource->type, 'is_pdf' => $isPdf, 'is_image' => $resource->isImage(), 'is_external' => $isUrl, 'url' => $isUrl ? $resource->url : null, 'description' => $resource->description]) }})">
                                <i class="bi bi-{{ $rIcon }}" style="color: var(--accent-color);"></i>
                                <span class="text-sm">{{ $resource->title }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif

                @php
                    $allUnits = $subject->stages->flatMap->units;
                @endphp
                @if($allUnits->isNotEmpty())
                    <div class="px-2 pt-1 pb-2 d-flex align-items-center justify-content-between">
                        <span class="text-xs fw-bold text-uppercase opacity-50" style="color: var(--text-primary);" data-en="Curriculum Units" data-ar="وحدات المنهاج">وحدات المنهاج</span>
                        <span class="text-xs opacity-50" style="color: var(--text-primary);">{{ $allUnits->count() }} <span data-en="unit(s)" data-ar="وحدة">وحدة</span></span>
                    </div>
                @endif
                @forelse($allUnits as $unitIndex => $unit)
                    @php
                        $unitResourceCount = $unit->lessons->sum(fn ($l) => $l->resources->count());
                    @endphp
                    <div class="unit-card">
                        <button type="button" class="unit-toggle" data-bs-toggle="collapse" data-bs-target="#unitPanel{{ $unit->id }}" aria-expanded="{{ $unitIndex === 0 ? 'true' : 'false' }}">
                            <span class="unit-num">{{ $unitIndex + 1 }}</span>
                            <span class="flex-grow-1">
                                <span class="unit-title d-block">{{ $unit->name }}</span>
                                <span class="unit-meta d-block">{{ $unit->lessons->count() }} <span data-en="lesson(s)" data-ar="درس">درس</span> · {{ $unitResourceCount }} <span data-en="resource(s)" data-ar="مورد">مورد</span></span>
                            </span>
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        {{-- No data-bs-parent: every unit opens/closes independently, fully under the student's control --}}
                        <div id="unitPanel{{ $unit->id }}" class="collapse {{ $unitIndex === 0 ? 'show' : '' }}">
                            @foreach($unit->lessons as $lesson)
                                <div class="lesson-block">
                                    <button type="button" class="lesson-toggle collapsed" data-bs-toggle="collapse" data-bs-target="#lessonPanel{{ $lesson->id }}" aria-expanded="false">
                                        <i class="bi bi-journal-text" style="color: var(--accent-color);"></i>
                                        <span>{{ $lesson->name }}</span>
                                        <span class="lesson-count">{{ $lesson->resources->count() }}</span>
                                        <i class="bi bi-chevron-down"></i>
                                    </button>
                                    <div id="lessonPanel{{ $lesson->id }}" class="collapse">
                                        <div class="lesson-resources">
                                            @foreach($lesson->resources as $resource)
                                                @php
                                                    $isUrl = \Illuminate\Support\Str::startsWith($resource->url, ['http://', 'https://']);
                                                    $isPdf = $resource->type === 'document' && !$isUrl && strtolower(pathinfo($resource->url ?? '', PATHINFO_EXTENSION)) === 'pdf';
                                                    $rIcon = match($resource->type) {
                                                        'video' => 'play-circle-fill',
                                                        'zoom' => 'camera-video-fill',
                                                        'image' => 'image-fill',
                                                        'link' => 'link-45deg',
                                                        default => 'file-earmark-text-fill',
                                                    };
                                                @endphp
                                                <a href="javascript:void(0)" class="resource-item" onclick="loadResource({{ json_encode(['id' => $resource->getRouteKey(), 'title' => $resource->title, 'type' => $resource->type, 'is_pdf' => $isPdf, 'is_image' => $resource->isImage(), 'is_external' => $isUrl, 'url' => $isUrl ? $resource->url : null, 'description' => $resource->description]) }})">
                                                    <i class="bi bi-{{ $rIcon }}" style="color: var(--accent-color);"></i>
                                                    <span class="text-sm">{{ $resource->title }}</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    @if($generalResources->isEmpty())
                        <div class="p-4 text-center opacity-50" style="color: var(--text-primary);">
                            <i class="bi bi-journal-x fs-2 d-block mb-2"></i>
                            <span data-en="No materials available" data-ar="لا يوجد مواد دراسية">لا يوجد مواد دراسية</span>
                        </div>
                    @endif
                @endforelse
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
            
            <style>
                /* In-page fullscreen — the wrapper itself goes fullscreen so the
                   watermark (a child of the wrapper) stays visible when zoomed */
                #groupVideoContainer:fullscreen, #groupVideoContainer:-webkit-full-screen,
                #iframeWrapper:fullscreen, #iframeWrapper:-webkit-full-screen {
                    width: 100vw; height: 100vh; aspect-ratio: auto; border-radius: 0;
                }
                #groupVideoContainer:fullscreen #videoPlayer,
                #groupVideoContainer:-webkit-full-screen #videoPlayer { object-fit: contain; }
                #documentWrapper:fullscreen, #documentWrapper:-webkit-full-screen,
                #imageWrapper:fullscreen, #imageWrapper:-webkit-full-screen {
                    width: 100vw; height: 100vh; overflow: auto; border-radius: 0; background: #111;
                }
                /* #playerContainer uses .glass-panel (backdrop-filter), which keeps
                   Chromium from fully isolating the fullscreen element into the
                   browser's top layer — so the title/badge row above the video
                   still paints on top of it as a big band across the upper part of
                   the screen instead of disappearing behind it. Explicitly hiding
                   it via a class (toggled in the fullscreenchange handler below) is
                   the reliable fix, independent of that rendering quirk. */
                #playerContainer.viewer-is-fullscreen #contentViewerHeader {
                    display: none !important;
                }
                /* Drifting watermark over embedded (iframe) resources */
                .embed-watermark {
                    position: absolute;
                    top: 8%;
                    inset-inline-start: 6%;
                    z-index: 5;
                    pointer-events: none;
                    user-select: none;
                    color: rgba(255, 255, 255, 0.4);
                    font-size: .85rem;
                    font-weight: 700;
                    text-shadow: 0 1px 3px rgba(0,0,0,.6);
                    animation: embedWatermarkDrift 24s linear infinite;
                    white-space: nowrap;
                }
                @keyframes embedWatermarkDrift {
                    0%   { top: 8%;  inset-inline-start: 6%; }
                    25%  { top: 78%; inset-inline-start: 62%; }
                    50%  { top: 14%; inset-inline-start: 68%; }
                    75%  { top: 70%; inset-inline-start: 10%; }
                    100% { top: 8%;  inset-inline-start: 6%; }
                }
            </style>

            <div id="contentViewer" class="w-100 d-none text-start">
                <div id="contentViewerHeader" class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <h2 class="fw-bold mb-0" id="contentTitle" style="color: var(--text-primary);"></h2>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" id="viewerFullscreenBtn" class="btn btn-sm btn-glass d-none rounded-pill px-3" onclick="toggleViewerFullscreen()" title="ملء الشاشة">
                            <i class="bi bi-arrows-fullscreen me-1"></i>
                            <span data-en="Fullscreen" data-ar="ملء الشاشة">ملء الشاشة</span>
                        </button>
                        <span id="contentTypeBadge" class="badge bg-gold text-dark px-3 py-2 rounded-pill"></span>
                    </div>
                </div>

                <div id="videoWrapper" class="d-none mb-4 shadow-lg">
                    <div id="groupVideoContainer" style="position: relative; width: 100%; aspect-ratio: 16/9; background: #000; border-radius: 12px; overflow: hidden;">
                        <video id="videoPlayer" controls playsinline oncontextmenu="return false;" style="width: 100%; height: 100%; object-fit: contain;"></video>
                    </div>
                    <p id="groupVideoError" class="text-danger mt-2 mb-0 d-none"></p>
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

                <div id="iframeWrapper" class="d-none mb-4 shadow-lg" style="position: relative; width: 100%; aspect-ratio: 16/9; background: #000; border-radius: 12px; overflow: hidden;" oncontextmenu="return false;">
                    <iframe id="iframePlayer" style="width: 100%; height: 100%; border: 0;" allow="encrypted-media; picture-in-picture; fullscreen" allowfullscreen referrerpolicy="strict-origin-when-cross-origin" oncontextmenu="return false;"></iframe>
                    <div class="embed-watermark">{{ auth()->guard('student')->user()->name }} — {{ auth()->guard('student')->user()->email }}</div>
                </div>

                <div class="glass-panel rounded-4 p-4 mt-4 bg-pattern-gold" style="border: 1px solid rgba(255,255,255,0.1);">
                    <h5 class="fw-bold mb-2 text-white" data-en="Description" data-ar="الوصف">الوصف</h5>
                    <p id="contentDescription" class="opacity-75 mb-0 text-sm lh-lg text-white"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══ Group Details: teacher / exams & grades / notes ═══ -->
<div class="row g-4 mt-1">
    <!-- Teacher details -->
    <div class="col-md-6 col-xl-4">
        <div class="detail-card rounded-4 p-4 h-100">
            <div class="card-head"><i class="bi bi-person-badge-fill"></i><span data-en="Group Teacher" data-ar="مدرس المجموعة">مدرس المجموعة</span></div>
            @if($teacher)
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img src="{{ $teacherPhoto }}" alt="{{ $teacher->name }}" class="rounded-circle" style="width: 64px; height: 64px; object-fit: cover; border: 2px solid var(--accent-color);">
                    <div>
                        <div class="fw-bold fs-5" style="color: var(--text-primary);">{{ $teacher->name }}</div>
                        <div class="fs-7" style="color: var(--accent-color);">{{ $subject->name }}</div>
                    </div>
                </div>
                <div class="d-flex flex-column gap-2">
                    @if($teacher->email)
                        <div class="d-flex align-items-center gap-2 fs-7" style="color: var(--text-secondary);">
                            <i class="bi bi-envelope-fill" style="color: var(--accent-color);"></i><span dir="ltr">{{ $teacher->email }}</span>
                        </div>
                    @endif
                    @if($teacher->phone)
                        <div class="d-flex align-items-center gap-2 fs-7" style="color: var(--text-secondary);">
                            <i class="bi bi-telephone-fill" style="color: var(--accent-color);"></i><span dir="ltr">{{ $teacher->phone }}</span>
                        </div>
                    @endif
                    @if(!empty($group->days))
                        <div class="d-flex align-items-center gap-2 fs-7" style="color: var(--text-secondary);">
                            <i class="bi bi-calendar-week" style="color: var(--accent-color);"></i>
                            <span>{{ collect($group->days)->map(fn($d) => $dayLabels[$d] ?? $d)->implode(' · ') }}</span>
                        </div>
                    @endif
                    @if($group->start_time)
                        <div class="d-flex align-items-center gap-2 fs-7" style="color: var(--text-secondary);">
                            <i class="bi bi-clock" style="color: var(--accent-color);"></i>
                            <span dir="ltr">{{ \Carbon\Carbon::parse($group->start_time)->format('h:i A') }} — {{ \Carbon\Carbon::parse($group->end_time)->format('h:i A') }}</span>
                        </div>
                    @endif
                </div>
            @else
                <p class="opacity-50 mb-0 fs-7" style="color: var(--text-primary);" data-en="No teacher assigned yet." data-ar="لم يُعيَّن مدرس للمجموعة بعد.">لم يُعيَّن مدرس للمجموعة بعد.</p>
            @endif
        </div>
    </div>

    <!-- Exam schedule + my grades -->
    <div class="col-md-6 col-xl-4">
        <div class="detail-card rounded-4 p-4 h-100 d-flex flex-column">
            <div class="card-head"><i class="bi bi-journal-check"></i><span data-en="Exam Schedule" data-ar="مواعيد الامتحانات">مواعيد الامتحانات</span></div>
            @if($exams->isEmpty())
                <p class="opacity-50 mb-0 fs-7" style="color: var(--text-primary);" data-en="No exams scheduled yet." data-ar="لا توجد امتحانات مجدولة بعد.">لا توجد امتحانات مجدولة بعد.</p>
            @else
                <div class="d-flex flex-column gap-2 mb-3 overflow-auto" style="max-height: 260px;">
                    @foreach($exams as $exam)
                        @php $isUpcoming = $exam->start_time && $exam->start_time->isFuture(); @endphp
                        <div class="rounded-3 p-3" style="background: var(--bg-primary); border: 1px solid var(--separator-color); border-inline-start: 3px solid {{ $isUpcoming ? '#10b981' : 'var(--accent-color)' }};">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <span class="fw-bold fs-7" style="color: var(--text-primary);">{{ $exam->title }}</span>
                                @if($isUpcoming)
                                    <span class="badge rounded-pill" style="background: rgba(16,185,129,.15); color: #10b981;" data-en="Upcoming" data-ar="قادم">قادم</span>
                                @else
                                    <span class="badge rounded-pill" style="background: rgba(197,168,128,.15); color: var(--accent-color);" data-en="Finished" data-ar="منتهي">منتهي</span>
                                @endif
                            </div>
                            <div class="fs-7 mt-1" style="color: var(--text-secondary);">
                                <i class="bi bi-calendar-event me-1" style="color: var(--accent-color);"></i>
                                {{ $exam->start_time ? $exam->start_time->format('Y-m-d h:i A') : '—' }}
                                @if($exam->duration_minutes)
                                    <span class="opacity-50 mx-1">·</span>{{ $exam->duration_minutes }} <span data-en="min" data-ar="دقيقة">دقيقة</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                <button type="button" class="btn btn-luxury w-100 rounded-pill py-2 mt-auto" data-bs-toggle="modal" data-bs-target="#myGradesModal">
                    <i class="bi bi-award-fill me-1"></i><span data-en="View my grades" data-ar="عرض علاماتي في هذه المجموعة">عرض علاماتي في هذه المجموعة</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Teacher notes & assessments -->
    <div class="col-12 col-xl-4">
        <div class="detail-card rounded-4 p-4 h-100">
            <div class="card-head"><i class="bi bi-chat-left-text-fill"></i><span data-en="Teacher Notes" data-ar="ملاحظات المدرس">ملاحظات المدرس</span></div>

            @if($studentNotes->isNotEmpty())
                <div class="text-xs fw-bold text-uppercase opacity-50 mb-2" style="color: var(--text-primary);" data-en="Your personal assessments" data-ar="تقييمات المدرس لك">تقييمات المدرس لك</div>
                <div class="d-flex flex-column gap-2 mb-3">
                    @foreach($studentNotes as $note)
                        <div class="rounded-3 p-3" style="background: rgba(197,168,128,.08); border-inline-start: 3px solid var(--accent-color);">
                            <div class="fw-bold fs-7 mb-1" style="color: var(--accent-color);"><i class="bi bi-star-fill me-1"></i>{{ $note->title }}</div>
                            <div class="text-sm" style="color: var(--text-primary);">{{ $note->content }}</div>
                            <div class="fs-7 opacity-50 mt-1" style="color: var(--text-secondary);">{{ $note->created_at->diffForHumans() }}</div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($notes->isNotEmpty())
                <div class="text-xs fw-bold text-uppercase opacity-50 mb-2" style="color: var(--text-primary);" data-en="Group announcements" data-ar="ملاحظات عامة للمجموعة">ملاحظات عامة للمجموعة</div>
                <div class="d-flex flex-column gap-2 overflow-auto" style="max-height: 240px;">
                    @foreach($notes as $note)
                        @if($note->is_alert)
                            <div class="rounded-3 p-3" style="background: rgba(220,53,69,.1); border-inline-start: 3px solid #dc3545;">
                                <div class="fw-bold fs-7 mb-1 text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $note->title }}</div>
                                <div class="text-sm" style="color: var(--text-primary);">{{ $note->content }}</div>
                                <div class="fs-7 opacity-50 mt-1" style="color: var(--text-secondary);">{{ $note->created_at->diffForHumans() }}</div>
                            </div>
                        @else
                            <div class="rounded-3 p-3" style="background: var(--bg-primary); border: 1px solid var(--separator-color);">
                                <div class="fw-bold fs-7 mb-1" style="color: var(--accent-color);">{{ $note->title }}</div>
                                <div class="text-sm" style="color: var(--text-primary);">{{ $note->content }}</div>
                                <div class="fs-7 opacity-50 mt-1" style="color: var(--text-secondary);">{{ $note->created_at->diffForHumans() }}</div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            @if($notes->isEmpty() && $studentNotes->isEmpty())
                <p class="opacity-50 mb-0 fs-7" style="color: var(--text-primary);" data-en="No notes yet." data-ar="لا توجد ملاحظات بعد.">لا توجد ملاحظات بعد.</p>
            @endif
        </div>
    </div>
</div>

<!-- ═══ My Grades Modal ═══ -->
<div class="modal fade" id="myGradesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="background: var(--bg-secondary); border: 1px solid var(--separator-color); color: var(--text-primary);">
            <div class="modal-header" style="border-color: var(--separator-color);">
                <h5 class="modal-title fw-bold"><i class="bi bi-award-fill me-2" style="color: var(--accent-color);"></i><span data-en="My grades in this group" data-ar="علاماتي في هذه المجموعة">علاماتي في هذه المجموعة</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if($exams->isEmpty())
                    <p class="opacity-50 mb-0 text-center py-4" data-en="No exams yet." data-ar="لا توجد امتحانات بعد.">لا توجد امتحانات بعد.</p>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" style="color: var(--text-primary);">
                            <thead>
                                <tr class="fs-7 text-uppercase" style="color: var(--text-secondary);">
                                    <th data-en="Exam" data-ar="الامتحان">الامتحان</th>
                                    <th data-en="Date" data-ar="التاريخ">التاريخ</th>
                                    <th data-en="Grade" data-ar="العلامة">العلامة</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($exams as $exam)
                                    @php $grade = $grades->get($exam->id); @endphp
                                    <tr style="border-color: var(--separator-color);">
                                        <td class="fw-bold fs-7">{{ $exam->title }}</td>
                                        <td class="fs-7" style="color: var(--text-secondary);">{{ $exam->start_time ? $exam->start_time->format('Y-m-d') : '—' }}</td>
                                        <td>
                                            @if($grade)
                                                @php
                                                    $pct = $grade->max_score > 0 ? round(($grade->score / $grade->max_score) * 100) : null;
                                                @endphp
                                                <span class="fw-bold" style="color: {{ !is_null($pct) && $pct >= 50 ? '#10b981' : '#ef4444' }};">
                                                    {{ $grade->score }} / {{ $grade->max_score }}
                                                </span>
                                                @if(!is_null($pct))
                                                    <span class="fs-7 opacity-75">({{ $pct }}%)</span>
                                                @endif
                                            @else
                                                <span class="badge rounded-pill" style="background: rgba(148,163,184,.15); color: var(--text-secondary);" data-en="Not taken" data-ar="لم يُقدَّم">لم يُقدَّم</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($grade)
                                                <a href="{{ route('student.results.show', $grade) }}" class="btn btn-sm btn-glass rounded-pill px-3" data-en="Details" data-ar="التفاصيل">التفاصيل</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/vendor/hlsjs/hls.min.js') }}"></script>
<script src="{{ asset('assets/js/secure-watermark.js') }}"></script>
<script src="{{ asset('assets/js/student-video-player.js') }}"></script>
<script src="{{ asset('assets/vendor/pdfjs/pdf.min.js') }}"></script>
<script src="{{ asset('assets/js/student-document-viewer.js') }}"></script>
<script src="{{ asset('assets/js/student-image-viewer.js') }}"></script>
<script>
    // Same page-level protection as the Learning Resources screen
    document.addEventListener('contextmenu', function (e) { e.preventDefault(); });

    var groupDocumentDestroy = null;
    var groupImageDestroy = null;
    var groupVideoDestroy = null;

    // Which element the in-page fullscreen button targets for the current resource
    var fullscreenTargetId = null;

    function setFullscreenTarget(id) {
        fullscreenTargetId = id;
        document.getElementById('viewerFullscreenBtn').classList.toggle('d-none', !id);
    }

    function toggleViewerFullscreen() {
        if (document.fullscreenElement || document.webkitFullscreenElement) {
            (document.exitFullscreen || document.webkitExitFullscreen).call(document);
            return;
        }
        var el = fullscreenTargetId ? document.getElementById(fullscreenTargetId) : null;
        if (!el) return;
        var request = el.requestFullscreen || el.webkitRequestFullscreen;
        if (request) request.call(el);
    }

    // Keeps the title/fullscreen-button/badge row from painting on top of the
    // fullscreen video/embed (see #playerContainer.viewer-is-fullscreen CSS above).
    function onViewerFullscreenChange() {
        var fsEl = document.fullscreenElement || document.webkitFullscreenElement;
        var isOurs = !!fsEl && (fsEl.id === 'groupVideoContainer' || fsEl.id === 'iframeWrapper' ||
            fsEl.id === 'documentWrapper' || fsEl.id === 'imageWrapper');
        document.getElementById('playerContainer').classList.toggle('viewer-is-fullscreen', isOurs);
    }
    document.addEventListener('fullscreenchange', onViewerFullscreenChange);
    document.addEventListener('webkitfullscreenchange', onViewerFullscreenChange);

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
        if (groupVideoDestroy) { groupVideoDestroy(); groupVideoDestroy = null; }
        setFullscreenTarget(null);

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
            setFullscreenTarget('groupVideoContainer');

            // Watermarked secure player — the same one used on the Learning
            // Resources page (moving name+photo overlay, protected stream).
            const videoError = document.getElementById('groupVideoError');
            videoError.classList.add('d-none');

            groupVideoDestroy = mountSecureVideoPlayer({
                resourceId: resource.id,
                container: document.getElementById('groupVideoContainer'),
                videoEl: videoPlayer,
                startUrl: '{{ url("student/videos") }}/' + encodeURIComponent(resource.id) + '/start',
                studentName: @json(auth()->guard('student')->user()->name),
                studentPhotoUrl: @json(auth()->guard('student')->user()->photo_url),
                csrfToken: '{{ csrf_token() }}',
                onError: function (message) {
                    videoError.textContent = message;
                    videoError.classList.remove('d-none');
                },
            });

        } else if (resource.type === 'zoom') {
            badge.innerText = 'رابط خارجي';
            badge.className = 'badge bg-primary text-white px-3 py-2 rounded-pill';
            document.getElementById('zoomWrapper').classList.remove('d-none');
            document.getElementById('zoomLink').href = resource.url;
        } else if (resource.type === 'link') {
            badge.innerText = 'رابط محمي';
            badge.className = 'badge bg-primary text-white px-3 py-2 rounded-pill';
            document.getElementById('iframeWrapper').classList.remove('d-none');
            setFullscreenTarget('iframeWrapper');
            // Load the secure embed route
            iframePlayer.src = '{{ url("student/secure-embed") }}/' + encodeURIComponent(resource.id);
        } else if (resource.is_pdf) {
            // Rendered in-page via the watermarked pdf.js canvas viewer — same
            // one used on the "Learning Resources" page — instead of opening
            // the raw file in a new browser tab.
            badge.innerText = 'ملف PDF';
            badge.className = 'badge bg-info text-dark px-3 py-2 rounded-pill';
            document.getElementById('documentWrapper').classList.remove('d-none');
            setFullscreenTarget('documentWrapper');

            const container = document.getElementById('documentContainer');
            const errorEl = document.getElementById('documentError');
            container.innerHTML = '<div class="text-center text-white-50 py-5" id="documentLoading">جاري تحميل الملف الآمن...</div>';
            errorEl.classList.add('d-none');

            groupDocumentDestroy = mountSecureDocumentViewer({
                container: container,
                fileUrl: '{{ url('student/resources') }}/' + encodeURIComponent(resource.id) + '/file',
                studentName: @json(auth()->guard('student')->user()->name),
                studentPhotoUrl: @json(auth()->guard('student')->user()->photo_url),
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
            setFullscreenTarget('imageWrapper');

            const container = document.getElementById('imageContainer');
            container.innerHTML = '<div class="text-center text-white-50 py-5" id="imageLoading">جاري تحميل الصورة الآمنة...</div>';

            groupImageDestroy = mountSecureImageViewer({
                container: container,
                fileUrl: '{{ url('student/resources') }}/' + encodeURIComponent(resource.id) + '/file',
                studentName: @json(auth()->guard('student')->user()->name),
                studentPhotoUrl: @json(auth()->guard('student')->user()->photo_url),
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
                : '{{ url("student/resources") }}/' + encodeURIComponent(resource.id) + '/file';
        }
    }
</script>
@endpush
@endsection
