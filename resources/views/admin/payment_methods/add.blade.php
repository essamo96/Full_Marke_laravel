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

                                    <!-- Credentials Repeater -->
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <label class="p-2 fw-bold fs-5">بيانات الاعتماد (مثال: رقم الحساب، المستفيد)</label>
                                            <div id="credentials_repeater">
                                                <div class="form-group">
                                                    <div data-repeater-list="credentials" class="d-flex flex-column gap-3">
                                                        @php
                                                            $credentials = old('credentials', $info->credentials ?? []);
                                                        @endphp
                                                        
                                                        @if(empty($credentials))
                                                            <div data-repeater-item class="form-group d-flex flex-wrap align-items-center gap-5">
                                                                <div class="w-100 w-md-250px">
                                                                    <input type="text" name="name" class="form-control" placeholder="الاسم (مثال: رقم الحساب)" />
                                                                </div>
                                                                <div class="flex-grow-1">
                                                                    <input type="text" name="value" class="form-control" placeholder="البيان (مثال: 1234567890)" />
                                                                </div>
                                                                <button type="button" data-repeater-delete class="btn btn-sm btn-light-danger mt-3 mt-md-0">
                                                                    <i class="bi bi-trash fs-5"></i>
                                                                </button>
                                                            </div>
                                                        @else
                                                            @foreach($credentials as $cred)
                                                                <div data-repeater-item class="form-group d-flex flex-wrap align-items-center gap-5">
                                                                    <div class="w-100 w-md-250px">
                                                                        <input type="text" name="name" value="{{ $cred['name'] ?? '' }}" class="form-control" placeholder="الاسم (مثال: رقم الحساب)" />
                                                                    </div>
                                                                    <div class="flex-grow-1">
                                                                        <input type="text" name="value" value="{{ $cred['value'] ?? '' }}" class="form-control" placeholder="البيان (مثال: 1234567890)" />
                                                                    </div>
                                                                    <button type="button" data-repeater-delete class="btn btn-sm btn-light-danger mt-3 mt-md-0">
                                                                        <i class="bi bi-trash fs-5"></i>
                                                                    </button>
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="form-group mt-5">
                                                    <button type="button" data-repeater-create class="btn btn-sm btn-light-primary">
                                                        <i class="bi bi-plus fs-3"></i> إضافة حقل جديد
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Credentials Repeater -->

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

@section('js')
<script src="{{ asset('assets/admin/plugins/custom/formrepeater/formrepeater.bundle.js') }}"></script>
<script>
    $(document).ready(function() {
        // Init Repeater (Fallback to manual if plugin not available)
        if($.fn.repeater) {
            $('#credentials_repeater').repeater({
                initEmpty: false,
                show: function () {
                    $(this).slideDown();
                },
                hide: function (deleteElement) {
                    if(confirm('هل أنت متأكد من حذف هذا الحقل؟')) {
                        $(this).slideUp(deleteElement);
                    }
                }
            });
        } else {
            console.error('FormRepeater plugin is missing!');
            // Simple manual repeater fallback
            var itemIndex = {{ empty($credentials) ? 1 : count($credentials) }};
            $('[data-repeater-create]').on('click', function() {
                var template = `
                <div data-repeater-item class="form-group d-flex flex-wrap align-items-center gap-5 mt-3">
                    <div class="w-100 w-md-250px">
                        <input type="text" name="credentials[${itemIndex}][name]" class="form-control" placeholder="الاسم (مثال: رقم الحساب)" />
                    </div>
                    <div class="flex-grow-1">
                        <input type="text" name="credentials[${itemIndex}][value]" class="form-control" placeholder="البيان (مثال: 1234567890)" />
                    </div>
                    <button type="button" data-repeater-delete class="btn btn-sm btn-light-danger mt-3 mt-md-0">
                        <i class="bi bi-trash fs-5"></i>
                    </button>
                </div>`;
                $('[data-repeater-list="credentials"]').append(template);
                itemIndex++;
            });
            $(document).on('click', '[data-repeater-delete]', function() {
                $(this).closest('[data-repeater-item]').remove();
            });
        }
    });
</script>
@endsection
