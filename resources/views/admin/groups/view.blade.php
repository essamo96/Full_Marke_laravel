@extends('admin.layout.mainLayouts.master')
@section('title')
    @lang('app.groups') {{ $subject ? ' - ' . (app()->getLocale() == 'ar' ? $subject->name_ar : $subject->name_en) : '' }}
@stop

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('programs.view') }}" class="text-muted text-hover-primary">@lang('app.programs')</a>
    </li>
    @if($program)
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('programs.subjects.view', Crypt::encrypt($program->id)) }}" class="text-muted text-hover-primary">@lang('app.subjects')</a>
    </li>
    @else
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('subjects.view') }}" class="text-muted text-hover-primary">@lang('app.subjects')</a>
    </li>
    @endif
    @if($subject)
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('subjects.groups.view', Crypt::encrypt($subject->id)) }}" class="text-muted text-hover-primary">@lang('app.groups')</a>
    </li>
    @else
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('groups.view') }}" class="text-muted text-hover-primary">@lang('app.groups')</a>
    </li>
    @endif
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">@lang('app.view')</li>
@endsection

@section('page-content')
    @section('toolbar-actions')
        <a href="#" data-bs-toggle="modal" data-bs-target="#kt_modal_enrolment" class="btn btn-flex btn-info h-40px fs-7 fw-bold me-2" title="تشعيب الطلاب المفلترين وتوزيعهم على المجموعات">
            <i class="ki-duotone ki-people fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
            تشعيب الطلاب
        </a>
        <a href="#" data-bs-toggle="modal" data-bs-target="#kt_modal_transfer" class="btn btn-flex btn-warning h-40px fs-7 fw-bold me-2" title="نقل طالب وتسجيله في مادة جديدة، مع إمكانية تشعيبه لاحقاً">
            <i class="ki-duotone ki-arrow-mix fs-2"><span class="path1"></span><span class="path2"></span></i>
            نقل طالب لمادة جديدة
        </a>
        @if($subject)
        <a href="{{ route('groups.add', Crypt::encrypt($subject->id)) }}" class="btn btn-flex btn-primary h-40px fs-7 fw-bold">
            <i class="bi bi-plus-lg"></i>@lang('app.add')
        </a>
        @else
        <a href="{{ route('groups.add.global') }}" class="btn btn-flex btn-primary h-40px fs-7 fw-bold">
            <i class="bi bi-plus-lg"></i>@lang('app.add')
        </a>
        @endif
    @endsection
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title w-100 mb-0 row">
                            <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                <div class="d-flex align-items-center position-relative">
                                    <i class="bi bi-search-heart fs-3 position-absolute ms-5"></i>
                                    <input type="text" id="generalSearch" value="{{ old('search_value') }}"
                                           class="form-control form-control-solid ps-13 generalSearch"
                                           placeholder="بحث عن مجموعة..." />
                                </div>
                            </div>
                            @if(!$subject)
                            <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                <select id="subject_id" name="subject_id" class="form-select form-select-solid" data-control="select2" data-placeholder="الكل">
                                    <option value=""></option>
                                    @foreach($subjects as $sub)
                                        <option value="{{ $sub->id }}">{{ app()->getLocale() == 'ar' ? $sub->name_ar : $sub->name_en }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                <select id="teacher_id" name="teacher_id" class="form-select form-select-solid" data-control="select2" data-placeholder="الكل">
                                    <option value=""></option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                <select id="is_active" name="is_active" class="form-select form-select-solid" data-control="select2" data-placeholder="الكل">
                                    <option value=""></option>
                                    <option value="1">مفعل</option>
                                    <option value="0">معطل</option>
                                </select>
                            </div>
                        
                            <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                <button type="button" class="btn btn-light-danger h-40px fs-7 fw-bold reset-filters-btn w-100">
                                    <i class="bi bi-eraser fs-3"></i> @lang('app.clear')
                                </button>
                            </div>
</div>
                    </div>
                    <div class="card-body py-4">
                        @include('admin.layout.masterLayouts.error')
                        <table id="groups" class="table table-striped table-row-bordered gy-5 gs-7">
                            <thead>
                                <tr class="fw-semibold fs-6 text-gray-800 fw-bold text-start">
                                    <th>صورة واسم المجموعة</th>
                                    <th>المدرس</th>
                                    <th>عدد الطلاب</th>
                                    <th> @lang('app.status')</th>
                                    <th> @lang('app.actions')</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('admin.groups.parts.modal')
    
    <div class="modal fade" id="kt_modal_group_details" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" id="group_details_content">
                <!-- Content will be loaded via AJAX -->
                <div class="modal-body text-center py-10">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">جاري التحميل...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="kt_modal_generate_code" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">إنشاء كود الانضمام للمجموعة</h3>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <form id="generate_code_form">
                    @csrf
                    <input type="hidden" name="group_id" id="generate_code_group_id">
                    <div class="modal-body">
                        <div class="mb-5 text-center d-none" id="generated_code_container">
                            <h4 class="text-success mb-3">تم إنشاء الكود بنجاح!</h4>
                            <div class="d-flex align-items-center justify-content-center">
                                <div class="bg-light-success px-5 py-3 rounded fs-2 fw-bold text-success me-3" id="generated_code_display" style="letter-spacing: 2px;"></div>
                                <button type="button" class="btn btn-icon btn-light-primary" id="copy_code_btn" data-bs-toggle="tooltip" title="نسخ الكود">
                                    <i class="ki-outline ki-copy fs-2"></i>
                                </button>
                            </div>
                        </div>
                        <div id="generate_code_inputs">
                            <div class="mb-5">
                                <label class="required form-label">عدد الطلاب المسموح لهم (الاستخدامات)</label>
                                <input type="number" class="form-control" name="max_uses" value="1" min="1" required />
                            </div>
                            <div class="mb-5">
                                <label class="required form-label">تاريخ ووقت انتهاء الصلاحية</label>
                                <input type="datetime-local" class="form-control" name="expires_at" required />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">إغلاق</button>
                        <button type="submit" class="btn btn-primary" id="btn_generate_code">
                            <span class="indicator-label">توليد الكود</span>
                            <span class="indicator-progress">الرجاء الانتظار... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
@section('js')
    <script>
        var table;
        var tableId = 'groups';
        var columns = [
            {
                data: 'name',
                className: 'text-start'
            },
            {
                data: 'teacher',
                className: 'text-start'
            },
            {
                data: 'capacity',
                className: 'text-start'
            },
            {
                data: 'status',
                className: 'text-start',
                orderable: false,
                searchable: false
            },
            {
                data: 'actions',
                responsivePriority: -1,
                orderable: false,
                searchable: false
            , className: 'text-start' }
        ];

        var filterFields = [
'#generalSearch',
            '#subject_id',
            '#teacher_id',
            '#is_active'
        ];
        
        // Custom ajax url for nested resource
        var customAjaxUrl = "{{ $subject ? route('subjects.groups.list', Crypt::encrypt($subject->id)) : route('groups.list') }}";

        @include('admin.layout.masterLayouts.datatableMaster')
    </script>

    <script>
        $(document).ready(function() {
            // Details Modal
            $(document).on('click', '.view-group-details', function() {
                var groupId = $(this).data('id');
                var $modal = $('#kt_modal_group_details');
                var $content = $('#group_details_content');
                
                $content.html('<div class="modal-body text-center py-10"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">جاري التحميل...</span></div></div>');
                $modal.modal('show');
                
                $.ajax({
                    url: "{{ url('admin/groups') }}/" + groupId + "/details",
                    type: "GET",
                    success: function(response) {
                        $content.html(response);
                    },
                    error: function() {
                        $content.html('<div class="modal-body text-center py-10 text-danger">حدث خطأ أثناء تحميل البيانات</div>');
                    }
                });
            });

            // Generate Code Modal
            $(document).on('click', '.btn-generate-code', function() {
                var groupId = $(this).data('id');
                $('#generate_code_group_id').val(groupId);
                $('#generated_code_container').addClass('d-none');
                $('#generate_code_inputs').removeClass('d-none');
                $('#btn_generate_code').removeClass('d-none');
                $('#generate_code_form')[0].reset();
                $('#kt_modal_generate_code').modal('show');
            });

            $('#generate_code_form').submit(function(e) {
                e.preventDefault();
                var $btn = $('#btn_generate_code');
                $btn.attr('data-kt-indicator', 'on').prop('disabled', true);
                
                $.ajax({
                    url: "{{ route('groups.generate_code') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        $btn.removeAttr('data-kt-indicator').prop('disabled', false);
                        if(response.success) {
                            $('#generate_code_inputs').addClass('d-none');
                            $('#btn_generate_code').addClass('d-none');
                            $('#generated_code_display').text(response.code);
                            $('#generated_code_container').removeClass('d-none');
                        } else {
                            toastr.error(response.message || "حدث خطأ!");
                        }
                    },
                    error: function(xhr) {
                        $btn.removeAttr('data-kt-indicator').prop('disabled', false);
                        toastr.error(xhr.responseJSON?.message || "حدث خطأ غير متوقع!");
                    }
                });
            });

            $('#copy_code_btn').click(function() {
                var code = $('#generated_code_display').text();
                navigator.clipboard.writeText(code).then(function() {
                    toastr.success("تم نسخ الكود بنجاح");
                });
            });
        });
    </script>
@stop
