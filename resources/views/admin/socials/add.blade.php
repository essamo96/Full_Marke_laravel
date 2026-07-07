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

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route($active_menu . '.view') }}" class="text-muted text-hover-primary">@lang('app.' . $active_menu)</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">{{ $info ? __('app.edit') : __('app.add') }}</li>
@endsection

@section('page-content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-body py-4">
                        @include('admin.layout.masterLayouts.error')
                        <form action="" method="POST" enctype="multipart/form-data" id="socials_form">
                            {{ csrf_field() }}
                            
                            @if($info)
                                <!-- Edit Mode -->
                                <div class="row mb-5">
                                    <div class="col-md-3 text-center">
                                        <label class="form-label d-block required">@lang('app.image')</label>
                                        <div class="image-input image-input-outline" data-kt-image-input="true" style="background-image: url('{{ asset('assets/media/svg/avatars/blank.svg') }}')">
                                            @php
                                                $editImageUrl = asset('assets/media/svg/avatars/blank.svg');
                                                if ($info->image) {
                                                    $editImageUrl = str_starts_with($info->image, 'assets/') ? asset($info->image) : asset('storage/' . $info->image);
                                                }
                                            @endphp
                                            <div class="image-input-wrapper w-125px h-125px image-preview" style="background-image: url('{{ $editImageUrl }}')"></div>
                                            <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Change avatar">
                                                <i class="bi bi-pencil-fill fs-7"></i>
                                                <input type="file" name="image" accept=".png, .jpg, .jpeg" class="image-input-file" />
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
                                    <div class="col-md-9">
                                        <div class="row mb-5">
                                            <div class="col-md-3">
                                                <label class="form-label required">@lang('app.name_ar')</label>
                                                <input type="text" name="name_ar" value="{{ old('name_ar', $info->name_ar) }}" class="form-control mb-2 mb-md-0" required />
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label required">@lang('app.name_en')</label>
                                                <input type="text" name="name_en" value="{{ old('name_en', $info->name_en) }}" class="form-control mb-2 mb-md-0" required />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label required">@lang('app.icon')</label>
                                                <select name="icon" class="form-select icon-select2" {{ empty($info->preset_logo) ? 'required' : '' }}>
                                                    <option value="">@lang('app.choose')</option>
                                                    <optgroup label="Bootstrap Icons">
                                                        <option value="bi bi-facebook" data-icon="bi bi-facebook" {{ $info->icon == 'bi bi-facebook' ? 'selected' : '' }}>Facebook</option>
                                                        <option value="bi bi-twitter-x" data-icon="bi bi-twitter-x" {{ $info->icon == 'bi bi-twitter-x' ? 'selected' : '' }}>Twitter X</option>
                                                        <option value="bi bi-instagram" data-icon="bi bi-instagram" {{ $info->icon == 'bi bi-instagram' ? 'selected' : '' }}>Instagram</option>
                                                        <option value="bi bi-linkedin" data-icon="bi bi-linkedin" {{ $info->icon == 'bi bi-linkedin' ? 'selected' : '' }}>LinkedIn</option>
                                                        <option value="bi bi-youtube" data-icon="bi bi-youtube" {{ $info->icon == 'bi bi-youtube' ? 'selected' : '' }}>YouTube</option>
                                                        <option value="bi bi-whatsapp" data-icon="bi bi-whatsapp" {{ $info->icon == 'bi bi-whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                                                        <option value="bi bi-telegram" data-icon="bi bi-telegram" {{ $info->icon == 'bi bi-telegram' ? 'selected' : '' }}>Telegram</option>
                                                        <option value="bi bi-snapchat" data-icon="bi bi-snapchat" {{ $info->icon == 'bi bi-snapchat' ? 'selected' : '' }}>Snapchat</option>
                                                    </optgroup>
                                                    <optgroup label="Metronic Icons">
                                                        <option value="ki-duotone ki-facebook" data-icon="ki-duotone ki-facebook" {{ $info->icon == 'ki-duotone ki-facebook' ? 'selected' : '' }}>Facebook (Ki)</option>
                                                        <option value="ki-duotone ki-twitter" data-icon="ki-duotone ki-twitter" {{ $info->icon == 'ki-duotone ki-twitter' ? 'selected' : '' }}>Twitter (Ki)</option>
                                                        <option value="ki-duotone ki-instagram" data-icon="ki-duotone ki-instagram" {{ $info->icon == 'ki-duotone ki-instagram' ? 'selected' : '' }}>Instagram (Ki)</option>
                                                        <option value="ki-duotone ki-youtube" data-icon="ki-duotone ki-youtube" {{ $info->icon == 'ki-duotone ki-youtube' ? 'selected' : '' }}>YouTube (Ki)</option>
                                                    </optgroup>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mb-5">
                                            <div class="col-md-5">
                                                <label class="form-label">@lang('app.preset_logo')</label>
                                                <select name="preset_logo" class="form-select form-select-solid preset-logo-select">
                                                    <option value="">@lang('app.upload_custom')</option>
                                                    <option value="dribbble.svg">Dribbble</option>
                                                    <option value="facebook.svg">Facebook</option>
                                                    <option value="google.svg">Google</option>
                                                    <option value="instagram.svg">Instagram</option>
                                                    <option value="linkedin.svg">LinkedIn</option>
                                                    <option value="twitter.svg">Twitter</option>
                                                    <option value="youtube.svg">YouTube</option>
                                                    <option value="whatsapp.svg">WhatsApp</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label required">@lang('app.link')</label>
                                                <input type="url" name="link" value="{{ old('link', $info->link) }}" class="form-control mb-2 mb-md-0" required />
                                            </div>
                                            <div class="col-md-3 d-flex flex-column">
                                                <label class="form-label">@lang('app.status')</label>
                                                <div class="form-check form-switch mt-2">
                                                    <input class="form-check-input" type="checkbox" name="status" value="1" {{ old('status', $info->status) == 1 ? 'checked' : '' }} style="width: 40px; height: 20px;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <!-- Add Mode (Repeater) -->
                                <div id="socials_repeater">
                                    <div class="form-group">
                                        <div data-repeater-list="socials">
                                            @php
                                                $oldSocials = old('socials', [[]]);
                                            @endphp
                                            @foreach($oldSocials as $index => $item)
                                            <div data-repeater-item class="border p-5 mb-5 rounded shadow-sm relative position-relative">
                                                <div class="row">
                                                    <div class="col-md-3 text-center">
                                                        <label class="form-label d-block required">@lang('app.image')</label>
                                                        @php
                                                            $presetLogoUrl = !empty($item['preset_logo']) ? asset('assets/admin/media/svg/social-logos/' . $item['preset_logo']) : asset('assets/media/svg/avatars/blank.svg');
                                                        @endphp
                                                        <div class="image-input image-input-outline" data-kt-image-input="true" style="background-image: url('{{ asset('assets/media/svg/avatars/blank.svg') }}')">
                                                            <div class="image-input-wrapper w-125px h-125px image-preview" style="background-image: url('{{ $presetLogoUrl }}')"></div>
                                                            <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Change avatar">
                                                                <i class="bi bi-pencil-fill fs-7"></i>
                                                                <input type="file" name="image" accept=".png, .jpg, .jpeg" class="image-input-file" {{ empty($item['preset_logo']) ? 'required' : '' }} />
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
                                                    <div class="col-md-9">
                                                        <div class="row mb-5">
                                                            <div class="col-md-3">
                                                                <label class="form-label required">@lang('app.name_ar')</label>
                                                                <input type="text" name="name_ar" value="{{ $item['name_ar'] ?? '' }}" class="form-control mb-2 mb-md-0" required />
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label class="form-label required">@lang('app.name_en')</label>
                                                                <input type="text" name="name_en" value="{{ $item['name_en'] ?? '' }}" class="form-control mb-2 mb-md-0" required />
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label required">@lang('app.icon')</label>
                                                                <select name="icon" class="form-select icon-select2" {{ empty($item['preset_logo']) ? 'required' : '' }}>
                                                                    <option value="">@lang('app.choose')</option>
                                                                    <optgroup label="Bootstrap Icons">
                                                                        <option value="bi bi-facebook" data-icon="bi bi-facebook" {{ ($item['icon'] ?? '') == 'bi bi-facebook' ? 'selected' : '' }}>Facebook</option>
                                                                        <option value="bi bi-twitter-x" data-icon="bi bi-twitter-x" {{ ($item['icon'] ?? '') == 'bi bi-twitter-x' ? 'selected' : '' }}>Twitter X</option>
                                                                        <option value="bi bi-instagram" data-icon="bi bi-instagram" {{ ($item['icon'] ?? '') == 'bi bi-instagram' ? 'selected' : '' }}>Instagram</option>
                                                                        <option value="bi bi-linkedin" data-icon="bi bi-linkedin" {{ ($item['icon'] ?? '') == 'bi bi-linkedin' ? 'selected' : '' }}>LinkedIn</option>
                                                                        <option value="bi bi-youtube" data-icon="bi bi-youtube" {{ ($item['icon'] ?? '') == 'bi bi-youtube' ? 'selected' : '' }}>YouTube</option>
                                                                        <option value="bi bi-whatsapp" data-icon="bi bi-whatsapp" {{ ($item['icon'] ?? '') == 'bi bi-whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                                                                        <option value="bi bi-telegram" data-icon="bi bi-telegram" {{ ($item['icon'] ?? '') == 'bi bi-telegram' ? 'selected' : '' }}>Telegram</option>
                                                                        <option value="bi bi-snapchat" data-icon="bi bi-snapchat" {{ ($item['icon'] ?? '') == 'bi bi-snapchat' ? 'selected' : '' }}>Snapchat</option>
                                                                    </optgroup>
                                                                    <optgroup label="Metronic Icons">
                                                                        <option value="ki-duotone ki-facebook" data-icon="ki-duotone ki-facebook" {{ ($item['icon'] ?? '') == 'ki-duotone ki-facebook' ? 'selected' : '' }}>Facebook (Ki)</option>
                                                                        <option value="ki-duotone ki-twitter" data-icon="ki-duotone ki-twitter" {{ ($item['icon'] ?? '') == 'ki-duotone ki-twitter' ? 'selected' : '' }}>Twitter (Ki)</option>
                                                                        <option value="ki-duotone ki-instagram" data-icon="ki-duotone ki-instagram" {{ ($item['icon'] ?? '') == 'ki-duotone ki-instagram' ? 'selected' : '' }}>Instagram (Ki)</option>
                                                                        <option value="ki-duotone ki-youtube" data-icon="ki-duotone ki-youtube" {{ ($item['icon'] ?? '') == 'ki-duotone ki-youtube' ? 'selected' : '' }}>YouTube (Ki)</option>
                                                                    </optgroup>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-5">
                                                            <div class="col-md-5">
                                                                <label class="form-label">@lang('app.preset_logo')</label>
                                                                <select name="preset_logo" class="form-select form-select-solid preset-logo-select">
                                                                    <option value="">@lang('app.upload_custom')</option>
                                                                    <option value="dribbble.svg" {{ ($item['preset_logo'] ?? '') == 'dribbble.svg' ? 'selected' : '' }}>Dribbble</option>
                                                                    <option value="facebook.svg" {{ ($item['preset_logo'] ?? '') == 'facebook.svg' ? 'selected' : '' }}>Facebook</option>
                                                                    <option value="google.svg" {{ ($item['preset_logo'] ?? '') == 'google.svg' ? 'selected' : '' }}>Google</option>
                                                                    <option value="instagram.svg" {{ ($item['preset_logo'] ?? '') == 'instagram.svg' ? 'selected' : '' }}>Instagram</option>
                                                                    <option value="linkedin.svg" {{ ($item['preset_logo'] ?? '') == 'linkedin.svg' ? 'selected' : '' }}>LinkedIn</option>
                                                                    <option value="twitter.svg" {{ ($item['preset_logo'] ?? '') == 'twitter.svg' ? 'selected' : '' }}>Twitter</option>
                                                                    <option value="youtube.svg" {{ ($item['preset_logo'] ?? '') == 'youtube.svg' ? 'selected' : '' }}>YouTube</option>
                                                                    <option value="whatsapp.svg" {{ ($item['preset_logo'] ?? '') == 'whatsapp.svg' ? 'selected' : '' }}>WhatsApp</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label required">@lang('app.link')</label>
                                                                <input type="url" name="link" value="{{ $item['link'] ?? '' }}" class="form-control mb-2 mb-md-0" required />
                                                            </div>
                                                            <div class="col-md-3 d-flex flex-column">
                                                                <label class="form-label">@lang('app.status')</label>
                                                                <div class="form-check form-switch mt-2">
                                                                    @php
                                                                        $isChecked = false;
                                                                        if (old('_token')) {
                                                                            $isChecked = isset($item['status']);
                                                                        } else {
                                                                            $isChecked = true;
                                                                        }
                                                                    @endphp
                                                                    <input class="form-check-input" type="checkbox" name="status" value="1" {{ $isChecked ? 'checked' : '' }} style="width: 40px; height: 20px;">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button type="button" data-repeater-delete class="btn btn-sm btn-icon btn-light-danger position-absolute top-0 end-0 m-3">
                                                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                                                </button>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="form-group mt-5">
                                        <a href="javascript:;" data-repeater-create class="btn btn-light-primary">
                                            <i class="ki-duotone ki-plus fs-3"></i>
                                            @lang('app.add')
                                        </a>
                                    </div>
                                </div>
                            @endif

                            <div class="text-center pt-5 border-top mt-5">
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
    <script src="{{ asset('assets/admin/plugins/custom/formrepeater/formrepeater.bundle.js') }}"></script>
    <script>
        // Custom formatting for Select2 to show icons
        const formatIcon = (item) => {
            if (!item.id) {
                return item.text;
            }
            var icon = item.element.getAttribute('data-icon');
            var $result = $(`<span><i class="${icon} fs-2 me-2"></i> ${item.text}</span>`);
            return $result;
        };

        const initSelect2 = (element) => {
            $(element).select2({
                templateSelection: formatIcon,
                templateResult: formatIcon,
                escapeMarkup: function(m) { return m; }
            });
        };

        $(document).ready(function() {
            // Init Select2 for existing elements
            $('.icon-select2').each(function() {
                initSelect2(this);
            });

            // Initialize repeater if it exists
            if ($('#socials_repeater').length) {
                $('#socials_repeater').repeater({
                    initEmpty: false,
                    show: function () {
                        $(this).slideDown();
                        
                        // Re-initialize Select2 for the newly added item
                        var select = $(this).find('.icon-select2');
                        select.removeAttr('data-select2-id');
                        select.find('option').removeAttr('data-select2-id');
                        select.removeClass('select2-hidden-accessible');
                        $(this).find('.select2-container').remove();
                        initSelect2(select);

                        // Reset Image Preview
                        $(this).find('.image-preview').css('background-image', 'url("{{ asset('assets/media/svg/avatars/blank.svg') }}")');
                        $(this).find('.image-input-file').val('');
                        $(this).find('.image-input-file').attr('required', 'required');
                        
                        // Reinitialize KTImageInput if necessary
                        if (typeof KTImageInput !== 'undefined') {
                            KTImageInput.createInstances();
                        }
                    },
                    hide: function (deleteElement) {
                        if(confirm('Are you sure you want to delete this element?')) {
                            $(this).slideUp(deleteElement);
                        }
                    }
                });
            }

            // Custom Image Preview Handler for dynamic file inputs
            $(document).on('change', '.image-input-file', function() {
                let input = this;
                if (input.files && input.files[0]) {
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        $(input).closest('.image-input').find('.image-preview').css('background-image', 'url('+e.target.result+')');
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            });

            // Handle Preset Logo Selection
            $(document).on('change', '.preset-logo-select', function() {
                let preset = $(this).val();
                let parentBlock = $(this).closest('.row').parent(); 
                if (parentBlock.closest('[data-repeater-item]').length) {
                    parentBlock = parentBlock.closest('[data-repeater-item]');
                }
                
                let imagePreview = parentBlock.find('.image-preview');
                let fileInput = parentBlock.find('.image-input-file');
                let iconSelect = parentBlock.find('.icon-select2');
                
                if (preset) {
                    let url = '{{ asset('assets/admin/media/svg/social-logos') }}/' + preset;
                    imagePreview.css('background-image', 'url(' + url + ')');
                    fileInput.removeAttr('required');
                    iconSelect.removeAttr('required');
                } else {
                    imagePreview.css('background-image', 'url("{{ asset('assets/media/svg/avatars/blank.svg') }}")');
                    fileInput.attr('required', 'required');
                    iconSelect.attr('required', 'required');
                }
            });
        });
    </script>
@stop
