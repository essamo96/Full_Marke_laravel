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
});
    </script>
@stop
