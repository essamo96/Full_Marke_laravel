@extends('admin.layout.mainLayouts.master')

@section('title')
    @lang('app.' . $active_menu) | {{ $info ? __('app.edit') : __('app.add') }}
@stop

@section('page-content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <form action="{{ $info ? route($active_menu . '.postEdit', Crypt::encrypt($info->id)) : route($active_menu . '.postAdd') }}"
                      method="POST" enctype="multipart/form-data" class="form">
                    @csrf
                    <div class="card card-flush py-4">
                        <div class="card-header">
                            <div class="card-title">
                                <h2>{{ $info ? __('app.edit') : __('app.add') }}</h2>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            @include('admin.layout.masterLayouts.error')
                            
                            <!-- Media Section -->
                            <h3 class="mb-5 text-primary">@lang('app.media_and_backgrounds')</h3>
                            <div class="row mb-5">
                                <div class="col-md-4 text-center">
                                    <label class="form-label d-block">@lang('app.image')</label>
                                    <div class="image-input image-input-outline" data-kt-image-input="true" style="background-image: url('{{ asset('assets/media/svg/avatars/blank.svg') }}')">
                                        @php
                                            $editImageUrl = asset('assets/media/svg/avatars/blank.svg');
                                            if ($info && $info->image) {
                                                $editImageUrl = asset('storage/' . $info->image);
                                            }
                                        @endphp
                                        <div class="image-input-wrapper w-125px h-125px image-preview" style="background-image: url('{{ $editImageUrl }}')"></div>
                                        <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Change avatar">
                                            <i class="bi bi-pencil-fill fs-7"></i>
                                            <input type="file" name="image" accept=".png, .jpg, .jpeg, .webp" class="image-input-file" />
                                            <input type="hidden" name="image_remove" />
                                        </label>
                                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Cancel avatar">
                                            <i class="bi bi-x fs-2"></i>
                                        </span>
                                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Remove avatar">
                                            <i class="bi bi-x fs-2"></i>
                                        </span>
                                    </div>
                                    <div class="text-muted fs-7 mt-2">@lang('app.fallback_image_help')</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">@lang('app.first_bg_video')</label>
                                    <input type="file" name="video1" class="form-control" accept="video/*" />
                                    @if($info && $info->video1)
                                        <div class="mt-2 text-success"><i class="bi bi-check-circle-fill text-success"></i> @lang('app.video_exists')</div>
                                    @endif
                                    <div class="text-muted fs-7 mt-2">@lang('app.first_bg_video_help')</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">@lang('app.second_bg_video')</label>
                                    <input type="file" name="video2" class="form-control" accept="video/*" />
                                    @if($info && $info->video2)
                                        <div class="mt-2 text-success"><i class="bi bi-check-circle-fill text-success"></i> @lang('app.video_exists')</div>
                                    @endif
                                    <div class="text-muted fs-7 mt-2">@lang('app.second_bg_video_help')</div>
                                </div>
                            </div>
                            
                            <hr class="my-10">

                            <!-- Text Section -->
                            <h3 class="mb-5 text-primary">@lang('app.texts_ar_en')</h3>
                            <div class="row mb-5">
                                <div class="col-md-6">
                                    <label class="form-label">@lang('app.title_ar')</label>
                                    <input type="text" name="title_ar" value="{{ old('title_ar', $info->title_ar ?? '') }}" class="form-control" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">@lang('app.title_en')</label>
                                    <input type="text" name="title_en" value="{{ old('title_en', $info->title_en ?? '') }}" class="form-control" />
                                </div>
                            </div>
                            
                            <div class="row mb-5">
                                <div class="col-md-6">
                                    <label class="form-label">@lang('app.desc_ar')</label>
                                    <textarea name="desc_ar" class="form-control" rows="3">{{ old('desc_ar', $info->desc_ar ?? '') }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">@lang('app.desc_en')</label>
                                    <textarea name="desc_en" class="form-control" rows="3">{{ old('desc_en', $info->desc_en ?? '') }}</textarea>
                                </div>
                            </div>

                            <hr class="my-10">

                            <!-- Buttons Section -->
                            <h3 class="mb-5 text-primary">@lang('app.buttons_and_links')</h3>
                            <div class="row mb-5">
                                <div class="col-md-4">
                                    <label class="form-label">@lang('app.btn1_text_ar')</label>
                                    <input type="text" name="btn1_text_ar" value="{{ old('btn1_text_ar', $info->btn1_text_ar ?? '') }}" class="form-control" placeholder="@lang('app.placeholder_btn1_ar')" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">@lang('app.btn1_text_en')</label>
                                    <input type="text" name="btn1_text_en" value="{{ old('btn1_text_en', $info->btn1_text_en ?? '') }}" class="form-control" placeholder="@lang('app.placeholder_btn1_en')" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">@lang('app.btn1_link')</label>
                                    <input type="text" name="btn1_link" value="{{ old('btn1_link', $info->btn1_link ?? '') }}" class="form-control" placeholder="@lang('app.placeholder_btn_link')" />
                                </div>
                            </div>

                            <div class="row mb-5">
                                <div class="col-md-4">
                                    <label class="form-label">@lang('app.btn2_text_ar')</label>
                                    <input type="text" name="btn2_text_ar" value="{{ old('btn2_text_ar', $info->btn2_text_ar ?? '') }}" class="form-control" placeholder="@lang('app.placeholder_btn2_ar')" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">@lang('app.btn2_text_en')</label>
                                    <input type="text" name="btn2_text_en" value="{{ old('btn2_text_en', $info->btn2_text_en ?? '') }}" class="form-control" placeholder="@lang('app.placeholder_btn2_en')" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">@lang('app.btn2_link')</label>
                                    <input type="text" name="btn2_link" value="{{ old('btn2_link', $info->btn2_link ?? '') }}" class="form-control" placeholder="@lang('app.placeholder_btn_link')" />
                                </div>
                            </div>

                            <hr class="my-10">

                            <!-- Settings Section -->
                            <h3 class="mb-5 text-primary">@lang('app.settings')</h3>
                            <div class="row mb-5 align-items-center">
                                <div class="col-md-6">
                                    <label class="form-label">@lang('app.sort')</label>
                                    <input type="number" name="sort" value="{{ old('sort', $info->sort ?? '1') }}" class="form-control" min="1" />
                                </div>
                                <div class="col-md-6 mt-6">
                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" value="1" id="status" name="status" {{ old('status', $info->status ?? 1) ? 'checked' : '' }} />
                                        <label class="form-check-label" for="status">@lang('app.active_status')</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer d-flex justify-content-end py-6 px-9">
                            <a href="{{ route($active_menu . '.view') }}" class="btn btn-light btn-active-light-primary me-2">@lang('app.cancel')</a>
                            <button type="submit" class="btn btn-primary">@lang('app.save')</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
