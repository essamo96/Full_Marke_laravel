@extends('admin.layout.mainLayouts.master')
@section('title', 'إدارة المحتوى التعليمي - ' . $subject->name_ar)

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('subject_content.view') }}" class="text-muted text-hover-primary">المحتوى التعليمي</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-dark">{{ $subject->name_ar }}</li>

  @endsection

@section('page-content')
<div class="row g-5 g-xl-10">
    <div class="col-xl-4 mb-5 mb-xl-10">
        <!-- Subject Info Card -->
        <div class="card card-flush h-xl-100">
            <div class="card-header pt-7">
                <div class="card-title">
                    <i class="ki-duotone ki-book fs-1 me-2 text-primary"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                    <h2>تفاصيل المادة</h2>
                </div>
            </div>
            <div class="card-body pt-5">
                <div class="d-flex flex-center flex-column mb-5">
                    <div class="symbol symbol-100px mb-7">
                        <img src="{{ $subject->image ? (str_starts_with($subject->image, 'site/') ? asset($subject->image) : asset('storage/' . $subject->image)) : asset('assets/admin/media/avatars/blank.png') }}" alt="image" />
                    </div>
                    <a href="#" class="fs-3 text-gray-800 text-hover-primary fw-bold mb-1">{{ $subject->name_ar }}</a>
                    <div class="fs-5 fw-semibold text-muted mb-6">{{ $subject->program ? $subject->program->name_ar : '-' }}</div>
                </div>
                <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-6">
                    <i class="ki-duotone ki-information-5 fs-2tx text-warning me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                    <div class="d-flex flex-stack flex-grow-1">
                        <div class="fw-semibold">
                            <h4 class="text-gray-900 fw-bold">هيكلية المحتوى</h4>
                            <div class="fs-6 text-gray-700">هذه الشاشة تمكنك من إضافة محتوى مقسم إلى (وحدات > دروس > ملفات/فيديوهات) لهذه المادة تحديداً.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-8 mb-5 mb-xl-10">
        <!-- Content Management Card -->
        <div class="card card-flush h-xl-100">
            <div class="card-header pt-7">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-800">المحتوى التعليمي</span>
                    <span class="text-gray-400 mt-1 fw-semibold fs-6">إدارة الوحدات، الدروس، والمرفقات</span>
                </h3>
                <div class="card-toolbar">
                    <button type="button" class="btn btn-sm btn-light-success me-2" onclick="openGeneralResourceModal()">
                        <i class="ki-duotone ki-plus fs-2"></i> مرفق عام (بدون وحدة)
                    </button>
                    <button type="button" class="btn btn-sm btn-light-primary" onclick="openUnitModal()">
                        <i class="ki-duotone ki-plus fs-2"></i> إضافة وحدة جديدة
                    </button>
                </div>
            </div>
            <div class="card-body pt-2">
                @if($groups->count() > 0)
                    <ul class="nav nav-pills mb-6" id="kt_group_tabs">
                        <li class="nav-item">
                            <a class="nav-link {{ is_null($selectedGroupId) ? 'active' : '' }}" href="{{ route('subject_content.manage', \Illuminate\Support\Facades\Crypt::encrypt($subject->id)) }}">
                                محتوى مشترك (كل المجموعات)
                            </a>
                        </li>
                        @foreach($groups as $group)
                            <li class="nav-item">
                                <a class="nav-link {{ $selectedGroupId === $group->id ? 'active' : '' }}" href="{{ route('subject_content.manage', \Illuminate\Support\Facades\Crypt::encrypt($subject->id)) }}?group={{ $group->id }}">
                                    {{ $group->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
                @php
                    $buildPreviewItem = function ($resource, $lessonName = null) {
                        $isExternal = $resource->isExternalLink();
                        $status = $resource->processing_status;
                        $ready = true;
                        $viewer = $resource->type;
                        $url = null;

                        if ($isExternal) {
                            $url = $resource->url;
                            $viewer = in_array($resource->type, ['video', 'link', 'zoom'], true) ? 'link' : $resource->type;
                        } elseif ($resource->type === 'video' && in_array($status, ['processing', 'failed'], true)) {
                            $ready = false;
                            $viewer = 'video';
                        } else {
                            $url = route('subject_content.resources.file', $resource);
                            if ($resource->isImage()) {
                                $viewer = 'image';
                            } elseif ($resource->type === 'video') {
                                $viewer = 'video';
                            } else {
                                $viewer = 'document';
                            }
                        }

                        return [
                            'id' => $resource->id,
                            'title' => $resource->title,
                            'type' => $resource->type,
                            'viewer' => $viewer,
                            'url' => $url,
                            'ready' => $ready,
                            'status' => $status,
                            'lesson' => $lessonName,
                        ];
                    };
                @endphp

                <!-- Accordion for Units -->
                <div class="accordion accordion-icon-toggle" id="kt_accordion_units">
                    @forelse($units as $unit)
                        @php
                            $unitPreviewItems = $unit->lessons->flatMap(function ($lesson) use ($buildPreviewItem) {
                                return $lesson->resources->map(fn ($r) => $buildPreviewItem($r, $lesson->name_ar));
                            })->values();
                        @endphp
                        <div class="mb-5">
                            <div class="accordion-header py-3 d-flex" data-bs-toggle="collapse" data-bs-target="#kt_accordion_unit_{{ $unit->id }}">
                                <span class="accordion-icon"><i class="ki-duotone ki-arrow-right fs-4"><span class="path1"></span><span class="path2"></span></i></span>
                                <h3 class="fs-4 fw-semibold mb-0 ms-4">{{ $unit->name_ar }}</h3>
                                <div class="ms-auto">
                                    <button type="button" class="btn btn-sm btn-icon btn-light-info me-2" title="معاينة مرفقات الوحدة" onclick='event.stopPropagation(); openGalleryPreview(@json($unitPreviewItems), @json($unit->name_ar), "وحدة")'><i class="bi bi-eye"></i></button>
                                    <button type="button" class="btn btn-sm btn-icon btn-light-primary me-2" title="تعديل الوحدة" onclick="event.stopPropagation(); openEditUnitModal('{{ $unit->getRouteKey() }}', {{ json_encode(['name_ar' => $unit->name_ar, 'name_en' => $unit->name_en, 'group_ids' => $unit->groups->pluck('id'), 'is_shared' => $unit->is_shared]) }})"><i class="bi bi-pencil"></i></button>
                                    <button type="button" class="btn btn-sm btn-icon btn-light-warning me-2" title="مشاركة مع مجموعات أخرى" onclick="event.stopPropagation(); openShareModal('unit', '{{ $unit->getRouteKey() }}', {{ json_encode($unit->groups->pluck('id')) }}, {{ $unit->is_shared ? 'true' : 'false' }})"><i class="bi bi-share"></i></button>
                                    <button type="button" class="btn btn-sm btn-icon btn-light-success me-2" title="إضافة درس" onclick="event.stopPropagation(); openLessonModal('{{ $unit->getRouteKey() }}')"><i class="ki-duotone ki-plus fs-4"></i></button>
                                    <button type="button" class="btn btn-sm btn-icon btn-light-danger" title="حذف الوحدة" onclick="event.stopPropagation(); deleteUnit('{{ $unit->getRouteKey() }}')"><i class="bi bi-trash"></i></button>
                                </div>
                            </div>
                            <div id="kt_accordion_unit_{{ $unit->id }}" class="fs-6 collapse" data-bs-parent="#kt_accordion_units">
                                <div class="p-5 border border-dashed rounded mt-4">
                                    @if($unit->lessons->count() > 0)
                                        @foreach($unit->lessons as $lesson)
                                            @php
                                                $lessonPreviewItems = $lesson->resources->map(fn ($r) => $buildPreviewItem($r, $lesson->name_ar))->values();
                                            @endphp
                                            <div class="bg-light p-3 rounded mb-3">
                                                <div class="d-flex flex-stack">
                                                    <div class="d-flex align-items-center">
                                                        <i class="ki-duotone ki-book-open fs-2 text-primary me-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                                        <span class="fw-bold fs-6">{{ $lesson->name_ar }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="badge badge-light-info me-2">{{ $lesson->resources->count() }} مرفق</span>
                                                        <button type="button" class="btn btn-sm btn-icon btn-light-info me-2" title="معاينة مرفقات الدرس" onclick='openGalleryPreview(@json($lessonPreviewItems), @json($lesson->name_ar), "درس")'><i class="bi bi-eye"></i></button>
                                                        <button type="button" class="btn btn-sm btn-icon btn-light-primary me-2" title="تعديل الدرس" onclick="openEditLessonModal('{{ $lesson->getRouteKey() }}', {{ json_encode(['name_ar' => $lesson->name_ar, 'name_en' => $lesson->name_en, 'group_ids' => $lesson->groups->pluck('id'), 'is_shared' => $lesson->is_shared]) }})"><i class="bi bi-pencil"></i></button>
                                                        <button type="button" class="btn btn-sm btn-icon btn-light-warning me-2" title="مشاركة مع مجموعات أخرى" onclick="openShareModal('lesson', '{{ $lesson->getRouteKey() }}', {{ json_encode($lesson->groups->pluck('id')) }}, {{ $lesson->is_shared ? 'true' : 'false' }})"><i class="bi bi-share"></i></button>
                                                        <button type="button" class="btn btn-sm btn-light-primary" data-bs-toggle="collapse" data-bs-target="#kt_lesson_resources_{{ $lesson->id }}">إدارة المرفقات</button>
                                                        <button type="button" class="btn btn-sm btn-icon btn-light-danger" title="حذف الدرس" onclick="deleteLesson('{{ $lesson->getRouteKey() }}')"><i class="bi bi-trash"></i></button>
                                                    </div>
                                                </div>
                                                <div id="kt_lesson_resources_{{ $lesson->id }}" class="collapse mt-4">
                                                    <div class="table-responsive mb-3">
                                                        <table class="table table-sm table-row-bordered">
                                                            <thead>
                                                                <tr class="fw-semibold fs-7 text-gray-700">
                                                                    <th>العنوان</th>
                                                                    <th>النوع</th>
                                                                    <th>الملف/الرابط</th>
                                                                    <th class="text-end">إجراء</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @forelse($lesson->resources as $resource)
                                                                    @php
                                                                        $excludedIds = $resource->contentExclusions->pluck('student_id')->values();
                                                                        $resourcePayload = [
                                                                            'title' => $resource->title,
                                                                            'type' => $resource->type,
                                                                            'url' => $resource->url,
                                                                            'description' => $resource->description,
                                                                            'allow_download' => $resource->allow_download,
                                                                            'is_shared' => (bool) $resource->is_shared,
                                                                            'group_ids' => $resource->groups->pluck('id'),
                                                                            'excluded_student_ids' => $excludedIds,
                                                                            'groups' => $resource->groups->map(fn ($g) => ['id' => $g->id, 'name' => $g->name]),
                                                                        ];
                                                                        $singlePreview = [$buildPreviewItem($resource, $lesson->name_ar)];
                                                                    @endphp
                                                                    <tr>
                                                                        <td>{{ $resource->title }}</td>
                                                                        <td><span class="badge badge-light-primary">{{ $resource->type }}</span></td>
                                                                        <td>
                                                                            @if($resource->isExternalLink())
                                                                                <button type="button" class="btn btn-sm btn-light-info me-1" title="معاينة" onclick='openGalleryPreview(@json($singlePreview), @json($resource->title), "مرفق")'><i class="bi bi-eye"></i></button>
                                                                                <a href="{{ $resource->url }}" target="_blank" class="btn btn-sm btn-light-primary">فتح</a>
                                                                            @elseif($resource->type === 'document' || $resource->isImage())
                                                                                <button type="button" class="btn btn-sm btn-light-info me-1" title="معاينة" onclick='openGalleryPreview(@json($singlePreview), @json($resource->title), "مرفق")'><i class="bi bi-eye"></i></button>
                                                                                <a href="{{ route('subject_content.resources.file', $resource) }}" target="_blank" class="btn btn-sm btn-light-primary">فتح</a>
                                                                            @elseif($resource->processing_status === 'processing')
                                                                                <span class="badge badge-light-warning">جاري المعالجة...</span>
                                                                            @elseif($resource->processing_status === 'failed')
                                                                                <span class="badge badge-light-danger" title="{{ $resource->processing_error }}">فشلت المعالجة</span>
                                                                            @else
                                                                                <button type="button" class="btn btn-sm btn-light-info me-1" title="معاينة" onclick='openGalleryPreview(@json($singlePreview), @json($resource->title), "مرفق")'><i class="bi bi-eye"></i></button>
                                                                                <span class="badge badge-light-success">جاهز (يُعرض للطالب)</span>
                                                                            @endif
                                                                            @if($excludedIds->count())
                                                                                <span class="badge badge-light-danger ms-1">{{ $excludedIds->count() }} مستثنى</span>
                                                                            @endif
                                                                        </td>
                                                                        <td class="text-end">
                                                                            <button type="button" class="btn btn-icon btn-sm btn-light-primary me-1" onclick='openEditResourceModal(@json($resource->getRouteKey()), @json($resourcePayload))'><i class="bi bi-pencil"></i></button>
                                                                            <button type="button" class="btn btn-icon btn-sm btn-light-warning me-1" title="مشاركة مع مجموعات أخرى" onclick='openShareModal("resource", @json($resource->getRouteKey()), @json($resource->groups->pluck("id")), {{ $resource->is_shared ? "true" : "false" }})'><i class="bi bi-share"></i></button>
                                                                            <button type="button" class="btn btn-icon btn-sm btn-light-danger" onclick="deleteResource('{{ $resource->getRouteKey() }}')"><i class="bi bi-trash"></i></button>
                                                                        </td>
                                                                    </tr>
                                                                @empty
                                                                    <tr><td colspan="4" class="text-center text-muted">لا توجد مرفقات لهذا الدرس</td></tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-light-success" onclick="openResourceModal('{{ $lesson->getRouteKey() }}')">
                                                        <i class="bi bi-plus-lg fs-4 me-2"></i> إضافة مرفق (فيديو / PDF / رابط)
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="text-center text-muted py-3">لا توجد دروس في هذه الوحدة</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-10">
                            <i class="ki-duotone ki-folder-cross fs-3x mb-3 text-gray-400"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                            <h5>لا توجد وحدات لهذه المادة حتى الآن</h5>
                        </div>
                    @endforelse
                </div>

                {{-- Standalone resources (no unit/lesson) --}}
                <div class="separator my-8"></div>
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h4 class="fw-bold mb-1">مرفقات عامة (بدون وحدة)</h4>
                        <div class="text-muted fs-7">تظهر مباشرة للطالب مع نفس تحكم السكوب والاستثناءات</div>
                    </div>
                    <div class="d-flex gap-2">
                        @php
                            $generalPreviewItems = $generalResources->map(fn ($r) => $buildPreviewItem($r, null))->values();
                        @endphp
                        <button type="button" class="btn btn-sm btn-light-info" title="معاينة المرفقات العامة" onclick='openGalleryPreview(@json($generalPreviewItems), "مرفقات عامة", "عام")'>
                            <i class="bi bi-eye me-1"></i> معاينة الكل
                        </button>
                        <button type="button" class="btn btn-sm btn-light-success" onclick="openGeneralResourceModal()">
                            <i class="bi bi-plus-lg me-1"></i> إضافة مرفق عام
                        </button>
                    </div>
                </div>
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-row-bordered align-middle">
                        <thead>
                            <tr class="fw-semibold fs-7 text-gray-700">
                                <th>العنوان</th>
                                <th>النوع</th>
                                <th>السكوب</th>
                                <th>مستثنون</th>
                                <th class="text-end">إجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($generalResources as $resource)
                                @php
                                    $excludedIds = $resource->contentExclusions->pluck('student_id')->values();
                                    $resourcePayload = [
                                        'title' => $resource->title,
                                        'type' => $resource->type,
                                        'url' => $resource->url,
                                        'description' => $resource->description,
                                        'allow_download' => $resource->allow_download,
                                        'is_shared' => (bool) $resource->is_shared,
                                        'group_ids' => $resource->groups->pluck('id'),
                                        'excluded_student_ids' => $excludedIds,
                                        'groups' => $resource->groups->map(fn ($g) => ['id' => $g->id, 'name' => $g->name]),
                                    ];
                                    $singlePreview = [$buildPreviewItem($resource, null)];
                                @endphp
                                <tr>
                                    <td>{{ $resource->title }}</td>
                                    <td><span class="badge badge-light-primary">{{ $resource->type }}</span></td>
                                    <td>
                                        @if($resource->is_shared)
                                            <span class="badge badge-light-success">كل المجموعات</span>
                                        @elseif($resource->groups->isEmpty())
                                            <span class="badge badge-light-warning">مسودة</span>
                                        @else
                                            {{ $resource->groups->pluck('name')->join('، ') }}
                                        @endif
                                    </td>
                                    <td>{{ $excludedIds->count() ?: '—' }}</td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-icon btn-sm btn-light-info me-1" title="معاينة" onclick='openGalleryPreview(@json($singlePreview), @json($resource->title), "مرفق")'><i class="bi bi-eye"></i></button>
                                        <button type="button" class="btn btn-icon btn-sm btn-light-primary me-1" onclick='openEditResourceModal(@json($resource->getRouteKey()), @json($resourcePayload))'><i class="bi bi-pencil"></i></button>
                                        <button type="button" class="btn btn-icon btn-sm btn-light-warning me-1" title="مشاركة" onclick='openShareModal("resource", @json($resource->getRouteKey()), @json($resource->groups->pluck("id")), {{ $resource->is_shared ? "true" : "false" }})'><i class="bi bi-share"></i></button>
                                        <button type="button" class="btn btn-icon btn-sm btn-light-danger" onclick="deleteResource('{{ $resource->getRouteKey() }}')"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-5">لا توجد مرفقات عامة بعد</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Unit Modal -->
<div class="modal fade" tabindex="-1" id="kt_modal_add_unit">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">إضافة وحدة تعليمية جديدة</h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <form id="kt_form_add_unit">
                <div class="modal-body">
                    <div class="mb-5">
                        <label class="form-label required">اسم الوحدة بالعربية</label>
                        <input type="text" name="name_ar" class="form-control" placeholder="مثال: الوحدة الأولى: مقدمة في المادة" required/>
                    </div>
                    <div class="mb-5">
                        <label class="form-label">اسم الوحدة بالإنجليزية</label>
                        <input type="text" name="name_en" class="form-control" placeholder="مثال: Unit 1: Introduction"/>
                    </div>
                    <div class="mb-5">
                        <div class="form-check form-switch">
                            <input class="form-check-input is-shared-checkbox" type="checkbox" value="1" name="is_shared" {{ !$selectedGroupId ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold">مرئي لكل مجموعات المادة (بدون تحديد مجموعة)</label>
                        </div>
                    </div>
                    <div class="mb-5 group-selection-container" style="display: {{ !$selectedGroupId ? 'none' : 'block' }};">
                        <label class="form-label fw-bold">مرئي في المجموعات المحددة (يمكن اختيار أكثر من مجموعة دون إعادة رفع)</label>
                        <select name="group_ids[]" class="form-select" data-control="select2" multiple="multiple">
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}" {{ $selectedGroupId == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Lesson Modal -->
<div class="modal fade" tabindex="-1" id="kt_modal_add_lesson">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">إضافة درس جديد</h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <form id="kt_form_add_lesson">
                <div class="modal-body">
                    <div class="mb-5">
                        <label class="form-label required">اسم الدرس بالعربية</label>
                        <input type="text" name="name_ar" class="form-control" placeholder="مثال: الدرس الأول" required/>
                    </div>
                    <div class="mb-5">
                        <label class="form-label">اسم الدرس بالإنجليزية</label>
                        <input type="text" name="name_en" class="form-control" placeholder="مثال: Lesson 1"/>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Resource Modal -->
<div class="modal fade" tabindex="-1" id="kt_modal_add_resource">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">إضافة مرفق تعليمي</h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <form id="kt_form_add_resource" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-5">
                        <label class="form-label required">عنوان المرفق</label>
                        <input type="text" name="title" class="form-control" placeholder="مثال: شرح الوحدة بالفيديو" required/>
                    </div>
                    <div class="mb-5">
                        <label class="form-label required">نوع المرفق</label>
                        <select name="type" id="resource_type" class="form-select" required>
                            <option value="video">فيديو</option>
                            <option value="document">ملف / PDF</option>
                            <option value="image">صورة</option>
                            <option value="link">رابط خارجي (يوتيوب، إلخ)</option>
                            <option value="zoom">رابط Zoom</option>
                        </select>
                    </div>
                    <div class="mb-5">
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_shared" value="0">
                            <input class="form-check-input is-shared-checkbox" type="checkbox" value="1" name="is_shared" {{ !$selectedGroupId ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold">مرئي لكل مجموعات المادة</label>
                        </div>
                    </div>
                    <div class="mb-5 group-selection-container" style="display: {{ !$selectedGroupId ? 'none' : 'block' }};">
                        <label class="form-label fw-bold">مرئي في المجموعات المحددة</label>
                        <select name="group_ids[]" id="resource_groups" class="form-select" data-control="select2" data-placeholder="اختر المجموعات..." multiple="multiple">
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}" {{ $selectedGroupId == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-5">
                        <label class="form-label fw-bold">استثناء طلاب (لن يروا هذا المرفق رغم السكوب)</label>
                        <select name="excluded_student_ids[]" id="resource_excluded_students" class="form-select" data-control="select2" data-placeholder="اختر طلاباً للاستثناء..." multiple="multiple">
                            @foreach($subjectStudents as $st)
                                <option value="{{ $st['id'] }}" data-group-id="{{ $st['group_id'] }}">{{ $st['name'] }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">الاستثناء يتفوق على المشاركة والمجموعات والمنح الفردية.</div>
                    </div>
                    <div class="mb-5" id="resource_video_field">
                        <label class="form-label">رفع فيديو</label>
                        <div class="d-flex align-items-center gap-3">
                            <button type="button" id="resource_video_browse" class="btn btn-light-primary btn-sm">
                                <i class="ki-duotone ki-folder-up fs-3"></i> اختر ملف الفيديو
                            </button>
                            <span id="resource_video_filename" class="text-muted fs-7"></span>
                        </div>
                        <div class="progress mt-3 d-none" id="resource_video_progress_wrap" style="height: 8px;">
                            <div class="progress-bar" id="resource_video_progress" role="progressbar" style="width: 0%"></div>
                        </div>
                        <div class="form-text">
                            يدعم الرفع المجزّأ (Chunked) القابل للاستئناف — إذا انقطع الاتصال بالإنترنت أثناء الرفع فسيكمل تلقائيًا من حيث توقف بمجرد عودة الاتصال.
                        </div>
                    </div>
                    <div class="mb-5" id="resource_document_field">
                        <label class="form-label">رفع ملف (PDF / مستند)</label>
                        <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx"/>
                    </div>
                    <div class="mb-5 d-none" id="resource_image_field">
                        <label class="form-label">رفع صورة</label>
                        <input type="file" name="file" class="form-control" accept=".jpg,.jpeg,.png,.webp,.gif"/>
                    </div>
                    <div class="mb-5" id="resource_url_field">
                        <label class="form-label">رابط خارجي</label>
                        <input type="text" name="url" class="form-control" placeholder="https://..."/>
                    </div>
                    <input type="hidden" name="uploaded_path" id="resource_uploaded_path"/>
                    <input type="hidden" name="original_filename" id="resource_original_filename"/>
                    <div class="mb-5">
                        <label class="form-label">وصف مختصر</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-5">
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" value="1" id="allow_download_check" name="allow_download"/>
                            <label class="form-check-label" for="allow_download_check">
                                السماح للطلاب بالتحميل
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Resource Modal -->
<div class="modal fade" tabindex="-1" id="kt_modal_edit_resource">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">تعديل المرفق التعليمي</h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <form id="kt_form_edit_resource" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="_method" value="PUT">
                    <div class="mb-5">
                        <label class="form-label required">عنوان المرفق</label>
                        <input type="text" name="title" id="edit_resource_title" class="form-control" required/>
                    </div>
                    <div class="mb-5">
                        <label class="form-label required">نوع المرفق</label>
                        <select name="type" id="edit_resource_type" class="form-select" required>
                            <option value="video">فيديو</option>
                            <option value="document">ملف / PDF</option>
                            <option value="image">صورة</option>
                            <option value="link">رابط خارجي (يوتيوب، إلخ)</option>
                            <option value="zoom">رابط Zoom</option>
                        </select>
                    </div>
                    <div class="mb-5">
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_shared" value="0">
                            <input class="form-check-input is-shared-checkbox" type="checkbox" value="1" name="is_shared" id="edit_resource_is_shared">
                            <label class="form-check-label fw-bold">مرئي لكل مجموعات المادة</label>
                        </div>
                    </div>
                    <div class="mb-5 group-selection-container">
                        <label class="form-label fw-bold">مرئي في المجموعات المحددة</label>
                        <select name="group_ids[]" id="edit_resource_groups" class="form-select" data-control="select2" data-placeholder="اختر المجموعات..." multiple="multiple">
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-5">
                        <label class="form-label fw-bold">استثناء طلاب</label>
                        <select name="excluded_student_ids[]" id="edit_resource_excluded_students" class="form-select" data-control="select2" data-placeholder="اختر طلاباً للاستثناء..." multiple="multiple">
                            @foreach($subjectStudents as $st)
                                <option value="{{ $st['id'] }}" data-group-id="{{ $st['group_id'] }}">{{ $st['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="alert alert-info d-flex align-items-center p-3 mb-5">
                        <i class="bi bi-info-circle fs-2 text-info me-3"></i>
                        <span>للاحتفاظ بالملف الحالي، اترك حقل الرفع/الرابط فارغاً. عند رفع ملف جديد سيتم مسح الملف القديم نهائياً.</span>
                    </div>

                    <div class="mb-5" id="edit_resource_video_field">
                        <label class="form-label">رفع فيديو جديد</label>
                        <div class="d-flex align-items-center gap-3">
                            <button type="button" id="edit_resource_video_browse" class="btn btn-light-primary btn-sm">
                                <i class="ki-duotone ki-folder-up fs-3"></i> اختر ملف الفيديو
                            </button>
                            <span id="edit_resource_video_filename" class="text-muted fs-7"></span>
                        </div>
                        <div class="progress mt-3 d-none" id="edit_resource_video_progress_wrap" style="height: 8px;">
                            <div class="progress-bar" id="edit_resource_video_progress" role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>
                    <div class="mb-5" id="edit_resource_document_field">
                        <label class="form-label">رفع ملف جديد (PDF / مستند)</label>
                        <input type="file" name="file" id="edit_resource_file_doc" class="form-control" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx"/>
                    </div>
                    <div class="mb-5 d-none" id="edit_resource_image_field">
                        <label class="form-label">رفع صورة جديدة</label>
                        <input type="file" name="file" id="edit_resource_file_img" class="form-control" accept=".jpg,.jpeg,.png,.webp,.gif"/>
                    </div>
                    <div class="mb-5" id="edit_resource_url_field">
                        <label class="form-label">رابط خارجي</label>
                        <input type="text" name="url" id="edit_resource_url" class="form-control" placeholder="https://..."/>
                    </div>
                    <input type="hidden" name="uploaded_path" id="edit_resource_uploaded_path"/>
                    <input type="hidden" name="original_filename" id="edit_resource_original_filename"/>
                    <div class="mb-5">
                        <label class="form-label">وصف مختصر</label>
                        <textarea name="description" id="edit_resource_description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-5">
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" value="1" id="edit_allow_download_check" name="allow_download"/>
                            <label class="form-check-label" for="edit_allow_download_check">
                                السماح للطلاب بالتحميل
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Upload Progress Drawer -->
<div id="kt_upload_progress_drawer" class="bg-body" data-kt-drawer="true" data-kt-drawer-name="upload_progress" data-kt-drawer-activate="true" data-kt-drawer-overlay="false" data-kt-drawer-width="{default:'300px', 'md': '400px'}" data-kt-drawer-direction="end" data-kt-drawer-close="#kt_upload_progress_close">
    <div class="card w-100 rounded-0 border-0 h-100">
        <div class="card-header pe-5">
            <div class="card-title">
                <div class="d-flex justify-content-center flex-column me-3">
                    <a href="#" class="fs-4 fw-bold text-gray-900 text-hover-primary me-1 lh-1">جاري الرفع...</a>
                </div>
            </div>
            <div class="card-toolbar">
                <div class="btn btn-sm btn-icon btn-active-light-primary" id="kt_upload_progress_close">
                    <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
        </div>
        <div class="card-body hover-scroll-overlay-y">
            <div class="d-flex align-items-center mb-5">
                <i class="ki-duotone ki-file-up fs-2x text-primary me-3"><span class="path1"></span><span class="path2"></span></i>
                <div class="d-flex flex-column">
                    <span class="fw-bold text-gray-800" id="upload_drawer_filename" style="word-break: break-all;">اسم الملف</span>
                    <span class="text-gray-400 fw-semibold" id="upload_drawer_size">0 MB</span>
                </div>
            </div>
            
            <div class="d-flex flex-column w-100 mt-5">
                <div class="d-flex justify-content-between mb-2 fs-6 fw-bold">
                    <span class="text-muted" id="upload_drawer_speed" dir="ltr">0 MB/s</span>
                    <span class="text-primary" id="upload_drawer_percentage">0%</span>
                </div>
                <div class="progress h-8px mb-2">
                    <div class="progress-bar bg-primary" id="upload_drawer_progress" role="progressbar" style="width: 0%"></div>
                </div>
                <div class="d-flex justify-content-between mb-2 fs-7 fw-semibold text-gray-600">
                    <span id="upload_drawer_time_remaining">جارِ الحساب...</span>
                    <span id="upload_drawer_uploaded" dir="ltr">0 MB / 0 MB</span>
                </div>
            </div>

            <div class="mt-5 notice d-flex bg-light-primary rounded border-primary border border-dashed p-6">
                <div class="d-flex flex-stack flex-grow-1">
                    <div class="fw-semibold">
                        <div class="fs-6 text-gray-700">يمكنك المتابعة في استخدام النظام أثناء رفع الفيديو، وسوف تكتمل العملية في الخلفية طالما لم تغلق هذه الصفحة.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Processing Progress Toast -->
<div id="kt_processing_progress_toast" class="bg-body shadow-sm rounded border d-none" style="position: fixed; bottom: 2rem; left: 2rem; width: 350px; z-index: 1055;">
    <div class="card w-100 rounded-0 border-0 h-100">
        <div class="card-header pe-5 min-h-50px">
            <div class="card-title">
                <div class="d-flex justify-content-center flex-column me-3">
                    <a href="#" class="fs-5 fw-bold text-gray-900 text-hover-primary me-1 lh-1">جاري المعالجة...</a>
                </div>
            </div>
            <div class="card-toolbar">
                <div class="btn btn-sm btn-icon btn-active-light-primary" onclick="document.getElementById('kt_processing_progress_toast').classList.add('d-none')">
                    <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="d-flex align-items-center mb-4">
                <i class="ki-duotone ki-setting-2 fs-2x text-primary me-3"><span class="path1"></span><span class="path2"></span></i>
                <div class="d-flex flex-column">
                    <span class="fw-bold text-gray-800 fs-7" style="word-break: break-all;">تحضير الفيديو</span>
                    <span class="text-gray-400 fw-semibold fs-8">يتم تحويل الفيديو وتشفيره...</span>
                </div>
            </div>
            
            <div class="d-flex flex-column w-100">
                <div class="d-flex justify-content-between mb-2 fs-7 fw-bold">
                    <span class="text-muted">نسبة الإنجاز</span>
                    <span class="text-primary" id="processing_drawer_percentage">0%</span>
                </div>
                <div class="progress h-6px mb-2">
                    <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated" id="processing_drawer_progress" role="progressbar" style="width: 0%"></div>
                </div>
                <div class="d-flex justify-content-between fs-8 fw-semibold text-gray-600">
                    <span id="processing_drawer_time_remaining">جارِ المعالجة...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gallery Preview Modal -->
<div class="modal fade" tabindex="-1" id="kt_modal_gallery_preview" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content overflow-hidden">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h3 class="modal-title mb-1" id="gallery_preview_title">معاينة المحتوى</h3>
                    <div class="text-muted fs-7" id="gallery_preview_subtitle"></div>
                </div>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body pt-4 pb-0 px-0">
                <div class="row g-0 gallery-preview-layout">
                    <div class="col-lg-4 col-xl-3 border-end gallery-preview-sidebar">
                        <div class="px-5 pb-3 d-flex align-items-center justify-content-between">
                            <span class="fw-bold text-gray-800">المرفقات</span>
                            <span class="badge badge-light-primary" id="gallery_count">0</span>
                        </div>
                        <div class="gallery-preview-list px-3 pb-4" id="gallery_list"></div>
                    </div>
                    <div class="col-lg-8 col-xl-9 d-flex flex-column">
                        <div class="gallery-preview-stage flex-grow-1" id="gallery_stage">
                            <div class="text-center text-muted p-10" id="gallery_empty_state">
                                <i class="bi bi-eye fs-2x mb-3 d-block"></i>
                                اختر مرفقاً من القائمة للمعاينة
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between px-5 py-3 border-top">
                            <button type="button" class="btn btn-sm btn-light" id="gallery_prev_btn" onclick="galleryNavigate(-1)">
                                <i class="bi bi-chevron-right"></i> السابق
                            </button>
                            <span class="fs-7 text-muted" id="gallery_position_label">—</span>
                            <button type="button" class="btn btn-sm btn-light" id="gallery_next_btn" onclick="galleryNavigate(1)">
                                التالي <i class="bi bi-chevron-left"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Unit Modal -->
  <div class="modal fade teacher-content-modal" tabindex="-1" id="modal_edit_unit">
    <div class="modal-dialog">
      <div class="modal-content glass-panel">
        <div class="modal-header">
          <h5 class="modal-title">تعديل وحدة تعليمية</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form id="form_edit_unit">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">اسم الوحدة بالعربية</label>
              <input type="text" name="name_ar" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">اسم الوحدة بالإنجليزية</label>
              <input type="text" name="name_en" class="form-control">
            </div>
                        <div class="mb-3">
              <div class="form-check form-switch">
                <input class="form-check-input is-shared-checkbox" type="checkbox" value="1" name="is_shared" checked>
                <label class="form-check-label">محتوى عام لجميع المجموعات (Shared)</label>
              </div>
            </div>
            <div class="mb-3 group-selection-container" style="display: none;">
              <label class="form-label">المجموعات (تترك فارغة لحفظها كمسودة)</label>
              <select name="group_ids[]" class="form-select" data-control="select2" multiple="multiple">
                @foreach($groups as $group)
                  <option value="{{ $group->id }}">{{ $group->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-glass" data-bs-dismiss="modal">إلغاء</button>
            <button type="submit" class="btn btn-luxury">حفظ</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Lesson Modal -->
  <div class="modal fade teacher-content-modal" tabindex="-1" id="modal_edit_lesson">
    <div class="modal-dialog">
      <div class="modal-content glass-panel">
        <div class="modal-header">
          <h5 class="modal-title">تعديل درس</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form id="form_edit_lesson">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">اسم الدرس بالعربية</label>
              <input type="text" name="name_ar" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">اسم الدرس بالإنجليزية</label>
              <input type="text" name="name_en" class="form-control">
            </div>
                        <div class="mb-3">
              <div class="form-check form-switch">
                <input class="form-check-input is-shared-checkbox" type="checkbox" value="1" name="is_shared" checked>
                <label class="form-check-label">محتوى عام لجميع المجموعات (Shared)</label>
              </div>
            </div>
            <div class="mb-3 group-selection-container" style="display: none;">
              <label class="form-label">المجموعات (تترك فارغة لحفظها كمسودة)</label>
              <select name="group_ids[]" class="form-select" data-control="select2" multiple="multiple">
                @foreach($groups as $group)
                  <option value="{{ $group->id }}">{{ $group->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-glass" data-bs-dismiss="modal">إلغاء</button>
            <button type="submit" class="btn btn-luxury">حفظ</button>
          </div>
        </form>
      </div>
    </div>
  </div>

{{-- Share content with additional groups without re-upload --}}
<div class="modal fade" tabindex="-1" id="kt_modal_share_content">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">مشاركة مع مجموعات أخرى</h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <form id="kt_form_share_content">
                <div class="modal-body">
                    <input type="hidden" id="share_content_type">
                    <input type="hidden" id="share_content_id">
                    <div class="alert alert-info fs-7 py-2">
                        ستظهر نفس الوحدة/الدرس/الملف للمجموعات المختارة بدون رفع من جديد. لن يُزال من مجموعاته الحالية.
                    </div>
                    <div id="share_already_shared_msg" class="alert alert-success d-none">هذا المحتوى مرئي لكل مجموعات المادة بالفعل.</div>
                    <div class="mb-5" id="share_groups_wrap">
                        <label class="form-label fw-bold">المجموعات الإضافية</label>
                        <select id="share_group_ids" class="form-select" data-control="select2" multiple="multiple" data-placeholder="اختر المجموعات...">
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary" id="share_submit_btn">مشاركة</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .gallery-preview-layout { min-height: 62vh; }
    .gallery-preview-sidebar { background: var(--bs-body-bg); }
    .gallery-preview-list { max-height: 58vh; overflow-y: auto; }
    .gallery-preview-item {
        display: flex; align-items: flex-start; gap: .75rem;
        width: 100%; text-align: start; border: 1px solid transparent;
        border-radius: .75rem; padding: .75rem .85rem; margin-bottom: .5rem;
        background: var(--bs-gray-100, #f5f8fa); color: inherit; transition: .15s ease;
    }
    [data-bs-theme="dark"] .gallery-preview-item { background: rgba(255,255,255,.04); }
    .gallery-preview-item:hover { border-color: var(--bs-primary); }
    .gallery-preview-item.is-active {
        border-color: var(--bs-primary);
        background: rgba(var(--bs-primary-rgb, 0, 158, 247), .08);
        box-shadow: 0 0 0 1px rgba(var(--bs-primary-rgb, 0, 158, 247), .15);
    }
    .gallery-preview-item__icon {
        width: 2.25rem; height: 2.25rem; border-radius: .65rem;
        display: grid; place-items: center; flex: 0 0 auto;
        background: rgba(var(--bs-primary-rgb, 0, 158, 247), .12);
        color: var(--bs-primary);
    }
    .gallery-preview-item__title {
        font-weight: 700; font-size: .9rem; line-height: 1.35;
        color: var(--bs-body-color); margin-bottom: .15rem;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .gallery-preview-item__meta { font-size: .75rem; color: var(--bs-secondary-color, #a1a5b7); }
    .gallery-preview-stage {
        min-height: 52vh; display: flex; align-items: center; justify-content: center;
        background: var(--bs-body-bg); position: relative; overflow: hidden;
    }
    .gallery-preview-stage.is-media { background: #0b0d12; }
    .gallery-preview-stage video,
    .gallery-preview-stage iframe,
    .gallery-preview-stage img {
        width: 100%; height: 100%; max-height: 62vh; border: 0; outline: none;
    }
    .gallery-preview-stage img { object-fit: contain; max-width: 100%; height: auto; padding: 1rem; }
    .gallery-preview-stage video { max-height: 62vh; background: #000; }
    .gallery-preview-link-card {
        width: min(520px, 92%); margin: 1.5rem auto; text-align: center;
        padding: 2rem 1.5rem; border-radius: 1rem;
        border: 1px dashed var(--bs-border-color);
        background: var(--bs-body-bg);
    }
    @media (max-width: 991.98px) {
        .gallery-preview-sidebar { border-inline-end: 0 !important; border-bottom: 1px solid var(--bs-border-color); }
        .gallery-preview-list { max-height: 220px; }
        .gallery-preview-stage { min-height: 42vh; }
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/vendor/resumable/resumable.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    const subjectContentUnitsStoreUrl = '{{ route('subject_content.units.store', \Illuminate\Support\Facades\Crypt::encrypt($subject->id)) }}';
    const subjectContentUnitsBaseUrl = '{{ url('admin/subject-content/units') }}';
    const subjectContentLessonsBaseUrl = '{{ url('admin/subject-content/lessons') }}';
    const subjectContentResourcesBaseUrl = '{{ url('admin/subject-content/resources') }}';
    const subjectContentGeneralResourcesStoreUrl = '{{ route('subject_content.general_resources.store', \Illuminate\Support\Facades\Crypt::encrypt($subject->id)) }}';
    const csrfToken = '{{ csrf_token() }}';
    let isGeneralResourceMode = false;

    const chunkUploadUrl = '{{ route('subject_content.upload_chunk') }}';

    let modalAddUnit, modalAddLesson, modalAddResource, modalEditResource;
      let modalEditUnit, modalEditLesson;
  document.addEventListener('change', function(e) {
    if (e.target.matches('.is-shared-checkbox')) {
        const container = e.target.closest('form').querySelector('.group-selection-container');
        if (container) {
            container.style.display = e.target.checked ? 'none' : 'block';
        }
    }
});
let editUnitId = null;
  let editLessonId = null;
  let editResourceId = null;

  document.addEventListener('DOMContentLoaded', function () {
    modalEditUnit = new bootstrap.Modal(document.getElementById('modal_edit_unit'));
    modalEditLesson = new bootstrap.Modal(document.getElementById('modal_edit_lesson'));
    

    // Initialize Sortable for units
    const unitsAccordion = document.getElementById('unitsAccordion');
    if (unitsAccordion) {
      new Sortable(unitsAccordion, {
        animation: 150,
        handle: '.unit-toggle',
        onEnd: function (evt) {
          let order = Array.from(unitsAccordion.children).map(el => el.getAttribute('data-id')).filter(Boolean);
          $.post(unitsBaseUrl + '/reorder', { _token: csrfToken, order: order });
        }
      });
    }

    // Initialize Sortable for lessons
    document.querySelectorAll('[data-sortable="lessons"]').forEach(el => {
      new Sortable(el, {
        animation: 150,
        handle: '.lesson-toggle',
        onEnd: function (evt) {
          let order = Array.from(el.children).map(child => child.getAttribute('data-id')).filter(Boolean);
          $.post(lessonsBaseUrl + '/reorder', { _token: csrfToken, order: order });
        }
      });
    });

    // Initialize Sortable for resources
    document.querySelectorAll('[data-sortable="resources"]').forEach(el => {
      new Sortable(el, {
        animation: 150,
        onEnd: function (evt) {
          let order = Array.from(el.children).map(child => child.getAttribute('data-id')).filter(Boolean);
          $.post(resourcesBaseUrl + '/reorder', { _token: csrfToken, order: order });
        }
      });
    });
  });

  function openEditUnitModal(unitId, data) {
    editUnitId = unitId;
    const form = document.getElementById('form_edit_unit');
    form.name_ar.value = data.name_ar || '';
    form.name_en.value = data.name_en || '';
    if(form.is_shared) { form.is_shared.checked = data.is_shared; $(form.is_shared).trigger('change'); }
    $(form).find('select[name="group_ids[]"]').val(data.group_ids).trigger('change');
    modalEditUnit.show();
  }

  function openEditLessonModal(lessonId, data) {
    editLessonId = lessonId;
    const form = document.getElementById('form_edit_lesson');
    form.name_ar.value = data.name_ar || '';
    form.name_en.value = data.name_en || '';
    if(form.is_shared) { form.is_shared.checked = data.is_shared; $(form.is_shared).trigger('change'); }
    $(form).find('select[name="group_ids[]"]').val(data.group_ids).trigger('change');
    modalEditLesson.show();
  }

  

  $('#form_edit_unit').on('submit', function (e) {
    e.preventDefault();
    $.ajax({
      url: unitsBaseUrl + '/' + editUnitId,
      type: 'PUT',
      data: $(this).serialize() + '&_token=' + csrfToken,
      success: function () { location.reload(); },
      error: function () { Swal.fire('خطأ', 'حدث خطأ، يرجى التأكد من البيانات.', 'error'); }
    });
  });

  $('#form_edit_lesson').on('submit', function (e) {
    e.preventDefault();
    $.ajax({
      url: lessonsBaseUrl + '/' + editLessonId,
      type: 'PUT',
      data: $(this).serialize() + '&_token=' + csrfToken,
      success: function () { location.reload(); },
      error: function () { Swal.fire('خطأ', 'حدث خطأ، يرجى التأكد من البيانات.', 'error'); }
    });
  });

  $('#form_edit_resource').on('submit', function (e) {
    e.preventDefault();
    $.ajax({
      url: resourcesBaseUrl + '/' + editResourceId,
      type: 'POST', // Laravel needs _method=PUT for multipart forms
      data: $(this).serialize() + '&_token=' + csrfToken + '&_method=PUT',
      success: function () { location.reload(); },
      error: function () { Swal.fire('خطأ', 'حدث خطأ، يرجى التأكد من البيانات.', 'error'); }
    });
  });
  let currentUnitId = null;
    let currentLessonId = null;
    let currentEditResourceId = null;
    let videoUploadResumable = null;
    let videoUploadDone = false;
    let editVideoUploadResumable = null;
    let editVideoUploadDone = true;

    let uploadStartTime = 0;
    let uploadDrawer;
    let processingDrawer;

    document.addEventListener('DOMContentLoaded', function () {
        modalAddUnit = new bootstrap.Modal(document.getElementById('kt_modal_add_unit'));
        modalAddLesson = new bootstrap.Modal(document.getElementById('kt_modal_add_lesson'));
        modalAddResource = new bootstrap.Modal(document.getElementById('kt_modal_add_resource'));
        modalEditResource = new bootstrap.Modal(document.getElementById('kt_modal_edit_resource'));

        // Initialize Drawer
        const drawerEl = document.getElementById('kt_upload_progress_drawer');
        if (drawerEl) {
            uploadDrawer = KTDrawer.getInstance(drawerEl);
            if (!uploadDrawer) uploadDrawer = new KTDrawer(drawerEl);
        }

        // We no longer need KTDrawer for processing toast

        document.getElementById('resource_type').addEventListener('change', toggleResourceFields);
        document.getElementById('edit_resource_type').addEventListener('change', toggleEditResourceFields);
        toggleResourceFields();
        toggleEditResourceFields();
        initVideoResumable();
        initEditVideoResumable();

        @if(isset($processingResources) && count($processingResources) > 0)
            @foreach($processingResources as $resId)
                showProcessingDrawer('{{ $resId }}');
            @endforeach
        @endif
    });

    function showProcessingDrawer(resourceId) {
        document.getElementById('processing_drawer_progress').style.width = '0%';
        document.getElementById('processing_drawer_percentage').textContent = '0%';
        document.getElementById('processing_drawer_time_remaining').textContent = 'جاري التحضير...';
        
        document.getElementById('kt_processing_progress_toast').classList.remove('d-none');

        const pollInterval = setInterval(() => {
            $.ajax({
                url: '{{ url('admin/subject-content/resources') }}/' + resourceId + '/progress',
                type: 'GET',
                success: function(data) {
                    const pct = data.percentage || 0;
                    document.getElementById('processing_drawer_progress').style.width = pct + '%';
                    document.getElementById('processing_drawer_percentage').textContent = pct + '%';
                    document.getElementById('processing_drawer_time_remaining').textContent = 'تتم المعالجة الآن...';

                    if (data.status === 'ready' || pct >= 100) {
                        clearInterval(pollInterval);
                        document.getElementById('processing_drawer_progress').style.width = '100%';
                        document.getElementById('processing_drawer_percentage').textContent = '100%';
                        document.getElementById('processing_drawer_time_remaining').textContent = 'اكتملت المعالجة!';
                        setTimeout(() => {
                            document.getElementById('kt_processing_progress_toast').classList.add('d-none');
                            showSuccess(function () { location.reload(); });
                        }, 2000);
                    } else if (data.status === 'failed') {
                        clearInterval(pollInterval);
                        document.getElementById('processing_drawer_time_remaining').textContent = 'فشلت المعالجة!';
                        document.getElementById('processing_drawer_time_remaining').classList.add('text-danger');
                    }
                },
                error: function() {}
            });
        }, 3000);
    }

    function toggleResourceFields() {
        const type = document.getElementById('resource_type').value;
        const videoField = document.getElementById('resource_video_field');
        const documentField = document.getElementById('resource_document_field');
        const imageField = document.getElementById('resource_image_field');
        const urlField = document.getElementById('resource_url_field');

        videoField.classList.toggle('d-none', type !== 'video');
        documentField.classList.toggle('d-none', type !== 'document');
        imageField.classList.toggle('d-none', type !== 'image');
        urlField.classList.toggle('d-none', type !== 'link' && type !== 'zoom');

        // The document/image inputs share name="file" — a hidden-but-enabled
        // input still rides along in FormData(form) and corrupts the "file"
        // field into an array, so disable whichever one isn't active.
        documentField.querySelector('input[name="file"]').disabled = (type !== 'document');
        imageField.querySelector('input[name="file"]').disabled = (type !== 'image');
    }

    function formatBytes(bytes, decimals = 2) {
        if (!+bytes) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
    }

    function formatTime(seconds) {
        if (!seconds || seconds === Infinity || seconds < 0) return 'جارِ الحساب...';
        seconds = Math.round(seconds);
        if (seconds < 60) return `متبقي ${seconds} ثانية`;
        const minutes = Math.floor(seconds / 60);
        const remSeconds = seconds % 60;
        return `متبقي ${minutes} دقيقة و ${remSeconds} ثانية`;
    }

    // Chunked, resumable video upload
    function initVideoResumable() {
        videoUploadResumable = new Resumable({
            target: chunkUploadUrl,
            chunkSize: 5 * 1024 * 1024,
            simultaneousUploads: 3,
            testChunks: false,
            maxChunkRetries: 8,
            chunkRetryInterval: 3000,
            query: { _token: csrfToken },
        });

        videoUploadResumable.assignBrowse(document.getElementById('resource_video_browse'));

        videoUploadResumable.on('fileAdded', function (file) {
            videoUploadDone = false;
            uploadStartTime = Date.now();
            document.getElementById('resource_uploaded_path').value = '';
            document.getElementById('resource_video_filename').textContent = file.fileName;
            document.getElementById('resource_video_progress_wrap').classList.remove('d-none');
            document.getElementById('resource_video_progress').style.width = '0%';

            // Update Drawer Info
            document.getElementById('upload_drawer_filename').textContent = file.fileName;
            document.getElementById('upload_drawer_size').textContent = formatBytes(file.size);
            document.getElementById('upload_drawer_progress').style.width = '0%';
            document.getElementById('upload_drawer_percentage').textContent = '0%';
            document.getElementById('upload_drawer_time_remaining').textContent = 'جارِ الحساب...';
            document.getElementById('upload_drawer_uploaded').textContent = '0 MB / ' + formatBytes(file.size);
            document.getElementById('upload_drawer_speed').textContent = '0 KB/s';

            if (uploadDrawer) uploadDrawer.show();

            videoUploadResumable.upload();
        });

        videoUploadResumable.on('fileProgress', function (file) {
            const progress = videoUploadResumable.progress();
            const pct = Math.floor(progress * 100);
            document.getElementById('resource_video_progress').style.width = pct + '%';

            // Calculate Speed and ETA
            const uploadedBytes = progress * file.size;
            const elapsedTime = (Date.now() - uploadStartTime) / 1000; // seconds
            let speedBps = 0;
            if (elapsedTime > 0) speedBps = uploadedBytes / elapsedTime;

            const remainingBytes = file.size - uploadedBytes;
            let remainingTimeSec = 0;
            if (speedBps > 0) remainingTimeSec = remainingBytes / speedBps;

            document.getElementById('upload_drawer_progress').style.width = pct + '%';
            document.getElementById('upload_drawer_percentage').textContent = pct + '%';
            document.getElementById('upload_drawer_uploaded').textContent = formatBytes(uploadedBytes) + ' / ' + formatBytes(file.size);
            document.getElementById('upload_drawer_speed').textContent = formatBytes(speedBps) + '/s';
            document.getElementById('upload_drawer_time_remaining').textContent = formatTime(remainingTimeSec);
        });

        videoUploadResumable.on('fileSuccess', function (file, response) {
            const data = JSON.parse(response);
            document.getElementById('resource_uploaded_path').value = data.path;
            document.getElementById('resource_original_filename').value = data.original_filename;
            document.getElementById('resource_video_progress').style.width = '100%';
            videoUploadDone = true;

            document.getElementById('upload_drawer_progress').style.width = '100%';
            document.getElementById('upload_drawer_percentage').textContent = '100%';
            document.getElementById('upload_drawer_time_remaining').textContent = 'اكتمل الرفع!';
            document.getElementById('upload_drawer_uploaded').textContent = formatBytes(file.size) + ' / ' + formatBytes(file.size);

            setTimeout(() => { 
                if (uploadDrawer) uploadDrawer.hide(); 
                // Auto-submit the form once upload reaches 100%
                $('#kt_form_add_resource').submit();
            }, 1000);
        });

        videoUploadResumable.on('fileError', function (file, message) {
            showError('تعذّر رفع الفيديو. سيتم إعادة المحاولة تلقائيًا عند استعادة الاتصال بالإنترنت.');
            document.getElementById('upload_drawer_time_remaining').textContent = 'حدث خطأ في الرفع';
            document.getElementById('upload_drawer_time_remaining').classList.add('text-danger');
        });
    }

    function openUnitModal() {
        $('#kt_form_add_unit')[0].reset();
        modalAddUnit.show();
    }

    function openLessonModal(unitId) {
        currentUnitId = unitId;
        $('#kt_form_add_lesson')[0].reset();
        modalAddLesson.show();
    }

    function openResourceModal(lessonId) {
        currentLessonId = lessonId;
        isGeneralResourceMode = false;
        const titleEl = document.querySelector('#kt_modal_add_resource .modal-title');
        if (titleEl) titleEl.textContent = 'إضافة مرفق تعليمي';
        $('#kt_form_add_resource')[0].reset();
        document.getElementById('resource_uploaded_path').value = '';
        document.getElementById('resource_original_filename').value = '';
        document.getElementById('resource_video_filename').textContent = '';
        document.getElementById('resource_video_progress_wrap').classList.add('d-none');
        $('#resource_excluded_students').val(null).trigger('change');
        @if($selectedGroupId)
        $('#resource_groups').val(['{{ $selectedGroupId }}']).trigger('change');
        @endif
        videoUploadDone = false;
        if (videoUploadResumable) videoUploadResumable.files = [];
        toggleResourceFields();
        modalAddResource.show();
    }

    function openGeneralResourceModal() {
        currentLessonId = null;
        isGeneralResourceMode = true;
        const titleEl = document.querySelector('#kt_modal_add_resource .modal-title');
        if (titleEl) titleEl.textContent = 'إضافة مرفق عام (بدون وحدة)';
        $('#kt_form_add_resource')[0].reset();
        document.getElementById('resource_uploaded_path').value = '';
        document.getElementById('resource_original_filename').value = '';
        document.getElementById('resource_video_filename').textContent = '';
        document.getElementById('resource_video_progress_wrap').classList.add('d-none');
        $('#resource_excluded_students').val(null).trigger('change');
        @if($selectedGroupId)
        const sharedCb = document.querySelector('#kt_form_add_resource .is-shared-checkbox');
        if (sharedCb) { sharedCb.checked = false; $(sharedCb).trigger('change'); }
        $('#resource_groups').val(['{{ $selectedGroupId }}']).trigger('change');
        @endif
        videoUploadDone = false;
        if (videoUploadResumable) videoUploadResumable.files = [];
        toggleResourceFields();
        modalAddResource.show();
    }

    function showSuccess(callback) {
        Swal.fire({
            text: "تم الحفظ بنجاح!",
            icon: "success",
            buttonsStyling: false,
            confirmButtonText: "حسناً",
            customClass: { confirmButton: "btn btn-primary" }
        }).then(callback);
    }

    function showError(message) {
        Swal.fire({
            text: message || "حدث خطأ، يرجى التأكد من البيانات.",
            icon: "error",
            buttonsStyling: false,
            confirmButtonText: "حسناً",
            customClass: { confirmButton: "btn btn-primary" }
        });
    }

    function confirmDelete(callback) {
        Swal.fire({
            text: "هل أنت متأكد من عملية الحذف؟ لا يمكن التراجع عن هذا الإجراء.",
            icon: "warning",
            showCancelButton: true,
            buttonsStyling: false,
            confirmButtonText: "نعم، احذف!",
            cancelButtonText: "إلغاء",
            customClass: {
                confirmButton: "btn fw-bold btn-danger",
                cancelButton: "btn fw-bold btn-active-light-primary"
            }
        }).then(function (result) {
            if (result.value) callback();
        });
    }

    // Gallery preview — loads only the selected attachment
    let modalGalleryPreview = null;
    let galleryItems = [];
    let galleryIndex = 0;

    document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById('kt_modal_gallery_preview');
        if (!el) return;
        modalGalleryPreview = new bootstrap.Modal(el);
        el.addEventListener('hidden.bs.modal', clearGalleryStage);
    });

    function galleryTypeMeta(type) {
        const map = {
            video: { label: 'فيديو', icon: 'bi-play-circle' },
            document: { label: 'مستند', icon: 'bi-file-earmark-pdf' },
            image: { label: 'صورة', icon: 'bi-image' },
            link: { label: 'رابط', icon: 'bi-link-45deg' },
            zoom: { label: 'Zoom', icon: 'bi-camera-video' },
        };
        return map[type] || { label: type || 'مرفق', icon: 'bi-paperclip' };
    }

    function toEmbeddableUrl(url) {
        if (!url) return url;
        let m;
        if ((m = url.match(/drive\.google\.com\/file\/d\/([\w-]+)/))) {
            return 'https://drive.google.com/file/d/' + m[1] + '/preview';
        }
        if ((m = url.match(/drive\.google\.com\/(?:open|uc)\?(?:export=\w+&)?id=([\w-]+)/))) {
            return 'https://drive.google.com/file/d/' + m[1] + '/preview';
        }
        if ((m = url.match(/youtube\.com\/watch\?(?:.*&)?v=([\w-]+)/))) {
            return 'https://www.youtube-nocookie.com/embed/' + m[1] + '?rel=0&modestbranding=1';
        }
        if ((m = url.match(/youtu\.be\/([\w-]+)/))) {
            return 'https://www.youtube-nocookie.com/embed/' + m[1] + '?rel=0&modestbranding=1';
        }
        if ((m = url.match(/youtube\.com\/(?:shorts|live|embed)\/([\w-]+)/))) {
            return 'https://www.youtube-nocookie.com/embed/' + m[1] + '?rel=0&modestbranding=1';
        }
        return url;
    }

    function stopGalleryMedia() {
        const stage = document.getElementById('gallery_stage');
        if (!stage) return;
        stage.querySelectorAll('video').forEach(function (v) {
            try { v.pause(); } catch (e) {}
            v.removeAttribute('src');
            try { v.load(); } catch (e2) {}
        });
        stage.querySelectorAll('iframe').forEach(function (frame) {
            frame.src = 'about:blank';
        });
    }

    function clearGalleryStage() {
        const stage = document.getElementById('gallery_stage');
        if (!stage) return;
        stopGalleryMedia();
        stage.innerHTML = '<div class="text-center text-muted p-10" id="gallery_empty_state"><i class="bi bi-eye fs-2x mb-3 d-block"></i>اختر مرفقاً من القائمة للمعاينة</div>';
        stage.classList.remove('is-media');
    }

    function openGalleryPreview(items, title, scopeLabel) {
        galleryItems = Array.isArray(items) ? items : [];
        galleryIndex = 0;

        document.getElementById('gallery_preview_title').textContent = title || 'معاينة المحتوى';
        document.getElementById('gallery_preview_subtitle').textContent = scopeLabel
            ? ('معاينة على مستوى ' + scopeLabel + ' — يُحمّل المرفق المحدد فقط')
            : 'يُحمّل المرفق المحدد فقط';
        document.getElementById('gallery_count').textContent = String(galleryItems.length);

        renderGalleryList();

        if (!galleryItems.length) {
            clearGalleryStage();
            document.getElementById('gallery_position_label').textContent = 'لا توجد مرفقات';
            document.getElementById('gallery_prev_btn').disabled = true;
            document.getElementById('gallery_next_btn').disabled = true;
            modalGalleryPreview.show();
            return;
        }

        selectGalleryItem(0);
        modalGalleryPreview.show();
    }

    function renderGalleryList() {
        const list = document.getElementById('gallery_list');
        if (!galleryItems.length) {
            list.innerHTML = '<div class="text-center text-muted py-8 px-3">لا توجد مرفقات للمعاينة في هذا المستوى</div>';
            return;
        }

        list.innerHTML = galleryItems.map(function (item, index) {
            const meta = galleryTypeMeta(item.type || item.viewer);
            const lesson = item.lesson ? '<div class="gallery-preview-item__meta">' + escapeHtml(item.lesson) + '</div>' : '';
            const statusNote = !item.ready
                ? '<div class="gallery-preview-item__meta text-warning">غير جاهز للمعاينة</div>'
                : '';
            return `
                <button type="button" class="gallery-preview-item ${index === galleryIndex ? 'is-active' : ''}" data-index="${index}" onclick="selectGalleryItem(${index})">
                    <span class="gallery-preview-item__icon"><i class="bi ${meta.icon}"></i></span>
                    <span class="flex-grow-1 min-w-0">
                        <div class="gallery-preview-item__title">${escapeHtml(item.title || 'بدون عنوان')}</div>
                        <div class="gallery-preview-item__meta">${meta.label}</div>
                        ${lesson}${statusNote}
                    </span>
                </button>
            `;
        }).join('');
    }

    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function selectGalleryItem(index) {
        if (!galleryItems.length) return;
        galleryIndex = Math.max(0, Math.min(index, galleryItems.length - 1));
        renderGalleryList();
        renderGalleryStage(galleryItems[galleryIndex]);

        document.getElementById('gallery_position_label').textContent =
            (galleryIndex + 1) + ' / ' + galleryItems.length;
        document.getElementById('gallery_prev_btn').disabled = galleryIndex <= 0;
        document.getElementById('gallery_next_btn').disabled = galleryIndex >= galleryItems.length - 1;

        const active = document.querySelector('.gallery-preview-item.is-active');
        if (active) active.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }

    function galleryNavigate(step) {
        selectGalleryItem(galleryIndex + step);
    }

    function renderGalleryStage(item) {
        const stage = document.getElementById('gallery_stage');
        stopGalleryMedia();
        stage.innerHTML = '';
        stage.classList.remove('is-media');

        if (!item) {
            clearGalleryStage();
            return;
        }

        if (!item.ready) {
            const statusText = item.status === 'failed' ? 'فشلت معالجة هذا المرفق' : 'جاري معالجة هذا المرفق...';
            stage.innerHTML = `
                <div class="text-center p-10">
                    <i class="bi bi-hourglass-split fs-2x text-warning mb-3 d-block"></i>
                    <div class="fw-bold text-gray-800 mb-1">${escapeHtml(item.title || '')}</div>
                    <div class="text-muted">${statusText}</div>
                </div>
            `;
            return;
        }

        if (!item.url) {
            stage.innerHTML = '<div class="text-center text-muted p-10">لا يتوفر رابط معاينة لهذا المرفق</div>';
            return;
        }

        const viewer = item.viewer || item.type;

        if (viewer === 'video') {
            stage.classList.add('is-media');
            stage.innerHTML = `
                <video controls playsinline controlsList="nodownload" style="width:100%;max-height:62vh;">
                    <source src="${escapeHtml(item.url)}" type="video/mp4">
                    متصفحك لا يدعم تشغيل الفيديو.
                </video>
            `;
            return;
        }

        if (viewer === 'image') {
            stage.classList.add('is-media');
            stage.innerHTML = `<img src="${escapeHtml(item.url)}" alt="${escapeHtml(item.title || '')}">`;
            return;
        }

        if (viewer === 'link' || viewer === 'zoom') {
            const embed = toEmbeddableUrl(item.url);
            if (embed !== item.url) {
                stage.classList.add('is-media');
                stage.innerHTML = `<iframe src="${escapeHtml(embed)}" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen referrerpolicy="no-referrer"></iframe>`;
            } else {
                stage.innerHTML = `
                    <div class="gallery-preview-link-card">
                        <i class="bi bi-box-arrow-up-right fs-2x text-primary mb-4 d-block"></i>
                        <div class="fw-bold fs-4 mb-2 text-gray-800">${escapeHtml(item.title || 'رابط خارجي')}</div>
                        <div class="text-muted mb-5 fs-7" style="word-break:break-all;">${escapeHtml(item.url)}</div>
                        <a href="${escapeHtml(item.url)}" target="_blank" rel="noopener" class="btn btn-primary">فتح الرابط</a>
                    </div>
                `;
            }
            return;
        }

        stage.classList.add('is-media');
        stage.innerHTML = `<iframe src="${escapeHtml(item.url)}" allowfullscreen></iframe>`;
    }

    // Backward-compatible alias used by any leftover callers
    function previewResource(url, type, title) {
        openGalleryPreview([{
            id: null,
            title: title,
            type: type,
            viewer: type,
            url: url,
            ready: true,
            status: 'ready',
            lesson: null,
        }], title, 'مرفق');
    }

    function stopPreviewVideo() {
        clearGalleryStage();
    }

    $('#kt_form_add_unit').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: subjectContentUnitsStoreUrl,
            type: 'POST',
            data: $(this).serialize() + '&_token=' + csrfToken,
            success: function () {
                modalAddUnit.hide();
                showSuccess(function () { location.reload(); });
            },
            error: function () { showError(); }
        });
    });

    $('#kt_form_add_lesson').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: subjectContentUnitsBaseUrl + '/' + currentUnitId + '/lessons',
            type: 'POST',
            data: $(this).serialize() + '&_token=' + csrfToken,
            success: function () {
                modalAddLesson.hide();
                showSuccess(function () { location.reload(); });
            },
            error: function () { showError(); }
        });
    });

    $('#kt_form_add_resource').on('submit', function (e) {
        e.preventDefault();

        const type = document.getElementById('resource_type').value;
        if (type === 'video' && !videoUploadDone) {
            showError('يرجى الانتظار حتى ينتهي رفع الفيديو قبل الحفظ.');
            return;
        }

        const formData = new FormData(this);
        formData.append('_token', csrfToken);
        const storeUrl = isGeneralResourceMode
            ? subjectContentGeneralResourcesStoreUrl
            : (subjectContentLessonsBaseUrl + '/' + currentLessonId + '/resources');
        $.ajax({
            url: storeUrl,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                modalAddResource.hide();
                // Videos are stored and served directly — no transcode/encryption
                // step to wait on — so only fall back to the processing drawer if
                // the backend explicitly says the resource isn't ready yet.
                if (type === 'video' && response.id && response.processing_status === 'processing') {
                    showProcessingDrawer(response.id);
                } else {
                    showSuccess(function () { location.reload(); });
                }
            },
            error: function (xhr) {
                const message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : null;
                showError(message);
            }
        });
    });

    function deleteUnit(unitId) {
        confirmDelete(function () {
            $.ajax({
                url: subjectContentUnitsBaseUrl + '/' + unitId,
                type: 'DELETE',
                data: { _token: csrfToken },
                success: function () { location.reload(); },
                error: function () { showError(); }
            });
        });
    }

    function deleteLesson(lessonId) {
        confirmDelete(function () {
            $.ajax({
                url: subjectContentLessonsBaseUrl + '/' + lessonId,
                type: 'DELETE',
                data: { _token: csrfToken },
                success: function () { location.reload(); },
                error: function () { showError(); }
            });
        });
    }

    function deleteResource(resourceId) {
        confirmDelete(function () {
            $.ajax({
                url: subjectContentResourcesBaseUrl + '/' + resourceId,
                type: 'DELETE',
                data: { _token: csrfToken },
                success: function () { location.reload(); },
                error: function () { showError(); }
            });
        });
    }

    let modalShareContent = null;
    function openShareModal(type, id, currentGroupIds, isShared) {
        if (!modalShareContent) {
            modalShareContent = new bootstrap.Modal(document.getElementById('kt_modal_share_content'));
        }
        $('#share_content_type').val(type);
        $('#share_content_id').val(id);
        const $select = $('#share_group_ids');
        $select.find('option').prop('disabled', false).prop('selected', false);
        (currentGroupIds || []).forEach(function (gid) {
            $select.find('option[value="' + gid + '"]').prop('disabled', true);
        });
        $select.trigger('change');

        if (isShared) {
            $('#share_already_shared_msg').removeClass('d-none');
            $('#share_groups_wrap').addClass('d-none');
            $('#share_submit_btn').prop('disabled', true);
        } else {
            $('#share_already_shared_msg').addClass('d-none');
            $('#share_groups_wrap').removeClass('d-none');
            $('#share_submit_btn').prop('disabled', false);
        }
        modalShareContent.show();
    }

    $('#kt_form_share_content').on('submit', function (e) {
        e.preventDefault();
        const type = $('#share_content_type').val();
        const id = $('#share_content_id').val();
        const groupIds = $('#share_group_ids').val() || [];
        if (!groupIds.length) {
            if (typeof toastr !== 'undefined') toastr.warning('اختر مجموعة واحدة على الأقل');
            return;
        }
        const base = type === 'unit' ? subjectContentUnitsBaseUrl
            : (type === 'lesson' ? subjectContentLessonsBaseUrl : subjectContentResourcesBaseUrl);
        $.ajax({
            url: base + '/' + id + '/share',
            type: 'POST',
            data: { _token: csrfToken, group_ids: groupIds },
            success: function (res) {
                if (typeof toastr !== 'undefined') toastr.success(res.message || 'تمت المشاركة');
                location.reload();
            },
            error: function (xhr) {
                const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'تعذر إتمام المشاركة';
                if (typeof toastr !== 'undefined') toastr.error(msg);
                else alert(msg);
            }
        });
    });

    function toggleEditResourceFields() {
        const type = document.getElementById('edit_resource_type').value;
        const videoField = document.getElementById('edit_resource_video_field');
        const docField = document.getElementById('edit_resource_document_field');
        const imageField = document.getElementById('edit_resource_image_field');
        const urlField = document.getElementById('edit_resource_url_field');

        videoField.classList.add('d-none');
        docField.classList.add('d-none');
        imageField.classList.add('d-none');
        urlField.classList.add('d-none');

        if (type === 'video') videoField.classList.remove('d-none');
        else if (type === 'document') docField.classList.remove('d-none');
        else if (type === 'image') imageField.classList.remove('d-none');
        else urlField.classList.remove('d-none');

        // Same name="file" collision as the add-resource modal — keep only
        // the visible one enabled so FormData(form) doesn't submit both.
        document.getElementById('edit_resource_file_doc').disabled = (type !== 'document');
        document.getElementById('edit_resource_file_img').disabled = (type !== 'image');
    }

    function initEditVideoResumable() {
        editVideoUploadResumable = new Resumable({
            target: chunkUploadUrl,
            chunkSize: 5 * 1024 * 1024,
            simultaneousUploads: 3,
            testChunks: false,
            maxChunkRetries: 8,
            chunkRetryInterval: 3000,
            query: { _token: csrfToken },
        });

        editVideoUploadResumable.assignBrowse(document.getElementById('edit_resource_video_browse'));

        editVideoUploadResumable.on('fileAdded', function (file) {
            editVideoUploadDone = false;
            uploadStartTime = Date.now();
            document.getElementById('edit_resource_uploaded_path').value = '';
            document.getElementById('edit_resource_video_filename').textContent = file.fileName;
            document.getElementById('edit_resource_video_progress_wrap').classList.remove('d-none');
            document.getElementById('edit_resource_video_progress').style.width = '0%';

            // Update Drawer Info
            document.getElementById('upload_drawer_filename').textContent = file.fileName;
            document.getElementById('upload_drawer_size').textContent = formatBytes(file.size);
            document.getElementById('upload_drawer_progress').style.width = '0%';
            document.getElementById('upload_drawer_percentage').textContent = '0%';
            document.getElementById('upload_drawer_time_remaining').textContent = 'جارِ الحساب...';
            document.getElementById('upload_drawer_uploaded').textContent = '0 MB / ' + formatBytes(file.size);
            document.getElementById('upload_drawer_speed').textContent = '0 KB/s';

            if (uploadDrawer) uploadDrawer.show();

            editVideoUploadResumable.upload();
        });

        editVideoUploadResumable.on('fileProgress', function (file) {
            const progress = editVideoUploadResumable.progress();
            const pct = Math.floor(progress * 100);
            document.getElementById('edit_resource_video_progress').style.width = pct + '%';

            // Calculate Speed and ETA
            const uploadedBytes = progress * file.size;
            const elapsedTime = (Date.now() - uploadStartTime) / 1000;
            let speedBps = 0;
            if (elapsedTime > 0) speedBps = uploadedBytes / elapsedTime;

            const remainingBytes = file.size - uploadedBytes;
            let remainingTimeSec = 0;
            if (speedBps > 0) remainingTimeSec = remainingBytes / speedBps;

            document.getElementById('upload_drawer_progress').style.width = pct + '%';
            document.getElementById('upload_drawer_percentage').textContent = pct + '%';
            document.getElementById('upload_drawer_uploaded').textContent = formatBytes(uploadedBytes) + ' / ' + formatBytes(file.size);
            document.getElementById('upload_drawer_speed').textContent = formatBytes(speedBps) + '/s';
            document.getElementById('upload_drawer_time_remaining').textContent = formatTime(remainingTimeSec);
        });

        editVideoUploadResumable.on('fileSuccess', function (file, response) {
            const data = JSON.parse(response);
            document.getElementById('edit_resource_uploaded_path').value = data.path;
            document.getElementById('edit_resource_original_filename').value = data.original_filename;
            document.getElementById('edit_resource_video_progress').style.width = '100%';
            editVideoUploadDone = true;

            document.getElementById('upload_drawer_progress').style.width = '100%';
            document.getElementById('upload_drawer_percentage').textContent = '100%';
            document.getElementById('upload_drawer_time_remaining').textContent = 'اكتمل الرفع!';
            document.getElementById('upload_drawer_uploaded').textContent = formatBytes(file.size) + ' / ' + formatBytes(file.size);

            setTimeout(() => { 
                if (uploadDrawer) uploadDrawer.hide(); 
                $('#kt_form_edit_resource').submit();
            }, 1000);
        });

        editVideoUploadResumable.on('fileError', function (file, message) {
            showError('تعذّر رفع الفيديو. سيتم إعادة المحاولة تلقائيًا عند استعادة الاتصال بالإنترنت.');
            document.getElementById('upload_drawer_time_remaining').textContent = 'حدث خطأ في الرفع';
            document.getElementById('upload_drawer_time_remaining').classList.add('text-danger');
        });
    }

    function openEditResourceModal(resourceId, resourceObj) {
        currentEditResourceId = resourceId;
        $('#kt_form_edit_resource')[0].reset();
        
        document.getElementById('edit_resource_title').value = resourceObj.title || '';
        document.getElementById('edit_resource_type').value = resourceObj.type || 'video';
        document.getElementById('edit_resource_description').value = resourceObj.description || '';
        const groupIds = resourceObj.group_ids
            || (resourceObj.groups ? resourceObj.groups.map(g => g.id) : []);
        $(document.getElementById('kt_form_edit_resource')).find('select[name="group_ids[]"]').val(groupIds).trigger('change');
        const excludedIds = (resourceObj.excluded_student_ids || []).map(String);
        $('#edit_resource_excluded_students').val(excludedIds).trigger('change');
        const isShared = resourceObj.is_shared;
        const isSharedCheckbox = document.getElementById('kt_form_edit_resource').querySelector('.is-shared-checkbox');
        if (isSharedCheckbox) { isSharedCheckbox.checked = !!isShared; $(isSharedCheckbox).trigger('change'); }
        if (resourceObj.allow_download) {
            document.getElementById('edit_allow_download_check').checked = true;
        }
        if (resourceObj.is_external_link || resourceObj.type === 'link' || resourceObj.type === 'zoom') {
            document.getElementById('edit_resource_url').value = resourceObj.url || '';
        }

        document.getElementById('edit_resource_uploaded_path').value = '';
        document.getElementById('edit_resource_original_filename').value = '';
        document.getElementById('edit_resource_video_filename').textContent = '';
        document.getElementById('edit_resource_video_progress_wrap').classList.add('d-none');
        editVideoUploadDone = true; // allow save without new upload
        if (editVideoUploadResumable) editVideoUploadResumable.files = [];
        toggleEditResourceFields();
        modalEditResource.show();
    }

    $('#kt_form_edit_resource').on('submit', function (e) {
        e.preventDefault();

        const type = document.getElementById('edit_resource_type').value;
        if (type === 'video' && !editVideoUploadDone) {
            showError('يرجى الانتظار حتى ينتهي رفع الفيديو قبل الحفظ.');
            return;
        }

        const formData = new FormData(this);
        formData.append('_token', csrfToken);
        
        $.ajax({
            url: subjectContentResourcesBaseUrl + '/' + currentEditResourceId,
            type: 'POST', // using POST with _method=PUT
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                modalEditResource.hide();
                if (type === 'video' && response.id && response.processing_status === 'processing') {
                    showProcessingDrawer(response.id);
                } else {
                    showSuccess(function () { location.reload(); });
                }
            },
            error: function (xhr) {
                const message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : null;
                showError(message);
            }
        });
    });
</script>
@endpush
