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
    <li class="breadcrumb-item text-muted">@lang('app.view')</li>
@endsection

@section('page-content')
<div class="card">
    <div class="card-body py-4">
        @include('admin.layout.masterLayouts.error')

        <form action="{{ route('site_settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
                <li class="nav-item">
                    <a class="nav-link active text-dark fw-bold" data-bs-toggle="tab" href="#kt_tab_pane_basic">
                        <i class="fa-solid fa-cogs fs-4 me-2"></i> {{ \App\Helpers\translate('Basic Data') }}
                    </a>
                </li>
                @foreach (['ar' => 'العربية', 'en' => 'English'] as $locale => $name)
                    <li class="nav-item">
                        <a class="nav-link text-dark fw-bold" data-bs-toggle="tab" href="#kt_tab_pane_{{ $locale }}">
                            <i class="fa-solid fa-globe fs-4 me-2"></i> {{ $name }}
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="tab-content" id="myTabContent">
                <!-- Basic Data Tab -->
                <div class="tab-pane fade show active" id="kt_tab_pane_basic" role="tabpanel">
                    @php
                        $options = $info && $info->options ? json_decode($info->options, true) : [];
                    @endphp

                    <div class="row mb-5">
                        <div class="col-12 mb-3">
                            <h4 class="text-primary">{{ \App\Helpers\translate('Contact Info') }}</h4>
                            <hr>
                        </div>
                        <div class="col-md-6 fv-row mb-3">
                            <label class="fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('site_email') }}</label>
                            <input type="email" class="form-control form-control-solid" name="site_email" value="{{ old('site_email', $info->site_email ?? '') }}">
                        </div>
                        <div class="col-md-6 fv-row mb-3">
                            <label class="fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('site_phone') }}</label>
                            <input type="text" class="form-control form-control-solid" name="site_phone" value="{{ old('site_phone', $info->site_phone ?? '') }}">
                        </div>
                    </div>

                    <div class="row mb-5">
                        <div class="col-12 mb-3">
                            <h4 class="text-primary">{{ \App\Helpers\translate('Statistics') }}</h4>
                            <hr>
                        </div>
                        <div class="col-md-4 fv-row">
                            <label class="fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('Completed Courses Count') }}</label>
                            <input type="number" min="0" class="form-control form-control-solid" name="completed_courses_count" value="{{ old('completed_courses_count', $info->completed_courses_count ?? 320) }}">
                        </div>
                        <div class="col-md-4 fv-row">
                            <label class="fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('Registered Students Count') }}</label>
                            <input type="number" min="0" class="form-control form-control-solid" name="registered_students_count" value="{{ old('registered_students_count', $info->registered_students_count ?? 8700) }}">
                        </div>
                        <div class="col-md-4 fv-row">
                            <label class="fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('Training Hours Count') }}</label>
                            <input type="number" min="0" class="form-control form-control-solid" name="training_hours_count" value="{{ old('training_hours_count', $info->training_hours_count ?? 1500) }}">
                        </div>
                    </div>

                    <div class="row mb-5">
                        <div class="col-12 mb-3">
                            <h4 class="text-primary">{{ \App\Helpers\translate('General Options') }}</h4>
                            <hr>
                        </div>
                        <div class="col-md-3 fv-row mb-3">
                            <label class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" name="maintenance_mode" type="checkbox" value="1" {{ old('maintenance_mode', $info->maintenance_mode ?? 0) ? 'checked' : '' }}>
                                <span class="form-check-label fw-semibold text-muted">{{ \App\Helpers\translate('maintenance_mode') }}</span>
                            </label>
                        </div>
                        <div class="col-md-3 fv-row mb-3">
                            <label class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" name="show_translation_button" type="checkbox" value="1" {{ old('show_translation_button', $info->show_translation_button ?? 1) ? 'checked' : '' }}>
                                <span class="form-check-label fw-semibold text-muted">{{ \App\Helpers\translate('show_translation_button') }}</span>
                            </label>
                        </div>
                        <div class="col-md-3 fv-row mb-3">
                            <label class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" name="show_contact_form" type="checkbox" value="1" {{ old('show_contact_form', $options['show_contact_form'] ?? 1) ? 'checked' : '' }}>
                                <span class="form-check-label fw-semibold text-muted">{{ \App\Helpers\translate('Show Contact Form') }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Language Tabs -->
                @foreach (['ar', 'en'] as $locale)
                    @php
                        $translation = $info ? $info->translations->where('locale', $locale)->first() : null;
                    @endphp
                    <div class="tab-pane fade" id="kt_tab_pane_{{ $locale }}" role="tabpanel">
                        <div class="row mb-5">
                            <div class="col-md-12 fv-row mb-5">
                                <label class="fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('seo_title') }} ({{ strtoupper($locale) }})</label>
                                <input type="text" class="form-control form-control-solid" name="{{ $locale }}[seo_title]" value="{{ old("{$locale}.seo_title", $translation->seo_title ?? '') }}">
                            </div>

                            <div class="col-md-12 fv-row mb-5">
                                <label class="fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('seo_description') }} ({{ strtoupper($locale) }})</label>
                                <textarea class="form-control form-control-solid" name="{{ $locale }}[seo_description]" rows="3">{{ old("{$locale}.seo_description", $translation->seo_description ?? '') }}</textarea>
                            </div>

                            <div class="col-md-12 fv-row mb-5">
                                <label class="fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('site_address') }} ({{ strtoupper($locale) }})</label>
                                <input type="text" class="form-control form-control-solid" name="{{ $locale }}[site_address]" value="{{ old("{$locale}.site_address", $translation->site_address ?? '') }}">
                            </div>

                            <div class="col-12 mb-3 mt-5">
                                <h4 class="text-primary">{{ \App\Helpers\translate('maintenance_settings') }} ({{ strtoupper($locale) }})</h4>
                                <hr>
                            </div>

                            <div class="col-md-12 fv-row mb-5">
                                <label class="fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('maintenance_title') }}</label>
                                <input type="text" class="form-control form-control-solid" name="{{ $locale }}[maintenance_title]" value="{{ old("{$locale}.maintenance_title", $translation->maintenance_title ?? '') }}">
                            </div>

                            <div class="col-12 fv-row mb-5">
                                <label class="fs-6 fw-semibold mb-2">{{ \App\Helpers\translate('maintenance_message') }}</label>
                                @include('admin.includes.tinymce-editor', [
                                    'name' => "{$locale}[maintenance_message]",
                                    'value' => old("{$locale}.maintenance_message", $translation->maintenance_message ?? ''),
                                    'height' => 300
                                ])
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="text-center pt-5">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save me-2"></i> {{ \App\Helpers\translate('save') }}
                </button>
            </div>
        </form>
    </div>
</div>
@stop
