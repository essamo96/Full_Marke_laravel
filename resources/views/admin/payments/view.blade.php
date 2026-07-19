@extends('admin.layout.mainLayouts.master')
@section('title')
    @lang('app.payment_history')
@stop

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('payments.view') }}" class="text-muted text-hover-primary">@lang('app.payment_history')</a>
    </li>
@endsection

@section('page-content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <form id="filter-form" action="javascript:void(0)" class="w-100">
                            <div class="row w-100 mb-0">
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                                    <label class="form-label fs-7 fw-bold">@lang('app.global_search')</label>
                                    <input type="text" name="name" id="generalSearch" class="form-control form-control-sm form-control-solid" placeholder="@lang('app.search_placeholder')">
                                </div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                                    <label class="form-label fs-7 fw-bold">@lang('app.status')</label>
                                    <select name="status" id="filter_status" class="form-select form-select-sm form-select-solid" data-control="select2" data-placeholder="@lang('app.all')" data-hide-search="true">
                                        <option value=""></option>
                                        <option value="pending">@lang('app.status_pending')</option>
                                        <option value="confirmed">@lang('app.status_confirmed')</option>
                                        <option value="rejected">@lang('app.status_rejected')</option>
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                                    <label class="form-label fs-7 fw-bold">@lang('app.program')</label>
                                    <select name="program_id" id="filter_program" class="form-select form-select-sm form-select-solid" data-control="select2" data-placeholder="@lang('app.all')">
                                        <option value=""></option>
                                        @foreach($programs as $program)
                                            <option value="{{ $program->id }}">{{ app()->getLocale() == 'ar' ? $program->name_ar : $program->name_en }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                                    <label class="form-label fs-7 fw-bold">@lang('app.date_from')</label>
                                    <input type="date" name="date_from" id="filter_date_from" class="form-control form-control-sm form-control-solid">
                                </div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                                    <label class="form-label fs-7 fw-bold">@lang('app.date_to')</label>
                                    <input type="date" name="date_to" id="filter_date_to" class="form-control form-control-sm form-control-solid">
                                </div>
                            </div>
                            <div class="row w-100 mb-0">
                                <div class="col-12 mb-3 d-flex justify-content-end gap-2">
                                    <button type="button" onclick="exportExcel()" class="btn btn-success btn-sm h-35px fs-7 fw-bold">
                                        <i class="bi bi-file-earmark-excel"></i> @lang('app.export_excel')
                                    </button>
                                    <button type="button" class="btn btn-light-danger btn-sm h-35px fs-7 fw-bold reset-filters-btn">
                                        <i class="bi bi-eraser"></i> @lang('app.clear')
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body py-4">
                        @include('admin.layout.masterLayouts.error')
                        <div class="table-responsive">
                            <table id="kt_table" class="table table-striped table-row-bordered gy-5 gs-7">
                                <thead>
                                    <tr class="fw-semibold fs-6 text-gray-800 fw-bold text-start">
                                        <th>@lang('app.payment_number')</th>
                                        <th>@lang('app.student')</th>
                                        <th>@lang('app.amount')</th>
                                        <th>@lang('app.status')</th>
                                        <th>@lang('app.date')</th>
                                        <th>@lang('app.receipt_invoice')</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Modal -->
    <div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('app.receipt_image')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="" id="receiptImage" class="img-fluid" alt="@lang('app.receipt')" style="max-height: 500px; object-fit: contain;">
                </div>
            </div>
        </div>
    </div>

    <!-- Invoice Modal -->
    <div class="modal fade" id="invoiceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('app.payment_invoice')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="invoiceContent">
                    <div class="text-center py-10">
                        <span class="spinner-border text-primary" role="status"></span>
                        <div class="mt-2">@lang('app.loading')</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    var columns = [
        {data: 'payment_number', name: 'payment_number', className: 'text-start'},
        {data: 'student_info', name: 'student_info', orderable: false, searchable: false, className: 'text-start'},
        {data: 'amount', name: 'amount', orderable: false, searchable: false, className: 'text-start'},
        {data: 'status_badge', name: 'status_badge', orderable: false, searchable: false, className: 'text-start'},
        {data: 'created_date', name: 'created_date', orderable: false, searchable: false, className: 'text-start'},
        {data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-start'}
    ];

    var filterFields = [
        '#filter_status',
        '#filter_program',
        '#filter_date_from',
        '#filter_date_to'
    ];

    function exportExcel() {
        let url = "{{ route('payments.export') }}?";
        url += "name=" + ($('#generalSearch').val() || '');
        url += "&status=" + ($('#filter_status').val() || '');
        url += "&program_id=" + ($('#filter_program').val() || '');
        url += "&date_from=" + ($('#filter_date_from').val() || '');
        url += "&date_to=" + ($('#filter_date_to').val() || '');
        window.location.href = url;
    }

    $(document).on('click', '.view-receipt', function() {
        var imageUrl = $(this).data('image');
        $('#receiptImage').attr('src', imageUrl);
        $('#receiptModal').modal('show');
    });

    $(document).on('click', '.view-invoice', function() {
        var url = $(this).data('url');
        $('#invoiceModal').modal('show');
        $('#invoiceContent').html('<div class="text-center py-10"><span class="spinner-border text-primary" role="status"></span><div class="mt-2">@lang('app.loading')</div></div>');
        
        $.get(url, function(data) {
            $('#invoiceContent').html(data);
        }).fail(function() {
            $('#invoiceContent').html('<div class="alert alert-danger text-center">@lang('app.error_loading_invoice')</div>');
        });
    });

    @include('admin.layout.masterLayouts.datatableMaster')
</script>
@stop
