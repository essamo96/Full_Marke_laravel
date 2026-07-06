@extends('admin.layout.mainLayouts.master')

@section('title')
    {{ $current_route->{'name_' . \App\Helpers\translate('lang')} }}
@stop

@section('page-content')
<div class="card">
    <div class="card-body py-4">
        @include('admin.layout.masterLayouts.error')

        <form action="" method="POST">
            <div class="row justify-content-center">
                <div class="col-9">
                    @php
                        $options = old('options', $info ? json_decode($info->options, true) : []);
                    @endphp

                    {{-- الشركة --}}
                    <div class="row mb-5">
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
                            <input type="hidden" name="company_id" id="company_id" value="{{ $company_id }}">
                        @endif

                        <div class="col-md-6 fv-row">
                            <label class="fs-5 fw-semibold mb-2 required">{{ \App\Helpers\translate('site_email') }}</label>
                            <input type="text" class="form-control form-control-solid" name="site_email" value="{{ old('site_email', $info->site_email ?? '') }}">
                        </div>
                    </div>

                    {{-- الهاتف والعنوان --}}
                    <div class="row mb-5">
                        <div class="col-md-6 fv-row">
                            <label class="fs-5 fw-semibold mb-2 required">{{ \App\Helpers\translate('site_phone') }}</label>
                            <input type="text" class="form-control form-control-solid" name="site_phone" value="{{ old('site_phone', $info->site_phone ?? '') }}">
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="fs-5 fw-semibold mb-2 required">{{ \App\Helpers\translate('site_address') }}</label>
                            <input type="text" class="form-control form-control-solid" name="site_address" value="{{ old('site_address', $info->site_address ?? '') }}">
                        </div>
                    </div>

                    {{-- الرسائل الترحيبية --}}
                    <div class="row mb-5">
                        @foreach (['ar', 'en', 'hi'] as $lang)
                            <div class="col-12 fv-row mb-3">
                                <label class="fs-6 fw-semibold mb-2 required">{{ \App\Helpers\translate('welcome_message') }} ({{ strtoupper($lang) }})</label>
                                @include('admin.includes.tinymce-editor', [
                                    'name' => "welcome_message[{$lang}]",
                                    'value' => old("welcome_message.$lang", $options['welcome_message'][$lang] ?? ''),
                                    'height' => 400
                                ])
                            </div>
                        @endforeach
                    </div>

                    {{-- رسالة إغلاق الموقع --}}
                    <div class="row mb-5">
                        @foreach (['ar', 'en', 'hi'] as $lang)
                            <div class="col-12 fv-row mb-3">
                                <label class="fs-6 fw-semibold mb-2 required">{{ \App\Helpers\translate('close_message') }} ({{ strtoupper($lang) }})</label>
                                @include('admin.includes.tinymce-editor', [
                                    'name' => "close_message[{$lang}]",
                                    'value' => old("close_message.$lang", $options['close_message'][$lang] ?? ''),
                                    'height' => 400
                                ])
                            </div>
                        @endforeach
                    </div>

                    {{-- الحالة --}}
                    <div class="row mb-5">
                        <div class="col fv-row">
                            <label class="p-2">{{ \App\Helpers\translate('status') }}</label>
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
