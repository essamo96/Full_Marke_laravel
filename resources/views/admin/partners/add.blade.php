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
                    <ul class="nav nav-tabs nav-pills border-2 flex-column flex-md-row me-5 mb-5 mb-md-0 fs-6" id="partnerTab" role="tablist">
                        <li class="nav-item mb-3" role="presentation">
                            <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab">{{ \App\Helpers\translate('basic_settings') }}</button>
                        </li>
                        @foreach ($languages as $lang)
                            <li class="nav-item mb-3" role="presentation">
                                <button class="nav-link" id="lang-{{ $lang->prefix }}-tab" data-bs-toggle="tab"
                                        data-bs-target="#lang-{{ $lang->prefix }}" type="button" role="tab">{{ $lang->name }}</button>
                            </li>
                        @endforeach
                    </ul>

                    {{-- Tabs Content --}}
                    <div class="tab-content mt-5" id="partnerTabContent">

                        {{-- Basic Tab --}}
                        <div class="tab-pane fade show active" id="basic" role="tabpanel">
                            <div class="row mb-5">
                                {{-- الشركة --}}
                                

                                {{-- الترتيب --}}
                                <div class="col-md-6 fv-row">
                                    <label class="fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('sort') }}</label>
                                    <input type="number" class="form-control form-control-solid" name="p_order"
                                           value="{{ $info?->p_order ?? old('p_order', 1) }}">
                                </div>
                            </div>

                            <div class="row mb-5">
                                {{-- صورة الشريك --}}
                                <div class="col-md-6 fv-row">
                                    <label class="fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('image') }}</label>
                                    @if ($info && $info->image)
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/' . $info->image) }}" alt="Image"
                                                 class="img-thumbnail" style="max-height: 100px;">
                                        </div>
                                    @endif
                                    <input type="file" name="image" class="form-control file-Input">
                                </div>

                                {{-- الحالة --}}
                                <div class="col-md-6 fv-row">
                                    <label class="p-2">{{ \App\Helpers\translate('status') }}</label>
                                    <label class="form-check form-switch">
                                        <input class="form-check-input" name="status" type="checkbox" value="1"
                                               {{ ($info?->status ?? old('status')) == 1 ? 'checked' : '' }}>
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
                                $trans = $translations[$lang->prefix] ?? null;
                            @endphp
                            <div class="tab-pane fade" id="lang-{{ $lang->prefix }}" role="tabpanel">
                                <div class="row mb-5">
                                    <div class="col-md-6 fv-row">
                                        <label class="fs-5 fw-semibold mb-2 required">{{ \App\Helpers\translate('name') }} - ({{ $lang->prefix }})</label>
                                        <input type="text" class="form-control form-control-solid"
                                               name="{{ $lang->prefix }}[name]"
                                               value="{{ old($lang->prefix . '.name', $trans?->name ?? '') }}">
                                    </div>
                                    <div class="col-md-6 fv-row">
                                        <label class="fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('address') }} ({{ $lang->prefix }})</label>
                                        <input type="text" class="form-control form-control-solid"
                                               name="{{ $lang->prefix }}[address]"
                                               value="{{ old($lang->prefix . '.address', $trans?->address ?? '') }}">
                                    </div>
                                </div>
                                <div class="row mb-5">
                                    <div class="col fv-row">
                                        <label class="fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('details') }} ({{ $lang->prefix }})</label>
                                        <textarea class="form-control form-control-solid" name="{{ $lang->prefix }}[details]" rows="3">{{ old($lang->prefix . '.details', $trans?->details ?? '') }}</textarea>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = Array.from(document.querySelectorAll('#partnerTab button'));
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
