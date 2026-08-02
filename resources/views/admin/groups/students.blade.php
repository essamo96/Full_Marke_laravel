@extends('admin.layout.mainLayouts.master')
@section('title')
    طلاب المجموعة: {{ $group->name }}
@stop

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('subjects.groups.view', Crypt::encrypt($group->subject_id)) }}" class="text-muted text-hover-primary">المجموعات</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">الطلاب</li>
@endsection

@section('page-content')
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
                                           placeholder="بحث بالاسم، الإيميل أو الجوال..." />
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                <select id="status" name="status" class="form-select form-select-solid" data-control="select2" data-placeholder="حالة التسجيل">
                                    <option value=""></option>
                                    <option value="pending">@lang('app.status_pending')</option>
                                    <option value="active">@lang('app.status_active')</option>
                                    <option value="rejected">@lang('app.status_rejected')</option>
                                    <option value="canceled">@lang('app.status_canceled')</option>
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
                        <table id="students" class="table table-striped table-row-bordered gy-5 gs-7">
                            <thead>
                                <tr class="fw-semibold fs-6 text-gray-800 fw-bold text-start">
                                    <th>الطالب</th>
                                    <th> @lang('app.phone')</th>
                                    <th>حالة التسجيل</th>
                                    <th>حالة الطالب في المجموعة</th>
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

    <!-- Transfer Student Modal -->
    <div class="modal fade" id="kt_modal_transfer_student" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">نقل الطالب إلى مجموعة أخرى</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="transfer_registration_id">
                    <p class="text-muted fs-7 mb-3">المجموعة الحالية: <strong id="transfer_current_group">-</strong></p>
                    <div id="transfer_no_groups_msg" class="alert alert-warning d-none">لا توجد مجموعات أخرى متاحة لنفس المادة.</div>
                    <div class="mb-3" id="transfer_group_select_wrap">
                        <label class="form-label fw-bold">المجموعة الجديدة</label>
                        <select id="transfer_group_id" class="form-select"></select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-primary" id="confirm_transfer_btn">نقل الطالب</button>
                </div>
            </div>
        </div>
    </div>
@stop
@section('js')
    <script>
        var table;
        var tableId = 'students';
        var columns = [
            {
                data: 'student',
                className: 'text-start'
            },
            {
                data: 'phone',
                className: 'text-start'
            },
            {
                data: 'status',
                className: 'text-start',
                orderable: false,
                searchable: false
            },
            {
                data: 'group_status_select',
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
            '#status'
        ];
        
        var active_menu = 'groups.students';
    </script>
    
    <script>
$(document).ready(function() {
    const dataTableLanguageUrl = "{{ route('datatables.lang', ['locale' => app()->getLocale()]) }}";
    var tableSelector = '#' + tableId;

    table = $(tableSelector).DataTable({
        responsive: true,
        ordering: false,
        processing: true,
        pageLength: 10,
        bLengthChange: true,
        bFilter: false,
        serverSide: true,
        stateSave: true,
        dom: "<'row'<'col-sm-12 col-md-6 d-flex align-items-center justify-content-start'l><'col-sm-12 col-md-6 d-flex align-items-center justify-content-end'f>>" +
             "<'table-responsive'tr>" +
             "<'row'<'col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start'i><'col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end'p>>",
        ajax: {
            url: '{{ route('groups.students.list', Crypt::encrypt($group->id)) }}',
            data: function(d) {
                if (typeof filterFields !== 'undefined') {
                    filterFields.forEach(function(field) {
                        let key = $(field).attr('name') || $(field).attr('id');
                        d[key] = $(field).val();
                    });
                }
            }
        },
        columns: columns,
        language: { url: dataTableLanguageUrl }
    });

    if (typeof filterFields !== 'undefined') {
        $(filterFields.join(',')).on('change keyup', function() {
            table.draw();
        });
    }

    $(document).on('click', '.reset-filters-btn', function(e) {
        e.preventDefault();
        if (typeof filterFields !== 'undefined') {
            filterFields.forEach(function(field) {
                let $el = $(field);
                if ($el.hasClass('select2-hidden-accessible')) {
                    $el.val(null).trigger('change.select2');
                } else if ($el.is(':checkbox') || $el.is(':radio')) {
                    $el.prop('checked', false);
                } else {
                    $el.val('');
                }
            });
        }
        table.search('').columns().search('').draw();
        table.ajax.reload(null, false);
    });

    // Toggle a student's status within this specific group: active <-> suspended.
    // Suspending fires a real-time notification + email to the student (with the
    // remaining amount due) and blocks them from opening this group — see
    // RegistrationsController::updateGroupStatus(), which this reuses as-is.
    $(document).on('change', '.group-status-toggle', function () {
        const toggle = $(this);
        const id = toggle.data('id');
        const groupStatus = toggle.is(':checked') ? 'active' : 'suspended';
        const label = toggle.closest('.form-switch').find('.group-status-toggle-label');

        toggle.prop('disabled', true);

        $.ajax({
            url: "{{ route('registrations.update-group-status') }}",
            type: 'POST',
            data: { _token: "{{ csrf_token() }}", id: id, group_status: groupStatus },
            success: function (response) {
                if (response.status) {
                    label.text(groupStatus === 'active' ? 'فعال' : 'موقوف')
                         .toggleClass('text-success', groupStatus === 'active')
                         .toggleClass('text-danger', groupStatus !== 'active');
                    if (typeof toastr !== 'undefined') toastr.success(response.message);
                } else {
                    toggle.prop('checked', !toggle.is(':checked'));
                    if (typeof toastr !== 'undefined') toastr.error(response.message || 'حدث خطأ.');
                }
            },
            error: function (xhr) {
                toggle.prop('checked', !toggle.is(':checked'));
                if (typeof toastr === 'undefined') return;
                const message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'حدث خطأ.';
                toastr.error(message);
            },
            complete: function () {
                toggle.prop('disabled', false);
            }
        });
    });

    // Transfer student to another group of the same subject.
    const transferModalEl = document.getElementById('kt_modal_transfer_student');
    const transferModal = new bootstrap.Modal(transferModalEl);

    $(document).on('click', '.transfer-student-btn', function () {
        const registrationId = $(this).data('registration-id');
        $('#transfer_registration_id').val(registrationId);
        $('#transfer_group_id').empty();
        $('#transfer_no_groups_msg').addClass('d-none');
        $('#transfer_group_select_wrap').removeClass('d-none');
        $('#confirm_transfer_btn').prop('disabled', true);

        $.ajax({
            url: '{{ url("admin/groups/students/transfer-options") }}/' + registrationId,
            type: 'GET',
            success: function (response) {
                $('#transfer_current_group').text(response.current_group);
                if (!response.groups.length) {
                    $('#transfer_group_select_wrap').addClass('d-none');
                    $('#transfer_no_groups_msg').removeClass('d-none');
                    return;
                }
                response.groups.forEach(function (g) {
                    $('#transfer_group_id').append('<option value="' + g.id + '">' + g.name + '</option>');
                });
                $('#confirm_transfer_btn').prop('disabled', false);
            },
            error: function () {
                if (typeof toastr !== 'undefined') toastr.error('تعذّر تحميل المجموعات المتاحة.');
            }
        });

        transferModal.show();
    });

    $('#confirm_transfer_btn').on('click', function () {
        const btn = $(this);
        btn.prop('disabled', true);

        $.ajax({
            url: "{{ route('groups.students.transfer') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                registration_id: $('#transfer_registration_id').val(),
                group_id: $('#transfer_group_id').val()
            },
            success: function (response) {
                transferModal.hide();
                if (typeof toastr !== 'undefined') {
                    response.success ? toastr.success(response.message) : toastr.error(response.message);
                }
                if (response.success) table.ajax.reload(null, false);
            },
            error: function (xhr) {
                const message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'حدث خطأ.';
                if (typeof toastr !== 'undefined') toastr.error(message);
            },
            complete: function () {
                btn.prop('disabled', false);
            }
        });
    });

    // Remove student from this group (keeps their registration/payment history intact).
    $(document).on('click', '.remove-from-group-btn', function () {
        const registrationId = $(this).data('registration-id');

        Swal.fire({
            title: 'تحذير !',
            text: 'هل أنت متأكد من حذف هذا الطالب من المجموعة؟ سيبقى سجل تسجيله ومدفوعاته دون تغيير.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم، احذف',
            cancelButtonText: 'إلغاء',
            confirmButtonColor: '#d33'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url: "{{ route('groups.students.remove') }}",
                type: 'POST',
                data: { _token: "{{ csrf_token() }}", registration_id: registrationId },
                success: function (response) {
                    if (typeof toastr !== 'undefined') {
                        response.success ? toastr.success(response.message) : toastr.error(response.message);
                    }
                    if (response.success) table.ajax.reload(null, false);
                },
                error: function (xhr) {
                    const message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'حدث خطأ.';
                    if (typeof toastr !== 'undefined') toastr.error(message);
                }
            });
        });
    });
});
    </script>
@stop
