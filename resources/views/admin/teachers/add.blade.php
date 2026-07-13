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
                                        <div class="col-md-4">
                                            <label class="p-2 required">@lang('app.name')</label>
                                            <input type="text" name="name" value="{{ old('name', $info->name ?? '') }}"
                                                class="form-control" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="p-2 required">@lang('app.email')</label>
                                            <input type="email" name="email" value="{{ old('email', $info->email ?? '') }}"
                                                class="form-control" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="p-2">@lang('app.phone')</label>
                                            <input type="text" name="phone" value="{{ old('phone', $info->phone ?? '') }}"
                                                class="form-control">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="p-2 {{ $info && $info->id ? '' : 'required' }}">@lang('app.password')</label>
                                            <input type="password" name="password" id="password" class="form-control" {{ $info && $info->id ? '' : 'required' }}>
                                        </div>
                                        <div class="col-md-8">
                                            <label class="p-2">المواد الأكاديمية</label>
                                            <select name="subject_ids[]" class="form-select" data-control="select2" data-placeholder="اختر المواد" multiple>
                                                @php $selectedSubjects = old('subject_ids', $info ? $info->subjects->pluck('id')->toArray() : []); @endphp
                                                @foreach ($subjects as $subject)
                                                    <option value="{{ $subject->id }}" {{ in_array($subject->id, $selectedSubjects) ? 'selected' : '' }}>
                                                        {{ app()->getLocale() == 'ar' ? $subject->name_ar : $subject->name_en }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6 text-center">
                                            <label class="d-block p-2 fw-semibold fs-6">@lang('app.photo')</label>
                                            <div class="image-input image-input-outline {{ $info && $info->photo ? '' : 'image-input-empty' }}" data-kt-image-input="true" style="background-image: url('{{ asset('assets/admin/media/svg/avatars/blank.svg') }}')">
                                                <div class="image-input-wrapper w-125px h-125px" style="background-image: {{ $info && $info->photo ? 'url('.asset('storage/'.$info->photo).')' : 'none' }}"></div>
                                                <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Change avatar">
                                                    <i class="bi bi-pencil-fill fs-7"></i>
                                                    <input type="file" name="photo" accept=".png, .jpg, .jpeg" />
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
                                            @php $statusValue = old('status', $info->status ?? 1); @endphp
                                            <div class="form-check form-switch mt-2">
                                                <input class="form-check-input" type="checkbox" name="status" value="1"
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
