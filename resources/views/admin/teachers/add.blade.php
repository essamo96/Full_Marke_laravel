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
                            method="POST">
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
                                        @if(!$info || !$info->id)
                                        <div class="col-md-4">
                                            <label class="p-2 required">@lang('app.password')</label>
                                            <input type="password" name="password" id="password" class="form-control" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="p-2 required">تأكيد كلمة المرور</label>
                                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                                            <span id="password_match_message" class="text-danger mt-1 d-block fs-8" style="display:none;"></span>
                                        </div>
                                        @endif
                                        <div class="col-md-{{ (!$info || !$info->id) ? '4' : '12' }}">
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
                                            @include('admin.components.file-picker', ['name' => 'photo', 'value' => $info->photo ?? old('photo'), 'label' => __('app.photo'), 'folder' => 'teachers'])
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
        $('#password, #password_confirmation').on('keyup', function() {
            var password = $('#password').val();
            var confirmPassword = $('#password_confirmation').val();
            var message = $('#password_match_message');
            
            if (password !== '' && confirmPassword !== '') {
                if (password !== confirmPassword) {
                    message.text('كلمتا المرور غير متطابقتين').show();
                    message.removeClass('text-success').addClass('text-danger');
                } else {
                    message.text('كلمتا المرور متطابقتان').show();
                    message.removeClass('text-danger').addClass('text-success');
                }
            } else {
                message.hide();
            }
        });
        
        $('form').on('submit', function(e) {
            if ($('#password').length > 0) {
                if ($('#password').val() !== $('#password_confirmation').val()) {
                    e.preventDefault();
                    $('#password_match_message').text('كلمتا المرور غير متطابقتين').show();
                    $('#password_match_message').removeClass('text-success').addClass('text-danger');
                }
            }
        });
    });
</script>
@stop
