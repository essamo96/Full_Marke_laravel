@extends('admin.layout.mainLayouts.master')

@section('title')
    @lang('app.' . $active_menu) - {{ isset($info) && $info->id ? __('app.edit') : __('app.add') }}
@stop

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route($active_menu . '.view') }}" class="text-muted text-hover-primary">@lang('app.' . $active_menu)</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">{{ isset($info) && $info->id ? __('app.edit') : __('app.add') }}</li>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route($active_menu . '.view') }}" class="text-muted text-hover-primary">@lang('app.' . $active_menu)</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">{{ $info->id ? __('app.edit') : __('app.add') }}</li>
@endsection

@section('page-content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-body py-4">
                        @include('admin.layout.masterLayouts.error')
                        <form action="" method="POST">
                            {{ csrf_field() }}
                            <div class="row justify-content-center">
                                <div class="col-12 col-md-9">
                                    <div class="row mb-3">
                                        <div class="col-md-12 mb-3">
                                            <label class="p-2 required">@lang('app.name')</label>
                                            <input type="text" name="name" value="{{ old('name', $info->name) }}"
                                                class="form-control" required>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6 mb-3">
                                            <label class="p-2 required">@lang('app.parent')</label>
                                            <select class="form-select" data-control="select2" name="group_id" required>
                                                <option value="">@lang('app.choose')</option>
                                                @php $selectedGroup = old('group_id', $info->group_id); @endphp
                                                @foreach ($permissions as $item)
                                                    <option value="{{ $item->id }}" {{ $selectedGroup == $item->id ? 'selected' : '' }}>
                                                        {{ $item->{'name_' . app()->getLocale()} }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="p-2 required">@lang('app.guard_name')</label>
                                            <select class="form-select" name="guard_name" required>
                                                <option value="">@lang('app.choose')</option>
                                                @php $selectedGuard = old('guard_name', $info->guard_name ?: 'admin'); @endphp
                                                @foreach ($guards as $item)
                                                    <option value="{{ $item }}" {{ $selectedGuard == $item ? 'selected' : '' }}>
                                                        {{ $item }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center pt-2 mt-5">
                                <button type="submit" class="btn btn-sm btn-primary">@lang('app.save')</button>
                                <a href="{{ route($active_menu . '.view') }}" class="btn btn-sm btn-light me-3">@lang('app.cancel')</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
