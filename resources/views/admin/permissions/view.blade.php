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
    @section('toolbar-actions')
        <a href="{{ route($active_menu . '.add') }}" class="btn btn-flex btn-primary btn-sm fs-7 fw-bold">
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
                                <label for="generalSearch" class="form-label">@lang('app.search')</label>
                                <div class="d-flex align-items-center position-relative">
                                    <i class="bi bi-search-heart fs-3 position-absolute ms-5"></i>
                                    <input type="text" id="generalSearch" value="{{ old('name') }}"
                                           class="form-control form-control-solid ps-13 generalSearch"
                                           placeholder="@lang('app.search')" />
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                                <label for="group_id" class="form-label"> @lang('app.parent')</label>
                                <select id="group_id" name="group_id" class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="@lang('app.choose')">
                                    <option value="">@lang('app.choose')</option>
                                    @foreach(\App\Models\PermissionsGroup::all() as $group)
                                        <option value="{{ $group->id }}">{{ $group->{'name_' . app()->getLocale()} }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body py-4">
                        @include('admin.layout.masterLayouts.error')
                        <table id="permissions" class="table table-striped table-row-bordered gy-5 gs-7">
                            <thead>
                                <tr class="fw-semibold fs-6 text-gray-800 fw-bold text-center">
                                    <th class="text-center">#</th>
                                    <th class="text-center">@lang('app.name')</th>
                                    <th class="text-center">@lang('app.parent')</th>
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
        var tableId = 'permissions';
        var columns = [{
                data: 'DT_RowIndex'
            },
            {
                data: 'name',
                className: 'text-center'
            },
            {
                data: 'group_id',
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
            '#group_id'
        ];
        @include('admin.layout.masterLayouts.datatableMaster')
    </script>
@stop
