<style>
@media print {
    body * {
        visibility: hidden;
    }
    #invoice-print-area, #invoice-print-area * {
        visibility: visible;
    }
    #invoice-print-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        padding: 0;
    }
    .modal-content, .modal-body {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
    }
    .d-print-none {
        display: none !important;
    }
}
</style>

<div id="invoice-print-area" class="card-body py-10" style="direction: rtl;">
    <!-- begin::Wrapper-->
    <div class="mw-lg-950px mx-auto w-100">
        
        <!-- begin::Header-->
        <div class="d-flex justify-content-between flex-column flex-sm-row mb-10">
            <!--begin::Logo-->
            <div>
                <a href="#">
                    <img alt="Logo" src="{{ asset('site/images/logo_v2_blue.png') }}" style="max-height: 80px;" class="theme-light-show" />
                    <img alt="Logo" src="{{ asset('site/images/full_mark_dark.png') }}" style="max-height: 80px;" class="theme-dark-show" />
                </a>
            </div>
            <!--end::Logo-->
            
            <!--begin::Text-->
            <div class="text-sm-end text-center mt-5 mt-sm-0">
                <h4 class="fw-bolder text-gray-800 fs-2qx mb-2">@lang('app.payment_invoice')</h4>
                <div class="text-gray-600 fs-6 fw-semibold">
                    <span class="me-2">@lang('app.invoice_number')</span> <span class="fw-bolder text-gray-800">#{{ $payment->payment_number ?? $payment->id }}</span>
                    <br>
                    <span class="me-2">@lang('app.payment_date')</span> <span class="fw-bolder text-gray-800">{{ $payment->created_at->format('Y-m-d') }}</span>
                </div>
            </div>
            <!--end::Text-->
        </div>
        <!--end::Header-->

        <!--begin::Student Info (Horizontal)-->
        <div class="d-flex justify-content-between bg-light-primary p-6 rounded mb-10 border border-primary border-dashed">
            <div class="d-flex flex-column">
                <span class="text-gray-600 fs-7 fw-semibold mb-1">اسم الطالب:</span>
                <span class="text-gray-800 fs-5 fw-bold">{{ app()->getLocale() == 'ar' ? $payment->student->full_name_ar : $payment->student->full_name_en }}</span>
            </div>
            <div class="d-flex flex-column">
                <span class="text-gray-600 fs-7 fw-semibold mb-1">رقم الجوال:</span>
                <span class="text-gray-800 fs-5 fw-bold" dir="ltr">{{ $payment->student->phone ?? '---' }}</span>
            </div>
            <div class="d-flex flex-column">
                <span class="text-gray-600 fs-7 fw-semibold mb-1">البريد الإلكتروني:</span>
                <span class="text-gray-800 fs-5 fw-bold">{{ $payment->student->email ?? '---' }}</span>
            </div>
        </div>
        <!--end::Student Info-->

        <!--begin::Body-->
        <div class="border-bottom pb-8 mb-8">
            <!--begin::Table-->
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="border-bottom border-gray-300 fs-6 fw-bold text-muted text-uppercase">
                            <th class="min-w-175px text-start pb-4">@lang('app.description_program')</th>
                            <th class="min-w-100px text-center pb-4">قيمة الدورة</th>
                            <th class="min-w-100px text-center pb-4">الخصم</th>
                            <th class="min-w-125px text-end pb-4">المدفوع من هذه الدفعة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payment->items as $item)
                        <tr class="fw-bold text-gray-700 fs-5 text-end">
                            <td class="d-flex align-items-center pt-8 text-start">
                                <i class="fa fa-genderless text-primary fs-1 ms-2"></i>
                                {{ app()->getLocale() == 'ar' ? ($item->registration->subject->name_ar ?? __('app.registration')) : ($item->registration->subject->name_en ?? __('app.registration')) }}
                                <span class="text-muted fs-7 me-2">({{ $item->registration->subject->program->name_ar ?? '' }})</span>
                            </td>
                            <td class="pt-8 text-center">{{ number_format($item->registration->fee_snapshot ?? 0, 2) }}</td>
                            <td class="pt-8 text-center text-danger">{{ number_format(($item->registration->fee_snapshot ?? 0) - ($item->registration->net_amount ?? $item->registration->fee_snapshot ?? 0), 2) }}</td>
                            <td class="pt-8 fs-5 pe-lg-6 text-dark fw-bolder text-end">{{ number_format($item->allocated_amount, 2) }}</td>
                        </tr>
                        @empty
                        <tr class="fw-bold text-gray-700 fs-5 text-end">
                            <td class="d-flex align-items-center pt-8 text-start">
                                <i class="fa fa-genderless text-primary fs-1 ms-2"></i>
                                @lang('app.financial_payment')
                            </td>
                            <td class="pt-8 text-center">---</td>
                            <td class="pt-8 text-center">---</td>
                            <td class="pt-8 fs-5 pe-lg-6 text-dark fw-bolder text-end">{{ number_format($payment->amount, 2) }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!--end::Table-->
        </div>
        <!--end::Body-->

        <!--begin::Bottom Section-->
        <div class="d-flex justify-content-between flex-column flex-md-row pb-15">
            <!--begin::Payment Method & Signature-->
            <div class="flex-grow-1">
                <div class="text-gray-600 fs-6 fw-semibold mb-2">طريقة الدفع:</div>
                <div class="fs-5 text-gray-800 fw-bold mb-10">
                    @if($payment->receipt_image)
                        حوالة بنكية (مرفق إيصال)
                    @else
                        سداد نقدي / إلكتروني
                    @endif
                </div>

                <div class="mt-8">
                    <div class="text-gray-600 fs-6 fw-semibold mb-3">توقيع الموظف المسؤول:</div>
                    <div class="border-bottom border-gray-400 w-200px" style="display:inline-block"></div>
                    <div class="text-muted mt-2 fs-8">هذا الإيصال معتمد إلكترونياً من النظام ولا يحتاج لختم.</div>
                </div>
            </div>
            <!--end::Payment Method & Signature-->

            <!--begin::Total Amount-->
            <div class="text-end pt-5 w-300px">
                <div class="fs-4 fw-bold text-muted mb-2">@lang('app.total_amount')</div>
                <div class="fs-xl-2qx fs-1 fw-bolder text-primary">{{ number_format($payment->amount, 2) }}</div>
            </div>
            <!--end::Total Amount-->
        </div>
        <!--end::Bottom Section-->

        <!--begin::Contact Footer (Horizontal)-->
        <div class="d-flex justify-content-center align-items-center bg-secondary p-5 rounded">
            <div class="px-5 text-center border-end border-gray-400">
                <span class="text-gray-600 fs-7 fw-semibold d-block">@lang('app.academy_name')</span>
            </div>
            <div class="px-5 text-center border-end border-gray-400">
                <span class="text-gray-600 fs-7 fw-semibold d-block">الهاتف: 0500000000</span>
            </div>
            <div class="px-5 text-center border-end border-gray-400">
                <span class="text-gray-600 fs-7 fw-semibold d-block">الإيميل: info@fullmark.com</span>
            </div>
            <div class="px-5 text-center">
                <span class="text-gray-600 fs-7 fw-semibold d-block">العنوان: شارع الستين، الرياض، المملكة العربية السعودية</span>
            </div>
        </div>
        <!--end::Contact Footer-->

        <!-- begin::Footer (Print Actions)-->
        <div class="d-flex flex-stack flex-wrap mt-10 d-print-none">
            <div class="my-1 mx-auto">
                <button type="button" class="btn btn-success" onclick="window.print()">
                    <i class="bi bi-printer"></i> @lang('app.print_invoice')
                </button>
            </div>
        </div>
        <!-- end::Footer-->
    </div>
    <!-- end::Wrapper-->
</div>
