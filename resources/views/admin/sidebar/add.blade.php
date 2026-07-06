@extends('admin.layout.mainLayouts.master')
@section('title')
    {{ $current_route->{'name_' . \App\Helpers\translate('lang')} }}
@stop
@section('page-breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ url('/') }}" class="text-muted text-hover-primary">{{ \App\Helpers\translate('home') }}</a>
    </li>
    <li class="breadcrumb-item text-muted">- {{ $current_route->{'name_' . \App\Helpers\translate('lang')} }}</li>
@stop
@section('page-content')
    <div class="card">
        <div class="card-body py-4">
            @include('admin.layout.masterLayouts.error')
             <form action="" method="POST">
                <div class="row justify-content-center">
                    <div class="col-9">
                        <div class="form-floating mb-9 row ">
                            <div class="col">
                                <label class="p-2  required">{{ \App\Helpers\translate('name') }} </label>
                                <input type="text" value="{{ $info ? $info->name : old('name') }}" name="name"
                                    class="form-control" />
                            </div>
                            <div class="col">
                                <label class="p-2  required"> {{ \App\Helpers\translate('icon') }} </label>
                                <input type="text" value="{{ $info ? $info->icon : old('icon') }}" name="icon"
                                    class="form-control" />
                            </div>

                        </div>
                        <div class="form-floating mb-9 row ">
                            <div class="col">
                                <label class="p-2  required"> {{ \App\Helpers\translate('name_ar') }} </label>
                                <input type="text" value="{{ $info ? $info->name_ar : old('name_ar') }}" name="name_ar"
                                    class="form-control" />
                            </div>
                            <div class="col">
                                <label class="p-2  required"> {{ \App\Helpers\translate('name_en') }} </label>
                                <input type="text" value="{{ $info ? $info->name_en : old('name_en') }}" name="name_en"
                                    class="form-control" />
                            </div>
                        </div>
                        <div class="form-floating mb-9 row ">
                            <div class="col">
                                <label class="p-2  required"> {{ \App\Helpers\translate('sort') }} </label>
                                <input type="numper" value="{{ $info ? $info->sort : old('sort') }}" name="sort"
                                    class="form-control" />
                            </div>
                            <div class="col">
                                <label class="p-2  required"> {{ \App\Helpers\translate('parent') }} </label>
                                <select class="form-select" aria-label="Select example" name="parent_id">
                                    <option value="0">{{ \App\Helpers\translate('choose') }}</option>
                                    <?php $data = $info ? $info->parent_id : old('parent_id'); ?>
                                    @foreach ($permissions as $item)
                                        <option value="{{ $item->id }}" {{ $data == $item->id ? 'selected' : '' }}>
                                            {{ $item->{'name_' . \App\Helpers\translate('lang')} }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-floating mb row">
                            <div class="col">
                                <label class="p-2  required"> {{ \App\Helpers\translate('color') }} </label>
                                <input type="text" value="{{ $info ? $info->color : old('color') }}" name="color"
                                    class="form-control" />
                            </div>
                            <div class="col">
                                <label class="p-2  required">{{ \App\Helpers\translate('status') }}</label>
                                <label class="form-check form-switch">
                                    <?php $data = $info ? $info->status : old('status'); ?>
                                    <input class="form-check-input" name="status" type="checkbox" value="1"
                                        {{ $data == 1 ? 'checked="checked"' : '' }}>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center pt-2">
                    {{ csrf_field() }}
                    <button type="submit" class="btn btn-primary">{{ \App\Helpers\translate('save') }} </button>
                    <a type="reset" href="{{ route($active_menu . '.view') }}"
                        class="btn btn-light me-3">{{ \App\Helpers\translate('cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@stop
