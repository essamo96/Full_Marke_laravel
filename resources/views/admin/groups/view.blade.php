@extends('admin.layout.mainLayouts.master')
@section('title')
    @lang('app.groups') {{ $subject ? ' - ' . (app()->getLocale() == 'ar' ? $subject->name_ar : $subject->name_en) : '' }}
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
    @if($subject)
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('subjects.groups.view', Crypt::encrypt($subject->id)) }}" class="text-muted text-hover-primary">@lang('app.groups')</a>
    </li>
    @else
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('groups.view') }}" class="text-muted text-hover-primary">@lang('app.groups')</a>
    </li>
    @endif
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">@lang('app.view')</li>
@endsection

@section('page-content')
    @section('toolbar-actions')
        @if($subject)
        <a href="{{ route('groups.add', Crypt::encrypt($subject->id)) }}" class="btn btn-flex btn-primary h-40px fs-7 fw-bold">
            <i class="bi bi-plus-lg"></i>@lang('app.add')
        </a>
        @else
        <a href="{{ route('groups.add.global') }}" class="btn btn-flex btn-primary h-40px fs-7 fw-bold">
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
                            <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                                <label for="generalSearch" class="form-label">بحث</label>
                                <div class="d-flex align-items-center position-relative">
                                    <i class="bi bi-search-heart fs-3 position-absolute ms-5"></i>
                                    <input type="text" id="generalSearch" value="{{ old('search_value') }}"
                                           class="form-control form-control-solid ps-13 generalSearch"
                                           placeholder="بحث عن مجموعة..." />
                                </div>
                            </div>
                            @if(!$subject)
                            <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                                <label for="subject_id" class="form-label">المادة الدراسية</label>
                                <select id="subject_id" name="subject_id" class="form-select form-select-solid" data-control="select2" data-placeholder="الكل">
                                    <option value=""></option>
                                    @foreach($subjects as $sub)
                                        <option value="{{ $sub->id }}">{{ app()->getLocale() == 'ar' ? $sub->name_ar : $sub->name_en }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                                <label for="teacher_id" class="form-label">المعلم</label>
                                <select id="teacher_id" name="teacher_id" class="form-select form-select-solid" data-control="select2" data-placeholder="الكل">
                                    <option value=""></option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                                <label for="is_active" class="form-label">الحالة</label>
                                <select id="is_active" name="is_active" class="form-select form-select-solid" data-control="select2" data-placeholder="الكل">
                                    <option value=""></option>
                                    <option value="1">مفعل</option>
                                    <option value="0">معطل</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body py-4">
                        @include('admin.layout.masterLayouts.error')
                        <table id="groups" class="table table-striped table-row-bordered gy-5 gs-7">
                            <thead>
                                <tr class="fw-semibold fs-6 text-gray-800 fw-bold text-start">
                                    <th> @lang('app.name')</th>
                                    <th>المعلم</th>
                                    <th>السعة / المسجلين</th>
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
    @include('admin.groups.parts.modal')
@stop
@section('js')
    <script>
        var table;
        var tableId = 'groups';
        var columns = [
            {
                data: 'name',
                className: 'text-center'
            },
            {
                data: 'teacher',
                className: 'text-center'
            },
            {
                data: 'capacity',
                className: 'text-center'
            },
            {
                data: 'status',
                className: 'text-center',
                orderable: false,
                searchable: false
            },
            {
                data: 'actions',
                responsivePriority: -1,
                orderable: false,
                searchable: false
            }
        ];

        var filterFields = [
            '#generalSearch',
            '#subject_id',
            '#teacher_id',
            '#is_active'
        ];
        
        // Custom ajax url for nested resource
        var customAjaxUrl = "{{ $subject ? route('subjects.groups.list', Crypt::encrypt($subject->id)) : route('groups.list') }}";

        @include('admin.layout.masterLayouts.datatableMaster')
    </script>
@stop
