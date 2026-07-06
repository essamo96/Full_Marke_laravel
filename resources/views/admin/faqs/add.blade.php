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

                        {{-- Tabs Navigation --}}
                        <ul class="nav nav-tabs nav-pills border-2 flex-column flex-md-row me-5 mb-5 mb-md-0 fs-6"
                            id="pageTab" role="tablist">
                            <li class="nav-item mb-3" role="presentation">
                                <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic"
                                    type="button" role="tab">{{ \App\Helpers\translate('basic_settings') }}</button>
                            </li>

                            {{-- Language Tabs --}}
                            @foreach ($languages as $lang)
                                <li class="nav-item mb-3" role="presentation">
                                    <button class="nav-link" id="lang-{{ $lang->prefix }}-tab" data-bs-toggle="tab"
                                        data-bs-target="#lang-{{ $lang->prefix }}" type="button"
                                        role="tab">{{ $lang->name }}</button>
                                </li>
                            @endforeach
                        </ul>

                        {{-- Tabs Content --}}
                        <div class="tab-content mt-5" id="pageTabContent">

                            {{-- Basic Tab --}}
                            <div class="tab-pane fade show active" id="basic" role="tabpanel">
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
                                    <input type="hidden" name="company_id" value="{{ $company_id }}">
                                @endif
                                    <div class="col-md-6 fv-row fv-plugins-icon-container">
                                        <label class="required fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('sort') }}</label>
                                        <input type="number" class="form-control form-control-solid" name="sort"
                                            value="{{ $info ? $info->sort : old('sort') }}">
                                    </div>
                                </div>

                                <div class="row mb-5">
                                    <div class="col-md-6 fv-row fv-plugins-icon-container">
                                        <label class=" fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('link') }}</label>
                                        <input type="text" class="form-control form-control-solid" name="link"
                                            value="{{ $info ? $info->link : old('link') }}">
                                    </div>
                                    <div class="col-md-6 fv-row fv-plugins-icon-container">
                                        <label class=" fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('icon') }}</label>
                                        <input type="text" class="form-control form-control-solid" name="icon"
                                            value="{{ $info ? $info->icon : old('icon') }}">
                                    </div>
                                </div>

                                <div class="row mb-5">
                                    <div class="col-md-6 fv-row fv-plugins-icon-container">
                                        <label class="p-2">{{ \App\Helpers\translate('status') }}</label>
                                        <label class="form-check form-switch">
                                            <?php $data = $info ? $info->status : old('status'); ?>
                                            <input class="form-check-input" name="status" type="checkbox" value="1"
                                                {{ $data == 1 ? 'checked' : '' }}>
                                        </label>
                                    </div>
                                </div>

                                {{-- Navigation Buttons --}}
                                <div class="text-center pt-2">
                                    <a href="{{ route($active_menu . '.view') }}"
                                        class="btn btn-light btn-sm">{{ \App\Helpers\translate('cancel') }}</a>
                                    <button type="button"
                                        class="btn btn-outline btn-outline-dashed btn-outline-primary btn-active-light-primary ms-2 btn-sm next-tab">{{ \App\Helpers\translate('next') }}</button>
                                </div>
                            </div>

                            {{-- Language Tabs --}}
                            @foreach ($languages as $lang)
                                @php
                                    $trans = $translations[$lang->prefix] ?? null;
                                @endphp
                                <div class="tab-pane fade" id="lang-{{ $lang->prefix }}" role="tabpanel">
                                    <div class="row mb-5">
                                        <div class="row mb-5">
                                            <div class="col-md-6 fv-row fv-plugins-icon-container">
                                                <label class=" fs-5 fw-semibold mb-2 required">{{ \App\Helpers\translate('answer') }}
                                                    ({{ $lang->prefix }})
                                                </label>
                                                <input type="text" class="form-control form-control-solid"
                                                    name="{{ $lang->prefix }}[answer]"
                                                    value="{{ old($lang->prefix . '.answer', $trans?->answer ?? '') }}">
                                            </div>
                                            <div class="col-md-6 fv-row fv-plugins-icon-container">
                                                <label class=" fs-5 fw-semibold mb-2 required">{{ \App\Helpers\translate('question') }}
                                                    ({{ $lang->prefix }})</label>
                                                <input type="text" class="form-control form-control-solid"
                                                    name="{{ $lang->prefix }}[question]"
                                                    value="{{ old($lang->prefix . '.question', $trans?->question ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="row mb-5">
                                            <div class="col-md-12 fv-row fv-plugins-icon-container">
                                                <label class=" fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('title') }}
                                                    ({{ $lang->prefix }})</label>
                                                <input type="text" class="form-control form-control-solid"
                                                    name="{{ $lang->prefix }}[title]"
                                                    value="{{ old($lang->prefix . '.title', $trans?->title ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="row mb-5">
                                            <div class="form-floating mb-9 row">
                                                <div class="fv-row mb-10 col-12">
                                                    <label class="fw-semibold fs-6 mb-2"
                                                        for="description">{{ \App\Helpers\translate('description') }} ({{ $lang->prefix }})</label>
                                                    <textarea name="{{ $lang->prefix }}[description]" id="description" class="form-control form-control-solid">{{ old($lang->prefix . '.description', $trans?->description ?? '') }}</textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-center pt-2">
                                            <a href="{{ route($active_menu . '.view') }}"
                                                class="btn btn-light btn-sm">{{ \App\Helpers\translate('cancel') }}</a>
                                            <button type="button"
                                                class="btn btn-outline btn-outline-dashed btn-outline-success btn-active-light-success ms-2 btn-sm prev-tab">{{ \App\Helpers\translate('previous') }}</button>

                                            @if ($loop->last)
                                                <button type="submit"
                                                    class="btn btn-primary ms-2 btn-sm">{{ \App\Helpers\translate('save') }}</button>
                                            @else
                                                <button type="button"
                                                    class="btn btn-outline btn-outline-dashed btn-outline-primary btn-active-light-primary ms-2 btn-sm next-tab">{{ \App\Helpers\translate('next') }}</button>
                                            @endif
                                        </div>
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
