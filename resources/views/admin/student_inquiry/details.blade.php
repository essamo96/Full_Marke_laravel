<div class="row g-5 g-xl-10">
    <!-- Main Info -->
    <div class="col-xl-4 mb-5 mb-xl-10">
        <div class="card card-flush h-xl-100">
            <div class="card-header pt-7">
                <div class="card-title">
                    <i class="ki-duotone ki-badge fs-1 me-2 text-primary"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                    <h2>البيانات الأساسية</h2>
                </div>
            </div>
            <div class="card-body pt-5">
                <div class="d-flex flex-center flex-column mb-5">
                    <div class="symbol symbol-100px symbol-circle mb-7">
                        <img src="{{ $student->image ? '/' . $student->image : '/assets/admin/media/avatars/blank.png' }}" alt="image" />
                    </div>
                    <a href="#" class="fs-3 text-gray-800 text-hover-primary fw-bold mb-1">{{ $student->full_name_ar }}</a>
                    <div class="fs-5 fw-semibold text-muted mb-6">{{ $student->full_name_en }}</div>
                </div>
                <div class="d-flex flex-stack fs-4 py-3">
                    <div class="fw-bold">رقم الهوية</div>
                    <div class="badge badge-light-info d-inline">{{ $student->national_id ?? '-' }}</div>
                </div>
                <div class="d-flex flex-stack fs-4 py-3 border-top border-dashed">
                    <div class="fw-bold">رقم الجوال</div>
                    <div class="badge badge-light-success d-inline">{{ $student->phone ?? '-' }}</div>
                </div>
                <div class="d-flex flex-stack fs-4 py-3 border-top border-dashed">
                    <div class="fw-bold">البريد الإلكتروني</div>
                    <div class="badge badge-light-primary d-inline">{{ $student->email ?? '-' }}</div>
                </div>
                <div class="d-flex flex-stack fs-4 py-3 border-top border-dashed">
                    <div class="fw-bold">الفرع</div>
                    <div class="badge badge-light-warning d-inline">{{ $student->branch ? $student->branch->name_ar : '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Registrations & Teachers -->
    <div class="col-xl-8 mb-5 mb-xl-10">
        <div class="card card-flush h-xl-100">
            <div class="card-header pt-7">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-800">المواد والمجموعات المسجلة</span>
                    <span class="text-gray-400 mt-1 fw-semibold fs-6">تفاصيل تسجيلات الطالب الحالية والسابقة</span>
                </h3>
            </div>
            <div class="card-body pt-2">
                <div class="table-responsive">
                    <table class="table table-row-dashed align-middle gs-0 gy-3 my-0">
                        <thead>
                            <tr class="fs-7 fw-bold text-gray-400 border-bottom-0">
                                <th class="p-0 pb-3 min-w-150px">المادة</th>
                                <th class="p-0 pb-3 min-w-100px text-end">المجموعة</th>
                                <th class="p-0 pb-3 min-w-150px text-end">المدرس</th>
                                <th class="p-0 pb-3 min-w-100px text-end">حالة التسجيل</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($student->registrations as $reg)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="d-flex justify-content-start flex-column">
                                            <a href="#" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">{{ $reg->subject ? $reg->subject->name_ar : '-' }}</a>
                                            <span class="text-gray-400 fw-semibold d-block fs-7">{{ $reg->subject && $reg->subject->program ? $reg->subject->program->name_ar : '-' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end fw-bold">
                                    {{ $reg->group ? $reg->group->name : '-' }}
                                </td>
                                <td class="text-end fw-bold text-gray-600">
                                    @if($reg->group && $reg->group->teachers->count() > 0)
                                        @foreach($reg->group->teachers as $teacher)
                                            <span class="badge badge-light-primary mb-1">{{ $teacher->name_ar }}</span>
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end pe-0">
                                    @if($reg->status === 'fully_paid')
                                        <span class="badge badge-light-success fs-7 fw-bold">مدفوع بالكامل</span>
                                    @elseif($reg->status === 'partially_paid')
                                        <span class="badge badge-light-warning fs-7 fw-bold">دفع جزئي</span>
                                    @else
                                        <span class="badge badge-light-danger fs-7 fw-bold">معلق</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-5">لا توجد مواد مسجلة</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-5 g-xl-10">
    <!-- Financials -->
    <div class="col-xl-6 mb-5 mb-xl-10">
        <div class="card card-flush h-xl-100">
            <div class="card-header pt-7">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-800">السجل المالي للدفعات</span>
                </h3>
            </div>
            <div class="card-body pt-2">
                <div class="table-responsive">
                    <table class="table table-row-dashed align-middle gs-0 gy-4">
                        <thead>
                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-100px">التاريخ</th>
                                <th class="min-w-100px">المبلغ</th>
                                <th class="min-w-125px">طريقة الدفع</th>
                                <th class="min-w-100px text-end">الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($student->payments as $payment)
                            <tr>
                                <td>
                                    <span class="text-gray-800 fw-bold d-block fs-6">{{ $payment->created_at->format('Y-m-d') }}</span>
                                </td>
                                <td>
                                    <span class="text-gray-800 fw-bold d-block fs-6">{{ number_format($payment->amount, 2) }}</span>
                                </td>
                                <td>
                                    <span class="text-gray-600 fw-semibold fs-6">{{ $payment->paymentMethod ? $payment->paymentMethod->name_ar : 'بنكي' }}</span>
                                </td>
                                <td class="text-end">
                                    @if($payment->status === 'confirmed')
                                        <span class="badge badge-light-success">مؤكد</span>
                                    @elseif($payment->status === 'rejected')
                                        <span class="badge badge-light-danger">مرفوض</span>
                                    @else
                                        <span class="badge badge-light-warning">معلق</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-5">لا توجد دفعات مسجلة</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs (Attendance, Grades) -->
    <div class="col-xl-6 mb-5 mb-xl-10">
        <div class="card card-flush h-xl-100">
            <div class="card-header pt-7">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-800">مؤشرات الأداء (KPIs)</span>
                </h3>
            </div>
            <div class="card-body pt-5">
                <!-- KPI Items Placeholder - Since real Attendance and Grades fetching would require a complex query, I will add static placeholders mapped to empty variables for now -->
                <div class="d-flex flex-stack border-dashed border-gray-300 rounded p-4 mb-4">
                    <div class="d-flex align-items-center">
                        <i class="ki-duotone ki-calendar-tick fs-1 me-4 text-success"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span></i>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-gray-800 fs-5">نسبة الحضور</span>
                            <span class="text-gray-400 fw-semibold fs-7">إجمالي أيام الحضور</span>
                        </div>
                    </div>
                    <div class="text-success fw-bold fs-3">--%</div>
                </div>

                <div class="d-flex flex-stack border-dashed border-gray-300 rounded p-4 mb-4">
                    <div class="d-flex align-items-center">
                        <i class="ki-duotone ki-document fs-1 me-4 text-primary"><span class="path1"></span><span class="path2"></span></i>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-gray-800 fs-5">أداء الواجبات</span>
                            <span class="text-gray-400 fw-semibold fs-7">الواجبات المسلمة من الإجمالي</span>
                        </div>
                    </div>
                    <div class="text-primary fw-bold fs-3">-- / --</div>
                </div>

                <div class="d-flex flex-stack border-dashed border-gray-300 rounded p-4 mb-4">
                    <div class="d-flex align-items-center">
                        <i class="ki-duotone ki-award fs-1 me-4 text-warning"><span class="path1"></span><span class="path2"></span></i>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-gray-800 fs-5">معدل التقييمات والامتحانات</span>
                            <span class="text-gray-400 fw-semibold fs-7">متوسط درجات الطالب</span>
                        </div>
                    </div>
                    <div class="text-warning fw-bold fs-3">--%</div>
                </div>
                
                <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed mt-6 p-6">
                    <i class="ki-duotone ki-information fs-2tx text-primary me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                    <div class="d-flex flex-stack flex-grow-1 flex-wrap flex-md-nowrap">
                        <div class="mb-3 mb-md-0 fw-semibold">
                            <h4 class="text-gray-900 fw-bold">ملاحظة</h4>
                            <div class="fs-6 text-gray-700 pe-7">جاري العمل على ربط النظام التلقائي لمؤشرات الأداء.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
