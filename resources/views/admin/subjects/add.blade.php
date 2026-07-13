@extends('admin.layout.mainLayouts.master')
@section('title')
    @lang('app.subjects') - {{ isset($program) ? (app()->getLocale() == 'ar' ? $program->name_ar : $program->name_en) : 'إضافة مادة' }}
@stop
@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('programs.view') }}" class="text-muted text-hover-primary">@lang('app.programs')</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    @if(isset($program))
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('programs.subjects.view', Crypt::encrypt($program->id)) }}" class="text-muted text-hover-primary">@lang('app.subjects')</a>
        </li>
    @else
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('subjects.view') }}" class="text-muted text-hover-primary">@lang('app.subjects')</a>
        </li>
    @endif
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
                            action="{{ $info && $info->id ? route('subjects.edit', [Crypt::encrypt($program->id), Crypt::encrypt($info->id)]) : (isset($program) ? route('subjects.add.submit', Crypt::encrypt($program->id)) : route('subjects.add.global.submit')) }}"
                            method="POST" enctype="multipart/form-data">
                            @csrf
                            @if(isset($program))
                                <input type="hidden" name="program_id" value="{{ $program->id }}">
                            @else
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="p-2 required">البرنامج (Program)</label>
                                        <select name="program_id" class="form-select" data-control="select2" data-placeholder="اختر البرنامج" required>
                                            <option value=""></option>
                                            @foreach ($programs as $prog)
                                                <option value="{{ $prog->id }}" {{ old('program_id') == $prog->id ? 'selected' : '' }}>
                                                    {{ app()->getLocale() == 'ar' ? $prog->name_ar : $prog->name_en }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endif
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
                                            <label class="p-2 required">الرسوم (Fee)</label>
                                            <input type="number" step="0.01" name="fee" value="{{ old('fee', $info->fee ?? '') }}"
                                                class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="p-2 required">الحد الأدنى للدفع (Min Payment)</label>
                                            <input type="number" step="0.01" name="min_payment" value="{{ old('min_payment', $info->min_payment ?? '') }}"
                                                class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="p-2">الوصف (عربي)</label>
                                            <textarea name="description_ar" class="form-control" rows="3">{{ old('description_ar', $info->description_ar ?? '') }}</textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="p-2">الوصف (انجليزي)</label>
                                            <textarea name="description_en" class="form-control" rows="3">{{ old('description_en', $info->description_en ?? '') }}</textarea>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="p-2">المعلمين</label>
                                            <select name="teacher_ids[]" class="form-select" data-control="select2" data-placeholder="اختر المعلمين" multiple>
                                                @php $selectedTeachers = old('teacher_ids', $info ? $info->teachers->pluck('id')->toArray() : []); @endphp
                                                @foreach ($teachers as $teacher)
                                                    <option value="{{ $teacher->id }}" {{ in_array($teacher->id, $selectedTeachers) ? 'selected' : '' }}>
                                                        {{ $teacher->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="p-2">الترتيب</label>
                                            <input type="number" name="order" value="{{ old('order', $info->order ?? 0) }}" class="form-control">
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
                                <a href="{{ isset($program) ? route('programs.subjects.view', Crypt::encrypt($program->id)) : route('subjects.view') }}" class="btn btn-light me-3">@lang('app.cancel')</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
