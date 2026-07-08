@extends('admin.layout.mainLayouts.master')

@section('title')
    {{ isset($info) ? \App\Helpers\translate('edit') : \App\Helpers\translate('add') }}
@stop

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route($active_menu . '.view') }}" class="text-muted text-hover-primary">@lang('app.' . $active_menu)</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">{{ isset($info) ? \App\Helpers\translate('edit') : \App\Helpers\translate('add') }}</li>
@endsection

@section('page-content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl">
                    
                    <div class="card">
                        <div class="card-body py-4">
                            @include('admin.layout.masterLayouts.error')
                            
                            <form id="kt_ecommerce_add_product_form" class="form"
                                action="{{ isset($info) ? route($active_menu . '.edit', \Illuminate\Support\Facades\Crypt::encrypt($info->id)) : route($active_menu . '.add') }}"
                                method="post" enctype="multipart/form-data">
                                @csrf
                                
                                <div class="row justify-content-center">
                                    <div class="col-10">
                                        {{-- Tabs Navigation --}}
                                        <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-5 fw-bold" id="pageTab" role="tablist">
                                            <li class="nav-item me-3" role="presentation">
                                                <button class="nav-link active d-flex align-items-center text-active-primary pb-4" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab">
                                                    <i class="bi bi-gear-fill fs-2 me-2"></i> البيانات الأساسية
                                                </button>
                                            </li>

                                            @foreach (['ar', 'en'] as $locale)
                                                <li class="nav-item me-3" role="presentation">
                                                    <button class="nav-link d-flex align-items-center text-active-success pb-4" id="lang-{{ $locale }}-tab" data-bs-toggle="tab"
                                                            data-bs-target="#lang-{{ $locale }}" type="button" role="tab">
                                                        <i class="bi bi-globe fs-2 me-2"></i> {{ strtoupper($locale) }}
                                                    </button>
                                                </li>
                                            @endforeach
                                        </ul>

                                        {{-- Tabs Content --}}
                                        <div class="tab-content mt-5" id="pageTabContent">
                                            
                                            {{-- Basic Tab --}}
                                            <div class="tab-pane fade show active" id="basic" role="tabpanel">
                                                <div class="row mb-5">
                                                    <div class="col-md-4 fv-row">
                                                        <label class="form-label">{{ \App\Helpers\translate('image') }}</label>
                                                        @php
                                                            $imageSrc = '';
                                                            if (isset($info) && $info->image) {
                                                                $imageSrc = \Illuminate\Support\Str::startsWith($info->image, ['http', 'site/']) ? asset($info->image) : asset('storage/' . $info->image);
                                                            }
                                                        @endphp
                                                        <input type="file" name="image" id="imageInput" class="form-control form-control-solid mb-4" accept="image/*">
                                                        
                                                        <div class="text-center" id="imagePreviewContainer" style="{{ $imageSrc ? '' : 'display: none;' }}">
                                                            <div class="d-inline-block position-relative shadow-sm rounded border border-gray-300 p-2 bg-light">
                                                                <img src="{{ $imageSrc }}" alt="Preview" id="imagePreview" class="rounded" style="max-width: 100%; max-height: 150px; object-fit: cover;">
                                                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary shadow-sm" style="font-size: 0.75rem;">
                                                                    <i class="bi bi-eye-fill text-white ms-1"></i>معاينة
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4 fv-row">
                                                        <label class="form-label">{{ \App\Helpers\translate('display_order') }}</label>
                                                        <input type="number" name="display_order" class="form-control"
                                                            value="{{ old('display_order', isset($info) ? $info->display_order : 0) }}">
                                                    </div>

                                                    <div class="col-md-4 fv-row">
                                                        <label class="p-2 required">{{ \App\Helpers\translate('status') }}</label>
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
                                            @foreach (['ar', 'en'] as $locale)
                                                <div class="tab-pane fade" id="lang-{{ $locale }}" role="tabpanel">
                                                    <div class="mb-10 fv-row">
                                                        <label class="required form-label">{{ \App\Helpers\translate('name') }} ({{ strtoupper($locale) }})</label>
                                                        <input type="text" name="name_{{ $locale }}" class="form-control form-control-solid mb-2"
                                                            placeholder="{{ \App\Helpers\translate('name') }}"
                                                            value="{{ old('name_' . $locale, isset($info) ? optional($info->translations->where('locale', $locale)->first())->name : '') }}" />
                                                    </div>
                                                    <div class="mb-10 fv-row">
                                                        <label class="form-label">المنصب (Optional) ({{ strtoupper($locale) }})</label>
                                                        <input type="text" name="position_{{ $locale }}" class="form-control form-control-solid mb-2"
                                                            placeholder="المنصب"
                                                            value="{{ old('position_' . $locale, isset($info) ? optional($info->translations->where('locale', $locale)->first())->position : '') }}" />
                                                    </div>
                                                    <div class="mb-10 fv-row">
                                                        <label class="required form-label">الرسالة ({{ strtoupper($locale) }})</label>
                                                        <textarea name="message_{{ $locale }}" class="form-control form-control-solid mb-2" rows="5"
                                                            placeholder="الرسالة">{{ old('message_' . $locale, isset($info) ? optional($info->translations->where('locale', $locale)->first())->message : '') }}</textarea>
                                                    </div>

                                                    <div class="text-center pt-2">
                                                        @if($loop->last)
                                                            <button type="submit" class="btn btn-primary btn-sm">
                                                                <span class="indicator-label">{{ \App\Helpers\translate('save') }}</span>
                                                            </button>
                                                        @else
                                                            <button type="button" class="btn btn-outline btn-outline-dashed btn-outline-primary btn-active-light-primary ms-2 btn-sm next-tab">{{ \App\Helpers\translate('next') }}</button>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    document.getElementById('imageInput').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imagePreviewContainer').style.display = 'block';
                document.getElementById('imagePreview').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });

    // Next Tab Button functionality
    document.querySelectorAll('.next-tab').forEach(button => {
        button.addEventListener('click', function() {
            let activeTab = document.querySelector('.nav-tabs .nav-link.active');
            let nextLi = activeTab.parentElement.nextElementSibling;
            if (nextLi) {
                let nextTabButton = nextLi.querySelector('.nav-link');
                let tab = new bootstrap.Tab(nextTabButton);
                tab.show();
            }
        });
    });
</script>
@stop
