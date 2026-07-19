@extends('admin.layout.mainLayouts.master')
@section('title')
    التسجيلات
@stop

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('registrations.view') }}" class="text-muted text-hover-primary">التسجيلات</a>
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
                                    <label class="form-label fs-7 fw-bold">@lang('app.payment_status')</label>
                                    <select name="status" id="filter_status" class="form-select form-select-sm form-select-solid" data-control="select2" data-placeholder="الكل" data-hide-search="true">
                                        <option value=""></option>
                                        <option value="pending">@lang('app.status_pending')</option>
                                        <option value="partially_paid">@lang('app.status_partially_paid')</option>
                                        <option value="fully_paid">@lang('app.status_fully_paid')</option>
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                                    <label class="form-label fs-7 fw-bold">@lang('app.program')</label>
                                    <select name="program_id" id="filter_program" class="form-select form-select-sm form-select-solid" data-control="select2" data-placeholder="الكل">
                                        <option value=""></option>
                                        @foreach($programs as $program)
                                            <option value="{{ $program->id }}">{{ app()->getLocale() == 'ar' ? $program->name_ar : $program->name_en }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                                    <label class="form-label fs-7 fw-bold">@lang('app.subject')</label>
                                    <select name="subject_id" id="filter_subject" class="form-select form-select-sm form-select-solid" data-control="select2" data-placeholder="الكل">
                                        <option value=""></option>
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject->id }}">{{ app()->getLocale() == 'ar' ? $subject->name_ar : $subject->name_en }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                                    <label class="form-label fs-7 fw-bold">@lang('app.group')</label>
                                    <select name="group_id" id="filter_group" class="form-select form-select-sm form-select-solid" data-control="select2" data-placeholder="الكل">
                                        <option value=""></option>
                                        @foreach($groups as $group)
                                            <option value="{{ $group->id }}">{{ $group->name }}</option>
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
                                        <th>@lang('app.registration_number')</th>
                                        <th>@lang('app.student')</th>
                                        <th>@lang('app.subject')</th>
                                        <th>@lang('app.group')</th>
                                        <th>@lang('app.remaining_amount')</th>
                                        <th>@lang('app.status')</th>
                                        <th>@lang('app.date')</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
    let dt;
    var columns = [
        {data: 'registration_number', name: 'registration_number', className: 'text-start'},
        {data: 'student_info', name: 'student_info', orderable: false, searchable: false, className: 'text-start'},
        {data: 'subject_name', name: 'subject_name', orderable: false, searchable: false, className: 'text-start'},
        {data: 'group_name', name: 'group_name', orderable: false, searchable: false, className: 'text-start'},
        {data: 'remaining_amount', name: 'remaining_amount', orderable: false, searchable: false, className: 'text-start'},
        {data: 'status_badge', name: 'status_badge', orderable: false, searchable: false, className: 'text-start'},
        {data: 'created_date', name: 'created_date', orderable: false, searchable: false, className: 'text-start'}
    ];

    var filterFields = [
        '#filter_status',
        '#filter_program',
        '#filter_subject',
        '#filter_group',
        '#filter_date_from',
        '#filter_date_to'
    ];

    function exportExcel() {
        let url = "{{ route('registrations.export') }}?";
        url += "name=" + ($('#generalSearch').val() || '');
        url += "&status=" + ($('#filter_status').val() || '');
        url += "&program_id=" + ($('#filter_program').val() || '');
        url += "&subject_id=" + ($('#filter_subject').val() || '');
        url += "&group_id=" + ($('#filter_group').val() || '');
        url += "&date_from=" + ($('#filter_date_from').val() || '');
        url += "&date_to=" + ($('#filter_date_to').val() || '');
        window.location.href = url;
    }

    @include('admin.layout.masterLayouts.datatableMaster')
</script>
@endsection
