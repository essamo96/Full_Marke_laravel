@extends('layouts.teacher')

@section('title', 'Finance — Enrolment | FULL MARK ACADEMY')
@section('page_title_en', 'Enrolment Finance')
@section('page_title_ar', 'تفاصيل التسجيل المالية')

@section('content')

  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <div>
      <h1 class="h3 fw-bold mb-1" style="color: var(--text-primary);">
        {{ $row->student->full_name_ar ?: $row->student->full_name_en }}
      </h1>
      <div class="text-muted" style="font-size: 0.82rem;">
        {{ $row->group->name ?? '—' }} — {{ $row->group->subject->name_ar ?? $row->subject->name_ar ?? '' }}
      </div>
    </div>
    <a href="{{ route('teacher.finance.group', $row->group) }}" class="btn btn-glass btn-sm rounded-pill px-3">
      <i class="bi bi-arrow-right ms-1"></i>
      <span data-en="Back to group" data-ar="عودة للمجموعة">عودة للمجموعة</span>
    </a>
  </div>

  <div class="finance-layout">
    @include('teacher.finance._nav')

    <div>
      {{-- Enrolment-level figures --}}
      <div class="finance-stats">
        <div class="finance-stat finance-stat--expected">
          <div class="finance-stat__label">
            <i class="bi bi-cash-stack"></i>
            <span data-en="Fee" data-ar="الرسوم">الرسوم</span>
          </div>
          <div class="finance-stat__value">
            {{ number_format((float) $row->fee_snapshot, 2) }} <span class="finance-stat__unit">JOD</span>
          </div>
        </div>

        <div class="finance-stat finance-stat--collected">
          <div class="finance-stat__label">
            <i class="bi bi-check2-circle"></i>
            <span data-en="Confirmed paid" data-ar="المدفوع المؤكّد">المدفوع المؤكّد</span>
          </div>
          <div class="finance-stat__value finance-amount-positive">
            {{ number_format($row->confirmed_paid, 2) }} <span class="finance-stat__unit">JOD</span>
          </div>
          <div class="finance-stat__hint" data-en="Confirmed by administration" data-ar="مؤكّد من قبل الإدارة">مؤكّد من قبل الإدارة</div>
        </div>

        <div class="finance-stat finance-stat--outstanding">
          <div class="finance-stat__label">
            <i class="bi bi-exclamation-circle"></i>
            <span data-en="Outstanding" data-ar="المتبقي">المتبقي</span>
          </div>
          <div class="finance-stat__value {{ $row->confirmed_outstanding > 0 ? 'finance-amount-negative' : '' }}">
            {{ number_format($row->confirmed_outstanding, 2) }} <span class="finance-stat__unit">JOD</span>
          </div>
        </div>

        @if($row->pending_amount > 0)
          <div class="finance-stat finance-stat--pending">
            <div class="finance-stat__label">
              <i class="bi bi-hourglass-split"></i>
              <span data-en="Awaiting review" data-ar="بانتظار التأكيد">بانتظار التأكيد</span>
            </div>
            <div class="finance-stat__value finance-amount-pending">
              {{ number_format($row->pending_amount, 2) }} <span class="finance-stat__unit">JOD</span>
            </div>
            <div class="finance-stat__hint" data-en="Not yet confirmed by administration" data-ar="لم تؤكّده الإدارة بعد">لم تؤكّده الإدارة بعد</div>
          </div>
        @endif
      </div>

      <div class="finance-card">
        <div class="finance-card__head">
          <h2 class="finance-card__title" data-en="Payment history" data-ar="سجل المدفوعات">سجل المدفوعات</h2>
          <span class="text-muted" dir="ltr" style="font-size: 0.75rem;">{{ $row->registration_number }}</span>
        </div>

        <div class="finance-table-wrap">
          <table class="finance-table">
            <thead>
              <tr>
                <th data-en="Payment #" data-ar="رقم الدفعة">رقم الدفعة</th>
                <th data-en="Method" data-ar="الطريقة">الطريقة</th>
                <th data-en="Amount" data-ar="المبلغ">المبلغ</th>
                <th data-en="Status" data-ar="الحالة">الحالة</th>
                <th data-en="Submitted" data-ar="تاريخ الإرسال">تاريخ الإرسال</th>
                <th data-en="Confirmed" data-ar="تاريخ التأكيد">تاريخ التأكيد</th>
              </tr>
            </thead>
            <tbody>
              @forelse($payments as $payment)
                <tr>
                  <td class="name" dir="ltr" style="text-align: start;">{{ $payment->payment_number }}</td>
                  <td>{{ $payment->method ?: '—' }}</td>
                  <td class="num {{ $payment->status === 'confirmed' ? 'finance-amount-positive' : 'finance-amount-pending' }}">
                    {{ number_format((float) $payment->allocated_amount, 2) }}
                  </td>
                  <td>
                    @if($payment->status === 'confirmed')
                      <span class="finance-badge finance-badge--settled" data-en="Confirmed" data-ar="مؤكّد">مؤكّد</span>
                    @elseif($payment->status === 'rejected')
                      <span class="finance-badge finance-badge--due" data-en="Rejected" data-ar="مرفوض">مرفوض</span>
                    @else
                      <span class="finance-badge finance-badge--pending" data-en="Pending" data-ar="قيد المراجعة">قيد المراجعة</span>
                    @endif
                  </td>
                  <td>{{ $payment->created_at ? \Illuminate\Support\Carbon::parse($payment->created_at)->format('Y-m-d') : '—' }}</td>
                  <td>{{ $payment->reviewed_at ? \Illuminate\Support\Carbon::parse($payment->reviewed_at)->format('Y-m-d') : '—' }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="finance-empty" data-en="No payments recorded for this enrolment." data-ar="لا توجد مدفوعات مسجلة لهذا التسجيل.">لا توجد مدفوعات مسجلة لهذا التسجيل.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

@endsection

@push('styles')
  @include('teacher.finance._styles')
@endpush
