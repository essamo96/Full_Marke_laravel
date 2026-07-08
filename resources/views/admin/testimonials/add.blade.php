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
    <li class="breadcrumb-item text-muted">
        @if (isset($info))
            @lang('app.edit')
        @else
            @lang('app.add')
        @endif
    </li>
@endsection

@section('page-content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <form
                    action="{{ isset($info) ? route($active_menu . '.edit', \Illuminate\Support\Facades\Crypt::encrypt($info->id)) : route($active_menu . '.add') }}"
                    method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card mb-5">
                                <div class="card-body">
                                    <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-bs-toggle="tab" href="#kt_tab_pane_ar">
                                                العربية <img src="{{ asset('admin_assets/media/flags/saudi-arabia.svg') }}"
                                                    alt="Arabic" class="w-15px ms-2">
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_pane_en">
                                                English <img src="{{ asset('admin_assets/media/flags/united-states.svg') }}"
                                                    alt="English" class="w-15px ms-2">
                                            </a>
                                        </li>
                                    </ul>

                                    <div class="tab-content" id="myTabContent">
                                        <!-- Arabic Tab -->
                                        <div class="tab-pane fade show active" id="kt_tab_pane_ar" role="tabpanel">
                                            <div class="mb-5">
                                                <label class="form-label">الاسم <span class="text-danger">*</span></label>
                                                <input type="text" name="name_ar" class="form-control"
                                                    value="{{ old('name_ar', isset($info) ? $info->translations->where('locale', 'ar')->first()?->name : '') }}">
                                            </div>
                                            <div class="mb-5">
                                                <label class="form-label">المنصب (اختياري)</label>
                                                <input type="text" name="position_ar" class="form-control"
                                                    value="{{ old('position_ar', isset($info) ? $info->translations->where('locale', 'ar')->first()?->position : '') }}">
                                            </div>
                                            <div class="mb-5">
                                                <label class="form-label">الرسالة <span class="text-danger">*</span></label>
                                                <textarea name="message_ar" class="form-control" rows="4">{{ old('message_ar', isset($info) ? $info->translations->where('locale', 'ar')->first()?->message : '') }}</textarea>
                                            </div>
                                        </div>

                                        <!-- English Tab -->
                                        <div class="tab-pane fade" id="kt_tab_pane_en" role="tabpanel">
                                            <div class="mb-5">
                                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                                <input type="text" name="name_en" class="form-control"
                                                    value="{{ old('name_en', isset($info) ? $info->translations->where('locale', 'en')->first()?->name : '') }}">
                                            </div>
                                            <div class="mb-5">
                                                <label class="form-label">Position (Optional)</label>
                                                <input type="text" name="position_en" class="form-control"
                                                    value="{{ old('position_en', isset($info) ? $info->translations->where('locale', 'en')->first()?->position : '') }}">
                                            </div>
                                            <div class="mb-5">
                                                <label class="form-label">Message <span class="text-danger">*</span></label>
                                                <textarea name="message_en" class="form-control" rows="4">{{ old('message_en', isset($info) ? $info->translations->where('locale', 'en')->first()?->message : '') }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card mb-5">
                                <div class="card-body">
                                    <div class="mb-5 text-center">
                                        <label class="form-label d-block">{{ \App\Helpers\translate('image') }}</label>
                                        <style>
                                            .image-input-placeholder {
                                                background-image: url('{{ asset('admin_assets/media/svg/avatars/blank.svg') }}');
                                            }
                                        </style>
                                        <div class="image-input image-input-outline image-input-placeholder"
                                            data-kt-image-input="true">
                                            <div class="image-input-wrapper w-125px h-125px"
                                                style="background-image: url('{{ isset($info) && $info->image ? asset('storage/' . $info->image) : asset('admin_assets/media/svg/avatars/blank.svg') }}');">
                                            </div>
                                            <label
                                                class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                                data-kt-image-input-action="change" data-bs-toggle="tooltip"
                                                title="Change avatar">
                                                <i class="bi bi-pencil-fill fs-7"></i>
                                                <input type="file" name="image" accept=".png, .jpg, .jpeg, .webp" />
                                                <input type="hidden" name="avatar_remove" />
                                            </label>
                                            <span
                                                class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                                data-kt-image-input-action="cancel" data-bs-toggle="tooltip"
                                                title="Cancel avatar">
                                                <i class="bi bi-x fs-2"></i>
                                            </span>
                                            <span
                                                class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                                data-kt-image-input-action="remove" data-bs-toggle="tooltip"
                                                title="Remove avatar">
                                                <i class="bi bi-x fs-2"></i>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mb-5">
                                        <label class="form-label">{{ \App\Helpers\translate('display_order') }}</label>
                                        <input type="number" name="display_order" class="form-control"
                                            value="{{ old('display_order', isset($info) ? $info->display_order : 0) }}">
                                    </div>

                                    <div class="mb-5">
                                        <label
                                            class="form-check form-switch form-switch-sm form-check-custom form-check-solid flex-stack">
                                            <span class="form-check-label ms-0 fw-bold fs-6 text-gray-700">
                                                {{ \App\Helpers\translate('status') }}
                                            </span>
                                            <input class="form-check-input" type="checkbox" name="status" value="1"
                                                {{ old('status', isset($info) ? $info->status : 1) ? 'checked' : '' }} />
                                        </label>
                                    </div>

                                    <div class="mt-8 text-center">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <span class="indicator-label">@lang('app.save')</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
