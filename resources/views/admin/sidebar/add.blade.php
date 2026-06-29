@extends('layouts.admin')

@section('title', isset($info) ? __('app.edit') : __('app.add_new'))

@php($pageTitle = isset($info) ? __('app.edit') : __('app.add_new'))

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST"
                  action="{{ isset($info) ? route('sidebar.edit.submit', \Illuminate\Support\Facades\Crypt::encrypt($info->id)) : route('sidebar.add.submit') }}">
                @csrf

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-6 mb-5">
                        <label class="form-label required">{{ __('app.route_name') }} (e.g. <code>users</code>)</label>
                        <input type="text" name="name" value="{{ old('name', $info->name ?? '') }}" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-5">
                        <label class="form-label">{{ __('app.parent_group') }}</label>
                        <select name="parent_id" class="form-select">
                            <option value="0">-- {{ __('app.parent_group') }} --</option>
                            @foreach ($parents as $parent)
                                <option value="{{ $parent->id }}" @selected(old('parent_id', $info->parent_id ?? 0) == $parent->id)>
                                    {{ $parent->name_en ?? $parent->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-5">
                        <label class="form-label">{{ __('app.name_ar') }}</label>
                        <input type="text" name="name_ar" value="{{ old('name_ar', $info->name_ar ?? '') }}" class="form-control">
                    </div>

                    <div class="col-md-6 mb-5">
                        <label class="form-label">{{ __('app.name_en') }}</label>
                        <input type="text" name="name_en" value="{{ old('name_en', $info->name_en ?? '') }}" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-5">
                        <label class="form-label">{{ __('app.icon') }}</label>
                        <input type="text" name="icon" value="{{ old('icon', $info->icon ?? '') }}" class="form-control" placeholder="ki-duotone ki-element-11">
                    </div>

                    <div class="col-md-6 mb-5">
                        <label class="form-label">{{ __('app.color') }}</label>
                        <input type="text" name="color" value="{{ old('color', $info->color ?? '') }}" class="form-control" placeholder="#3699FF">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-5">
                        <label class="form-label">{{ __('app.sort') }}</label>
                        <input type="number" name="sort" value="{{ old('sort', $info->sort ?? 0) }}" class="form-control">
                    </div>

                    <div class="col-md-6 mb-5">
                        <label class="form-label d-block">{{ __('app.status') }}</label>
                        <div class="form-check form-switch">
                            <input type="checkbox" name="status" class="form-check-input" value="1" {{ old('status', $info->status ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label">{{ __('app.active') }}</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">{{ __('app.save') }}</button>
                <a href="{{ route('sidebar.view') }}" class="btn btn-light">{{ __('app.cancel') }}</a>
            </form>
        </div>
    </div>
@endsection
