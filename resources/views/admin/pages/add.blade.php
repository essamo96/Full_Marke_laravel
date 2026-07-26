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

        {{-- نفس الصفحة تستخدم للإضافة والتعديل --}}
        <form action="" method="POST">
            <div class="row justify-content-center">
                <div class="col-9">

                    {{-- Tabs Navigation --}}
                    <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-5 fw-bold" id="pageTab" role="tablist">
                        <li class="nav-item me-3" role="presentation">
                            <button class="nav-link active d-flex align-items-center text-active-primary pb-4" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab">
                                <i class="bi bi-gear-fill fs-2 me-2"></i> {{ \App\Helpers\translate('basic_settings') }}
                            </button>
                        </li>

                        @foreach ($languages as $lang)
                            <li class="nav-item me-3" role="presentation">
                                <button class="nav-link d-flex align-items-center text-active-success pb-4" id="lang-{{ $lang->prefix }}-tab" data-bs-toggle="tab"
                                        data-bs-target="#lang-{{ $lang->prefix }}" type="button" role="tab">
                                    <i class="bi bi-globe fs-2 me-2"></i> {{ $lang->name }}
                                </button>
                            </li>
                        @endforeach
                    </ul>

                    {{-- Tabs Content --}}
                    <div class="tab-content mt-5" id="pageTabContent">

                        {{-- Basic Tab --}}
                        <div class="tab-pane fade show active" id="basic" role="tabpanel">
                            <div class="row mb-5">
                                

                                <div class="col-md-6 fv-row">
                                    <label class="required fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('slug') }}</label>
                                    @php $currentSlug = $info?->slug ?? old('slug'); @endphp
                                    <select class="form-select form-select-solid" data-control="select2" data-tags="true" name="slug" data-placeholder="اختر أو اكتب الرابط المختصر">
                                        <option value=""></option>
                                        <option value="about_us" {{ $currentSlug == 'about_us' ? 'selected' : '' }}>من نحن (about_us)</option>
                                        <option value="features" {{ $currentSlug == 'features' ? 'selected' : '' }}>مميزاتنا (features)</option>
                                        <option value="services" {{ $currentSlug == 'services' ? 'selected' : '' }}>خدماتنا والوصول السريع (services)</option>
                                        <option value="training_hours" {{ $currentSlug == 'training_hours' ? 'selected' : '' }}>ساعات تدريبية (training_hours)</option>
                                        @if($currentSlug && !in_array($currentSlug, ['about_us', 'features', 'services', 'training_hours']))
                                            <option value="{{ $currentSlug }}" selected>{{ $currentSlug }}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-5">
                                {{-- Image --}}
                                <div class="col-md-6 fv-row">
                                    @include('admin.components.file-picker', ['name' => 'image', 'value' => $info?->image ?? old('image'), 'label' => \App\Helpers\translate('image'), 'folder' => 'pages'])
                                </div>

                                {{-- Video --}}
                                <div class="col-md-6 fv-row">
                                    @include('admin.components.file-picker', ['name' => 'video', 'value' => $info?->video ?? old('video'), 'label' => \App\Helpers\translate('video'), 'folder' => 'pages/videos'])
                                </div>
                            </div>

                            <div class="row mb-5">
                                {{-- Tags --}}
                                <div class="col-md-6 fv-row">
                                    <label class="fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('tags') }}</label>
                                    <input class="form-control form-control-solid" name="tags" id="kt_tagify_4"
                                           value="{{ $info?->tags ?? old('tags') }}" placeholder="Enter tags" />
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="p-2">{{ \App\Helpers\translate('status') }}</label>
                                    <label class="form-check form-switch mt-2">
                                        <input class="form-check-input" name="status" type="checkbox" value="1"
                                               {{ ($info?->status ?? old('status', 1)) == 1 ? 'checked' : '' }}>
                                    </label>
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
                                // اجلب الترجمة إذا كانت موجودة، وإلا خليها فارغة
                                $trans = $translations[$lang->prefix] ?? null;
                            @endphp
                            <div class="tab-pane fade" id="lang-{{ $lang->prefix }}" role="tabpanel">
                                <div class="row mb-5">
                                    <div class="col-md-6 fv-row">
                                        <label class="fs-5 fw-semibold mb-2 required">{{ \App\Helpers\translate('title') }} - ({{ $lang->prefix }})</label>
                                        <input type="text" class="form-control form-control-solid"
                                               name="{{ $lang->prefix }}[title]"
                                               value="{{ old($lang->prefix . '.title', $trans?->title ?? '') }}">
                                    </div>
                                    <div class="col-md-6 fv-row">
                                        <label class="fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('subtitle') ?? 'الوصف الخاص بالعنوان' }} - ({{ $lang->prefix }})</label>
                                        <input type="text" class="form-control form-control-solid"
                                               name="{{ $lang->prefix }}[subtitle]"
                                               value="{{ old($lang->prefix . '.subtitle', $trans?->subtitle ?? '') }}">
                                    </div>
                                </div>

                                <div class="form-floating mb-5">
                                    <label class="fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('details') }} - ({{ $lang->prefix }})</label>
                                    @include('admin.includes.tinymce-editor', [
                                        'name' => "{$lang->prefix}[details]",
                                        'value' => old($lang->prefix . '.details', $trans?->details ?? ''),
                                        'placeholder' => "({$lang->prefix}) - " . \App\Helpers\translate('details'),
                                        'height' => 300
                                    ])
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tagify init
    new Tagify(document.querySelector("#kt_tagify_4"));

    // Tab navigation
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
});
</script>

@stop
