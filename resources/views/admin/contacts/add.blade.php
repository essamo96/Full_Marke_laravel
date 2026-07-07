@extends('admin.layout.mainLayouts.master')

@section('title')
    @lang('app.' . $active_menu) - {{ isset($info) && $info->id ? __('app.edit') : __('app.add') }}
@stop

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route($active_menu . '.view') }}" class="text-muted text-hover-primary">@lang('app.' . $active_menu)</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">{{ isset($info) && $info->id ? __('app.edit') : __('app.add') }}</li>
@endsection

@section('page-content')
    <div class="card">
        <div class="card-body py-4">
            @include('admin.layout.masterLayouts.error')
            <form action="" method="POST">
                <div class="row justify-content-center">
                    <div class="col-9">
                        <div class="row mb-5">
                                
                            <div class="col-md-6 fv-row fv-plugins-icon-container">
                                <label class="required fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('full_name') }}</label>
                                <input type="text" class="form-control form-control-solid" name="full_name"
                                    value="{{ $info ? $info->full_name : old('full_name') }}">
                            </div>
                        </div>
                        <div class="row mb-5">
                            <div class="col-md-6 fv-row fv-plugins-icon-container">
                                <label class="p-2 required fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('contact_type') }}</label>
                                <select class="form-select form-select-solid" data-control="select2" aria-label="Select example" name="contact_type">
                                    <option value="0">{{ \App\Helpers\translate('choose') }}</option>
                                    <?php $data = $info ? $info->contact_type : old('contact_type'); ?>
                                    <option value="government" {{ $data == 'government' ? 'selected' : '' }}>{{ \App\Helpers\translate('government') }}</option>
                                    <option value="person" {{ $data == 'person' ? 'selected' : '' }}>{{ \App\Helpers\translate('person') }}</option>
                                </select>
                            </div>
                            <div class="col-md-6 fv-row fv-plugins-icon-container">
                                <label class="required fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('subject') }}</label>
                                <input type="text" class="form-control form-control-solid" name="subject"
                                    value="{{ $info ? $info->subject : old('subject') }}">
                            </div>
                        </div>
                        <div class="row mb-5">
                            <div class="col-md-6 fv-row fv-plugins-icon-container">
                                <label class=" fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('phone') }}</label>
                                <input type="text" class="form-control form-control-solid" name="phone"
                                    value="{{ $info ? $info->phone : old('phone') }}">
                            </div>

                            <div class="col-md-6 fv-row fv-plugins-icon-container">
                                <label class=" fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('email') }}</label>
                                <input type="text" class="form-control form-control-solid" name="email"
                                    value="{{ $info ? $info->email : old('email') }}">
                            </div>
                        </div>
                        <div class="row mb-5">
                            <div class="col-md-12 fv-row fv-plugins-icon-container">
                                <label class=" fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('message') }}</label>
                                @include('admin.includes.tinymce-editor', [
                                    'name' => 'message',
                                    'value' => $info ? $info->message : old('message'),
                                    'height' => 300
                                ])
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center pt-2">
                    {{ csrf_field() }}
                    <button type="submit"
                        class="btn btn-outline btn-outline-solid btn-outline-primary btn-active-light-primary btn-sm">{{ \App\Helpers\translate('save') }}</button>
                    <a type="reset" href="{{ route($active_menu . '.view') }}"
                        class="btn btn-light me-3">{{ \App\Helpers\translate('cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
@stop
