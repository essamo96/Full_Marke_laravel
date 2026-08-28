@extends('admin.layout.mainLayouts.master')

@section('title', 'مكتبة المرفقات')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('resource-library.view') }}" class="text-muted text-hover-primary">مكتبة المرفقات</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-dark">استعراض الموارد والتوزيع</li>
@endsection

@push('styles')
  <link rel="stylesheet" href="{{ asset_ver('assets/css/admin-resources.css') }}">
  <link rel="stylesheet" href="{{ asset_ver('assets/css/curriculum-accordion.css') }}">
@endpush

@section('page-content')

<div class="row g-5 g-xl-10">
    <div class="col-xl-4 mb-5 mb-xl-10">
        <!-- Filter Card -->
        <div class="card card-flush h-xl-100">
            <div class="card-header pt-7">
                <div class="card-title">
                    <i class="ki-duotone ki-filter fs-1 me-2 text-primary"><span class="path1"></span><span class="path2"></span></i>
                    <h2>فلترة الموارد</h2>
                </div>
            </div>
            <div class="card-body pt-5">
                <form action="{{ route('resource-library.view') }}" method="GET" id="filterForm">
                    <div class="mb-5">
                        <label class="form-label fs-5 fw-semibold mb-3">اختر المادة الدراسية:</label>
                        <select name="subject_id" class="form-select form-select-solid" data-control="select2" data-placeholder="-- اختر المادة --" onchange="document.getElementById('filterForm').submit()">
                            <option value=""></option>
                            @foreach($subjects as $subject)
                                <option value="{{ \Illuminate\Support\Facades\Crypt::encrypt($subject->id) }}" {{ $selectedSubjectId == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name_ar ?? $subject->name }} ({{ $subject->program->name_ar ?? $subject->program->title ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>

                <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-6 mt-10">
                    <i class="ki-duotone ki-information fs-2tx text-primary me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                    <div class="d-flex flex-stack flex-grow-1">
                        <div class="fw-semibold">
                            <h4 class="text-gray-900 fw-bold">الهدف من المكتبة</h4>
                            <div class="fs-6 text-gray-700">تتيح لك هذه الشاشة استعراض كيفية توزيع المحتوى والموارد على مختلف المجموعات في المادة المحددة.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-8 mb-5 mb-xl-10">
        <!-- Content Card -->
        <div class="card card-flush h-xl-100">
            <div class="card-header pt-7">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-800">تفاصيل توزيع المرفقات</span>
                    <span class="text-gray-400 mt-1 fw-semibold fs-6">
                        @if($selectedSubject)
                            المرفقات والموارد الخاصة بمادة: {{ $selectedSubject->name_ar ?? $selectedSubject->name }}
                        @else
                            يرجى اختيار مادة لعرض التفاصيل
                        @endif
                    </span>
                </h3>
            </div>
            <div class="card-body pt-5">
                
                @if(!$selectedSubject)
                    <div class="d-flex flex-column flex-center text-center p-10">
                        <i class="ki-duotone ki-search-list fs-5x text-muted mb-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        <div class="fs-4 fw-bold text-gray-900 mb-2">لم يتم اختيار أي مادة</div>
                        <div class="fs-6 text-gray-500">الرجاء اختيار مادة من القائمة الجانبية لعرض مكتبة المرفقات وتوزيعها على المجموعات.</div>
                    </div>
                @else
                    
                    @if($groups->isEmpty())
                        <div class="alert alert-warning d-flex align-items-center p-5">
                            <i class="ki-duotone ki-shield-cross fs-2hx text-warning me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                            <div class="d-flex flex-column">
                                <h4 class="mb-1 text-warning">لا توجد مجموعات</h4>
                                <span>لا توجد مجموعات مسجلة في هذه المادة حالياً لعرض وتوزيع المرفقات.</span>
                            </div>
                        </div>
                    @else
                        
                        <div class="accordion admin-accordion" id="groupsAccordion">
                            @foreach($groups as $groupIndex => $group)
                                <div class="card bg-light mb-5 shadow-sm">
                                    <div class="card-header py-3 d-flex cursor-pointer" data-bs-toggle="collapse" data-bs-target="#groupPanel{{ $group->id }}" aria-expanded="{{ $groupIndex === 0 ? 'true' : 'false' }}">
                                        <h3 class="fs-4 fw-semibold mb-0 d-flex align-items-center">
                                            <i class="ki-duotone ki-people fs-2 me-3 text-primary"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                            مجموعة: {{ $group->name }}
                                        </h3>
                                    </div>
                                    <div id="groupPanel{{ $group->id }}" class="collapse {{ $groupIndex === 0 ? 'show' : '' }}" data-bs-parent="#groupsAccordion">
                                        <div class="card-body p-5">
                                            
                                            @forelse($units as $unitIndex => $unit)
                                                
                                                @php 
                                                    // Calculate resources available for THIS group in this unit
                                                    $groupUnitResourceCount = 0;
                                                    foreach($unit->lessons as $lesson) {
                                                        $groupUnitResourceCount += $lesson->resources->filter(function($r) use ($group) {
                                                            $gids = is_string($r->group_ids) ? json_decode($r->group_ids, true) : $r->group_ids;
                                                            return empty($gids) || in_array($group->id, $gids);
                                                        })->count();
                                                    }
                                                @endphp
                                                
                                                @continue($groupUnitResourceCount === 0)
                                                
                                                <div class="unit-card mb-3 border border-dashed border-gray-300">
                                                    <div class="unit-toggle text-dark" data-bs-toggle="collapse" data-bs-target="#unit_{{ $group->id }}_{{ $unit->id }}" aria-expanded="{{ $unitIndex === 0 ? 'true' : 'false' }}">
                                                        <span class="unit-num bg-light-primary text-primary">{{ $unitIndex + 1 }}</span>
                                                        <span class="flex-grow-1">
                                                            <span class="unit-title d-block">{{ $unit->name_ar ?? $unit->name_en }}</span>
                                                            <span class="unit-meta text-muted d-block">{{ $unit->lessons->count() }} درس · {{ $groupUnitResourceCount }} مورد لهذه المجموعة</span>
                                                        </span>
                                                        <i class="bi bi-chevron-down text-muted"></i>
                                                    </div>
                                                    
                                                    <div id="unit_{{ $group->id }}_{{ $unit->id }}" class="collapse {{ $unitIndex === 0 ? 'show' : '' }}">
                                                        @forelse($unit->lessons as $lesson)
                                                            @php
                                                                $groupLessonResources = $lesson->resources->filter(function($r) use ($group) {
                                                                    $gids = is_string($r->group_ids) ? json_decode($r->group_ids, true) : $r->group_ids;
                                                                    return empty($gids) || in_array($group->id, $gids);
                                                                });
                                                            @endphp
                                                            
                                                            @continue($groupLessonResources->isEmpty())
                                                            
                                                            <div class="lesson-block border-top border-dashed border-gray-300">
                                                                <div class="lesson-toggle collapsed text-dark" data-bs-toggle="collapse" data-bs-target="#lesson_{{ $group->id }}_{{ $lesson->id }}" aria-expanded="false">
                                                                    <i class="ki-duotone ki-book-open fs-3 text-primary"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                                                    <span class="flex-grow-1 ms-2">{{ $lesson->name_ar ?? $lesson->name_en }}</span>
                                                                    <div class="d-flex gap-2 align-items-center me-3">
                                                                        <span class="lesson-count bg-light-info text-info">{{ $groupLessonResources->count() }} مرفق</span>
                                                                    </div>
                                                                    <i class="bi bi-chevron-down"></i>
                                                                </div>

                                                                <div id="lesson_{{ $group->id }}_{{ $lesson->id }}" class="collapse">
                                                                    <div class="lesson-resources mt-2">
                                                                        <div class="row g-3 mb-3">
                                                                            @foreach($groupLessonResources as $resource)
                                                                                @php
                                                                                    $resourceType = $resource->type ?? 'link';
                                                                                    if ($resourceType === 'link' && str_contains($resource->url ?? '', 'drive.google.com')) {
                                                                                        $resourceType = 'drive';
                                                                                    }
                                                                                    $iconMap = [
                                                                                        'video' => 'ki-youtube',
                                                                                        'document' => 'ki-file',
                                                                                        'image' => 'ki-picture',
                                                                                        'link' => 'ki-link',
                                                                                        'zoom' => 'ki-monitor-mobile',
                                                                                        'drive' => 'ki-cloud',
                                                                                    ];
                                                                                    $icon = $iconMap[$resourceType] ?? 'ki-link';
                                                                                    $title = match($resourceType) {
                                                                                        'video' => 'فيديو',
                                                                                        'document' => 'ملف',
                                                                                        'image' => 'صورة',
                                                                                        'zoom' => 'Zoom',
                                                                                        'drive' => 'جوجل درايف',
                                                                                        default => 'رابط',
                                                                                    };
                                                                                @endphp
                                                                                
                                                                                @php
                                                                                    $isExternal = $resource->isExternalLink() || in_array($resourceType, ['link', 'zoom']);
                                                                                    $previewLink = $isExternal ? (preg_match('#^https?://#i', $resource->url) ? $resource->url : 'https://' . $resource->url) : route('subject_content.resources.file', $resource);
                                                                                    $btnIcon = $resourceType === 'link' ? 'ki-link' : 'ki-eye';
                                                                                    $btnText = $resourceType === 'link' ? 'فتح الرابط' : 'معاينة';
                                                                                    
                                                                                    if ($resourceType === 'video' && !$isExternal) {
                                                                                        $fsType = 'video';
                                                                                    } elseif ($resourceType === 'image') {
                                                                                        $fsType = 'image';
                                                                                    } else {
                                                                                        $fsType = 'iframe';
                                                                                    }
                                                                                @endphp
                                                                                <div class="col-12 col-md-6">
                                                                                    <div class="admin-resource-card bg-body">
                                                                                        <div class="d-flex align-items-start gap-3 mb-3">
                                                                                            <span class="admin-resource-icon {{ $resourceType }}"><i class="ki-duotone {{ $icon }} fs-2"><span class="path1"></span><span class="path2"></span></i></span>
                                                                                            <div>
                                                                                                <div class="fw-bold text-gray-800">{{ $resource->title }}</div>
                                                                                                <div class="admin-resource-badge bg-light-dark text-dark mt-1">{{ $title }}</div>
                                                                                            </div>
                                                                                        </div>

                                                                                        <div class="mt-auto pt-2 border-top border-gray-200 border-dashed d-flex justify-content-end align-items-center flex-wrap gap-2">
                                                                                            <button type="button" class="btn btn-sm btn-light-info fw-bold copy-btn" data-clipboard-text="{{ $previewLink }}" onclick="copyToClipboard(this)">
                                                                                                <i class="ki-duotone ki-copy fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> نسخ الرابط
                                                                                            </button>

                                                                                            @php
                                                                                                if ($resourceType === 'video' && !$isExternal) {
                                                                                                    $viewerType = 'video';
                                                                                                } elseif ($resourceType === 'image') {
                                                                                                    $viewerType = 'image';
                                                                                                } elseif (in_array($resourceType, ['link', 'zoom'])) {
                                                                                                    $viewerType = 'external_link';
                                                                                                } else {
                                                                                                    $viewerType = 'iframe';
                                                                                                }
                                                                                            @endphp
                                                                                            <button type="button" class="btn btn-sm btn-light-success fw-bold" onclick="openPreviewModal('{{ $previewLink }}', '{{ $viewerType }}', '{{ addslashes($resource->title) }}')">
                                                                                                <i class="ki-duotone ki-eye fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> معاينة
                                                                                            </button>

                                                                                            <a href="{{ route('subject_content.manage', \Illuminate\Support\Facades\Crypt::encrypt($selectedSubject->id)) }}" class="btn btn-sm btn-light-primary fw-bold">
                                                                                                <i class="ki-duotone ki-pencil fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> تعديل
                                                                                            </a>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @empty
                                                            <div class="text-center text-muted py-3">لا توجد دروس في هذه الوحدة.</div>
                                                        @endforelse
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="text-center text-muted py-8">
                                                    <i class="ki-duotone ki-folder-cross fs-5x text-gray-300 d-block mb-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                    لا توجد موارد مخصصة لهذه المجموعة.
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="previewModalTitle">معاينة المورد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="previewModalBody" style="min-height: 400px; display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                <!-- Content will be injected here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openPreviewModal(url, type, title) {
        const modalEl = document.getElementById('previewModal');
        const modal = new bootstrap.Modal(modalEl);
        
        document.getElementById('previewModalTitle').innerText = title;
        const body = document.getElementById('previewModalBody');
        
        // Show loading spinner
        body.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">جاري التحميل...</span></div>';
        
        modal.show();

        setTimeout(() => {
            let content = '';
            if (type === 'image') {
                content = `<img src="${url}" class="img-fluid" style="max-height: 75vh;" alt="${title}">`;
            } else if (type === 'video') {
                content = `<video controls autoplay style="width: 100%; max-height: 75vh;"><source src="${url}" type="video/mp4">المتصفح الخاص بك لا يدعم تشغيل الفيديو.</video>`;
            } else if (type === 'external_link') {
                content = `
                    <div class="text-center p-10 py-20 w-100">
                        <i class="ki-duotone ki-link fs-5tx text-primary mb-5"><span class="path1"></span><span class="path2"></span></i>
                        <h3 class="mb-5 text-gray-800">هذا المرفق عبارة عن رابط خارجي</h3>
                        <p class="text-gray-500 mb-8 fs-5">لضمان عرض الرابط بشكل صحيح وتجنب مشاكل الأمان، يرجى فتحه في صفحة جديدة.</p>
                        <a href="${url}" target="_blank" class="btn btn-primary btn-lg px-10">
                            <i class="ki-duotone ki-external-link fs-2 me-2"><span class="path1"></span><span class="path2"></span></i> فتح الرابط الآن
                        </a>
                    </div>
                `;
            } else {
                // iframe
                content = `<iframe src="${url}" style="width: 100%; height: 75vh; border: none;" allowfullscreen></iframe>`;
            }
            body.innerHTML = content;
        }, 300);
    }

    // Stop video and clear content when modal closes
    document.getElementById('previewModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('previewModalBody').innerHTML = '';
    });

    function copyToClipboard(button) {
        const link = button.getAttribute('data-clipboard-text');
        navigator.clipboard.writeText(link).then(() => {
            const originalHtml = button.innerHTML;
            button.innerHTML = '<i class="ki-duotone ki-check fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> تم النسخ';
            button.classList.remove('btn-light-info');
            button.classList.add('btn-light-success');
            
            setTimeout(() => {
                button.innerHTML = originalHtml;
                button.classList.remove('btn-light-success');
                button.classList.add('btn-light-info');
            }, 2000);
        });
    }
</script>
@endpush
