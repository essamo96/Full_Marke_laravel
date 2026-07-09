@extends('admin.layout.mainLayouts.master')
@section('title', __('app.branches') ?? 'الفروع الدراسية')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route($active_menu . '.view') }}" class="text-muted text-hover-primary">{{ __('app.branches') ?? 'الفروع الدراسية' }}</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">@lang('app.view')</li>
@endsection

@section('toolbar-actions')
    @if(auth('admin')->user()->can('admin.branches.add'))
    <a href="{{ route($active_menu . '.add') }}" class="btn btn-flex btn-primary h-40px fs-7 fw-bold">
        <i class="bi bi-plus-lg"></i>@lang('app.add')
    </a>
    @endif
@endsection

@section('page-content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title w-100 mb-0 row">
                            <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                <label for="generalSearch" class="form-label">{{ \App\Helpers\translate('search') ?? 'بحث' }}</label>
                                <div class="d-flex align-items-center position-relative">
                                    <i class="bi bi-search-heart fs-3 position-absolute ms-5"></i>
                                    <input type="text" id="generalSearch" class="form-control form-control-solid ps-13 generalSearch" placeholder="{{ \App\Helpers\translate('search') ?? 'بحث' }}" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body py-4">
                        @include('admin.layout.masterLayouts.error')
                        <table id="branches" class="table table-striped table-row-bordered gy-5 gs-7">
                            <thead>
                                <tr class="fw-semibold fs-6 text-gray-800 fw-bold text-start">
                                    <th>#</th>
                                    <th>{{ \App\Helpers\translate('name') ?? 'الاسم' }}</th>
                                    <th>{{ \App\Helpers\translate('status') ?? 'الحالة' }}</th>
                                    <th class="text-end">{{ \App\Helpers\translate('actions') ?? 'الإجراءات' }}</th>
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
        var tableId = 'branches';
        var columns = [
            { data: 'DT_RowIndex', name: 'id' },
            { data: 'name', name: 'name_ar' },
            { data: 'status', name: 'status' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
        ];

        var filterFields = [
            '#generalSearch',
        ];
        
        @include('admin.layout.masterLayouts.datatableMaster')
    </script>
@stop
