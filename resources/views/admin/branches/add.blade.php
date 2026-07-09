@extends('admin.layout.mainLayouts.master')
@section('title', isset($info) ? __('app.edit') : __('app.add'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route($active_menu . '.view') }}" class="text-muted text-hover-primary">{{ __('app.branches') ?? 'الفروع الدراسية' }}</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">{{ isset($info) ? __('app.edit') : __('app.add') }}</li>
@endsection

@section('page-content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-body py-4">
                        <form action="{{ isset($info) ? route('branches.edit.submit', ['id' => Crypt::encrypt($info->id)]) : route('branches.add.submit') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-5">
                                    <label class="form-label required">{{ \App\Helpers\translate('name_ar') ?? 'الاسم (عربي)' }}</label>
                                    <input type="text" name="name_ar" class="form-control" value="{{ old('name_ar', $info->name_ar ?? '') }}" required>
                                </div>
                                <div class="col-md-6 mb-5">
                                    <label class="form-label required">{{ \App\Helpers\translate('name_en') ?? 'الاسم (إنجليزي)' }}</label>
                                    <input type="text" name="name_en" class="form-control" value="{{ old('name_en', $info->name_en ?? '') }}" required>
                                </div>
                                <div class="col-md-6 mb-5">
                                    <div class="form-check form-switch form-check-custom form-check-solid mt-8">
                                        <input class="form-check-input" type="checkbox" name="status" value="1" id="status" {{ old('status', $info->status ?? 1) ? 'checked' : '' }} />
                                        <label class="form-check-label" for="status">
                                            {{ \App\Helpers\translate('status') ?? 'مفعل' }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-5">
                                <a href="{{ route('branches.view') }}" class="btn btn-light me-3">{{ \App\Helpers\translate('cancel') ?? 'إلغاء' }}</a>
                                <button type="submit" class="btn btn-primary">{{ \App\Helpers\translate('save') ?? 'حفظ' }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
