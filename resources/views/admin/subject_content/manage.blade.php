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
                        <img src="{{ $subject->image ? '/' . $subject->image : '/assets/admin/media/avatars/blank.png' }}" alt="image" />
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
                    <button type="button" class="btn btn-sm btn-light-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_unit">
                        <i class="ki-duotone ki-plus fs-2"></i> إضافة وحدة جديدة
                    </button>
                </div>
            </div>
            <div class="card-body pt-2">
                <!-- Accordion for Units -->
                <div class="accordion accordion-icon-toggle" id="kt_accordion_units">
                    @forelse($units as $unit)
                        <div class="mb-5">
                            <div class="accordion-header py-3 d-flex" data-bs-toggle="collapse" data-bs-target="#kt_accordion_unit_{{ $unit->id }}">
                                <span class="accordion-icon"><i class="ki-duotone ki-arrow-right fs-4"><span class="path1"></span><span class="path2"></span></i></span>
                                <h3 class="fs-4 fw-semibold mb-0 ms-4">{{ $unit->name_ar }}</h3>
                                <div class="ms-auto">
                                    <button class="btn btn-sm btn-icon btn-light-success me-2" title="إضافة درس" onclick="event.stopPropagation();"><i class="ki-duotone ki-plus fs-4"></i></button>
                                </div>
                            </div>
                            <div id="kt_accordion_unit_{{ $unit->id }}" class="fs-6 collapse" data-bs-parent="#kt_accordion_units">
                                <div class="p-5 border border-dashed rounded mt-4">
                                    @if($unit->lessons->count() > 0)
                                        @foreach($unit->lessons as $lesson)
                                            <div class="d-flex flex-stack bg-light p-3 rounded mb-3">
                                                <div class="d-flex align-items-center">
                                                    <i class="ki-duotone ki-book-open fs-2 text-primary me-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                                    <span class="fw-bold fs-6">{{ $lesson->name_ar }}</span>
                                                </div>
                                                <div>
                                                    <span class="badge badge-light-info me-2">{{ $lesson->resources->count() }} مرفق</span>
                                                    <button class="btn btn-sm btn-light-primary">إدارة المرفقات</button>
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
                            <h5>لا يوجد محتوى لهذه المادة حتى الآن</h5>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Placeholder (Actual add/edit functionality will be tied to JS later) -->
<div class="modal fade" tabindex="-1" id="kt_modal_add_unit">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">إضافة وحدة تعليمية جديدة</h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body">
                <div class="mb-5">
                    <label class="form-label required">اسم الوحدة بالعربية</label>
                    <input type="text" class="form-control" placeholder="مثال: الوحدة الأولى: مقدمة في المادة"/>
                </div>
                <div class="mb-5">
                    <label class="form-label">اسم الوحدة بالإنجليزية</label>
                    <input type="text" class="form-control" placeholder="مثال: Unit 1: Introduction"/>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">حفظ والتالي</button>
            </div>
        </div>
    </div>
</div>
@endsection
