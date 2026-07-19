@extends('admin.layout.mainLayouts.master')
@section('title')
    الموافقات المالية
@stop

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('approvals.view') }}" class="text-muted text-hover-primary">الموافقات المالية</a>
    </li>
@endsection

@section('page-content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <form method="GET" action="{{ route('approvals.view') }}" class="w-100">
                            <div class="row w-100 mb-0">
                                <div class="col-12 mb-5">
                                    <h3>طلبات الدفع المعلقة</h3>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-6 mb-3">
                                    <label class="form-label fs-7 fw-bold">@lang('app.payment_number')</label>
                                    <input type="text" name="payment_number" value="{{ request('payment_number') }}" class="form-control form-control-sm form-control-solid" placeholder="رقم العملية" />
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-6 mb-3">
                                    <label class="form-label fs-7 fw-bold">@lang('app.student')</label>
                                    <input type="text" name="student" value="{{ request('student') }}" class="form-control form-control-sm form-control-solid" placeholder="اسم الطالب" />
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-6 mb-3">
                                    <label class="form-label fs-7 fw-bold">@lang('app.payment_method')</label>
                                    <select name="method" class="form-select form-select-sm form-select-solid" data-control="select2" data-placeholder="الكل">
                                        <option value=""></option>
                                        @foreach($methods as $method)
                                            <option value="{{ $method->id }}" {{ request('method') == $method->id ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? $method->name_ar : $method->name_en }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-6 mb-3">
                                    <label class="form-label fs-7 fw-bold">@lang('app.amount')</label>
                                    <input type="number" step="0.01" name="amount" value="{{ request('amount') }}" class="form-control form-control-sm form-control-solid" placeholder="المبلغ" />
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-6 mb-3">
                                    <label class="form-label fs-7 fw-bold">@lang('app.date')</label>
                                    <input type="date" name="date" value="{{ request('date') }}" class="form-control form-control-sm form-control-solid" />
                                </div>
                                <div class="col-lg-1 col-md-12 col-sm-12 mb-3 d-flex align-items-end gap-1">
                                    <button type="submit" class="btn btn-primary btn-sm h-35px fs-8 fw-bold w-100" title="بحث">
                                        <i class="bi bi-search"></i>
                                    </button>
                                    <a href="{{ route('approvals.view') }}" class="btn btn-light-danger btn-sm h-35px fs-8 fw-bold w-100" title="مسح">
                                        <i class="bi bi-eraser"></i>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body py-4">
                        @include('admin.layout.masterLayouts.error')
                        <table class="table table-striped table-row-bordered gy-5 gs-7">
                            <thead>
                                <tr class="fw-semibold fs-6 text-gray-800 fw-bold text-start">
                                    <th>رقم العملية</th>
                                    <th>الطالب</th>
                                    <th>طريقة الدفع</th>
                                    <th>المبلغ</th>
                                    <th>تاريخ العملية</th>
                                    <th>الإيصال</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $payment)
                                    <tr>
                                        <td>{{ $payment->payment_number ?? $payment->id }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="symbol symbol-40px me-3">
                                                    <img src="{{ $payment->student->image ? asset('storage/'.$payment->student->image) : 'https://ui-avatars.com/api/?name='.urlencode($payment->student->full_name_ar).'&background=random' }}" alt="">
                                                </div>
                                                <div class="d-flex justify-content-start flex-column">
                                                    <div class="fw-bold">{{ app()->getLocale() == 'ar' ? $payment->student->full_name_ar : $payment->student->full_name_en }}</div>
                                                    <div class="text-muted small">{{ $payment->student->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $payment->paymentMethod ? (app()->getLocale() == 'ar' ? $payment->paymentMethod->name_ar : $payment->paymentMethod->name_en) : 'بنكي' }}</td>
                                        <td>{{ number_format($payment->amount, 2) }}</td>
                                        <td>{{ $payment->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            @if($payment->receipt_image)
                                                <a href="javascript:void(0)" class="btn btn-sm btn-light-info view-receipt" data-image="{{ URL::signedRoute('payments.receipt', $payment->id) }}">عرض الإيصال</a>
                                            @else
                                                <span class="text-muted">لا يوجد</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-end flex-shrink-0">
                                                <form action="{{ route('approvals.confirm') }}" method="POST" class="me-2 confirm-payment-form">
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{ Crypt::encrypt($payment->id) }}">
                                                    <button type="submit" class="btn btn-icon btn-success btn-sm" title="موافقة">
                                                        <i class="bi bi-check-circle-fill fs-4"></i>
                                                    </button>
                                                </form>

                                                <a href="javascript:void(0)" class="btn btn-icon btn-danger btn-sm reject-payment" 
                                                   data-id="{{ Crypt::encrypt($payment->id) }}" title="رفض">
                                                    <i class="bi bi-x-circle-fill fs-4"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">لا توجد طلبات معلقة حالياً</td>
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

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('approvals.reject') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">رفض الدفعة</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="rejectPaymentId">
                        <div class="form-group">
                            <label class="required form-label">سبب الرفض</label>
                            <textarea class="form-control" name="rejection_reason" rows="3" required placeholder="اكتب سبب الرفض هنا... سيتم إرساله للطالب عبر البريد"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-danger">تأكيد الرفض</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('.view-receipt').click(function() {
            var imageUrl = $(this).data('image');
            $('#receiptImage').attr('src', imageUrl);
            $('#receiptModal').modal('show');
        });

        $('.reject-payment').click(function() {
            var id = $(this).data('id');
            $('#rejectPaymentId').val(id);
            $('#rejectModal').modal('show');
        });

        $('.confirm-payment-form').on('submit', function(e) {
            e.preventDefault();
            var form = this;
            Swal.fire({
                title: 'تأكيد الدفعة',
                text: 'هل أنت متأكد من تأكيد هذه الدفعة؟ سيتم إرسال إشعار للطالب بذلك.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'نعم، موافق',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@stop
