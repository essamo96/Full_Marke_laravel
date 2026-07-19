@extends('admin.layout.mainLayouts.master')
@section('title')
    طرق الدفع
@stop

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('payment_methods.view') }}" class="text-muted text-hover-primary">طرق الدفع</a>
    </li>
@endsection

@section('toolbar-actions')
    @can('admin.payment_methods.add')
        <a href="{{ route('payment_methods.add') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg me-1"></i>
            إضافة طريقة دفع
        </a>
    @endcan
@endsection

@section('page-content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <h3>طرق الدفع المتاحة</h3>
                        </div>
                    </div>
                    <div class="card-body py-4">
                        @include('admin.layout.masterLayouts.error')

                        <div class="table-responsive">
                            <table class="table table-striped table-row-bordered gy-5 gs-7">
                                <thead>
                                <tr class="fw-semibold fs-6 text-gray-800 fw-bold text-start">
                                        <th class="ps-4 min-w-50px rounded-start">#</th>
                                        <th class="min-w-150px">الاسم (عربي)</th>
                                        <th class="min-w-150px">الاسم (إنجليزي)</th>
                                        <th class="min-w-200px">التفاصيل</th>
                                        <th class="min-w-80px">الترتيب</th>
                                        <th class="min-w-100px">الحالة</th>
                                        <th class="min-w-100px  pe-4 rounded-end">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($methods as $method)
                                        <tr>
                                            <td class="ps-4 text-muted">{{ $loop->iteration }}</td>
                                            <td>
                                                <span class="text-dark fw-bold">{{ $method->name_ar }}</span>
                                            </td>
                                            <td>
                                                <span class="text-gray-600">{{ $method->name_en }}</span>
                                            </td>
                                            <td>
                                                <span class="text-muted fs-7">{{ Str::limit(strip_tags($method->details), 80) }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-light">{{ $method->sort_order ?? 0 }}</span>
                                            </td>
                                            <td class="text-center">
                                                @can('admin.payment_methods.status')
                                                    <div class="form-check form-switch form-check-custom form-check-solid justify-content-center">
                                                        <input class="form-check-input status-toggle" type="checkbox"
                                                            {{ $method->is_active ? 'checked' : '' }}
                                                            data-id="{{ Crypt::encrypt($method->id) }}"
                                                            data-url="{{ route('payment_methods.status') }}">
                                                    </div>
                                                @else
                                                    <span class="badge {{ $method->is_active ? 'badge-light-success' : 'badge-light-danger' }}">
                                                        {{ $method->is_active ? 'نشط' : 'معطل' }}
                                                    </span>
                                                @endcan
                                            </td>
                                            <td class="text-end pe-4">
                                                @can('admin.payment_methods.edit')
                                                    <a href="{{ route('payment_methods.edit', Crypt::encrypt($method->id)) }}"
                                                        class="btn btn-icon btn-primary btn-sm me-1" title="تعديل">
                                                        <i class="bi bi-pencil-square fs-5"></i>
                                                    </a>
                                                @endcan
                                                @can('admin.payment_methods.delete')
                                                    <a href="javascript:void(0)"
                                                        class="btn btn-icon btn-danger btn-sm"
                                                        data-href="{{ Crypt::encrypt($method->id) }}"
                                                        data-name="{{ $method->name_ar }}"
                                                        data-bs-toggle="modal" data-bs-target="#confirm"
                                                        title="حذف">
                                                        <i class="bi bi-trash3-fill fs-5"></i>
                                                    </a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-6">
                                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                                لا توجد طرق دفع مضافة بعد
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $methods->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Confirm Delete Modal --}}
    <div class="modal fade" id="confirm" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered mw-450px">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تأكيد الحذف</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    هل أنت متأكد من حذف طريقة الدفع "<strong id="confirm-name"></strong>"؟
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-danger" id="confirm-delete-btn">حذف</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    // Status toggle
    $(document).on('change', '.status-toggle', function () {
        const $toggle = $(this);
        $.ajax({
            url: $toggle.data('url'),
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', id: $toggle.data('id') },
            success: function (res) {
                if (!res.success) $toggle.prop('checked', !$toggle.prop('checked'));
            },
            error: function () { $toggle.prop('checked', !$toggle.prop('checked')); }
        });
    });

    // Delete confirm modal
    let deleteId = null;
    $(document).on('click', '[data-bs-target="#confirm"]', function () {
        deleteId = $(this).data('href');
        $('#confirm-name').text($(this).data('name'));
    });

    $('#confirm-delete-btn').on('click', function () {
        if (!deleteId) return;
        $.ajax({
            url: '{{ route("payment_methods.delete") }}',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', id: deleteId },
            success: function (res) {
                if (res.success) location.reload();
                else Swal.fire('خطأ', res.message || 'تعذر الحذف', 'error');
            },
            error: function () { Swal.fire('خطأ', 'تعذر الحذف', 'error'); }
        });
    });
});
</script>
@endpush
