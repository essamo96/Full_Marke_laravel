<div class="d-flex justify-content-start flex-shrink-0">
    <div class="dropdown d-inline-block">
        <button class="btn btn-sm btn-light btn-active-light-primary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-tools me-1"></i> أدوات
        </button>
        <ul class="dropdown-menu dropdown-menu-end menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-200px py-4">
            <li class="menu-item px-3">
                <a href="{{ route('students.show', Crypt::encrypt($registration->student_id)) }}" class="menu-link px-3">
                    <i class="bi bi-person-lines-fill me-2"></i> عرض بيانات الطالب
                </a>
            </li>
            <li class="menu-item px-3">
                <a href="javascript:void(0)" class="menu-link px-3 transfer-student-btn"
                   data-registration-id="{{ $registration->id }}">
                    <i class="bi bi-arrow-left-right me-2"></i> نقل الطالب
                </a>
            </li>
            <li class="menu-item px-3">
                <a href="javascript:void(0)" class="menu-link px-3 text-danger remove-from-group-btn"
                   data-registration-id="{{ $registration->id }}">
                    <i class="bi bi-person-dash me-2"></i> حذف من المجموعة
                </a>
            </li>
        </ul>
    </div>
</div>
