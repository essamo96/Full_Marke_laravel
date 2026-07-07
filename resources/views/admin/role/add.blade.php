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
@section('page-content')
    <div class="card">
        <div class="card-body py-4">
            @include('admin.layout.masterLayouts.error')
            <form action="" method="POST">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-md-6 mb-7">
                        <label class="required p-2">@lang('app.group_name')</label>
                        <input type="text" value="{{ $info ? $info->name : old('name') }}" name="name" class="form-control" placeholder="@lang('app.group_name')" required />
                    </div>

                    <div class="col-md-3 mb-7 d-flex flex-column">
                        <label class="p-2">@lang('app.status')</label>
                        <div class="form-check form-switch form-check-custom form-check-solid mt-2">
                            <?php $statusVal = $info ? $info->status : old('status'); ?>
                            <input class="form-check-input" name="status" type="checkbox" value="1" {{ $statusVal == 1 ? 'checked' : '' }}>
                        </div>
                    </div>

                    <div class="col-md-3 mb-7 d-flex flex-column">
                        <label class="p-2">@lang('app.is_user')</label>
                        <div class="form-check form-switch form-check-custom form-check-solid mt-2">
                            <?php $isUserVal = $info ? $info->is_user : old('is_user'); ?>
                            <input class="form-check-input" name="is_user" type="checkbox" value="1" {{ $isUserVal == 1 ? 'checked' : '' }}>
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
@stop
