@extends('admin.layout.mainLayouts.master')
@section('title')
    تاريخ الدفعات
@stop

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('payments.view') }}" class="text-muted text-hover-primary">تاريخ الدفعات</a>
    </li>
@endsection

@section('page-content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title w-100 mb-0 row">
                            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                <form action="{{ route('payments.view') }}" method="GET" class="d-flex w-100">
                                    <select name="status" class="form-select form-select-sm form-select-solid me-2 w-150px">
                                        <option value="">كل الحالات</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>معلق</option>
                                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>مؤكد</option>
                                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                                    </select>
                                    <select name="program_id" class="form-select form-select-sm form-select-solid me-2 w-200px">
                                        <option value="">كل البرامج</option>
                                        @foreach($programs as $program)
                                            <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? $program->name_ar : $program->name_en }}</option>
                                        @endforeach
                                    </select>
                                    <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm form-control-solid me-2 w-150px" placeholder="من تاريخ">
                                    <input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm form-control-solid me-2 w-150px" placeholder="إلى تاريخ">
                                    <button type="submit" class="btn btn-sm btn-primary">تصفية</button>
                                    <a href="{{ route('payments.view') }}" class="btn btn-sm btn-light ms-2">إلغاء</a>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card-body py-4">
                        @include('admin.layout.masterLayouts.error')
                        <table class="table table-striped table-row-bordered gy-5 gs-7">
                            <thead>
                                <tr class="fw-semibold fs-6 text-gray-800 fw-bold text-start">
                                    <th>رقم الدفعة</th>
                                    <th>الطالب</th>
                                    <th>المبلغ</th>
                                    <th>الحالة</th>
                                    <th>التاريخ</th>
                                    <th>الإيصال / الفاتورة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $payment)
                                    <tr>
                                        <td>{{ $payment->payment_number ?? $payment->id }}</td>
                                        <td>
                                            <div class="fw-bold">{{ app()->getLocale() == 'ar' ? $payment->student->full_name_ar : $payment->student->full_name_en }}</div>
                                            <div class="text-muted small">{{ $payment->student->email }}</div>
                                        </td>
                                        <td>{{ number_format($payment->amount, 2) }}</td>
                                        <td>
                                            @if($payment->status === 'confirmed')
                                                <span class="badge badge-light-success">مؤكد</span>
                                            @elseif($payment->status === 'rejected')
                                                <span class="badge badge-light-danger" title="{{ $payment->rejection_reason }}">مرفوض</span>
                                            @else
                                                <span class="badge badge-light-warning">معلق</span>
                                            @endif
                                        </td>
                                        <td>{{ $payment->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            @if($payment->receipt_image)
                                                <a href="javascript:void(0)" class="btn btn-sm btn-light-info view-receipt" data-image="{{ Storage::url($payment->receipt_image) }}">الإيصال</a>
                                            @endif
                                            @if($payment->status === 'confirmed')
                                                <a href="#" class="btn btn-sm btn-light-primary ms-1"><i class="bi bi-file-pdf"></i> الفاتورة</a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">لا توجد دفعات</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="mt-4">
                            {{ $payments->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Modal -->
    <div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">صورة الإيصال</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="" id="receiptImage" class="img-fluid" alt="إيصال" style="max-height: 500px; object-fit: contain;">
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
<script>
    $(document).ready(function() {
        $('.view-receipt').click(function() {
            var imageUrl = $(this).data('image');
            $('#receiptImage').attr('src', imageUrl);
            $('#receiptModal').modal('show');
        });
    });
</script>
@stop
