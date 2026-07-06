@extends('admin.layout.mainLayouts.master')
@section('title')
    {{ $current_route->{'name_' . \App\Helpers\translate('lang')} }}
@stop

@section('page-content')
<div class="card">
    <div class="card-body py-4">
        @include('admin.layout.masterLayouts.error')

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="row justify-content-center">
                <div class="col-9">

                    {{-- Tabs Navigation --}}
                    <ul class="nav nav-tabs nav-pills border-2 flex-column flex-md-row me-5 mb-5 mb-md-0 fs-6" id="pageTab" role="tablist">
                        <li class="nav-item mb-3" role="presentation">
                            <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab">{{ \App\Helpers\translate('basic_settings') }}</button>
                        </li>

                        {{-- Dynamic Tabs for Languages --}}
                        @foreach ($languages as $lang)
                            <li class="nav-item mb-3" role="presentation">
                                <button class="nav-link" id="lang-{{ $lang->prefix }}-tab" data-bs-toggle="tab"
                                        data-bs-target="#lang-{{ $lang->prefix }}" type="button" role="tab">{{ $lang->name }}</button>
                            </li>
                        @endforeach
                    </ul>

                    {{-- Tabs Content --}}
                    <div class="tab-content mt-5" id="pageTabContent">

                        {{-- Basic Tab --}}
                        <div class="tab-pane fade show active" id="basic" role="tabpanel">

                            <div class="row mb-5">
                                {{-- الشركة --}}
                                @if ($company_id == 0)
                                    <div class="col-md-6 fv-row fv-plugins-icon-container">
                                        <label class="p-2 required">@lang('app.company_id')</label>

                                        <select class="form-select form-select-solid" data-control="select2" name="company_id">
                                            <option value="0">{{ \App\Helpers\translate('choose') }}</option>

                                            @php
                                                $data = $info ? $info->company_id : old('company_id');
                                            @endphp

                                            @foreach ($companies as $item)
                                                <option value="{{ $item->id }}" {{ $data == $item->id ? 'selected' : '' }}>
                                                    {{ $item->translation?->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @else
                                    <input type="hidden" name="company_id" value="{{ $company_id }}">
                                @endif

                                {{-- المسمى الوظيفي --}}
                                <div class="col-md-6 fv-row">
                                    <label class="p-2">{{ \App\Helpers\translate('status') }}</label>
                                    <label class="form-check form-switch">
                                        <input class="form-check-input" name="status" type="checkbox" value="1"
                                               {{ ($info?->status ?? old('status')) == 1 ? 'checked' : '' }}>
                                    </label>
                                </div>
                            </div>

                            <div class="row mb-5">
                                {{-- صورة العضو --}}
                                <div class="col-md-6 fv-row">
                                    <label class="fs-5 fw-semibold mb-2 required">{{ \App\Helpers\translate('image') }}</label>
                                    @if ($info && $info->image)
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/' . $info->image) }}" alt="Image"
                                                 class="img-thumbnail" style="max-height: 100px;">
                                        </div>
                                    @endif
                                    <input type="file" name="image" class="form-control file-Input">
                                </div>

                                {{-- نوع العضوية --}}
                                <div class="col-md-6 fv-row">
                                    <label class="fs-5 fw-semibold mb-2 required">{{ \App\Helpers\translate('member_type') }}</label>
                                    <select class="form-select form-select-solid" data-control="select2" name="member_type" id="memberTypeSelect">
                                        @php
                                            $memberType = $info?->member_type ?? old('member_type', 'team');
                                        @endphp
                                        <option value="team" {{ $memberType == 'team' ? 'selected' : '' }}>{{ __('Team') }}</option>
                                        <option value="board" {{ $memberType == 'board' ? 'selected' : '' }}>{{ __('Board of Directors') }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-5">
                                {{-- رئيس مجلس الإدارة --}}
                                <div class="col-md-6 fv-row" id="chairmanField" style="{{ ($info?->member_type ?? old('member_type', 'team')) != 'board' ? 'display:none;' : '' }}">
                                    <label class="p-2">{{ \App\Helpers\translate('is_chairman') }}</label>
                                    <label class="form-check form-switch">
                                        <input class="form-check-input" name="is_chairman" type="checkbox" value="1"
                                               {{ ($info?->is_chairman ?? old('is_chairman')) == 1 ? 'checked' : '' }}>
                                    </label>
                                </div>

                                {{-- ترتيب العرض --}}
                                <div class="col-md-6 fv-row">
                                    <label class="fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('display_order') }}</label>
                                    <input type="number" class="form-control form-control-solid" name="display_order"
                                           value="{{ old('display_order', $info?->display_order ?? 0) }}" min="0">
                                </div>
                            </div>

                            {{-- روابط منصات التواصل --}}
                            <div class="row mb-5">
                                <label class="fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('socials') }}</label>

                                <div id="socialsRepeater">
                                    <div data-repeater-list="socials">
                                        @php
                                            $socials = old('socials', $info?->socials ? json_decode($info->socials, true) : []);
                                        @endphp

                                        @if(!empty($socials))
                                            @foreach($socials as $social)
                                                <div data-repeater-item class="row mb-3 align-items-center">
                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control form-control-solid"
                                                               name="platform"
                                                               placeholder="{{ \App\Helpers\translate('platform') }}"
                                                               value="{{ $social['platform'] ?? '' }}">
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="url" class="form-control form-control-solid"
                                                               name="link"
                                                               placeholder="{{ \App\Helpers\translate('link') }}"
                                                               value="{{ $social['link'] ?? '' }}">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <button data-repeater-delete type="button"
                                                                class="btn btn-sm btn-light-danger">
                                                            <i class="la la-trash"></i> {{ \App\Helpers\translate('delete') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div data-repeater-item class="row mb-3 align-items-center">
                                                <div class="col-md-5">
                                                    <input type="text" class="form-control form-control-solid"
                                                           name="platform"
                                                           placeholder="{{ \App\Helpers\translate('platform') }}">
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="url" class="form-control form-control-solid"
                                                           name="link"
                                                           placeholder="{{ \App\Helpers\translate('link') }}">
                                                </div>
                                                <div class="col-md-2">
                                                    <button data-repeater-delete type="button"
                                                            class="btn btn-sm btn-light-danger">
                                                        <i class="la la-trash"></i> {{ \App\Helpers\translate('delete') }}
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="mt-3">
                                        <button data-repeater-create type="button"
                                                class="btn btn-sm btn-light-primary">
                                            <i class="la la-plus"></i> {{ \App\Helpers\translate('add_social') }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center pt-2">
                                <a href="{{ route($active_menu . '.view') }}" class="btn btn-light btn-sm">{{ \App\Helpers\translate('cancel') }}</a>
                                <button type="button" class="btn btn-outline btn-outline-dashed btn-outline-primary btn-active-light-primary ms-2 btn-sm next-tab">{{ \App\Helpers\translate('next') }}</button>
                            </div>
                        </div>

                        {{-- Language Tabs --}}
                        @foreach ($languages as $lang)
                            @php
                                $trans = $translations[$lang->prefix] ?? null;
                            @endphp
                            <div class="tab-pane fade" id="lang-{{ $lang->prefix }}" role="tabpanel">
                                <div class="row mb-5">
                                    <div class="col-md-6 fv-row">
                                        <label class="fs-5 fw-semibold mb-2 required">{{ \App\Helpers\translate('name') }} ({{ $lang->prefix }})</label>
                                        <input type="text" class="form-control form-control-solid"
                                               name="{{ $lang->prefix }}[name]"
                                               value="{{ old($lang->prefix . '.name', $trans?->name ?? '') }}">
                                    </div>
                                    <div class="col-md-6 fv-row">
                                        <label class="fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('position') }} ({{ $lang->prefix }})</label>
                                        <input type="text" class="form-control form-control-solid"
                                               name="{{ $lang->prefix }}[address1]"
                                               value="{{ old($lang->prefix . '.address1', $trans?->address1 ?? '') }}">
                                    </div>
                                </div>

                                <div class="row mb-5">
                                    <div class="col-md-6 fv-row">
                                        <label class="fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('address2') }} ({{ $lang->prefix }})</label>
                                        <input type="text" class="form-control form-control-solid"
                                               name="{{ $lang->prefix }}[address2]"
                                               value="{{ old($lang->prefix . '.address2', $trans?->address2 ?? '') }}">
                                    </div>
                                    <div class="col-md-6 fv-row">
                                        <label class="fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('description') }} ({{ $lang->prefix }})</label>
                                        <textarea class="form-control form-control-solid" name="{{ $lang->prefix }}[description]" rows="3">{{ old($lang->prefix . '.description', $trans?->description ?? '') }}</textarea>
                                    </div>
                                </div>

                                <div class="row mb-5" id="boardDescField-{{ $lang->prefix }}">
                                    <div class="col-md-12 fv-row">
                                        <label class="fs-5 fw-semibold mb-2 fw-bold text-dark">{{ \App\Helpers\translate('board_description') }} ({{ $lang->prefix }})</label>
                                        @include('admin.includes.tinymce-editor', [
                                            'name' => "{$lang->prefix}[board_description]",
                                            'value' => old($lang->prefix . '.board_description', $trans?->board_description ?? ''),
                                            'placeholder' => "({$lang->prefix}) - " . \App\Helpers\translate('Message from the Chairman'),
                                            'height' => 300
                                        ])
                                        <div class="text-muted fs-7 mt-2">{{ \App\Helpers\translate('This field is used specifically for the Chairman\'s speech in the Board of Directors section.') }}</div>
                                    </div>
                                </div>

                                <div class="text-center pt-2">
                                    <a href="{{ route($active_menu . '.view') }}" class="btn btn-light btn-sm">{{ \App\Helpers\translate('cancel') }}</a>
                                    <button type="button" class="btn btn-outline btn-outline-dashed btn-outline-success btn-active-light-success ms-2 btn-sm prev-tab">{{ \App\Helpers\translate('previous') }}</button>

                                    @if ($loop->last)
                                        <button type="submit" class="btn btn-primary ms-2 btn-sm">{{ \App\Helpers\translate('save') }}</button>
                                    @else
                                        <button type="button" class="btn btn-outline btn-outline-dashed btn-outline-primary btn-active-light-primary ms-2 btn-sm next-tab">{{ \App\Helpers\translate('next') }}</button>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>
            </div>
            {{ csrf_field() }}
        </form>
    </div>
</div>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.repeater/1.2.1/jquery.repeater.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = Array.from(document.querySelectorAll('#pageTab button'));

    document.querySelectorAll('.next-tab').forEach(btn => {
        btn.addEventListener('click', function() {
            const activeIndex = tabButtons.findIndex(tab => tab.classList.contains('active'));
            if (activeIndex < tabButtons.length - 1) new bootstrap.Tab(tabButtons[activeIndex + 1]).show();
        });
    });

    document.querySelectorAll('.prev-tab').forEach(btn => {
        btn.addEventListener('click', function() {
            const activeIndex = tabButtons.findIndex(tab => tab.classList.contains('active'));
            if (activeIndex > 0) new bootstrap.Tab(tabButtons[activeIndex - 1]).show();
        });
    });

    // Init repeater بدون تأكيد الحذف
    $('#socialsRepeater').repeater({
        initEmpty: false,
        defaultValues: {
            'platform': '',
            'link': ''
        },
        show: function () {
            $(this).slideDown();
        },
        hide: function (deleteElement) {
            $(this).slideUp(deleteElement);
        }
    });

    // Toggle chairman field based on member type
    $('#memberTypeSelect').on('change', function() {
        var value = $(this).val();
        var chairmanField = $('#chairmanField');
        
        if (value === 'board') {
            chairmanField.show();
        } else {
            chairmanField.hide();
            // Uncheck chairman when switching to team
            chairmanField.find('input[name="is_chairman"]').prop('checked', false);
        }
    });

    // Trigger change on load to set initial state
    $('#memberTypeSelect').trigger('change');
});
</script>
@stop
