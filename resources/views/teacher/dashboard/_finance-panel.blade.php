{{--
  Dashboard finance panel.

  A tabbed summary of the teacher's own financial position, backed by the same
  TeacherFinanceReport the finance section uses. "Collected" means confirmed by
  the administration throughout. Each tab links through to the full screen
  rather than duplicating it.
--}}
<section class="fade-in-up delay-2 mb-5">
  <div class="glass-panel rounded-4 overflow-hidden" style="border: 1px solid var(--separator-color);">

    {{-- Header + tab strip --}}
    <div class="p-4 pb-0">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
          <div class="p-3 rounded-circle shadow-sm" style="background: rgba(197, 168, 128, 0.1); color: var(--accent-color);">
            <i class="bi bi-wallet2 fs-4"></i>
          </div>
          <div>
            <h2 class="h5 fw-bold mb-1" style="color: var(--text-primary);"
                data-en="Financial Dashboard" data-ar="لوحة التحكم المالية">لوحة التحكم المالية</h2>
            <p class="mb-0 text-sm opacity-75"
               data-en="Confirmed by administration" data-ar="المبالغ المؤكّدة من قبل الإدارة">المبالغ المؤكّدة من قبل الإدارة</p>
          </div>
        </div>
        <a href="{{ route('teacher.finance.index') }}" class="btn btn-luxury btn-sm rounded-pill px-4 fw-bold">
          <i class="bi bi-box-arrow-up-left ms-1"></i>
          <span data-en="Open finance" data-ar="فتح القسم المالي">فتح القسم المالي</span>
        </a>
      </div>

      <ul class="nav nav-tabs finance-tabs border-0" id="financeTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="fin-tab-summary" data-bs-toggle="tab" data-bs-target="#fin-pane-summary"
                  type="button" role="tab" aria-controls="fin-pane-summary" aria-selected="true">
            <i class="bi bi-speedometer2 ms-1"></i>
            <span data-en="Summary" data-ar="الملخص">الملخص</span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="fin-tab-groups" data-bs-toggle="tab" data-bs-target="#fin-pane-groups"
                  type="button" role="tab" aria-controls="fin-pane-groups" aria-selected="false">
            <i class="bi bi-collection ms-1"></i>
            <span data-en="By group" data-ar="حسب المجموعة">حسب المجموعة</span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="fin-tab-due" data-bs-toggle="tab" data-bs-target="#fin-pane-due"
                  type="button" role="tab" aria-controls="fin-pane-due" aria-selected="false">
            <i class="bi bi-exclamation-circle ms-1"></i>
            <span data-en="Outstanding" data-ar="المتبقي">المتبقي</span>
            @if($financeOutstanding->isNotEmpty())
              <span class="finance-tab-count">{{ $financeOutstanding->count() }}</span>
            @endif
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="fin-tab-recent" data-bs-toggle="tab" data-bs-target="#fin-pane-recent"
                  type="button" role="tab" aria-controls="fin-pane-recent" aria-selected="false">
            <i class="bi bi-receipt ms-1"></i>
            <span data-en="Recent" data-ar="آخر المدفوعات">آخر المدفوعات</span>
          </button>
        </li>
      </ul>
    </div>

    <div class="tab-content p-4">

      {{-- Summary --}}
      <div class="tab-pane fade show active" id="fin-pane-summary" role="tabpanel" aria-labelledby="fin-tab-summary">
        <div class="row g-3 g-lg-4">
          <div class="col-6 col-xl-3">
            <div class="fin-tile" style="border-inline-start: 3px solid var(--accent-color);">
              <p class="fin-tile__label" data-en="Expected" data-ar="إجمالي المستحق">إجمالي المستحق</p>
              <h3 class="fin-tile__value">{{ number_format($financeTotals['expected'], 2) }} <small>JOD</small></h3>
            </div>
          </div>
          <div class="col-6 col-xl-3">
            <div class="fin-tile" style="border-inline-start: 3px solid #22c55e;">
              <p class="fin-tile__label" data-en="Collected" data-ar="المحصّل">المحصّل</p>
              <h3 class="fin-tile__value fin-pos">{{ number_format($financeTotals['collected'], 2) }} <small>JOD</small></h3>
            </div>
          </div>
          <div class="col-6 col-xl-3">
            <div class="fin-tile" style="border-inline-start: 3px solid #ef4444;">
              <p class="fin-tile__label" data-en="Outstanding" data-ar="المتبقي">المتبقي</p>
              <h3 class="fin-tile__value {{ $financeTotals['outstanding'] > 0 ? 'fin-neg' : '' }}">
                {{ number_format($financeTotals['outstanding'], 2) }} <small>JOD</small>
              </h3>
            </div>
          </div>
          <div class="col-6 col-xl-3">
            <div class="fin-tile" style="border-inline-start: 3px solid var(--accent-color);">
              <p class="fin-tile__label" data-en="Students" data-ar="الطلاب">الطلاب</p>
              <h3 class="fin-tile__value">{{ number_format($financeTotals['students']) }}</h3>
            </div>
          </div>
        </div>

        {{-- Overall collection rate --}}
        <div class="mt-4">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-sm fw-bold" style="color: var(--text-secondary);"
                  data-en="Collection rate" data-ar="نسبة التحصيل">نسبة التحصيل</span>
            <span class="text-sm fw-bold" style="color: var(--text-primary);">{{ $financeTotals['rate'] }}%</span>
          </div>
          <div class="fin-bar">
            <div class="fin-bar__fill {{ $financeTotals['rate'] >= 100 ? '' : ($financeTotals['rate'] > 0 ? 'is-partial' : 'is-none') }}"
                 style="width: {{ $financeTotals['rate'] }}%"></div>
          </div>
        </div>
      </div>

      {{-- By group --}}
      <div class="tab-pane fade" id="fin-pane-groups" role="tabpanel" aria-labelledby="fin-tab-groups">
        <div class="fin-scroll">
          <table class="fin-table">
            <thead>
              <tr>
                <th data-en="Group" data-ar="المجموعة">المجموعة</th>
                <th data-en="Students" data-ar="الطلاب">الطلاب</th>
                <th data-en="Collected" data-ar="المحصّل">المحصّل</th>
                <th data-en="Outstanding" data-ar="المتبقي">المتبقي</th>
                <th data-en="Rate" data-ar="النسبة">النسبة</th>
              </tr>
            </thead>
            <tbody>
              @forelse($financeGroups as $row)
                <tr>
                  <td>
                    <a href="{{ route('teacher.finance.group', $row->group) }}" class="fin-link">{{ $row->group->name }}</a>
                    <div class="fin-sub">{{ $row->group->subject->name_ar ?? '—' }}</div>
                  </td>
                  <td class="num">{{ $row->students }}</td>
                  <td class="num fin-pos">{{ number_format($row->collected, 2) }}</td>
                  <td class="num {{ $row->outstanding > 0 ? 'fin-neg' : '' }}">{{ number_format($row->outstanding, 2) }}</td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <div class="fin-bar" style="min-width: 70px;">
                        <div class="fin-bar__fill {{ $row->collection_rate >= 100 ? '' : ($row->collection_rate > 0 ? 'is-partial' : 'is-none') }}"
                             style="width: {{ $row->collection_rate }}%"></div>
                      </div>
                      <span class="num" style="font-size: 0.75rem;">{{ $row->collection_rate }}%</span>
                    </div>
                  </td>
                </tr>
              @empty
                <tr><td colspan="5" class="fin-empty" data-en="No groups assigned to you yet." data-ar="لا توجد مجموعات مسندة إليك بعد.">لا توجد مجموعات مسندة إليك بعد.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      {{-- Outstanding --}}
      <div class="tab-pane fade" id="fin-pane-due" role="tabpanel" aria-labelledby="fin-tab-due">
        <div class="fin-scroll">
          <table class="fin-table">
            <thead>
              <tr>
                <th data-en="Student" data-ar="الطالب">الطالب</th>
                <th data-en="Group" data-ar="المجموعة">المجموعة</th>
                <th data-en="Paid" data-ar="المدفوع">المدفوع</th>
                <th data-en="Outstanding" data-ar="المتبقي">المتبقي</th>
              </tr>
            </thead>
            <tbody>
              @forelse($financeOutstanding as $row)
                <tr>
                  <td>
                    <a href="{{ route('teacher.finance.registration', $row) }}" class="fin-link">
                      {{ $row->student->full_name_ar ?: $row->student->full_name_en }}
                    </a>
                    <div class="fin-sub" dir="ltr" style="text-align: start;">{{ $row->student->phone ?: '—' }}</div>
                  </td>
                  <td>{{ $row->group->name ?? '—' }}</td>
                  <td class="num fin-pos">{{ number_format($row->confirmed_paid, 2) }}</td>
                  <td class="num fin-neg">{{ number_format($row->confirmed_outstanding, 2) }}</td>
                </tr>
              @empty
                <tr><td colspan="4" class="fin-empty" data-en="Everything is settled." data-ar="لا توجد مبالغ متبقية.">لا توجد مبالغ متبقية.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if($financeOutstanding->isNotEmpty())
          <div class="text-center mt-3">
            <a href="{{ route('teacher.finance.students', ['filter' => 'outstanding']) }}" class="fin-link fw-bold"
               data-en="View all outstanding" data-ar="عرض كل المبالغ المتبقية">عرض كل المبالغ المتبقية</a>
          </div>
        @endif
      </div>

      {{-- Recent payments --}}
      <div class="tab-pane fade" id="fin-pane-recent" role="tabpanel" aria-labelledby="fin-tab-recent">
        <div class="fin-scroll">
          <table class="fin-table">
            <thead>
              <tr>
                <th data-en="Student" data-ar="الطالب">الطالب</th>
                <th data-en="Group" data-ar="المجموعة">المجموعة</th>
                <th data-en="Amount" data-ar="المبلغ">المبلغ</th>
                <th data-en="Confirmed on" data-ar="تاريخ التأكيد">تاريخ التأكيد</th>
              </tr>
            </thead>
            <tbody>
              @forelse($financeRecentPayments as $payment)
                <tr>
                  <td class="fin-name">{{ $payment->student_name_ar ?: $payment->student_name_en }}</td>
                  <td>{{ $payment->group_name }}</td>
                  <td class="num fin-pos">{{ number_format((float) $payment->allocated_amount, 2) }}</td>
                  <td>{{ $payment->reviewed_at ? \Illuminate\Support\Carbon::parse($payment->reviewed_at)->format('Y-m-d') : '—' }}</td>
                </tr>
              @empty
                <tr><td colspan="4" class="fin-empty" data-en="No confirmed payments yet." data-ar="لا توجد مدفوعات مؤكدة بعد.">لا توجد مدفوعات مؤكدة بعد.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</section>

@push('styles')
<style>
  /* Tab strip. Built on theme variables so it reads correctly under the dark,
     gold and light themes without per-theme rules. */
  .finance-tabs { gap: 4px; flex-wrap: wrap; }
  .finance-tabs .nav-link {
    border: 1px solid transparent;
    border-radius: 12px 12px 0 0;
    color: var(--text-secondary);
    font-size: 0.84rem;
    font-weight: 700;
    padding: 10px 16px;
    background: transparent;
    display: flex;
    align-items: center;
    gap: 4px;
    transition: color 0.2s ease, background 0.2s ease, border-color 0.2s ease;
  }
  .finance-tabs .nav-link:hover { color: var(--text-primary); background: var(--input-bg); }
  .finance-tabs .nav-link.active {
    color: var(--accent-color);
    background: var(--accent-glow);
    border-color: var(--separator-color);
    border-bottom-color: transparent;
  }
  .finance-tab-count {
    display: inline-block;
    min-width: 18px;
    padding: 1px 6px;
    border-radius: 999px;
    background: rgba(239, 68, 68, 0.18);
    color: #dc2626;
    font-size: 0.66rem;
    font-weight: 800;
  }
  .theme-dark .finance-tab-count,
  .theme-gold .finance-tab-count { color: #f87171; }

  /* Tiles */
  .fin-tile {
    padding: 14px 16px;
    border-radius: 14px;
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    height: 100%;
  }
  .fin-tile__label { font-size: 0.72rem; color: var(--text-muted); font-weight: 600; margin-bottom: 6px; }
  .fin-tile__value {
    font-size: 1.2rem;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0;
    font-variant-numeric: tabular-nums;
  }
  .fin-tile__value small { font-size: 0.68rem; font-weight: 600; color: var(--text-muted); }

  /* Semantic amount colours, tuned per theme for contrast. */
  .fin-pos { color: #16a34a; }
  .fin-neg { color: #dc2626; }
  .theme-dark .fin-pos, .theme-gold .fin-pos { color: #4ade80; }
  .theme-dark .fin-neg, .theme-gold .fin-neg { color: #f87171; }

  .fin-bar { height: 6px; border-radius: 999px; background: var(--separator-color); overflow: hidden; }
  .fin-bar__fill { height: 100%; border-radius: 999px; background: #22c55e; transition: width 0.3s ease; }
  .fin-bar__fill.is-partial { background: #f59e0b; }
  .fin-bar__fill.is-none { background: #ef4444; }

  /* Wide tables scroll in their own box so the page never scrolls sideways. */
  .fin-scroll { overflow-x: auto; }
  .fin-table { width: 100%; border-collapse: collapse; font-size: 0.83rem; margin: 0; }
  .fin-table th {
    text-align: start;
    padding: 10px 14px;
    font-size: 0.7rem;
    font-weight: 700;
    color: var(--text-muted);
    white-space: nowrap;
    border-bottom: 1px solid var(--separator-color);
  }
  .fin-table td {
    padding: 11px 14px;
    color: var(--text-secondary);
    border-bottom: 1px solid var(--separator-color);
    vertical-align: middle;
  }
  .fin-table tbody tr:last-child td { border-bottom: 0; }
  .fin-table .num { font-variant-numeric: tabular-nums; white-space: nowrap; font-weight: 700; }
  .fin-name { color: var(--text-primary); font-weight: 700; }
  .fin-sub { font-size: 0.71rem; color: var(--text-muted); margin-top: 2px; }
  .fin-link { color: var(--accent-color); text-decoration: none; font-weight: 700; }
  .fin-link:hover { text-decoration: underline; }
  .fin-empty { padding: 28px 14px; text-align: center; color: var(--text-muted); }
</style>
@endpush
