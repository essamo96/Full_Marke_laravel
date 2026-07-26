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
                                method="post">
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
                                                    <div class="col-md-6 fv-row">
                                                        @include('admin.components.file-picker', ['name' => 'image', 'value' => (isset($info) ? $info->image : null) ?? old('image'), 'label' => \App\Helpers\translate('image'), 'folder' => 'news'])
                                                    </div>

                                                    <div class="col-md-6 fv-row">
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
                                                        <label class="required form-label">{{ \App\Helpers\translate('title') }} ({{ strtoupper($locale) }})</label>
                                                        <input type="text" name="title_{{ $locale }}" class="form-control form-control-solid mb-2"
                                                            placeholder="{{ \App\Helpers\translate('title') }}"
                                                            value="{{ old('title_' . $locale, isset($info) ? optional($info->translations->where('locale', $locale)->first())->title : '') }}" />
                                                    </div>
                                                    <div class="mb-10 fv-row">
                                                        <label class="required form-label">{{ \App\Helpers\translate('description') }} ({{ strtoupper($locale) }})</label>
                                                        <textarea name="description_{{ $locale }}" class="form-control form-control-solid mb-2" rows="5"
                                                            placeholder="{{ \App\Helpers\translate('description') }}">{{ old('description_' . $locale, isset($info) ? optional($info->translations->where('locale', $locale)->first())->description : '') }}</textarea>
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
