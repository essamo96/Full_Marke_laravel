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
        <a href="#" data-bs-toggle="modal" data-bs-target="#kt_modal_enrolment" class="btn btn-flex btn-info h-40px fs-7 fw-bold me-2" title="تشعيب ونقل الطلاب وتوزيعهم على المجموعات">
            <i class="ki-duotone ki-people fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
            تشعيب / نقل الطلاب
        </a>
        @can('admin.students.add')
        <a href="{{ route('students.add') }}" class="btn btn-flex btn-primary h-40px fs-7 fw-bold">
            <i class="bi bi-plus-lg"></i>@lang('app.add')
        </a>
        @endcan
    @endsection
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
                                           placeholder="بحث..." />
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                <select id="branch_id" name="branch_id" class="form-select form-select-solid" data-control="select2" data-placeholder="الكل">
                                    <option value=""></option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ app()->getLocale() == 'ar' ? $branch->name_ar : $branch->name_en }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                <select id="region_id" name="region_id" class="form-select form-select-solid" data-control="select2" data-placeholder="الكل">
                                    <option value=""></option>
                                    @foreach($regions as $region)
                                        <option value="{{ $region->id }}">{{ app()->getLocale() == 'ar' ? $region->name_ar : $region->name_en }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                <select id="gender" name="gender" class="form-select form-select-solid" data-control="select2" data-placeholder="الكل">
                                    <option value=""></option>
                                    <option value="male">ذكر</option>
                                    <option value="female">أنثى</option>
                                </select>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                <select id="is_child" name="is_child" class="form-select form-select-solid" data-control="select2" data-placeholder="الكل">
                                    <option value=""></option>
                                    <option value="1">طفل</option>
                                    <option value="0">بالغ</option>
                                </select>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                <select id="status" name="status" class="form-select form-select-solid" data-control="select2" data-placeholder="الكل">
                                    <option value=""></option>
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
                        <table id="students" class="table table-striped table-row-bordered gy-5 gs-7">
                            <thead>
                                <tr class="fw-semibold fs-6 text-gray-800 fw-bold text-start">
                                    <th>#</th>
                                    <th> @lang('app.name')</th>
                                    <th> @lang('app.email')</th>
                                    <th> @lang('app.phone')</th>
                                    <th>الفرع</th>
                                    <th>عدد التسجيلات</th>
                                    <th> @lang('app.status')</th>
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
    @include('admin.students.parts.modal')
@stop
@section('js')
    <script>
        var table;
        var tableId = 'students';
        var columns = [
            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false
            , className: 'text-start' },
            {
                data: 'name',
                name: 'name',
                className: 'text-start'
            },
            {
                data: 'email',
                name: 'email',
                className: 'text-start'
            },
            {
                data: 'phone',
                name: 'phone',
                className: 'text-start'
            },
            {
                data: 'branch',
                name: 'branch',
                className: 'text-start'
            },
            {
                data: 'registrations_count',
                name: 'registrations_count',
                className: 'text-start',
                orderable: false,
                searchable: false
            },
            {
                data: 'status',
                name: 'status',
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
            '#branch_id',
            '#region_id',
            '#gender',
            '#is_child',
            '#status'
        ];

        @include('admin.layout.masterLayouts.datatableMaster')
    </script>
@stop
