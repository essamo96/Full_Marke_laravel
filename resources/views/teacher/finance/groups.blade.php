@extends('layouts.teacher')

@section('title', 'Finance — Groups | FULL MARK ACADEMY')
@section('page_title_en', 'Finance by Group')
@section('page_title_ar', 'المالية حسب المجموعة')

@section('content')

  <h1 class="h3 fw-bold mb-4" style="color: var(--text-primary);"
      data-en="Finance by Group" data-ar="المالية حسب المجموعة">المالية حسب المجموعة</h1>

  <div class="finance-layout">
    @include('teacher.finance._nav')

    <div>
      @include('teacher.finance._stats', ['totals' => $totals])

      <div class="finance-card">
        <div class="finance-card__head">
          <h2 class="finance-card__title" data-en="All groups" data-ar="جميع المجموعات">جميع المجموعات</h2>
        </div>

        <div class="finance-table-wrap">
          <table class="finance-table">
            <thead>
              <tr>
                <th data-en="Group" data-ar="المجموعة">المجموعة</th>
                <th data-en="Subject" data-ar="المادة">المادة</th>
                <th data-en="Students" data-ar="الطلاب">الطلاب</th>
                <th data-en="Expected" data-ar="المستحق">المستحق</th>
                <th data-en="Collected" data-ar="المحصّل">المحصّل</th>
                <th data-en="Outstanding" data-ar="المتبقي">المتبقي</th>
                <th data-en="Rate" data-ar="النسبة">النسبة</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              @forelse($groupRows as $row)
                <tr>
                  <td class="name">{{ $row->group->name }}</td>
                  <td>{{ $row->group->subject->name_ar ?? '—' }}</td>
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
                  <td>
                    <a href="{{ route('teacher.finance.group', $row->group) }}" class="finance-link"
                       data-en="Details" data-ar="التفاصيل">التفاصيل</a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="finance-empty" data-en="No groups assigned to you yet." data-ar="لا توجد مجموعات مسندة إليك بعد.">لا توجد مجموعات مسندة إليك بعد.</td>
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
