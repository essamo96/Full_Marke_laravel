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
                                    {{-- Company --}}
                                

                                    {{-- Order --}}
                                    <div class="col-md-6 fv-row fv-plugins-icon-container">
                                        <label class="required fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('order') }}</label>
                                        <input type="number" class="form-control form-control-solid" name="p_order"
                                            value="{{ $info ? $info->p_order : old('p_order') }}">
                                    </div>
                                </div>

                                <div class="row mb-5">
                                    {{-- Image --}}
                                    <div class="col-md-6 fv-row fv-plugins-icon-container">
                                        <label class="required fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('image') }}</label>
                                        <input type="file" class="form-control form-control-solid" name="image">
                                        @if ($info && $info->image)
                                            <img src="{{ Storage::url($info->image) }}" alt="image" class="mt-3" height="80">
                                        @endif
                                    </div>

                                    {{-- Status --}}
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
                                        {{-- Name --}}
                                        <div class="col-md-6 fv-row fv-plugins-icon-container">
                                            <label class="fs-5 fw-semibold mb-2 required">{{ \App\Helpers\translate('name') }}
                                                ({{ $lang->prefix }})</label>
                                            <input type="text" class="form-control form-control-solid"
                                                name="{{ $lang->prefix }}[name]"
                                                value="{{ old($lang->prefix . '.name', $trans?->name ?? '') }}">
                                        </div>

                                        {{-- Title --}}
                                        <div class="col-md-6 fv-row fv-plugins-icon-container">
                                            <label class="fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('title') }}
                                                ({{ $lang->prefix }})</label>
                                            <input type="text" class="form-control form-control-solid"
                                                name="{{ $lang->prefix }}[title]"
                                                value="{{ old($lang->prefix . '.title', $trans?->title ?? '') }}">
                                        </div>
                                    </div>

                                    {{-- Description --}}
                                    <div class="row mb-5">
                                        <div class="form-floating mb-9 row">
                                            <div class="fv-row mb-10 col-12">
                                                <label class="fw-semibold fs-6 mb-2"
                                                    for="description-{{ $lang->prefix }}">{{ \App\Helpers\translate('description') }}
                                                    ({{ $lang->prefix }})</label>
                                                <textarea name="{{ $lang->prefix }}[descs]" id="description-{{ $lang->prefix }}" class="form-control form-control-solid">{{ old($lang->prefix . '.descs', $trans?->descs ?? '') }}</textarea>
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
            const tabButtons = Array.from(document.querySelectorAll('#pageTab button'));

            // Next button
            document.querySelectorAll('.next-tab').forEach(btn => {
                btn.addEventListener('click', function() {
                    const activeIndex = tabButtons.findIndex(tab => tab.classList.contains('active'));
                    if (activeIndex < tabButtons.length - 1)
                        new bootstrap.Tab(tabButtons[activeIndex + 1]).show();
                });
            });

            // Previous button
            document.querySelectorAll('.prev-tab').forEach(btn => {
                btn.addEventListener('click', function() {
                    const activeIndex = tabButtons.findIndex(tab => tab.classList.contains('active'));
                    if (activeIndex > 0)
                        new bootstrap.Tab(tabButtons[activeIndex - 1]).show();
                });
            });
        });
    </script>
@stop
