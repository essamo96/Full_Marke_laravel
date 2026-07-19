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
                <!-- Accordion for Units -->
                <div class="accordion accordion-icon-toggle" id="kt_accordion_units">
                    @forelse($units as $unit)
                        <div class="mb-5">
                            <div class="accordion-header py-3 d-flex" data-bs-toggle="collapse" data-bs-target="#kt_accordion_unit_{{ $unit->id }}">
                                <span class="accordion-icon"><i class="ki-duotone ki-arrow-right fs-4"><span class="path1"></span><span class="path2"></span></i></span>
                                <h3 class="fs-4 fw-semibold mb-0 ms-4">{{ $unit->name_ar }}</h3>
                                <div class="ms-auto">
                                    <button type="button" class="btn btn-sm btn-icon btn-light-success me-2" title="إضافة درس" onclick="event.stopPropagation(); openLessonModal({{ $unit->id }})"><i class="ki-duotone ki-plus fs-4"></i></button>
                                    <button type="button" class="btn btn-sm btn-icon btn-light-danger" title="حذف الوحدة" onclick="event.stopPropagation(); deleteUnit({{ $unit->id }})"><i class="bi bi-trash"></i></button>
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
                                                        <button type="button" class="btn btn-sm btn-icon btn-light-danger" title="حذف الدرس" onclick="deleteLesson({{ $lesson->id }})"><i class="bi bi-trash"></i></button>
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
                                                                            <a href="{{ preg_match('#^https?://#i', $resource->url) ? $resource->url : asset('storage/' . $resource->url) }}" target="_blank" class="btn btn-sm btn-light-primary">فتح</a>
                                                                        </td>
                                                                        <td class="text-end">
                                                                            <button type="button" class="btn btn-icon btn-sm btn-light-danger" onclick="deleteResource({{ $resource->id }})"><i class="bi bi-trash"></i></button>
                                                                        </td>
                                                                    </tr>
                                                                @empty
                                                                    <tr><td colspan="4" class="text-center text-muted">لا توجد مرفقات لهذا الدرس</td></tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-light-success" onclick="openResourceModal({{ $lesson->id }})">
                                                        <i class="ki-duotone ki-plus fs-4"></i> إضافة مرفق (فيديو / PDF / رابط)
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
                            <option value="link">رابط خارجي</option>
                            <option value="zoom">رابط Zoom</option>
                        </select>
                    </div>
                    <div class="mb-5" id="resource_file_field">
                        <label class="form-label">رفع ملف (فيديو أو PDF أو مستند)</label>
                        <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.mp4,.mov,.avi,.webm"/>
                        <div class="form-text">أو أدخل رابطاً خارجياً بدلاً من رفع ملف.</div>
                    </div>
                    <div class="mb-5" id="resource_url_field">
                        <label class="form-label">رابط خارجي</label>
                        <input type="text" name="url" class="form-control" placeholder="https://..."/>
                    </div>
                    <div class="mb-5">
                        <label class="form-label">وصف مختصر</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
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
@endsection

@push('scripts')
<script>
    const subjectContentUnitsStoreUrl = '{{ route('subject_content.units.store', \Illuminate\Support\Facades\Crypt::encrypt($subject->id)) }}';
    const subjectContentUnitsBaseUrl = '{{ url('admin/subject-content/units') }}';
    const subjectContentLessonsBaseUrl = '{{ url('admin/subject-content/lessons') }}';
    const subjectContentResourcesBaseUrl = '{{ url('admin/subject-content/resources') }}';
    const csrfToken = '{{ csrf_token() }}';

    let modalAddUnit, modalAddLesson, modalAddResource;
    let currentUnitId = null;
    let currentLessonId = null;

    document.addEventListener('DOMContentLoaded', function () {
        modalAddUnit = new bootstrap.Modal(document.getElementById('kt_modal_add_unit'));
        modalAddLesson = new bootstrap.Modal(document.getElementById('kt_modal_add_lesson'));
        modalAddResource = new bootstrap.Modal(document.getElementById('kt_modal_add_resource'));

        document.getElementById('resource_type').addEventListener('change', toggleResourceFields);
        toggleResourceFields();
    });

    function toggleResourceFields() {
        const type = document.getElementById('resource_type').value;
        const fileField = document.getElementById('resource_file_field');
        const urlField = document.getElementById('resource_url_field');
        if (type === 'link' || type === 'zoom') {
            fileField.classList.add('d-none');
            urlField.classList.remove('d-none');
        } else {
            fileField.classList.remove('d-none');
            urlField.classList.remove('d-none');
        }
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
        const formData = new FormData(this);
        formData.append('_token', csrfToken);
        $.ajax({
            url: subjectContentLessonsBaseUrl + '/' + currentLessonId + '/resources',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function () {
                modalAddResource.hide();
                showSuccess(function () { location.reload(); });
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
</script>
@endpush
