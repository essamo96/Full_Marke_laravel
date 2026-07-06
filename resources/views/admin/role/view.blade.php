@extends('admin.layout.mainLayouts.master')
@section('title')
    {{ $current_route->{'name_' . app()->getLocale()} }}
@stop

@section('page-content')
    @section('toolbar-actions')
        <a href="{{ route($active_menu . '.add') }}" class="btn btn-flex btn-primary btn-sm fs-7 fw-bold">
            <i class="ki-duotone ki-plus fs-2"></i>@lang('app.add_new')
        </a>
    @endsection

    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="row w-100">
                            <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                                <label for="generalSearch" class="form-label">@lang('app.search')</label>
                                <div class="d-flex align-items-center position-relative my-1">
                                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    <input type="text" id="generalSearch"
                                        class="form-control form-control-solid ps-13 generalSearch"
                                        placeholder="@lang('app.search')" />
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                                <label for="status" class="form-label">@lang('app.status')</label>
                                <select id="status" name="status" class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="@lang('app.status')">
                                    <option value="">@lang('app.status')</option>
                                    <option value="1">@lang('app.active')</option>
                                    <option value="0">@lang('app.inactive')</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body py-4">
                        @include('admin.layout.masterLayouts.error')
                        <table id="roles" class="table table-striped table-row-bordered gy-5 gs-7">
                            <thead>
                                <tr class="fw-semibold fs-6 text-gray-800 fw-bold text-center">
                                    <th class="text-center">#</th>
                                    <th class="text-center">@lang('app.group_name')</th>
                                    <th class="text-center">@lang('app.status')</th>
                                    <th class="text-center">@lang('app.guard_name')</th>
                                    <th class="text-center">@lang('app.actions')</th>
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
        var tableId = 'roles';
        var columns = [{
                data: 'DT_RowIndex'
            },
            {
                data: 'name',
                className: 'text-center'
            },
            {
                data: 'status',
                className: 'text-center'
            },
            {
                data: 'guard_name',
                className: 'text-center'
            },
            {
                data: 'actions',
                className: 'text-center',
                responsivePriority: -1
            }
        ];

        var filterFields = [
            '#generalSearch',
            '#status',
        ];
        @include('admin.layout.masterLayouts.datatableMaster')
    </script>
@stop
