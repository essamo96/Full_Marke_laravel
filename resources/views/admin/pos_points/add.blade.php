@extends('admin.layout.mainLayouts.master')
@section('title')
    @lang('app.' . $active_menu)
@stop
@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route($active_menu . '.view') }}" class="text-muted text-hover-primary">@lang('app.' . $active_menu)</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">@lang('app.' . ($info && $info->id ? 'edit' : 'add'))</li>
@endsection
@section('page-content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-body py-4">
                        @include('admin.layout.masterLayouts.error')
                        <form
                            action="{{ $info && $info->id ? route($active_menu . '.edit.submit', Crypt::encrypt($info->id)) : route($active_menu . '.add.submit') }}"
                            method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="p-2 required">اسم نقطة البيع (عربي)</label>
                                            <input type="text" name="name_ar" value="{{ old('name_ar', $info->name_ar ?? '') }}"
                                                class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="p-2 required">اسم نقطة البيع (انجليزي)</label>
                                            <input type="text" name="name_en" value="{{ old('name_en', $info->name_en ?? '') }}"
                                                class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="p-2 required">العنوان التفصيلي (عربي)</label>
                                            <textarea name="address_ar" class="form-control" rows="2" required>{{ old('address_ar', $info->address_ar ?? '') }}</textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="p-2 required">العنوان التفصيلي (انجليزي)</label>
                                            <textarea name="address_en" class="form-control" rows="2" required>{{ old('address_en', $info->address_en ?? '') }}</textarea>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="p-2">مواعيد الدوام (عربي)</label>
                                            <input type="text" name="working_hours_ar" value="{{ old('working_hours_ar', $info->working_hours_ar ?? '') }}"
                                                class="form-control" placeholder="مثال: يومياً 9 صباحاً - 9 مساءً">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="p-2">مواعيد الدوام (انجليزي)</label>
                                            <input type="text" name="working_hours_en" value="{{ old('working_hours_en', $info->working_hours_en ?? '') }}"
                                                class="form-control" placeholder="e.g. Daily 9 AM - 9 PM">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="p-2 required">رقم جوال صاحب المحل</label>
                                            <input type="text" name="phone" value="{{ old('phone', $info->phone ?? '') }}"
                                                class="form-control" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="p-2">سعر الملزمة في هذه النقطة</label>
                                            <input type="number" step="0.01" min="0" name="booklet_price" value="{{ old('booklet_price', $info->booklet_price ?? '') }}"
                                                class="form-control">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="p-2">الترتيب</label>
                                            <input type="number" name="sort_order" value="{{ old('sort_order', $info->sort_order ?? 0) }}" class="form-control">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="p-2">خط العرض (Latitude)</label>
                                            <input type="text" name="latitude" value="{{ old('latitude', $info->latitude ?? '') }}"
                                                class="form-control" placeholder="مثال: 31.3469">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="p-2">خط الطول (Longitude)</label>
                                            <input type="text" name="longitude" value="{{ old('longitude', $info->longitude ?? '') }}"
                                                class="form-control" placeholder="مثال: 34.3029">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            @include('admin.components.file-picker', ['name' => 'image', 'value' => $info->image ?? old('image'), 'label' => __('app.image'), 'folder' => 'pos_points'])
                                        </div>
                                        <div class="col-md-6 d-flex flex-column justify-content-center align-items-center">
                                            <label class="p-2 fw-semibold fs-6">@lang('app.status')</label>
                                            @php $statusValue = old('is_active', $info->is_active ?? 1); @endphp
                                            <div class="form-check form-switch mt-2">
                                                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                                    {{ $statusValue == 1 ? 'checked' : '' }} style="width: 40px; height: 20px;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center pt-2">
                                <button type="submit" class="btn btn-primary">@lang('app.save')</button>
                                <a href="{{ route($active_menu . '.view') }}" class="btn btn-light me-3">@lang('app.cancel')</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
