@extends('layouts.teacher')

@section('title', 'Finance | FULL MARK ACADEMY')
@section('page_title_en', 'Finance')
@section('page_title_ar', 'القسم المالي')

@section('content')

  <h1 class="h3 fw-bold mb-4" style="color: var(--text-primary);"
      data-en="Finance Overview" data-ar="نظرة عامة مالية">نظرة عامة مالية</h1>

  <div class="finance-layout">
    @include('teacher.finance._nav')

    <div>
      @include('teacher.finance._stats', ['totals' => $totals])

      {{-- Per-group breakdown --}}
      <div class="finance-card mb-4">
        <div class="finance-card__head">
          <h2 class="finance-card__title" data-en="Collections by group" data-ar="التحصيل حسب المجموعة">التحصيل حسب المجموعة</h2>
          <a href="{{ route('teacher.finance.groups') }}" class="finance-link" data-en="View all" data-ar="عرض الكل">عرض الكل</a>
        </div>

        <div class="finance-table-wrap">
          <table class="finance-table">
            <thead>
              <tr>
                <th data-en="Group" data-ar="المجموعة">المجموعة</th>
                <th data-en="Students" data-ar="الطلاب">الطلاب</th>
                <th data-en="Expected" data-ar="المستحق">المستحق</th>
                <th data-en="Collected" data-ar="المحصّل">المحصّل</th>
                <th data-en="Outstanding" data-ar="المتبقي">المتبقي</th>
                <th data-en="Rate" data-ar="النسبة">النسبة</th>
              </tr>
            </thead>
            <tbody>
              @forelse($groupRows as $row)
                <tr>
                  <td>
                    <a href="{{ route('teacher.finance.group', $row->group) }}" class="name finance-link">{{ $row->group->name }}</a>
                    <div class="text-muted" style="font-size: 0.72rem;">{{ $row->group->subject->name_ar ?? '—' }}</div>
                  </td>
                  <td class="num">{{ $row->students }}</td>
                  <td class="num">{{ number_format($row->expected, 2) }}</td>
                  <td class="num finance-amount-positive">{{ number_format($row->collected, 2) }}</td>
                  <td class="num {{ $row->outstanding > 0 ? 'finance-amount-negative' : '' }}">{{ number_format($row->outstanding, 2) }}</td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <div class="finance-progress">
                        <div class="finance-progress__fill {{ $row->collection_rate >= 100 ? '' : ($row->collection_rate > 0 ? 'is-partial' : 'is-none') }}"
                             style="width: {{ $row->collection_rate }}%"></div>
                      </div>
                      <span class="num" style="font-size: 0.75rem;">{{ $row->collection_rate }}%</span>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="finance-empty" data-en="No groups assigned to you yet." data-ar="لا توجد مجموعات مسندة إليك بعد.">لا توجد مجموعات مسندة إليك بعد.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      {{-- Latest confirmed payments --}}
      <div class="finance-card">
        <div class="finance-card__head">
          <h2 class="finance-card__title" data-en="Latest confirmed payments" data-ar="أحدث المدفوعات المؤكدة">أحدث المدفوعات المؤكدة</h2>
          <a href="{{ route('teacher.finance.payments') }}" class="finance-link" data-en="Full log" data-ar="السجل الكامل">السجل الكامل</a>
        </div>

        <div class="finance-table-wrap">
          <table class="finance-table">
            <thead>
              <tr>
                <th data-en="Student" data-ar="الطالب">الطالب</th>
                <th data-en="Group" data-ar="المجموعة">المجموعة</th>
                <th data-en="Amount" data-ar="المبلغ">المبلغ</th>
                <th data-en="Confirmed on" data-ar="تاريخ التأكيد">تاريخ التأكيد</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentPayments as $payment)
                <tr>
                  <td class="name">{{ $payment->student_name_ar ?: $payment->student_name_en }}</td>
                  <td>{{ $payment->group_name }}</td>
                  <td class="num finance-amount-positive">{{ number_format((float) $payment->allocated_amount, 2) }}</td>
                  <td>{{ $payment->confirmed_at ? \Illuminate\Support\Carbon::parse($payment->confirmed_at)->format('Y-m-d') : '—' }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="finance-empty" data-en="No confirmed payments yet." data-ar="لا توجد مدفوعات مؤكدة بعد.">لا توجد مدفوعات مؤكدة بعد.</td>
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
