@extends('admin.layout.mainLayouts.master')
@section('title')
    @lang('app.subjects') {{ $program ? ' - ' . (app()->getLocale() == 'ar' ? $program->name_ar : $program->name_en) : '' }}
@stop

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('programs.view') }}" class="text-muted text-hover-primary">@lang('app.programs')</a>
    </li>
    @if($program)
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('programs.subjects.view', Crypt::encrypt($program->id)) }}" class="text-muted text-hover-primary">@lang('app.subjects')</a>
    </li>
    @else
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('subjects.view') }}" class="text-muted text-hover-primary">@lang('app.subjects')</a>
    </li>
    @endif
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">@lang('app.view')</li>
@endsection
@section('page-content')
    @section('toolbar-actions')
        @if($program)
        <a href="{{ route('subjects.add', Crypt::encrypt($program->id)) }}" class="btn btn-flex btn-primary h-40px fs-7 fw-bold">
            <i class="bi bi-plus-lg"></i>@lang('app.add')
        </a>
        @else
        <a href="{{ route('subjects.add.global') }}" class="btn btn-flex btn-primary h-40px fs-7 fw-bold">
            <i class="bi bi-plus-lg"></i>@lang('app.add')
        </a>
        @endif
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
                                           placeholder="بحث عن مادة..." />
                                </div>
                            </div>
                            @if(!$program)
                            <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                <select id="program_id" name="program_id" class="form-select form-select-solid" data-control="select2" data-placeholder="الكل">
                                    <option value=""></option>
                                    @foreach($programs as $prog)
                                        <option value="{{ $prog->id }}">{{ app()->getLocale() == 'ar' ? $prog->name_ar : $prog->name_en }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
                                <select id="is_active" name="is_active" class="form-select form-select-solid" data-control="select2" data-placeholder="الكل">
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
                        <table id="subjects" class="table table-striped table-row-bordered gy-5 gs-7">
                            <thead>
                                <tr class="fw-semibold fs-6 text-gray-800 fw-bold text-start">
                                    <th> @lang('app.image')</th>
                                    <th> @lang('app.name')</th>
                                    <th>عدد المجموعات</th>
                                    <th>الرسوم</th>
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
    @include('admin.subjects.parts.modal')
@stop
@section('js')
    <script>
        var table;
        var tableId = 'subjects';
        var columns = [
            {
                data: 'image',
                orderable: false,
                searchable: false
            , className: 'text-start' },
            {
                data: 'name',
                className: 'text-start'
            },
            {
                data: 'groups_count',
                className: 'text-start'
            },
            {
                data: 'fee',
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
            '#program_id',
            '#is_active'
        ];
        
        // Custom ajax url for nested resource
        var customAjaxUrl = "{{ $program ? route('programs.subjects.list', Crypt::encrypt($program->id)) : route('subjects.list') }}";

        @include('admin.layout.masterLayouts.datatableMaster')
    </script>
@stop
