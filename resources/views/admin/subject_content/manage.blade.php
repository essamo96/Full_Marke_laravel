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
                <!-- Accordion for Units -->
                <div class="accordion accordion-icon-toggle" id="kt_accordion_units">
                    @forelse($units as $unit)
                        <div class="mb-5">
                            <div class="accordion-header py-3 d-flex" data-bs-toggle="collapse" data-bs-target="#kt_accordion_unit_{{ $unit->id }}">
                                <span class="accordion-icon"><i class="ki-duotone ki-arrow-right fs-4"><span class="path1"></span><span class="path2"></span></i></span>
                                <h3 class="fs-4 fw-semibold mb-0 ms-4">{{ $unit->name_ar }}</h3>
                                <div class="ms-auto">
                                    <button type="button" class="btn btn-sm btn-icon btn-light-success me-2" title="إضافة درس" onclick="event.stopPropagation(); openLessonModal('{{ $unit->getRouteKey() }}')"><i class="ki-duotone ki-plus fs-4"></i></button>
                                    <button type="button" class="btn btn-sm btn-icon btn-light-danger" title="حذف الوحدة" onclick="event.stopPropagation(); deleteUnit('{{ $unit->getRouteKey() }}')"><i class="bi bi-trash"></i></button>
                                </div>
                            </div>
                            <div id="kt_accordion_unit_{{ $unit->id }}" class="fs-6 collapse" data-bs-parent="#kt_accordion_units">
                                <div class="p-5 border border-dashed rounded mt-4">
                                    @if($unit->lessons->count() > 0)
                                        @foreach($unit->lessons as $lesson)
                                            <div class="bg-light p-3 rounded mb-3">
                                                <div class="d-flex flex-stack">
                                                    <div class="d-flex align-items-center">
                                                        <i class="ki-duotone ki-book-open fs-2 text-primary me-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                                        <span class="fw-bold fs-6">{{ $lesson->name_ar }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="badge badge-light-info me-2">{{ $lesson->resources->count() }} مرفق</span>
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
                                                                    <tr>
                                                                        <td>{{ $resource->title }}</td>
                                                                        <td><span class="badge badge-light-primary">{{ $resource->type }}</span></td>
                                                                        <td>
                                                                            @if($resource->isExternalLink())
                                                                                <a href="{{ $resource->url }}" target="_blank" class="btn btn-sm btn-light-primary">فتح</a>
                                                                            @elseif($resource->type === 'document' || $resource->isImage())
                                                                                <a href="{{ route('subject_content.resources.file', $resource) }}" target="_blank" class="btn btn-sm btn-light-primary">فتح</a>
                                                                            @elseif($resource->processing_status === 'processing')
                                                                                <span class="badge badge-light-warning">جاري المعالجة...</span>
                                                                            @elseif($resource->processing_status === 'failed')
                                                                                <span class="badge badge-light-danger" title="{{ $resource->processing_error }}">فشلت المعالجة</span>
                                                                            @else
                                                                                <span class="badge badge-light-success">جاهز (يُعرض للطالب)</span>
                                                                            @endif
                                                                        </td>
                                                                        <td class="text-end">
                                                                            <button type="button" class="btn btn-icon btn-sm btn-light-primary me-1" onclick="openEditResourceModal('{{ $resource->getRouteKey() }}', {{ $resource->toJson() }})"><i class="bi bi-pencil"></i></button>
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
                            <h5>لا يوجد محتوى لهذه المادة حتى الآن</h5>
                        </div>
                    @endforelse
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
                    <input type="hidden" name="group_id" value="{{ $selectedGroupId }}"/>
                    <div class="text-muted fs-7">
                        @if($selectedGroupId)
                            سيتم إضافة هذه الوحدة لمجموعة "{{ $groups->firstWhere('id', $selectedGroupId)?->name }}" فقط.
                        @else
                            سيتم إضافة هذه الوحدة كمحتوى مشترك يظهر لكل المجموعات.
                        @endif
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
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/resumable/resumable.js') }}"></script>
<script>
    const subjectContentUnitsStoreUrl = '{{ route('subject_content.units.store', \Illuminate\Support\Facades\Crypt::encrypt($subject->id)) }}';
    const subjectContentUnitsBaseUrl = '{{ url('admin/subject-content/units') }}';
    const subjectContentLessonsBaseUrl = '{{ url('admin/subject-content/lessons') }}';
    const subjectContentResourcesBaseUrl = '{{ url('admin/subject-content/resources') }}';
    const csrfToken = '{{ csrf_token() }}';

    const chunkUploadUrl = '{{ route('subject_content.upload_chunk') }}';

    let modalAddUnit, modalAddLesson, modalAddResource, modalEditResource;
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
        $('#kt_form_add_resource')[0].reset();
        document.getElementById('resource_uploaded_path').value = '';
        document.getElementById('resource_original_filename').value = '';
        document.getElementById('resource_video_filename').textContent = '';
        document.getElementById('resource_video_progress_wrap').classList.add('d-none');
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
        $.ajax({
            url: subjectContentLessonsBaseUrl + '/' + currentLessonId + '/resources',
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
