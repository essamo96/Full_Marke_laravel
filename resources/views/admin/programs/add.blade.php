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
    <li class="breadcrumb-item text-muted">@lang('app.' . ($info && $info->id ? 'edit' : 'add'))</li>
@endsection
@section('page-content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-body py-4">
                        @include('admin.layout.masterLayouts.error')
                        <form
                            action="{{ $info && $info->id ? route($active_menu . '.edit.submit', Crypt::encrypt($info->id)) : route($active_menu . '.add.submit') }}"
                            method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="p-2 required">الاسم (عربي)</label>
                                            <input type="text" name="name_ar" value="{{ old('name_ar', $info->name_ar ?? '') }}"
                                                class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="p-2 required">الاسم (انجليزي)</label>
                                            <input type="text" name="name_en" value="{{ old('name_en', $info->name_en ?? '') }}"
                                                class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="p-2 required">الرابط الدائم (Slug)</label>
                                            <input type="text" name="slug" value="{{ old('slug', $info->slug ?? '') }}"
                                                class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="p-2 required">@lang('app.type')</label>
                                            <select name="type" class="form-select" required>
                                                <option value="primary" {{ old('type', $info->type ?? '') == 'primary' ? 'selected' : '' }}>@lang('app.type_primary')</option>
                                                <option value="middle" {{ old('type', $info->type ?? '') == 'middle' ? 'selected' : '' }}>@lang('app.type_middle')</option>
                                                <option value="high" {{ old('type', $info->type ?? '') == 'high' ? 'selected' : '' }}>@lang('app.type_high')</option>
                                                <option value="university" {{ old('type', $info->type ?? '') == 'university' ? 'selected' : '' }}>@lang('app.type_university')</option>
                                                <option value="general" {{ old('type', $info->type ?? '') == 'general' ? 'selected' : '' }}>@lang('app.type_general')</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="p-2">وصف قصير</label>
                                            <textarea name="short_description" class="form-control" rows="3">{{ old('short_description', $info->short_description ?? '') }}</textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="p-2">وصف طويل</label>
                                            <textarea name="long_description" class="form-control" rows="3">{{ old('long_description', $info->long_description ?? '') }}</textarea>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="p-2">الترتيب</label>
                                            <input type="number" name="sort_order" value="{{ old('sort_order', $info->sort_order ?? 0) }}" class="form-control">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6 text-center">
                                            <label class="d-block p-2 fw-semibold fs-6">@lang('app.image')</label>
                                            <div class="image-input image-input-outline {{ $info && $info->image ? '' : 'image-input-empty' }}" data-kt-image-input="true" style="background-image: url('{{ asset('assets/admin/media/svg/avatars/blank.svg') }}')">
                                                <div class="image-input-wrapper w-125px h-125px" style="background-image: {{ $info && $info->image ? 'url('.asset('storage/'.$info->image).')' : 'none' }}"></div>
                                                <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Change avatar">
                                                    <i class="bi bi-pencil-fill fs-7"></i>
                                                    <input type="file" name="image" accept=".png, .jpg, .jpeg" />
                                                    <input type="hidden" name="avatar_remove" />
                                                </label>
                                                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Cancel avatar">
                                                    <i class="bi bi-x fs-2"></i>
                                                </span>
                                                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Remove avatar">
                                                    <i class="bi bi-x fs-2"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-6 d-flex flex-column justify-content-center align-items-center">
                                            <label class="p-2 fw-semibold fs-6">@lang('app.status')</label>
                                            @php $statusValue = old('is_active', $info->is_active ?? 1); @endphp
                                            <div class="form-check form-switch mt-2">
                                                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                                    {{ $statusValue == 1 ? 'checked' : '' }} style="width: 40px; height: 20px;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center pt-2">
                                <button type="submit" class="btn btn-primary">@lang('app.save')</button>
                                <a href="{{ route($active_menu . '.view') }}" class="btn btn-light me-3">@lang('app.cancel')</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
