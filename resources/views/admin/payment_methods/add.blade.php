@extends('admin.layout.mainLayouts.master')
@section('title')
    {{ $info && $info->id ? 'تعديل' : 'إضافة' }} طريقة دفع
@stop

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('payment_methods.view') }}" class="text-muted text-hover-primary">طرق الدفع</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">{{ $info && $info->id ? 'تعديل' : 'إضافة' }}</li>
@endsection

@section('page-content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-body py-4">
                        @include('admin.layout.masterLayouts.error')

                        <form
                            action="{{ $info && $info->id ? route('payment_methods.edit.submit', Crypt::encrypt($info->id)) : route('payment_methods.add.submit') }}"
                            method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="p-2 required">الاسم (عربي)</label>
                                            <input type="text" name="name_ar"
                                                value="{{ old('name_ar', $info->name_ar ?? '') }}"
                                                class="form-control @error('name_ar') is-invalid @enderror"
                                                placeholder="اسم طريقة الدفع بالعربية" required>
                                            @error('name_ar')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="p-2">الاسم (إنجليزي)</label>
                                            <input type="text" name="name_en"
                                                value="{{ old('name_en', $info->name_en ?? '') }}"
                                                class="form-control @error('name_en') is-invalid @enderror"
                                                placeholder="Payment method name in English">
                                            @error('name_en')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <label class="p-2">التفاصيل / تعليمات الدفع</label>
                                            <textarea name="details" rows="4"
                                                class="form-control @error('details') is-invalid @enderror"
                                                placeholder="أدخل تعليمات الدفع أو أي تفاصيل إضافية...">{{ old('details', $info->details ?? '') }}</textarea>
                                            @error('details')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="p-2">الترتيب</label>
                                            <input type="number" name="sort_order" min="0"
                                                value="{{ old('sort_order', $info->sort_order ?? 0) }}"
                                                class="form-control @error('sort_order') is-invalid @enderror">
                                            @error('sort_order')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="p-2">الحالة</label>
                                            <div class="form-check form-switch form-check-custom form-check-solid mt-3">
                                                <input class="form-check-input" type="checkbox" name="is_active"
                                                    value="1" {{ old('is_active', $info->is_active ?? true) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold text-gray-700">نشط</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end mt-5">
                                        <a href="{{ route('payment_methods.view') }}" class="btn btn-light me-3">إلغاء</a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-lg me-1"></i>
                                            {{ $info && $info->id ? 'حفظ التعديلات' : 'إضافة' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
