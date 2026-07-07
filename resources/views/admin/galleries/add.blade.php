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

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="row justify-content-center">
                <div class="col-9">

                    {{-- Company Logic --}}
                    <div class="row mb-5">
                        {{-- --}}
                             {{-- @if(isset($company_id))
                                
                             @else
                                <div class="col-md-6 fv-row">
                                    <label class="fs-5 fw-semibold mb-2 required">@lang('company_id')</label>
                                    company_id ?? '') }}">
                                </div>
                        @endif --}}

                        <div class="col-md-6 fv-row">
                            <label class="fs-5 fw-semibold mb-2 required">{{ \App\Helpers\translate('section') }}</label>
                            <input type="text" class="form-control form-control-solid" name="section" value="{{ old('section', $info->section ?? '') }}">
                        </div>
                    </div>

                    {{-- Title & Link --}}
                    <div class="row mb-5">
                        <div class="col-md-6 fv-row">
                            <label class="fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('title') }}</label>
                            <input type="text" class="form-control form-control-solid" name="title" value="{{ old('title', $info->title ?? '') }}">
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('link') }}</label>
                            <input type="text" class="form-control form-control-solid" name="link" value="{{ old('link', $info->link ?? '') }}">
                        </div>
                    </div>

                    {{-- Image --}}
                    <div class="row mb-5">
                        <div class="col-md-6 fv-row">
                            <label class="form-label required">{{ \App\Helpers\translate('image') }}</label>
                            <input type="file" class="form-control form-control-solid" name="image">
                        </div>
                        <div class="col-md-6 fv-row">
                            @if ($info?->image)
                                <img src="{{ asset('storage/' . $info->image) }}" width="100"
                                    class="mt-2 rounded border">
                            @endif
                        </div>
                    </div>

                        <div class="mb-5">
                            <label class="form-label">{{ \App\Helpers\translate('short_description') }}</label>
                            @include('admin.includes.tinymce-editor', [
                                'name' => 'short_description',
                                'value' => old('short_description', $info->short_description),
                                'height' => 300
                            ])
                        </div>

                        <div class="mb-5">
                            <label class="form-label">{{ \App\Helpers\translate('description') }}</label>
                            @include('admin.includes.tinymce-editor', [
                                'name' => 'description',
                                'value' => old('description', $info->description),
                                'height' => 300
                            ])
                        </div>

                    {{-- Status --}}
                    <div class="row mb-5">
                        <div class="col fv-row">
                            <label class="fw-semibold mb-2">{{ \App\Helpers\translate('status') }}</label>
                            <label class="form-check form-switch">
                                <input class="form-check-input" name="status" type="checkbox" value="1"
                                {{ old('status', $info->status ?? 0) == 1 ? 'checked' : '' }}>
                            </label>
                        </div>
                    </div>

                    <div class="text-center pt-2">
                        {{ csrf_field() }}
                        <button type="submit" class="btn btn-sm btn-primary">{{ \App\Helpers\translate('save') }}</button>
                        <a href="{{ route($active_menu . '.view') }}" class="btn btn-sm btn-light me-3">{{ \App\Helpers\translate('cancel') }}</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@stop
