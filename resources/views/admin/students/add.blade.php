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
                                            <label class="p-2 required">الاسم الكامل (عربي)</label>
                                            <input type="text" name="full_name_ar" value="{{ old('full_name_ar', $info->full_name_ar ?? '') }}"
                                                class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="p-2 required">الاسم الكامل (انجليزي)</label>
                                            <input type="text" name="full_name_en" value="{{ old('full_name_en', $info->full_name_en ?? '') }}"
                                                class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="p-2 required">@lang('app.email')</label>
                                            <input type="email" name="email" value="{{ old('email', $info->email ?? '') }}"
                                                class="form-control" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="p-2 required">@lang('app.phone')</label>
                                            <input type="text" name="phone" value="{{ old('phone', $info->phone ?? '') }}"
                                                class="form-control" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="p-2 {{ $info && $info->id ? '' : 'required' }}">@lang('app.password')</label>
                                            <input type="password" name="password" id="password" class="form-control" {{ $info && $info->id ? '' : 'required' }}>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="p-2">الرقم الوطني (National ID)</label>
                                            <input type="text" name="national_id" value="{{ old('national_id', $info->national_id ?? '') }}"
                                                class="form-control">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="p-2">تاريخ الميلاد</label>
                                            <input type="date" name="date_of_birth" value="{{ old('date_of_birth', isset($info->date_of_birth) ? $info->date_of_birth->format('Y-m-d') : '') }}"
                                                class="form-control">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="p-2">الجنس</label>
                                            <select name="gender" class="form-select" data-control="select2" data-placeholder="اختر الجنس">
                                                <option value=""></option>
                                                @php $selectedGender = old('gender', $info->gender ?? ''); @endphp
                                                <option value="male" {{ $selectedGender == 'male' ? 'selected' : '' }}>ذكر</option>
                                                <option value="female" {{ $selectedGender == 'female' ? 'selected' : '' }}>أنثى</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="p-2">الفرع الدراسي</label>
                                            <select name="branch_id" class="form-select" data-control="select2" data-placeholder="اختر الفرع">
                                                <option value=""></option>
                                                @php $selectedBranch = old('branch_id', $info->branch_id ?? ''); @endphp
                                                @foreach($branches as $branch)
                                                    <option value="{{ $branch->id }}" {{ $selectedBranch == $branch->id ? 'selected' : '' }}>
                                                        {{ app()->getLocale() == 'ar' ? $branch->name_ar : $branch->name_en }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="p-2">المنطقة</label>
                                            <select name="region_id" class="form-select" data-control="select2" data-placeholder="اختر المنطقة">
                                                <option value=""></option>
                                                @php $selectedRegion = old('region_id', $info->region_id ?? ''); @endphp
                                                @foreach($regions as $region)
                                                    <option value="{{ $region->id }}" {{ $selectedRegion == $region->id ? 'selected' : '' }}>
                                                        {{ app()->getLocale() == 'ar' ? $region->name_ar : $region->name_en }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="p-2">التخصص / المهنة</label>
                                            <input type="text" name="major_profession" value="{{ old('major_profession', $info->major_profession ?? '') }}"
                                                class="form-control">
                                        </div>
                                        <div class="col-md-6 d-flex align-items-center mt-6">
                                            <div class="form-check form-switch mt-2">
                                                <input class="form-check-input" type="checkbox" name="is_child" id="is_child" value="1"
                                                    {{ old('is_child', $info->is_child ?? false) ? 'checked' : '' }} style="width: 40px; height: 20px;">
                                                <label class="form-check-label p-2 fw-semibold fs-6" for="is_child">هل الطالب طفل؟ (بحاجة لولي أمر)</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3" id="guardian_container" style="{{ old('is_child', $info->is_child ?? false) ? '' : 'display:none;' }}">
                                        <div class="col-md-12">
                                            <label class="p-2 required">ولي الأمر (Guardian)</label>
                                            <select name="guardian_id" id="guardian_id" class="form-select" data-control="select2" data-placeholder="اختر ولي الأمر" {{ old('is_child', $info->is_child ?? false) ? 'required' : '' }}>
                                                <option value=""></option>
                                                @php $selectedGuardian = old('guardian_id', $info->guardian_id ?? ''); @endphp
                                                @foreach($guardians as $guardian)
                                                    <option value="{{ $guardian->id }}" {{ $selectedGuardian == $guardian->id ? 'selected' : '' }}>
                                                        {{ $guardian->name }} ({{ $guardian->phone }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="p-2">العنوان</label>
                                            <textarea name="address" class="form-control" rows="2">{{ old('address', $info->address ?? '') }}</textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="p-2">المعلومات الصحية</label>
                                            <textarea name="health_information" class="form-control" rows="2">{{ old('health_information', $info->health_information ?? '') }}</textarea>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6 text-center">
                                            <label class="d-block p-2 fw-semibold fs-6">@lang('app.photo')</label>
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

@section('js')
<script>
$(document).ready(function() {
    $('#is_child').on('change', function() {
        if($(this).is(':checked')) {
            $('#guardian_container').slideDown();
            $('#guardian_id').prop('required', true);
        } else {
            $('#guardian_container').slideUp();
            $('#guardian_id').prop('required', false).val('').trigger('change');
        }
    });
});
</script>
@endsection
