@extends('admin.layout.mainLayouts.master')

@section('title')
    {{ $current_route->{'name_' . app()->getLocale()} }}
@stop

@section('page-content')
<?php
    // قائمة أيقونات Bootstrap Icons الشائعة (مختارة لتغطية الاحتياجات الأساسية)
    $bsIcons = [
        'bi-house-door','bi-house-fill','bi-grid-fill','bi-grid','bi-list','bi-list-ul','bi-list-check',
        'bi-people','bi-people-fill','bi-person','bi-person-fill','bi-person-badge','bi-person-circle','bi-person-check','bi-person-vcard',
        'bi-gear','bi-gear-fill','bi-tools','bi-wrench','bi-sliders',
        'bi-bell','bi-bell-fill','bi-envelope','bi-envelope-fill','bi-chat','bi-chat-fill','bi-chat-heart-fill','bi-chat-dots',
        'bi-calendar2-check','bi-calendar2','bi-calendar2-event','bi-clock','bi-clock-history','bi-clock-fill',
        'bi-cash','bi-cash-coin','bi-credit-card','bi-bank','bi-currency-dollar',
        'bi-shield','bi-shield-check','bi-shield-fill','bi-shield-lock','bi-lock','bi-lock-fill','bi-unlock','bi-key-fill',
        'bi-image','bi-images','bi-camera','bi-camera-fill','bi-file-image',
        'bi-folder','bi-folder-fill','bi-files','bi-file-earmark','bi-file-earmark-text',
        'bi-globe2','bi-translate','bi-flag','bi-flag-fill',
        'bi-megaphone','bi-megaphone-fill','bi-newspaper','bi-broadcast',
        'bi-briefcase','bi-briefcase-fill','bi-buildings','bi-building','bi-shop',
        'bi-tag','bi-tag-fill','bi-tags-fill','bi-bookmark','bi-bookmark-fill',
        'bi-cart','bi-cart-fill','bi-bag','bi-bag-fill','bi-receipt',
        'bi-link','bi-link-45deg','bi-share','bi-share-fill',
        'bi-pin-angle-fill','bi-bookmark-star','bi-star','bi-star-fill','bi-heart','bi-heart-fill',
        'bi-trophy','bi-trophy-fill','bi-award','bi-award-fill','bi-patch-check','bi-patch-question-fill',
        'bi-question-circle','bi-info-circle','bi-exclamation-circle','bi-check-circle','bi-x-circle',
        'bi-graph-up','bi-graph-up-arrow','bi-bar-chart','bi-pie-chart','bi-activity',
        'bi-diagram-3-fill','bi-diagram-2','bi-collection','bi-columns-gap','bi-layout-text-window',
        'bi-pencil','bi-pencil-fill','bi-pencil-square','bi-eraser',
        'bi-eye','bi-eye-fill','bi-eye-slash','bi-search','bi-zoom-in',
        'bi-telephone','bi-telephone-fill','bi-telephone-inbound-fill','bi-phone',
        'bi-palette','bi-palette-fill','bi-brush','bi-droplet','bi-paint-bucket',
        'bi-puzzle','bi-puzzle-fill','bi-boxes','bi-box','bi-box-seam',
        'bi-truck','bi-airplane','bi-geo-alt','bi-geo-alt-fill','bi-map','bi-pin-map-fill',
        'bi-cloud','bi-cloud-fill','bi-server','bi-database','bi-database-fill',
        'bi-trash','bi-trash-fill','bi-archive','bi-archive-fill',
        'bi-arrow-up','bi-arrow-down','bi-arrow-left','bi-arrow-right','bi-arrow-clockwise','bi-arrow-counterclockwise',
        'bi-play','bi-play-fill','bi-pause','bi-pause-fill','bi-stop-fill','bi-record-fill',
        'bi-printer','bi-printer-fill','bi-display','bi-laptop','bi-phone-fill','bi-tablet',
    ];

    $metronicColors = ['primary','success','info','warning','danger','dark','muted','secondary'];
?>
@section('page-breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ url('/') }}" class="text-muted text-hover-primary">{{ __('home') }}</a>
    </li>
    <li class="breadcrumb-item text-muted">
        - {{ $current_route->{'name_' . app()->getLocale()} }}
    </li>
@stop

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">
            <div class="card card-flush shadow-sm">
                <div class="card-body py-4">
                    @include('admin.layout.masterLayouts.error')

                    <form action="" method="POST">
                        @csrf

                        <div class="row g-5 mb-5">
                            <div class="col-md-4">
                                <label class="form-label required fw-semibold">{{ \App\Helpers\translate('name') }}</label>
                                <input type="text" value="{{ $info ? $info->name : old('name') }}" name="name" class="form-control form-control-solid">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label required fw-semibold">{{ \App\Helpers\translate('name_ar') }}</label>
                                <input type="text" value="{{ $info ? $info->name_ar : old('name_ar') }}" name="name_ar" class="form-control form-control-solid">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label required fw-semibold">{{ \App\Helpers\translate('name_en') }}</label>
                                <input type="text" value="{{ $info ? $info->name_en : old('name_en') }}" name="name_en" class="form-control form-control-solid">
                            </div>
                        </div>

                        <div class="row g-5 mb-5">
                            {{-- Icon Picker (Select2) --}}
                            <div class="col-md-6">
                                <label class="form-label required fw-semibold">
                                    <i class="bi bi-emoji-smile me-1"></i>{{ \App\Helpers\translate('icon') }}
                                </label>
                                <select name="icon" id="icon_picker" class="form-select form-select-solid">
                                    <option value="">{{ \App\Helpers\translate('choose_icon') }}</option>
                                    @php $current = $info ? trim($info->icon) : old('icon'); @endphp
                                    @foreach ($bsIcons as $ic)
                                        <option value="{{ $ic }}" {{ $current == $ic ? 'selected' : '' }}>{{ $ic }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">{{ \App\Helpers\translate('icon_picker_hint') }}</small>
                            </div>

                            {{-- Color picker --}}
                            <div class="col-md-6">
                                <label class="form-label required fw-semibold">
                                    <i class="bi bi-palette-fill me-1"></i>{{ \App\Helpers\translate('color') }}
                                </label>
                                <select name="color" id="color_picker" class="form-select form-select-solid">
                                    @php $currentColor = $info ? $info->color : old('color', 'primary'); @endphp
                                    @foreach ($metronicColors as $c)
                                        <option value="{{ $c }}" {{ $currentColor == $c ? 'selected' : '' }}>{{ $c }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row g-5 mb-5">
                            <div class="col-md-4">
                                <label class="form-label required fw-semibold">{{ \App\Helpers\translate('sort') }}</label>
                                <input type="number" value="{{ $info ? $info->sort : old('sort', 999) }}" name="sort" class="form-control form-control-solid">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">@lang('app.parent')</label>
                                @php 
                                    $parentData = $info ? $info->parent_id : old('parent_id'); 
                                    $lang = app()->getLocale(); 
                                @endphp
                                <select class="form-select form-select-solid" name="parent_id" data-control="select2">
                                    <option value="0">@lang('app.top_level')</option>
                                    @foreach ($permissions as $item)
                                        <option value="{{ $item->id }}" {{ $parentData == $item->id ? 'selected' : '' }}>
                                            {{ $item->{'name_' . $lang} }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold d-block">{{ \App\Helpers\translate('status') }}</label>
                                @php $sv = $info ? $info->status : old('status', 1); @endphp
                                <div class="form-check form-switch form-check-custom form-check-solid mt-2">
                                    <input class="form-check-input h-30px w-50px" name="status" type="checkbox" value="1" {{ $sv == 1 ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>

                        {{-- معاينة --}}
                        <div class="bg-light-info rounded p-4 mb-5 d-flex align-items-center">
                            <strong class="me-3">{{ \App\Helpers\translate('preview') }}:</strong>
                            <i id="preview_icon" class="bi {{ $current ?: 'bi-question-circle' }} fs-2 text-{{ $currentColor }}"></i>
                            <span class="ms-3 fs-5 fw-bold" id="preview_name">{{ $info ? ($info->{'name_' . app()->getLocale()} ?: '—') : '—' }}</span>
                        </div>

                        <div class="text-center pt-2 border-top pt-5">
                            <a href="{{ route($active_menu . '.view') }}" class="btn btn-light me-3">
                                <i class="bi bi-x-lg me-1"></i>{{ \App\Helpers\translate('cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary px-7">
                                <i class="bi bi-save me-1"></i>{{ \App\Helpers\translate('save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .select2-icon-option { display: flex; align-items: center; }
    .select2-icon-option i { font-size: 1.3rem; margin-inline-end: 8px; width: 25px; text-align: center; }
    .select2-color-option { display: flex; align-items: center; }
    .select2-color-option .color-circle { width: 18px; height: 18px; border-radius: 50%; margin-inline-end: 10px; }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // ============ Icon picker with preview ============
    function formatIcon(state) {
        if (!state.id) return state.text;
        return $('<span class="select2-icon-option"><i class="bi ' + state.id + '"></i>' + state.text + '</span>');
    }

    $('#icon_picker').select2({
        placeholder: '{{ \App\Helpers\translate("choose_icon") }}',
        templateResult: formatIcon,
        templateSelection: formatIcon,
        width: '100%',
    });

    // ============ Color picker with circle preview ============
    const colorMap = {
        primary: '#009ef7', success: '#50cd89', info: '#7239ea', warning: '#ffc700',
        danger: '#f1416c', dark: '#181c32', muted: '#a1a5b7', secondary: '#e1e3ea'
    };

    function formatColor(state) {
        if (!state.id) return state.text;
        const c = colorMap[state.id] || '#ccc';
        return $('<span class="select2-color-option"><span class="color-circle" style="background:' + c + '"></span>' + state.text + '</span>');
    }

    $('#color_picker').select2({
        templateResult: formatColor,
        templateSelection: formatColor,
        minimumResultsForSearch: -1,
        width: '100%',
    });

    // ============ Live preview ============
    function updatePreview() {
        const icon = $('#icon_picker').val() || 'bi-question-circle';
        const color = $('#color_picker').val() || 'primary';
        $('#preview_icon').attr('class', 'bi ' + icon + ' fs-2 text-' + color);
    }

    function updateNamePreview() {
        const lang = '{{ app()->getLocale() }}';
        const nameField = lang === 'ar' ? '[name="name_ar"]' : '[name="name_en"]';
        const val = $(nameField).val() || $('[name="name"]').val() || '—';
        $('#preview_name').text(val);
    }

    $('#icon_picker, #color_picker').on('change', updatePreview);
    $('[name="name"], [name="name_ar"], [name="name_en"]').on('input', updateNamePreview);
});
</script>
@stop
