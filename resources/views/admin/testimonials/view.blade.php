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

@section('toolbar-actions')
    <a href="{{ route($active_menu . '.add') }}" class="btn btn-flex btn-primary h-40px fs-7 fw-bold">
        <i class="bi bi-plus-lg"></i>@lang('app.add')
    </a>
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
                                    <input type="text" id="generalSearch" value="{{ old('name') }}"
                                        class="form-control form-control-solid ps-13 generalSearch"
                                        placeholder="{{ \App\Helpers\translate('search') }}" />
                                </div>
                            </div>
                        
                            <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                <select id="status" name="status" class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="الكل">
                                    <option value="">الكل</option>
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
                        <table id="testimonials" class="table table-striped table-row-bordered gy-5 gs-7">
                            <thead>
                                <tr class="fw-semibold fs-6 text-gray-800 fw-bold text-start">
                                    <th>#</th>
                                    <th>{{ \App\Helpers\translate('image') }}</th>
                                    <th>{{ \App\Helpers\translate('name') }}</th>
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
        var tableId = 'testimonials';
        var columns = [{
                data: 'DT_RowIndex'
            , className: 'text-start' },
            {
                data: "image"
            , className: 'text-start' },
            {
                data: "name",
                className: 'text-start'
            },
            {
                data: "status"
            , className: 'text-start' },
            {
                data: 'actions',
                responsivePriority: -1
            , className: 'text-start' }
        ];

        var filterFields = [
'#generalSearch',
            '#status'
        ];
        
        @include('admin.layout.masterLayouts.datatableMaster')
    </script>
@stop
