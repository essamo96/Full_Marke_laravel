@extends('admin.layout.mainLayouts.master')

@section('title')
    @lang('app.' . $active_menu) - {{ $info->id ? __('app.edit') : __('app.add') }}
@stop

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
                        <form action="" method="POST" enctype="multipart/form-data">
                            {{ csrf_field() }}
                            <div class="row justify-content-center">
                                <div class="col-12 col-md-9">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="p-2 required">@lang('app.full_name')</label>
                                            <input type="text" name="name" value="{{ old('name', $info->name) }}"
                                                class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="p-2 required">@lang('app.email')</label>
                                            <input type="email" name="email" value="{{ old('email', $info->email) }}"
                                                class="form-control" required>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="p-2 {{ $info->id ? '' : 'required' }}">@lang('app.password')</label>
                                            <input type="password" name="password" id="password" class="form-control" {{ $info->id ? '' : 'required' }}>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="p-2 {{ $info->id ? '' : 'required' }}">@lang('app.confirm_password')</label>
                                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" {{ $info->id ? '' : 'required' }}>
                                            <div id="password-match-msg" class="mt-2 fs-7 fw-bold" style="display: none;"></div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="p-2 required">@lang('app.group')</label>
                                            <select name="role" class="form-select" required>
                                                <option value="">@lang('app.choose')</option>
                                                @php 
                                                    $userRoleId = $info->roles ? $info->roles->pluck('id')->first() : '';
                                                    $selectedRole = old('role', $userRoleId); 
                                                @endphp
                                                @foreach ($roles as $role)
                                                    <option value="{{ $role->id }}"
                                                        {{ $selectedRole == $role->id ? 'selected' : '' }}>
                                                        {{ $role->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6 text-center">
                                            <label class="d-block p-2 fw-semibold fs-6">@lang('app.photo')</label>
                                            <!--begin::Image input-->
                                            <div class="image-input image-input-outline {{ $info->photo ? '' : 'image-input-empty' }}" data-kt-image-input="true" style="background-image: url('{{ asset('assets/admin/media/svg/avatars/blank.svg') }}')">
                                                <!--begin::Preview existing avatar-->
                                                <div class="image-input-wrapper w-125px h-125px" style="background-image: {{ $info->photo ? 'url('.asset('storage/'.$info->photo).')' : 'none' }}"></div>
                                                <!--end::Preview existing avatar-->
                                                <!--begin::Label-->
                                                <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Change avatar">
                                                    <i class="bi bi-pencil-fill fs-7"></i>
                                                    <!--begin::Inputs-->
                                                    <input type="file" name="photo" accept=".png, .jpg, .jpeg" />
                                                    <input type="hidden" name="avatar_remove" />
                                                    <!--end::Inputs-->
                                                </label>
                                                <!--end::Label-->
                                                <!--begin::Cancel-->
                                                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Cancel avatar">
                                                    <i class="bi bi-x fs-2"></i>
                                                </span>
                                                <!--end::Cancel-->
                                                <!--begin::Remove-->
                                                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Remove avatar">
                                                    <i class="bi bi-x fs-2"></i>
                                                </span>
                                                <!--end::Remove-->
                                            </div>
                                            <!--end::Image input-->
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
    document.addEventListener('DOMContentLoaded', function () {
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('password_confirmation');
        const matchMsg = document.getElementById('password-match-msg');

        function validatePassword() {
            if (password.value === '' || confirmPassword.value === '') {
                matchMsg.style.display = 'none';
                return;
            }
            
            matchMsg.style.display = 'block';
            if (password.value === confirmPassword.value) {
                matchMsg.textContent = "@lang('app.passwords_match')";
                matchMsg.classList.remove('text-danger');
                matchMsg.classList.add('text-success');
            } else {
                matchMsg.textContent = "@lang('app.passwords_not_match')";
                matchMsg.classList.remove('text-success');
                matchMsg.classList.add('text-danger');
            }
        }

        let timeout = null;
        function debounceValidation() {
            clearTimeout(timeout);
            timeout = setTimeout(validatePassword, 300); // 300ms delay after typing stops
        }

        password.addEventListener('keyup', debounceValidation);
        confirmPassword.addEventListener('keyup', debounceValidation);
    });
</script>
@stop
