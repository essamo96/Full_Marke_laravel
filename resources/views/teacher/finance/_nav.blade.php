{{--
  Finance section navigation.

  A secondary, section-scoped sidebar so the finance area can be split into its
  own parts without lengthening the main teacher sidebar. Collapses to a
  horizontal scroller on small screens.
--}}
<nav class="finance-nav glass-panel rounded-4 p-2 p-lg-3" aria-label="أقسام المالية">
  <div class="finance-nav__title d-none d-lg-block">
    <i class="bi bi-wallet2 me-1"></i>
    <span data-en="Finance" data-ar="القسم المالي">القسم المالي</span>
  </div>

  <ul class="finance-nav__list">
    <li>
      <a href="{{ route('teacher.finance.index') }}"
         class="finance-nav__item {{ request()->routeIs('teacher.finance.index') ? 'active' : '' }}">
        <i class="bi bi-speedometer2"></i>
        <span data-en="Overview" data-ar="نظرة عامة">نظرة عامة</span>
      </a>
    </li>
    <li>
      <a href="{{ route('teacher.finance.groups') }}"
         class="finance-nav__item {{ request()->routeIs('teacher.finance.groups') || request()->routeIs('teacher.finance.group') ? 'active' : '' }}">
        <i class="bi bi-collection"></i>
        <span data-en="By Group" data-ar="حسب المجموعة">حسب المجموعة</span>
      </a>
    </li>
    <li>
      <a href="{{ route('teacher.finance.students') }}"
         class="finance-nav__item {{ request()->routeIs('teacher.finance.students') || request()->routeIs('teacher.finance.registration') ? 'active' : '' }}">
        <i class="bi bi-people"></i>
        <span data-en="By Student" data-ar="حسب الطالب">حسب الطالب</span>
      </a>
    </li>
    <li>
      <a href="{{ route('teacher.finance.students', ['filter' => 'outstanding']) }}"
         class="finance-nav__item {{ request()->routeIs('teacher.finance.students') && request()->query('filter') === 'outstanding' ? 'active' : '' }}">
        <i class="bi bi-exclamation-circle"></i>
        <span data-en="Outstanding" data-ar="المبالغ المتبقية">المبالغ المتبقية</span>
      </a>
    </li>
    <li>
      <a href="{{ route('teacher.finance.payments') }}"
         class="finance-nav__item {{ request()->routeIs('teacher.finance.payments') ? 'active' : '' }}">
        <i class="bi bi-receipt"></i>
        <span data-en="Payments" data-ar="سجل المدفوعات">سجل المدفوعات</span>
      </a>
    </li>
  </ul>
</nav>
