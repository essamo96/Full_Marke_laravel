@extends('layouts.teacher')

@section('title', 'Finance — Students | FULL MARK ACADEMY')
@section('page_title_en', 'Finance by Student')
@section('page_title_ar', 'المالية حسب الطالب')

@section('content')

  <h1 class="h3 fw-bold mb-4" style="color: var(--text-primary);"
      data-en="Finance by Student" data-ar="المالية حسب الطالب">المالية حسب الطالب</h1>

  <div class="finance-layout">
    @include('teacher.finance._nav')

    <div>
      @include('teacher.finance._stats', ['totals' => $summary])

      <div class="finance-card">
        <div class="finance-card__head">
          <h2 class="finance-card__title" data-en="Enrolments" data-ar="التسجيلات">التسجيلات</h2>

          <form method="GET" action="{{ route('teacher.finance.students') }}" class="d-flex gap-2 align-items-center">
            @if(request()->query('filter'))
              <input type="hidden" name="filter" value="{{ request()->query('filter') }}">
            @endif
            <input type="search" name="q" value="{{ $search }}" class="form-control form-control-sm"
                   style="max-width: 220px;" placeholder="ابحث باسم الطالب أو الهاتف">
            <button type="submit" class="btn btn-luxury btn-sm rounded-pill px-3">
              <i class="bi bi-search"></i>
            </button>
          </form>
        </div>

        {{-- Filter chips mirror the section nav so the current cut is obvious. --}}
        <div class="px-3 pt-3 d-flex gap-2 flex-wrap">
          @php($currentFilter = request()->query('filter'))
          <a href="{{ route('teacher.finance.students', array_filter(['q' => $search])) }}"
             class="finance-badge {{ $currentFilter ? '' : 'finance-badge--settled' }}"
             style="{{ $currentFilter ? 'background: var(--input-bg); color: var(--text-secondary);' : '' }}"
             data-en="All" data-ar="الكل">الكل</a>
          <a href="{{ route('teacher.finance.students', array_filter(['q' => $search, 'filter' => 'outstanding'])) }}"
             class="finance-badge {{ $currentFilter === 'outstanding' ? 'finance-badge--due' : '' }}"
             style="{{ $currentFilter === 'outstanding' ? '' : 'background: var(--input-bg); color: var(--text-secondary);' }}"
             data-en="Outstanding" data-ar="عليه متبقي">عليه متبقي</a>
          <a href="{{ route('teacher.finance.students', array_filter(['q' => $search, 'filter' => 'settled'])) }}"
             class="finance-badge {{ $currentFilter === 'settled' ? 'finance-badge--settled' : '' }}"
             style="{{ $currentFilter === 'settled' ? '' : 'background: var(--input-bg); color: var(--text-secondary);' }}"
             data-en="Settled" data-ar="مكتمل">مكتمل</a>
        </div>

        <div class="finance-table-wrap mt-3">
          <table class="finance-table">
            <thead>
              <tr>
                <th data-en="Student" data-ar="الطالب">الطالب</th>
                <th data-en="Group" data-ar="المجموعة">المجموعة</th>
                <th data-en="Fee" data-ar="الرسوم">الرسوم</th>
                <th data-en="Paid" data-ar="المدفوع">المدفوع</th>
                <th data-en="Outstanding" data-ar="المتبقي">المتبقي</th>
                <th data-en="Progress" data-ar="النسبة">النسبة</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              @forelse($rows as $row)
                <tr>
                  <td>
                    <div class="name">{{ $row->student->full_name_ar ?: $row->student->full_name_en }}</div>
                    <div class="text-muted" dir="ltr" style="font-size: 0.72rem; text-align: start;">{{ $row->student->phone ?: '—' }}</div>
                  </td>
                  <td>
                    {{ $row->group->name ?? '—' }}
                    <div class="text-muted" style="font-size: 0.72rem;">{{ $row->group->subject->name_ar ?? '' }}</div>
                  </td>
                  <td class="num">{{ number_format((float) $row->fee_snapshot, 2) }}</td>
                  <td class="num finance-amount-positive">
                    {{ number_format($row->confirmed_paid, 2) }}
                    @if($row->pending_amount > 0)
                      <div class="finance-amount-pending" style="font-size: 0.68rem; font-weight: 600;">
                        +{{ number_format($row->pending_amount, 2) }}
                        <span data-en="awaiting" data-ar="بانتظار التأكيد">بانتظار التأكيد</span>
                      </div>
                    @endif
                  </td>
                  <td class="num {{ $row->confirmed_outstanding > 0 ? 'finance-amount-negative' : '' }}">
                    {{ number_format($row->confirmed_outstanding, 2) }}
                  </td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <div class="finance-progress">
                        <div class="finance-progress__fill {{ $row->confirmed_progress >= 100 ? '' : ($row->confirmed_progress > 0 ? 'is-partial' : 'is-none') }}"
                             style="width: {{ $row->confirmed_progress }}%"></div>
                      </div>
                      <span class="num" style="font-size: 0.75rem;">{{ $row->confirmed_progress }}%</span>
                    </div>
                  </td>
                  <td>
                    <a href="{{ route('teacher.finance.registration', $row) }}" class="finance-link"
                       data-en="Details" data-ar="التفاصيل">التفاصيل</a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="finance-empty" data-en="No enrolments match this view." data-ar="لا توجد تسجيلات مطابقة.">لا توجد تسجيلات مطابقة.</td>
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
