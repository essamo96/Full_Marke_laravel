{{-- Headline financial tiles. Expects $totals from FinanceController::summarise(). --}}
<div class="finance-stats">
  <div class="finance-stat finance-stat--expected">
    <div class="finance-stat__label">
      <i class="bi bi-cash-stack"></i>
      <span data-en="Expected total" data-ar="إجمالي المستحق">إجمالي المستحق</span>
    </div>
    <div class="finance-stat__value">
      {{ number_format($totals['expected'], 2) }} <span class="finance-stat__unit">JOD</span>
    </div>
    <div class="finance-stat__hint" data-en="Sum of enrolment fees" data-ar="مجموع رسوم التسجيلات">مجموع رسوم التسجيلات</div>
  </div>

  <div class="finance-stat finance-stat--collected">
    <div class="finance-stat__label">
      <i class="bi bi-check2-circle"></i>
      <span data-en="Collected" data-ar="المحصّل">المحصّل</span>
    </div>
    <div class="finance-stat__value finance-amount-positive">
      {{ number_format($totals['collected'], 2) }} <span class="finance-stat__unit">JOD</span>
    </div>
    <div class="finance-stat__hint" data-en="Confirmed by administration" data-ar="مؤكّد من قبل الإدارة">مؤكّد من قبل الإدارة</div>
  </div>

  <div class="finance-stat finance-stat--outstanding">
    <div class="finance-stat__label">
      <i class="bi bi-exclamation-circle"></i>
      <span data-en="Outstanding" data-ar="المتبقي">المتبقي</span>
    </div>
    <div class="finance-stat__value {{ $totals['outstanding'] > 0 ? 'finance-amount-negative' : '' }}">
      {{ number_format($totals['outstanding'], 2) }} <span class="finance-stat__unit">JOD</span>
    </div>
    <div class="finance-stat__hint" data-en="Still to be collected" data-ar="لم يتم تحصيله بعد">لم يتم تحصيله بعد</div>
  </div>

  <div class="finance-stat">
    <div class="finance-stat__label">
      <i class="bi bi-people"></i>
      <span data-en="Students" data-ar="الطلاب">الطلاب</span>
    </div>
    <div class="finance-stat__value">{{ number_format($totals['students']) }}</div>
    <div class="finance-stat__hint" data-en="Active enrolments" data-ar="تسجيلات نشطة">تسجيلات نشطة</div>
  </div>
</div>
