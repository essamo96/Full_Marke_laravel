@extends('layouts.teacher')

@section('title', 'Finance — ' . $group->name . ' | FULL MARK ACADEMY')
@section('page_title_en', 'Group Finance')
@section('page_title_ar', 'مالية المجموعة')

@section('content')

  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <div>
      <h1 class="h3 fw-bold mb-1" style="color: var(--text-primary);">{{ $group->name }}</h1>
      <div class="text-muted" style="font-size: 0.82rem;">{{ $group->subject->name_ar ?? '—' }}</div>
    </div>
    <a href="{{ route('teacher.finance.groups') }}" class="btn btn-glass btn-sm rounded-pill px-3">
      <i class="bi bi-arrow-right ms-1"></i>
      <span data-en="Back to groups" data-ar="عودة للمجموعات">عودة للمجموعات</span>
    </a>
  </div>

  <div class="finance-layout">
    @include('teacher.finance._nav')

    <div>
      @include('teacher.finance._stats', ['totals' => $summary])

      <div class="finance-card">
        <div class="finance-card__head">
          <h2 class="finance-card__title" data-en="Students in this group" data-ar="طلاب هذه المجموعة">طلاب هذه المجموعة</h2>
          <span class="text-muted" style="font-size: 0.75rem;">
            {{ $rows->count() }} <span data-en="students" data-ar="طالب">طالب</span>
          </span>
        </div>

        <div class="finance-table-wrap">
          <table class="finance-table">
            <thead>
              <tr>
                <th data-en="Student" data-ar="الطالب">الطالب</th>
                <th data-en="Phone" data-ar="الهاتف">الهاتف</th>
                <th data-en="Fee" data-ar="الرسوم">الرسوم</th>
                <th data-en="Paid" data-ar="المدفوع">المدفوع</th>
                <th data-en="Outstanding" data-ar="المتبقي">المتبقي</th>
                <th data-en="Status" data-ar="الحالة">الحالة</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              @forelse($rows as $row)
                <tr>
                  <td class="name">{{ $row->student->full_name_ar ?: $row->student->full_name_en }}</td>
                  <td dir="ltr" style="text-align: start;">{{ $row->student->phone ?: '—' }}</td>
                  <td class="num">{{ number_format((float) $row->fee_snapshot, 2) }}</td>
                  <td class="num finance-amount-positive">
                    {{ number_format($row->confirmed_paid, 2) }}
                    @if($row->pending_amount > 0)
                      <div class="finance-amount-pending" style="font-size: 0.68rem; font-weight: 600;">
                        +{{ number_format($row->pending_amount, 2) }}
                        <span data-en="awaiting review" data-ar="بانتظار التأكيد">بانتظار التأكيد</span>
                      </div>
                    @endif
                  </td>
                  <td class="num {{ $row->confirmed_outstanding > 0 ? 'finance-amount-negative' : '' }}">
                    {{ number_format($row->confirmed_outstanding, 2) }}
                  </td>
                  <td>
                    @if($row->confirmed_outstanding <= 0)
                      <span class="finance-badge finance-badge--settled">
                        <i class="bi bi-check-lg"></i>
                        <span data-en="Settled" data-ar="مكتمل">مكتمل</span>
                      </span>
                    @elseif($row->confirmed_paid > 0)
                      <span class="finance-badge finance-badge--pending">
                        <span data-en="Partial" data-ar="جزئي">جزئي</span>
                      </span>
                    @else
                      <span class="finance-badge finance-badge--due">
                        <span data-en="Unpaid" data-ar="غير مدفوع">غير مدفوع</span>
                      </span>
                    @endif
                  </td>
                  <td>
                    <a href="{{ route('teacher.finance.registration', $row) }}" class="finance-link"
                       data-en="Details" data-ar="التفاصيل">التفاصيل</a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="finance-empty" data-en="No students enrolled in this group yet." data-ar="لا يوجد طلاب مسجلون في هذه المجموعة بعد.">لا يوجد طلاب مسجلون في هذه المجموعة بعد.</td>
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
