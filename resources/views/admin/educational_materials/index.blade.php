@extends('admin.layout.mainLayouts.master')
@section('title')
    المواد التعليمية (الهرمية)
@stop

@section('page-content')
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">
            
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">إدارة الهرمية التعليمية للمواد</h3>
                </div>
                <div class="card-body">
                    <div class="accordion accordion-icon-toggle" id="kt_accordion_subjects">
                        @foreach($subjects as $subject)
                        <div class="mb-5">
                            <div class="accordion-header py-3 d-flex" data-bs-toggle="collapse" data-bs-target="#kt_accordion_subject_{{ $subject->id }}">
                                <span class="accordion-icon"><i class="ki-duotone ki-arrow-right fs-4"><span class="path1"></span><span class="path2"></span></i></span>
                                <h3 class="fs-4 fw-semibold mb-0 ms-4">{{ $subject->name }}</h3>
                            </div>
                            <div id="kt_accordion_subject_{{ $subject->id }}" class="fs-6 collapse p-5" data-bs-parent="#kt_accordion_subjects">
                                
                                <div class="d-flex justify-content-between mb-4">
                                    <h4 class="text-primary">المراحل التعليمية</h4>
                                    <button class="btn btn-sm btn-light-primary" onclick="openModal('stage', {{ $subject->id }})">
                                        <i class="ki-duotone ki-plus fs-3"></i> إضافة مرحلة
                                    </button>
                                </div>

                                <div class="accordion accordion-icon-toggle" id="kt_accordion_stages_{{ $subject->id }}">
                                    @foreach($subject->educationalStages as $stage)
                                    <div class="mb-5 ms-5 border-start border-3 border-primary ps-4">
                                        <div class="accordion-header py-3 d-flex justify-content-between" data-bs-toggle="collapse" data-bs-target="#kt_accordion_stage_{{ $stage->id }}">
                                            <div class="d-flex align-items-center">
                                                <span class="accordion-icon"><i class="ki-duotone ki-arrow-right fs-4"><span class="path1"></span><span class="path2"></span></i></span>
                                                <h4 class="fs-5 fw-semibold mb-0 ms-4">{{ $stage->name }}</h4>
                                            </div>
                                            <button class="btn btn-icon btn-sm btn-light-danger" onclick="deleteItem('stage', {{ $stage->id }})"><i class="bi bi-trash"></i></button>
                                        </div>
                                        <div id="kt_accordion_stage_{{ $stage->id }}" class="fs-6 collapse p-5" data-bs-parent="#kt_accordion_stages_{{ $subject->id }}">
                                            
                                            <div class="d-flex justify-content-between mb-4">
                                                <h5 class="text-success">الوحدات التعليمية</h5>
                                                <button class="btn btn-sm btn-light-success" onclick="openModal('unit', {{ $stage->id }})">
                                                    <i class="ki-duotone ki-plus fs-3"></i> إضافة وحدة
                                                </button>
                                            </div>

                                            <div class="accordion accordion-icon-toggle" id="kt_accordion_units_{{ $stage->id }}">
                                                @foreach($stage->units as $unit)
                                                <div class="mb-5 ms-5 border-start border-3 border-success ps-4">
                                                    <div class="accordion-header py-3 d-flex justify-content-between" data-bs-toggle="collapse" data-bs-target="#kt_accordion_unit_{{ $unit->id }}">
                                                        <div class="d-flex align-items-center">
                                                            <span class="accordion-icon"><i class="ki-duotone ki-arrow-right fs-4"><span class="path1"></span><span class="path2"></span></i></span>
                                                            <h5 class="fs-6 fw-semibold mb-0 ms-4">{{ $unit->name }}</h5>
                                                        </div>
                                                        <button class="btn btn-icon btn-sm btn-light-danger" onclick="deleteItem('unit', {{ $unit->id }})"><i class="bi bi-trash"></i></button>
                                                    </div>
                                                    <div id="kt_accordion_unit_{{ $unit->id }}" class="fs-6 collapse p-5" data-bs-parent="#kt_accordion_units_{{ $stage->id }}">
                                                        
                                                        <div class="d-flex justify-content-between mb-4">
                                                            <h6 class="text-info">الدروس التعليمية</h6>
                                                            <button class="btn btn-sm btn-light-info" onclick="openModal('lesson', {{ $unit->id }})">
                                                                <i class="ki-duotone ki-plus fs-3"></i> إضافة درس
                                                            </button>
                                                        </div>

                                                        <div class="accordion accordion-icon-toggle" id="kt_accordion_lessons_{{ $unit->id }}">
                                                            @foreach($unit->lessons as $lesson)
                                                            <div class="mb-5 ms-5 border-start border-3 border-info ps-4">
                                                                <div class="accordion-header py-3 d-flex justify-content-between" data-bs-toggle="collapse" data-bs-target="#kt_accordion_lesson_{{ $lesson->id }}">
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="accordion-icon"><i class="ki-duotone ki-arrow-right fs-4"><span class="path1"></span><span class="path2"></span></i></span>
                                                                        <h6 class="fs-6 fw-semibold mb-0 ms-4">{{ $lesson->name }}</h6>
                                                                    </div>
                                                                    <button class="btn btn-icon btn-sm btn-light-danger" onclick="deleteItem('lesson', {{ $lesson->id }})"><i class="bi bi-trash"></i></button>
                                                                </div>
                                                                <div id="kt_accordion_lesson_{{ $lesson->id }}" class="fs-6 collapse p-5" data-bs-parent="#kt_accordion_lessons_{{ $unit->id }}">
                                                                    
                                                                    <div class="d-flex justify-content-between mb-4">
                                                                        <span class="fw-bold text-gray-700">المحتويات والمصادر</span>
                                                                        <button class="btn btn-sm btn-light" onclick="openModal('resource', {{ $lesson->id }}, {{ $subject->id }})">
                                                                            <i class="ki-duotone ki-plus fs-3"></i> إضافة محتوى
                                                                        </button>
                                                                    </div>

                                                                    <div class="table-responsive">
                                                                        <table class="table table-striped table-row-bordered gy-5 gs-7">
                                                                            <thead>
                                <tr class="fw-semibold fs-6 text-gray-800 fw-bold text-start">
                                                                                    <th>العنوان</th>
                                                                                    <th>النوع</th>
                                                                                    <th>الرابط</th>
                                                                                    <th >العمليات</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                @foreach($lesson->resources as $resource)
                                                                                <tr>
                                                                                    <td>{{ $resource->title }}</td>
                                                                                    <td><span class="badge badge-light-primary">{{ $resource->type }}</span></td>
                                                                                    <td><a href="{{ $resource->url }}" target="_blank" class="btn btn-sm btn-light-primary">فتح الرابط</a></td>
                                                                                    <td class="text-end">
                                                                                        <button class="btn btn-icon btn-sm btn-light-danger" onclick="deleteItem('resource', {{ $resource->id }})"><i class="bi bi-trash"></i></button>
                                                                                    </td>
                                                                                </tr>
                                                                                @endforeach
                                                                                @if($lesson->resources->isEmpty())
                                                                                <tr><td colspan="4" class="text-center text-muted">لا توجد محتويات لهذا الدرس</td></tr>
                                                                                @endif
                                                                            </tbody>
                                                                        </table>
                                                                    </div>

                                                                </div>
                                                            </div>
                                                            @endforeach
                                                        </div>

                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>

                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="kt_modal_add" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold" id="modal_title">إضافة عنصر</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <form id="kt_modal_add_form" class="form" action="#">
                    @csrf
                    <input type="hidden" id="item_type" name="type">
                    <input type="hidden" id="parent_id" name="parent_id">
                    <input type="hidden" id="extra_id" name="extra_id">

                    <div class="d-flex flex-column mb-7 fv-row" id="field_name_ar">
                        <label class="d-flex align-items-center fs-6 fw-semibold form-label mb-2">
                            <span class="required">الاسم بالعربية</span>
                        </label>
                        <input type="text" class="form-control form-control-solid" placeholder="الاسم" name="name_ar" />
                    </div>

                    <div class="d-flex flex-column mb-7 fv-row" id="field_name_en">
                        <label class="d-flex align-items-center fs-6 fw-semibold form-label mb-2">
                            <span>الاسم بالانجليزية</span>
                        </label>
                        <input type="text" class="form-control form-control-solid" placeholder="Name" name="name_en" />
                    </div>

                    <div class="d-flex flex-column mb-7 fv-row d-none" id="field_title">
                        <label class="d-flex align-items-center fs-6 fw-semibold form-label mb-2">
                            <span class="required">عنوان المحتوى</span>
                        </label>
                        <input type="text" class="form-control form-control-solid" placeholder="العنوان" name="title" />
                    </div>

                    <div class="d-flex flex-column mb-7 fv-row d-none" id="field_resource_type">
                        <label class="d-flex align-items-center fs-6 fw-semibold form-label mb-2">
                            <span class="required">نوع المحتوى</span>
                        </label>
                        <select name="resource_type" class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="اختر النوع">
                            <option value="url">رابط خارجي</option>
                            <option value="video">فيديو</option>
                            <option value="file">ملف</option>
                        </select>
                    </div>

                    <div class="d-flex flex-column mb-7 fv-row d-none" id="field_url">
                        <label class="d-flex align-items-center fs-6 fw-semibold form-label mb-2">
                            <span class="required">الرابط / المسار</span>
                        </label>
                        <input type="text" class="form-control form-control-solid" placeholder="https://..." name="url" />
                    </div>

                    <div class="text-center pt-15">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" id="kt_modal_add_submit" class="btn btn-primary">
                            <span class="indicator-label">حفظ</span>
                            <span class="indicator-progress">يرجى الانتظار...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var modal = new bootstrap.Modal(document.getElementById('kt_modal_add'));
    
    function openModal(type, parentId, extraId = null) {
        $('#kt_modal_add_form')[0].reset();
        $('#item_type').val(type);
        $('#parent_id').val(parentId);
        $('#extra_id').val(extraId);

        if (type === 'resource') {
            $('#modal_title').text('إضافة محتوى تعليمي');
            $('#field_name_ar, #field_name_en').addClass('d-none');
            $('#field_title, #field_resource_type, #field_url').removeClass('d-none');
        } else {
            let typeName = type === 'stage' ? 'مرحلة تعليمية' : (type === 'unit' ? 'وحدة تعليمية' : 'درس تعليمي');
            $('#modal_title').text('إضافة ' + typeName);
            $('#field_name_ar, #field_name_en').removeClass('d-none');
            $('#field_title, #field_resource_type, #field_url').addClass('d-none');
        }
        modal.show();
    }

    $('#kt_modal_add_form').on('submit', function(e) {
        e.preventDefault();
        let type = $('#item_type').val();
        let url = '';
        let data = {
            _token: '{{ csrf_token() }}'
        };

        if (type === 'stage') {
            url = '{{ route("educational_materials.store_stage") }}';
            data.subject_id = $('#parent_id').val();
            data.name_ar = $('input[name="name_ar"]').val();
            data.name_en = $('input[name="name_en"]').val();
        } else if (type === 'unit') {
            url = '{{ route("educational_materials.store_unit") }}';
            data.educational_stage_id = $('#parent_id').val();
            data.name_ar = $('input[name="name_ar"]').val();
            data.name_en = $('input[name="name_en"]').val();
        } else if (type === 'lesson') {
            url = '{{ route("educational_materials.store_lesson") }}';
            data.educational_unit_id = $('#parent_id').val();
            data.name_ar = $('input[name="name_ar"]').val();
            data.name_en = $('input[name="name_en"]').val();
        } else if (type === 'resource') {
            url = '{{ route("educational_materials.store_resource") }}';
            data.educational_lesson_id = $('#parent_id').val();
            data.subject_id = $('#extra_id').val();
            data.title = $('input[name="title"]').val();
            data.type = $('select[name="resource_type"]').val();
            data.url = $('input[name="url"]').val();
        }

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            success: function(res) {
                if(res.success) {
                    Swal.fire({
                        text: "تم الحفظ بنجاح!",
                        icon: "success",
                        buttonsStyling: false,
                        confirmButtonText: "حسناً",
                        customClass: { confirmButton: "btn btn-primary" }
                    }).then(function (result) {
                        location.reload();
                    });
                }
            },
            error: function(err) {
                Swal.fire({
                    text: "حدث خطأ، يرجى التأكد من البيانات.",
                    icon: "error",
                    buttonsStyling: false,
                    confirmButtonText: "حسناً",
                    customClass: { confirmButton: "btn btn-primary" }
                });
            }
        });
    });

    function deleteItem(type, id) {
        Swal.fire({
            text: "هل أنت متأكد من عملية الحذف؟",
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
            if (result.value) {
                let url = '/admin/educational-materials/' + type + '/' + id;
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        location.reload();
                    }
                });
            }
        });
    }
</script>
@endpush
