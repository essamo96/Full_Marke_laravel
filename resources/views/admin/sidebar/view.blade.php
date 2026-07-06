@extends('admin.layout.mainLayouts.master')
@section('title')
    {{ $current_route->{'name_' . \App\Helpers\translate('lang')} }}
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
                                <input type="text" id="generalSearch" value="{{ old('name') }}"
                                    class="form-control form-control-solid w-250px ps-13 generalSearch"
                                    placeholder="{{ \App\Helpers\translate('search') }}" />
                            </div>
                        </div>
                        <div class="card-toolbar">
                            <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                                <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true">
                                </div>
                                <a href="{{ route($active_menu . '.add') }}" class="btn btn-outline btn-outline-solid btn-outline-primary btn-active-light-primary btn-sm">
                                    <i class="bi bi-plus-lg"></i>{{ \App\Helpers\translate('add') }}</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body py-4">
                        @include('admin.layout.masterLayouts.error')
                        <table id="permissions_group" class="table table-row-bordered gy-5">
                            <thead>
                                <tr class="fw-semibold fs-6 text-muted">
                                    <th>#</th>
                                    <th>{{ \App\Helpers\translate('id') }}</th>
                                    <th>{{ \App\Helpers\translate('name') }} </th>
                                    <th>{{ \App\Helpers\translate('name_ar') }}</th>
                                    <th> {{ \App\Helpers\translate('name_en') }}</th>
                                    <th> {{ \App\Helpers\translate('parent') }}</th>
                                    <th> {{ \App\Helpers\translate('sort') }}</th>
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
        var tableId = 'permissions_group';
        var columns = [{
                data: 'DT_RowIndex'
            },
            {
                data: 'id'
            },
            {
                data: 'name'
            },
            {
                data: 'name_ar'
            },
            {
                data: 'name_en'
            },
            {
                data: 'parent_id'
            },
            {
                data: 'sort'
            },
            {
                data: 'status'
            },
            {
                data: 'actions',
                responsivePriority: -1
            }
        ];

        var filterFields = [
            '#generalSearch',
        ];
        @include('admin.layout.masterLayouts.datatableMaster')
    </script>

@stop
