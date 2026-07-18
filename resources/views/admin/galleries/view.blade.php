@extends('admin.layout.mainLayouts.master')
@section('title')
    @lang('app.' . $active_menu)
@stop

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route($active_menu . '.view') }}" class="text-muted text-hover-primary">@lang('app.' . $active_menu)</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">@lang('app.view')</li>
@endsection
@section('page-breadcrumb')
<li class="breadcrumb-item text-muted">
    <a href="{{ url('/') }}" class="text-muted text-hover-primary">الرئيسية</a>
</li>
<li class="breadcrumb-item text-muted">- {{ $current_route->name_ar ?? null }}</li>
@stop
@section('page-content')
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">
            <div class="card">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <div class="d-flex align-items-center position-relative my-1">
                            <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            <input type="text" id="generalSearch" value="{{ old('name') }}" class="form-control form-control-solid w-250px ps-13 generalSearch" placeholder="البحث " />
                        </div>
                    </div>
                    <div class="card-toolbar">
                        <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                            <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true"></div>
                            @can('admin.'.$active_menu.'.add')
                            <a href="{{ route($active_menu . '.add') }}" class="btn btn-primary">
                                <i class="bi bi-plus-lg"></i>اضافة
                            </a>
                            @endcan
                        </div>
                    </div>
                </div>
                <div class="card-body py-4">
                    @include('admin.layout.masterLayouts.error')
                    <table id="kt_table" class="table table-striped table-row-bordered gy-5 gs-7">
                        <thead>
                                <tr class="fw-semibold fs-6 text-gray-800 fw-bold text-start">
                                <th> # </th>
                                <!-- <th>{{ \App\Helpers\translate('title') }}</th> -->
                               
                               <th>{{ \App\Helpers\translate('image') }}</th>
                               <th>{{ \App\Helpers\translate('section') }}</th>
                               <th>{{ \App\Helpers\translate('status') }}</th>
                                <th>{{ \App\Helpers\translate('actions') }}</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@include('admin.' . $active_menu . '.parts.modal')
@stop
@section('js')
<script>
    $(document).ready(function () {
        var table = $('#kt_table').DataTable({
            responsive: true,
            ordering: false,
            processing: true,
            "bLengthChange": false,
            "bFilter": false,
            serverSide: true,
            ajax: {
                url: "<?= route($active_menu . '.list') ?>",
                data: function (d) {
                    d.name = $('#generalSearch').val();
                }
            },
            columns: [{data: 'DT_RowIndex'},
            {data: "image"},
            {data: "section"},
            {data: "status"},
            {data: 'actions', responsivePriority: -1}
            ],
            "createdRow": function (row, data, dataIndex) {
                $(row).find('td:eq(1)').addClass(' align-items-center');
            }
        });
        $('.generalSearch').on('input', function () {
            table.ajax.reload();
        });
        @include('admin.layout.masterLayouts.delete')
    });
@include('admin.layout.masterLayouts.status')
</script>
@stop
