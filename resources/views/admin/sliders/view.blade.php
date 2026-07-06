@extends('admin.layout.mainLayouts.master')

@section('title')
    @lang('app.' . $active_menu)
@stop

@section('page-content')
    @section('toolbar-actions')
        <a href="{{ route($active_menu . '.add') }}" class="btn btn-flex btn-primary h-40px fs-7 fw-bold">
            <i class="bi bi-plus-lg"></i>@lang('app.add')
        </a>
    @endsection
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title w-100 mb-0 row">
                            <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                                <label for="generalSearch" class="form-label">@lang('app.search_by_name')</label>
                                <div class="d-flex align-items-center position-relative">
                                    <i class="bi bi-search-heart fs-3 position-absolute ms-5"></i>
                                    <input type="text" id="generalSearch" value="{{ old('title') }}"
                                           class="form-control form-control-solid ps-13 generalSearch"
                                           placeholder="@lang('app.search')" />
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                                <label for="status" class="form-label"> @lang('app.status')</label>
                                <select id="status" name="status" class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="@lang('app.all')">
                                    <option value="">@lang('app.all')</option>
                                    <option value="1">@lang('app.active_status')</option>
                                    <option value="0">@lang('app.no')</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body py-4">
                        @include('admin.layout.masterLayouts.error')
                        <table id="sliders" class="table table-striped table-row-bordered gy-5 gs-7">
                            <thead>
                                <tr class="fw-semibold fs-6 text-gray-800 fw-bold text-start">
                                    <th>#</th>
                                    <th>@lang('app.image')</th>
                                    <th>@lang('app.name')</th>
                                    <th>@lang('app.status')</th>
                                    <th>@lang('app.actions')</th>
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
    @include('admin.' . $active_menu . '.parts.modal')
@stop

@section('js')
    <script>
        var table;
        var tableId = 'sliders';
        var columns = [{
                data: 'DT_RowIndex'
            },
            {
                data: 'image',
                className: 'text-center'
            },
            {
                data: 'title',
                className: 'text-center'
            },
            {
                data: 'status',
                className: 'text-center'
            },
            {
                data: 'actions',
                responsivePriority: -1
            }
        ];

        var filterFields = [
            '#generalSearch',
            '#status'
        ];
        @include('admin.layout.masterLayouts.datatableMaster')
    </script>
@stop
