@extends('admin.layout.mainLayouts.master')
@section('title')
    التقارير المالية
@stop

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('financial_reports.view') }}" class="text-muted text-hover-primary">التقارير المالية</a>
    </li>
@endsection

@section('page-content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">

                {{-- Stats Row --}}
                <div class="row g-4 mb-6">
                    <div class="col-md-6 col-xl-3">
                        <div class="card h-100">
                            <div class="card-body d-flex align-items-center">
                                <div class="symbol symbol-50px me-4">
                                    <span class="symbol-label bg-light-success">
                                        <i class="bi bi-cash-coin text-success fs-2"></i>
                                    </span>
                                </div>
                                <div>
                                    <div class="text-muted fs-7 fw-semibold">إجمالي الإيرادات المحصلة</div>
                                    <div class="fs-4 fw-bold text-success">{{ number_format($totalRevenue, 2) }} JOD</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card h-100">
                            <div class="card-body d-flex align-items-center">
                                <div class="symbol symbol-50px me-4">
                                    <span class="symbol-label bg-light-warning">
                                        <i class="bi bi-hourglass-split text-warning fs-2"></i>
                                    </span>
                                </div>
                                <div>
                                    <div class="text-muted fs-7 fw-semibold">المبالغ المعلقة (الرصيد المتبقي)</div>
                                    <div class="fs-4 fw-bold text-warning">{{ number_format($totalOutstanding, 2) }} JOD</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card h-100">
                            <div class="card-body d-flex align-items-center">
                                <div class="symbol symbol-50px me-4">
                                    <span class="symbol-label bg-light-primary">
                                        <i class="bi bi-people text-primary fs-2"></i>
                                    </span>
                                </div>
                                <div>
                                    <div class="text-muted fs-7 fw-semibold">عدد الطلاب المدينين</div>
                                    <div class="fs-4 fw-bold text-primary">{{ $debtors->count() }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card h-100">
                            <div class="card-body d-flex align-items-center">
                                <div class="symbol symbol-50px me-4">
                                    <span class="symbol-label bg-light-info">
                                        <i class="bi bi-bar-chart text-info fs-2"></i>
                                    </span>
                                </div>
                                <div>
                                    <div class="text-muted fs-7 fw-semibold">البرامج النشطة</div>
                                    <div class="fs-4 fw-bold text-info">{{ $revenueByProgram->count() }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-6">
                    <div class="col-md-3">
                        <div class="card h-100 bg-success bg-opacity-10">
                            <div class="card-body d-flex align-items-center">
                                <div class="symbol symbol-50px me-4">
                                    <span class="symbol-label bg-success">
                                        <i class="bi bi-person-check text-white fs-2"></i>
                                    </span>
                                </div>
                                <div>
                                    <div class="text-muted fs-7 fw-semibold">الطلاب النشطين</div>
                                    <div class="fs-4 fw-bold text-success">{{ $studentStats['active'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card h-100 bg-danger bg-opacity-10">
                            <div class="card-body d-flex align-items-center">
                                <div class="symbol symbol-50px me-4">
                                    <span class="symbol-label bg-danger">
                                        <i class="bi bi-person-dash text-white fs-2"></i>
                                    </span>
                                </div>
                                <div>
                                    <div class="text-muted fs-7 fw-semibold">الطلاب غير النشطين</div>
                                    <div class="fs-4 fw-bold text-danger">{{ $studentStats['inactive'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card h-100 bg-primary bg-opacity-10">
                            <div class="card-body d-flex align-items-center">
                                <div class="symbol symbol-50px me-4">
                                    <span class="symbol-label bg-primary">
                                        <i class="bi bi-envelope-check text-white fs-2"></i>
                                    </span>
                                </div>
                                <div>
                                    <div class="text-muted fs-7 fw-semibold">إيميلات موثقة</div>
                                    <div class="fs-4 fw-bold text-primary">{{ $studentStats['email_verified'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card h-100 bg-warning bg-opacity-10">
                            <div class="card-body d-flex align-items-center">
                                <div class="symbol symbol-50px me-4">
                                    <span class="symbol-label bg-warning">
                                        <i class="bi bi-envelope-x text-white fs-2"></i>
                                    </span>
                                </div>
                                <div>
                                    <div class="text-muted fs-7 fw-semibold">إيميلات غير موثقة</div>
                                    <div class="fs-4 fw-bold text-warning">{{ $studentStats['email_unverified'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-6">
                    {{-- Revenue by Program --}}
                    <div class="col-lg-6">
                        <div class="card h-100">
                            <div class="card-header border-0 pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold fs-5 mb-1">الإيرادات حسب البرنامج</span>
                                </h3>
                            </div>
                            <div class="card-body py-3">
                                <div class="table-responsive">
                                    <table class="table table-striped table-row-bordered gy-5 gs-7">
                                        <thead>
                                <tr class="fw-semibold fs-6 text-gray-800 fw-bold text-start">
                                                <th class="p-0 min-w-150px">البرنامج</th>
                                                <th class="p-0 min-w-140px">الإيراد</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($revenueByProgram as $item)
                                                <tr>
                                                    <td class="fw-semibold">{{ app()->getLocale() == 'ar' ? $item->name_ar : $item->name_en }}</td>
                                                    <td class="text-end fw-bold text-success">{{ number_format($item->revenue, 2) }} JOD</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="2" class="text-center text-muted py-4">لا توجد بيانات</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Revenue by Payment Method --}}
                    <div class="col-lg-6">
                        <div class="card h-100">
                            <div class="card-header border-0 pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold fs-5 mb-1">الإيرادات حسب طريقة الدفع</span>
                                </h3>
                            </div>
                            <div class="card-body py-3">
                                <div class="table-responsive">
                                    <table class="table table-row-dashed table-row-gray-200 align-middle gs-0 gy-4">
                                        <thead>
                                            <tr class="border-0">
                                                <th class="p-0 min-w-150px">طريقة الدفع</th>
                                                <th class="p-0 min-w-70px">عدد</th>
                                                <th class="p-0 min-w-140px">الإيراد</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($revenueByMethod as $item)
                                                <tr>
                                                    <td class="fw-semibold">{{ $item->method }}</td>
                                                    <td class="text-center">{{ $item->count }}</td>
                                                    <td class="text-end fw-bold text-success">{{ number_format($item->revenue, 2) }} JOD</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted py-4">لا توجد بيانات</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Monthly Revenue Chart --}}
                <div class="card mb-6">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-5 mb-1">الإيرادات الشهرية (آخر 6 أشهر)</span>
                        </h3>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyChart" height="80"></canvas>
                    </div>
                </div>

                {{-- Debtors Table --}}
                <div class="card">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-5 mb-1">قائمة المديونيات</span>
                            <span class="text-muted mt-1 fw-semibold fs-7">الطلاب الذين لديهم رصيد متبقٍّ</span>
                        </h3>
                    </div>
                    <div class="card-body py-3">
                        <div class="table-responsive">
                            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                <thead>
                                    <tr class="fw-bold text-muted bg-light text-start">
                                        <th class="ps-4 rounded-start">الطالب</th>
                                        <th>المادة</th>
                                        <th>الرسوم الكاملة</th>
                                        <th>المدفوع</th>
                                        <th>المتبقي</th>
                                        <th>الحالة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($debtors as $reg)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-40px me-3">
                                                        @if($reg->student?->photo)
                                                            <img src="{{ Storage::url($reg->student->photo) }}" alt="" class="rounded-circle w-40px h-40px object-fit-cover">
                                                        @else
                                                            <span class="symbol-label bg-light-primary text-primary fw-bold fs-6">{{ substr($reg->student?->name ?? 'S', 0, 1) }}</span>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <span class="text-dark fw-bold d-block">{{ $reg->student?->name ?? 'غير معروف' }}</span>
                                                        <span class="text-muted fw-semibold fs-7">{{ $reg->student?->email }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-dark fw-bold">{{ app()->getLocale() == 'ar' ? $reg->subject?->name_ar : $reg->subject?->name_en }}</span>
                                            </td>
                                            <td><span class="fw-semibold">{{ number_format($reg->fee_snapshot, 2) }} JOD</span></td>
                                            <td><span class="text-success fw-semibold">{{ number_format($reg->amount_paid, 2) }} JOD</span></td>
                                            <td><span class="text-danger fw-bold">{{ number_format($reg->remaining_amount, 2) }} JOD</span></td>
                                            <td>
                                                @if($reg->status == 'fully_paid')
                                                    <span class="badge badge-light-success">مدفوع بالكامل</span>
                                                @elseif($reg->status == 'partially_paid')
                                                    <span class="badge badge-light-warning">مدفوع جزئياً</span>
                                                @else
                                                    <span class="badge badge-light-danger">معلق</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-6">
                                                <i class="bi bi-check-circle fs-2 text-success d-block mb-2"></i>
                                                لا يوجد مديونيات حالياً
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const months = @json($monthlyRevenue->pluck('month'));
    const revenues = @json($monthlyRevenue->pluck('revenue'));

    const ctx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: months,
            datasets: [{
                label: 'الإيراد (JOD)',
                data: revenues,
                backgroundColor: 'rgba(54, 153, 255, 0.7)',
                borderColor: 'rgba(54, 153, 255, 1)',
                borderWidth: 1,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: val => val.toLocaleString() + ' JOD'
                    }
                }
            }
        }
    });
});
</script>
@endpush
