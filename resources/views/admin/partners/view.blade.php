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

@section('page-content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <div class="col-lg-6 col-md-6 col-sm-12 mb-3 mx-2">
                                <label for="generalSearch" class="form-label">{{ \App\Helpers\translate('generalSearch') }}</label>
                                <div class="d-flex align-items-center position-relative">
                                    <i class="bi bi-search-heart fs-3 position-absolute ms-5"></i>
                                    <input type="text" id="generalSearch" value="{{ old('name') }}"
                                        class="form-control form-control-solid ps-13 generalSearch"
                                        placeholder="{{ \App\Helpers\translate('search') }}" />
                                </div>
                            </div>
                            
                        </div>
                        <div class="card-toolbar">
                            <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                                <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true"></div>
                                <a href="{{ route($active_menu . '.add') }}" class="btn btn-outline btn-outline-solid btn-outline-primary btn-active-light-primary btn-sm">
                                    <i class="bi bi-plus-lg"></i>{{ \App\Helpers\translate('add') }}
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body py-4">
                        @include('admin.layout.masterLayouts.error')
                        <table id="partners" class="table table-striped table-row-bordered gy-5 gs-7">
                            <thead>
                                <tr class="fw-semibold fs-6 text-gray-800 fw-bold text-start">
                                    <th>#</th>
                                    <th>{{ \App\Helpers\translate('name') }}</th>
                                    
                                    <th>{{ \App\Helpers\translate('image') }}</th>

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
        var table;
        var tableId = 'partners';
        var columns = [{
                data: 'DT_RowIndex'
            , className: 'text-start' },
            {
                data: "partner_name"
            , className: 'text-start' },
            {
                data: "image"
            , className: 'text-start' },
            {
                data: "status"
            , className: 'text-start' },

            {
                data: 'actions',
                responsivePriority: -1
            , className: 'text-start' }
        ];

        @include('admin.layout.masterLayouts.datatableMaster')
    </script>
@stop
