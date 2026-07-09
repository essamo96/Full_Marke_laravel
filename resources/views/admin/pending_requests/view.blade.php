@extends('admin.layout.mainLayouts.master')
@section('title', __('app.pending_requests') ?? 'الطلبات العالقة')

@section('content')
<div id="kt_app_content" class="app-content flex-column-fluid">
    <div id="kt_app_content_container" class="app-container container-fluid">
        <div class="card">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <div class="d-flex align-items-center position-relative my-1">
                        <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"></i>
                        <input type="text" data-kt-docs-table-filter="search" class="form-control form-control-solid w-250px ps-13" placeholder="{{ \App\Helpers\translate('search') }}" />
                    </div>
                </div>
            </div>

            <div class="card-body py-4">
                <div class="table-responsive">
                    <table id="kt_datatable" class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                <th>#</th>
                                <th>{{ \App\Helpers\translate('student_name') ?? 'اسم الطالب' }}</th>
                                <th>{{ \App\Helpers\translate('branch') ?? 'الفرع' }}</th>
                                <th>{{ \App\Helpers\translate('study_branch') ?? 'الفرع الدراسي' }}</th>
                                <th>{{ \App\Helpers\translate('status') ?? 'الحالة' }}</th>
                                <th>{{ \App\Helpers\translate('created_at') ?? 'تاريخ الإنشاء' }}</th>
                                <th class="text-end min-w-100px">{{ \App\Helpers\translate('actions') ?? 'الإجراءات' }}</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var table;
    
    var KTDatatablesServerSide = function () {
        var initDatatable = function () {
            table = $("#kt_datatable").DataTable({
                searchDelay: 500,
                processing: true,
                serverSide: true,
                order: [[5, 'desc']], // order by created_at desc
                stateSave: true,
                ajax: {
                    url: "{{ route('pending_requests.list') }}",
                    data: function(d) {
                        d.status = $('#status_filter').val();
                    }
                },
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'full_name_ar', name: 'full_name_ar'},
                    {data: 'branch', name: 'branch'},
                    {data: 'study_branch', name: 'study_branch'},
                    {data: 'status', name: 'status'},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end'},
                ],
                language: {
                    url: "{{ app()->getLocale() == 'ar' ? asset('admin/assets/plugins/custom/datatables/ar.json') : asset('admin/assets/plugins/custom/datatables/en.json') }}"
                }
            });

            document.querySelector('[data-kt-docs-table-filter="search"]').addEventListener('keyup', function (e) {
                table.search(e.target.value).draw();
            });
        }
        return {
            init: function () {
                initDatatable();
            }
        }
    }();

    KTUtil.onDOMContentLoaded(function () {
        KTDatatablesServerSide.init();
    });

    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        
        Swal.fire({
            text: "{{ \App\Helpers\translate('confirm_delete') }}",
            icon: "warning",
            showCancelButton: true,
            buttonsStyling: false,
            confirmButtonText: "{{ \App\Helpers\translate('yes_delete') }}",
            cancelButtonText: "{{ \App\Helpers\translate('no_cancel') }}",
            customClass: {
                confirmButton: "btn btn-danger",
                cancelButton: "btn btn-active-light"
            }
        }).then(function(result) {
            if (result.value) {
                $.ajax({
                    url: "{{ route('pending_requests.delete') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                text: response.message,
                                icon: "success",
                                buttonsStyling: false,
                                confirmButtonText: "{{ \App\Helpers\translate('ok') }}",
                                customClass: {
                                    confirmButton: "btn btn-primary"
                                }
                            }).then(function() {
                                table.ajax.reload(null, false);
                            });
                        } else {
                            Swal.fire({
                                text: response.message,
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "{{ \App\Helpers\translate('ok') }}",
                                customClass: {
                                    confirmButton: "btn btn-primary"
                                }
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            text: "{{ \App\Helpers\translate('execution_error') }}",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "{{ \App\Helpers\translate('ok') }}",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        });
                    }
                });
            }
        });
    });
</script>
@endpush
