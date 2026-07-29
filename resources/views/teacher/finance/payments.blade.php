@extends('layouts.teacher')

@section('title', 'Finance — Payments | FULL MARK ACADEMY')
@section('page_title_en', 'Payment Log')
@section('page_title_ar', 'سجل المدفوعات')

@section('content')

  <h1 class="h3 fw-bold mb-4" style="color: var(--text-primary);"
      data-en="Confirmed Payment Log" data-ar="سجل المدفوعات المؤكدة">سجل المدفوعات المؤكدة</h1>

  <div class="finance-layout">
    @include('teacher.finance._nav')

    <div>
      <div class="finance-stats">
        <div class="finance-stat finance-stat--collected">
          <div class="finance-stat__label">
            <i class="bi bi-check2-circle"></i>
            <span data-en="Total collected" data-ar="إجمالي المحصّل">إجمالي المحصّل</span>
          </div>
          <div class="finance-stat__value finance-amount-positive">
            {{ number_format($total, 2) }} <span class="finance-stat__unit">JOD</span>
          </div>
          <div class="finance-stat__hint" data-en="Across all your groups" data-ar="عبر جميع مجموعاتك">عبر جميع مجموعاتك</div>
        </div>

        <div class="finance-stat">
          <div class="finance-stat__label">
            <i class="bi bi-receipt"></i>
            <span data-en="Transactions" data-ar="عدد العمليات">عدد العمليات</span>
          </div>
          <div class="finance-stat__value">{{ number_format($payments->total()) }}</div>
        </div>
      </div>

      <div class="finance-card">
        <div class="finance-card__head">
          <h2 class="finance-card__title" data-en="Confirmed payments" data-ar="المدفوعات المؤكدة">المدفوعات المؤكدة</h2>
        </div>

        <div class="finance-table-wrap">
          <table class="finance-table">
            <thead>
              <tr>
                <th data-en="Payment #" data-ar="رقم الدفعة">رقم الدفعة</th>
                <th data-en="Student" data-ar="الطالب">الطالب</th>
                <th data-en="Group" data-ar="المجموعة">المجموعة</th>
                <th data-en="Method" data-ar="الطريقة">الطريقة</th>
                <th data-en="Amount" data-ar="المبلغ">المبلغ</th>
                <th data-en="Confirmed on" data-ar="تاريخ التأكيد">تاريخ التأكيد</th>
              </tr>
            </thead>
            <tbody>
              @forelse($payments as $payment)
                <tr>
                  <td class="name" dir="ltr" style="text-align: start;">{{ $payment->payment_number }}</td>
                  <td>{{ $payment->student_name_ar ?: $payment->student_name_en }}</td>
                  <td>{{ $payment->group_name }}</td>
                  <td>{{ $payment->method ?: '—' }}</td>
                  <td class="num finance-amount-positive">{{ number_format((float) $payment->allocated_amount, 2) }}</td>
                  <td>{{ $payment->reviewed_at ? \Illuminate\Support\Carbon::parse($payment->reviewed_at)->format('Y-m-d') : '—' }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="finance-empty" data-en="No confirmed payments yet." data-ar="لا توجد مدفوعات مؤكدة بعد.">لا توجد مدفوعات مؤكدة بعد.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if($payments->hasPages())
          <div class="p-3 border-top" style="border-color: var(--separator-color) !important;">
            {{ $payments->links() }}
          </div>
        @endif
      </div>
    </div>
  </div>

@endsection

@push('styles')
  @include('teacher.finance._styles')
@endpush
