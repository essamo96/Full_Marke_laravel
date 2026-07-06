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
                        <ul class="nav nav-tabs nav-pills border-2 flex-column flex-md-row me-5 mb-5 mb-md-0 fs-6"
                            id="pageTab" role="tablist">
                            <li class="nav-item mb-3" role="presentation">
                                <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic"
                                    type="button" role="tab">
                                    {{ \App\Helpers\translate('basic_settings') }}
                                </button>
                            </li>
                            {{-- Language Tabs --}}
                            @foreach ($languages as $lang)
                                <li class="nav-item mb-3" role="presentation">
                                    <button class="nav-link" id="lang-{{ $lang->prefix }}-tab" data-bs-toggle="tab"
                                        data-bs-target="#lang-{{ $lang->prefix }}" type="button" role="tab">
                                        {{ $lang->name }}
                                    </button>
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
                                    {{-- الكاتيجوري --}}
                                    <div class="col-md-6 fv-row">
                                        <label class="p-2">{{ \App\Helpers\translate('category_id') }}</label>
                                        <select class="form-select form-select-solid" data-control="select2"
                                            name="category_id">
                                            <option value="">{{ \App\Helpers\translate('choose') }}</option>
                                            <?php $data = $info ? $info->category_id : old('category_id'); ?>
                                            @foreach ($categories as $item)
                                                <option value="{{ $item->id }}"
                                                    {{ $data == $item->id ? 'selected' : '' }}>
                                                    {{ $item->{'name_' . app()->getLocale()} ?? $item->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-5">
                                    {{-- Image --}}
                                    <div class="col-md-6 fv-row">
                                        <label class="fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('image') }}</label>
                                        <div class="mb-2">
                                            @if ($info && $info->image)
                                                <img src="{{ asset('storage/' . $info->image) }}" alt="Image"
                                                    class="img-thumbnail mb-2" style="max-height: 100px;">
                                            @endif
                                        </div>
                                        <input type="file" name="image" class="form-control file-Input">
                                    </div>

                                    {{-- تاريخ النشر --}}
                                    <div class="col-md-3 fv-row">
                                        <label class="fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('pub_date') }}</label>
                                        <input type="text" class="form-control form-control-solid flatpickr"
                                            name="pub_date" value="{{ $info ? $info->pub_date : old('pub_date') }}">
                                    </div>
                                    {{-- Slug --}}
                                    <div class="col-md-3 fv-row">
                                        <label class="required fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('slug') }}</label>
                                        <input type="text" class="form-control form-control-solid" name="slug"
                                            value="{{ $info ? $info->slug : old('slug') }}">
                                    </div>
                                </div>

                                <div class="row mb-5">
                                    {{-- Tags --}}
                                    <div class="col-md-6 fv-row">
                                        <label class="fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('tags') }}</label>
                                        <input type="text" class="form-control form-control-solid" id="kt_tagify_4"
                                            name="tags" value="{{ $info ? $info->tags : old('tags') }}">
                                    </div>

                                    {{-- المشاهدات --}}
                                    <div class="col-md-6 fv-row">
                                        <label class="required fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('views') }}</label>
                                        <input type="number" class="form-control form-control-solid" name="views"
                                            value="{{ $info ? $info->views : old('views') }}">
                                    </div>
                                </div>

                                <div class="row mb-5">
                                    {{-- Main --}}
                                    <div class="col-md-6 fv-row">
                                        <label class="p-2">{{ \App\Helpers\translate('main') }}</label>
                                        <?php $data = $info ? $info->main : old('main'); ?>
                                        <label class="form-check form-switch">
                                            <input class="form-check-input" name="main" type="checkbox" value="1"
                                                {{ $data == 1 ? 'checked' : '' }}>
                                        </label>
                                    </div>

                                    {{-- Publish --}}
                                    <div class="col-md-6 fv-row">
                                        <label class="p-2">{{ \App\Helpers\translate('publish') }}</label>
                                        <?php $data = $info ? $info->publish : old('publish'); ?>
                                        <label class="form-check form-switch">
                                            <input class="form-check-input" name="publish" type="checkbox" value="1"
                                                {{ $data == 1 ? 'checked' : '' }}>
                                        </label>
                                    </div>
                                </div>

                                {{-- Navigation Buttons --}}
                                <div class="text-center pt-2">
                                    <a href="{{ route($active_menu . '.view') }}"
                                        class="btn btn-light btn-sm">{{ \App\Helpers\translate('cancel') }}</a>
                                    <button type="button"
                                        class="btn btn-outline btn-outline-dashed btn-outline-primary btn-active-light-primary ms-2 btn-sm next-tab">
                                        {{ \App\Helpers\translate('next') }}
                                    </button>
                                </div>
                            </div>

                            {{-- Language Tabs --}}
                            @foreach ($languages as $lang)
                                @php
                                    $trans = $translations[$lang->prefix] ?? null;
                                @endphp
                                <div class="tab-pane fade" id="lang-{{ $lang->prefix }}" role="tabpanel">
                                    <div class="row mb-5">
                                        <div class="col-md-12 fv-row">
                                            <label class="fs-5 fw-semibold mb-2 required">{{ \App\Helpers\translate('title') }}
                                                ({{ $lang->prefix }})
                                            </label>
                                            <input type="text" class="form-control form-control-solid"
                                                name="{{ $lang->prefix }}[title]"
                                                value="{{ old($lang->prefix . '.title', $trans?->title ?? '') }}">
                                        </div>
                                    </div>

                                    <div class="row mb-5">
                                        <div class="col-md-12 fv-row">
                                            <label class="fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('sub') }}
                                                ({{ $lang->prefix }})</label>
                                            <textarea class="form-control form-control-solid" rows="3" name="{{ $lang->prefix }}[sub]">{{ old($lang->prefix . '.sub', $trans?->sub ?? '') }}</textarea>
                                        </div>
                                    </div>

                                    <div class="row mb-5">
                                        <div class="col-md-12 fv-row">
                                            <label class="fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('descs') }}
                                                ({{ $lang->prefix }})</label>
                                            <textarea class="form-control form-control-solid" rows="5" name="{{ $lang->prefix }}[descs]">{{ old($lang->prefix . '.descs', $trans?->descs ?? '') }}</textarea>
                                        </div>
                                    </div>

                                    <div class="text-center pt-2">
                                        <a href="{{ route($active_menu . '.view') }}"
                                            class="btn btn-light btn-sm">{{ \App\Helpers\translate('cancel') }}</a>
                                        <button type="button"
                                            class="btn btn-outline btn-outline-dashed btn-outline-success btn-active-light-success ms-2 btn-sm prev-tab">
                                            {{ \App\Helpers\translate('previous') }}
                                        </button>

                                        @if ($loop->last)
                                            <button type="submit"
                                                class="btn btn-primary ms-2 btn-sm">{{ \App\Helpers\translate('save') }}</button>
                                        @else
                                            <button type="button"
                                                class="btn btn-outline btn-outline-dashed btn-outline-primary btn-active-light-primary ms-2 btn-sm next-tab">
                                                {{ \App\Helpers\translate('next') }}
                                            </button>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // flatpickr init
            $('.flatpickr').flatpickr();

            // Tagify init
            new Tagify(document.querySelector("#kt_tagify_4"));

            const tabButtons = Array.from(document.querySelectorAll('#pageTab button'));

            // Next button
            document.querySelectorAll('.next-tab').forEach(btn => {
                btn.addEventListener('click', function() {
                    const activeIndex = tabButtons.findIndex(tab => tab.classList.contains(
                        'active'));
                    if (activeIndex < tabButtons.length - 1) new bootstrap.Tab(tabButtons[
                        activeIndex + 1]).show();
                });
            });

            // Previous button
            document.querySelectorAll('.prev-tab').forEach(btn => {
                btn.addEventListener('click', function() {
                    const activeIndex = tabButtons.findIndex(tab => tab.classList.contains(
                        'active'));
                    if (activeIndex > 0) new bootstrap.Tab(tabButtons[activeIndex - 1]).show();
                });
            });
        });
    </script>
@stop
