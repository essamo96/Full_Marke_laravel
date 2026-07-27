@extends('admin.layout.mainLayouts.master')
@section('title', __('app.dashboard'))

@push('styles')
    <link href="{{ asset('assets/admin/plugins/custom/fullcalendar/fullcalendar.bundle.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .bullet-custom { display: none; }
        .nav-link.active .bullet-custom { display: block; }
    </style>
@endpush

@section('page-content')
<div id="kt_app_content" class="app-content flex-column-fluid">
    <div id="kt_app_content_container" class="app-container container-fluid">
        <!-- Begin Row 1: KPI Widgets -->
        <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
            <!-- Widget 1: Main Stats -->
            <div class="col-xl-4 mb-xl-10">
                <div class="card card-flush h-xl-100">
                    <div class="card-header rounded bgi-no-repeat bgi-size-cover bgi-position-y-top bgi-position-x-center align-items-start h-250px" style="background-image:url('{{ asset('assets/admin/media/svg/shapes/top-green.png') }}')" data-bs-theme="light">
                        <h3 class="card-title align-items-start flex-column text-white pt-15">
                            <span class="fw-bold fs-2x mb-3">إحصائيات الأكاديمية</span>
                            <div class="fs-4 text-white">
                                <span class="opacity-75">أهم المؤشرات الحيوية</span>
                            </div>
                        </h3>
                    </div>
                    <div class="card-body mt-n20">
                        <div class="mt-n20 position-relative">
                            <div class="row g-3 g-lg-6">
                                <div class="col-6">
                                    <div class="bg-white shadow-sm rounded-2 px-6 py-5">
                                        <div class="symbol symbol-30px me-5 mb-8">
                                            <span class="symbol-label"><i class="ki-duotone ki-people fs-1 text-primary"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></span>
                                        </div>
                                        <div class="m-0">
                                            <span class="text-gray-700 fw-bolder d-block fs-2qx lh-1 ls-n1 mb-1">{{ $stats['students'] }}</span>
                                            <span class="text-gray-500 fw-semibold fs-6">الطلاب</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-white shadow-sm rounded-2 px-6 py-5">
                                        <div class="symbol symbol-30px me-5 mb-8">
                                            <span class="symbol-label"><i class="ki-duotone ki-teacher fs-1 text-success"><span class="path1"></span><span class="path2"></span></i></span>
                                        </div>
                                        <div class="m-0">
                                            <span class="text-gray-700 fw-bolder d-block fs-2qx lh-1 ls-n1 mb-1">{{ $stats['teachers'] }}</span>
                                            <span class="text-gray-500 fw-semibold fs-6">المعلمين</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-white shadow-sm rounded-2 px-6 py-5">
                                        <div class="symbol symbol-30px me-5 mb-8">
                                            <span class="symbol-label"><i class="ki-duotone ki-book-open fs-1 text-warning"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i></span>
                                        </div>
                                        <div class="m-0">
                                            <span class="text-gray-700 fw-bolder d-block fs-2qx lh-1 ls-n1 mb-1">{{ $stats['subjects'] }}</span>
                                            <span class="text-gray-500 fw-semibold fs-6">المواد</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-white shadow-sm rounded-2 px-6 py-5">
                                        <div class="symbol symbol-30px me-5 mb-8">
                                            <span class="symbol-label"><i class="ki-duotone ki-element-11 fs-1 text-info"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i></span>
                                        </div>
                                        <div class="m-0">
                                            <span class="text-gray-700 fw-bolder d-block fs-2qx lh-1 ls-n1 mb-1">{{ $stats['groups'] }}</span>
                                            <span class="text-gray-500 fw-semibold fs-6">المجموعات</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Widget 2: Academic Stats -->
            <div class="col-xl-4 mb-xl-10">
                <div class="card card-flush h-xl-100">
                    <div class="card-header rounded bgi-no-repeat bgi-size-cover bgi-position-y-top bgi-position-x-center align-items-start h-250px" style="background-image:url('{{ asset('assets/admin/media/svg/shapes/widget-bg-2.png') }}')">
                        <h3 class="card-title align-items-start flex-column text-gray-900 pt-15">
                            <span class="fw-bold fs-2x mb-3">الأداء والبرامج</span>
                            <div class="fs-4 text-gray-800">
                                <span class="opacity-75">مؤشرات الأداء الأكاديمي</span>
                            </div>
                        </h3>
                    </div>
                    <div class="card-body mt-n20">
                        <div class="mt-n20 position-relative">
                            <div class="row g-3 g-lg-6">
                                <div class="col-6">
                                    <div class="bg-white shadow-sm rounded-2 px-6 py-5">
                                        <div class="symbol symbol-30px me-5 mb-8">
                                            <span class="symbol-label"><i class="ki-duotone ki-chart-pie-4 fs-1 text-primary"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></span>
                                        </div>
                                        <div class="m-0">
                                            <span class="text-gray-700 fw-bolder d-block fs-2qx lh-1 ls-n1 mb-1">{{ $stats['attendance_rate'] }}%</span>
                                            <span class="text-gray-500 fw-semibold fs-6">نسبة الحضور</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-white shadow-sm rounded-2 px-6 py-5">
                                        <div class="symbol symbol-30px me-5 mb-8">
                                            <span class="symbol-label"><i class="ki-duotone ki-medal-star fs-1 text-success"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i></span>
                                        </div>
                                        <div class="m-0">
                                            <span class="text-gray-700 fw-bolder d-block fs-2qx lh-1 ls-n1 mb-1">{{ $stats['average_grade'] }}</span>
                                            <span class="text-gray-500 fw-semibold fs-6">متوسط العلامات</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-white shadow-sm rounded-2 px-6 py-5">
                                        <div class="symbol symbol-30px me-5 mb-8">
                                            <span class="symbol-label"><i class="ki-duotone ki-bookmark-2 fs-1 text-warning"><span class="path1"></span><span class="path2"></span></i></span>
                                        </div>
                                        <div class="m-0">
                                            <span class="text-gray-700 fw-bolder d-block fs-2qx lh-1 ls-n1 mb-1">{{ $stats['programs'] }}</span>
                                            <span class="text-gray-500 fw-semibold fs-6">البرامج</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-white shadow-sm rounded-2 px-6 py-5">
                                        <div class="symbol symbol-30px me-5 mb-8">
                                            <span class="symbol-label"><i class="ki-duotone ki-map fs-1 text-info"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></span>
                                        </div>
                                        <div class="m-0">
                                            <span class="text-gray-700 fw-bolder d-block fs-2qx lh-1 ls-n1 mb-1">{{ $stats['branches'] }}</span>
                                            <span class="text-gray-500 fw-semibold fs-6">الفروع</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Widget 3: Outstanding Fees -->
            <div class="col-xl-4 mb-xl-10">
                <div class="card card-flush h-xl-100">
                    <div class="card-header pt-7">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-800">طلاب مستحقي الرسوم</span>
                            <span class="text-gray-400 mt-1 fw-semibold fs-6">أحدث التنبيهات</span>
                        </h3>
                    </div>
                    <div class="card-body pt-5">
                        @foreach($outstandingFeesStudents as $reg)
                        <div class="d-flex align-items-sm-center mb-7">
                            <div class="symbol symbol-50px me-5">
                                <span class="symbol-label">
                                    <i class="ki-duotone ki-user fs-2x text-danger"><span class="path1"></span><span class="path2"></span></i>
                                </span>
                            </div>
                            <div class="d-flex align-items-center flex-row-fluid flex-wrap">
                                <div class="flex-grow-1 me-2">
                                    <a href="{{ $reg->student ? route('students.show', $reg->student_id) : '#' }}" class="text-gray-800 text-hover-primary fs-6 fw-bold">
                                        {{ $reg->student?->full_name_ar ?? 'طالب غير موجود' }}
                                    </a>
                                    <span class="text-muted fw-semibold d-block fs-7">{{ $reg->subject?->name ?? '-' }}</span>
                                </div>
                                <span class="badge badge-light-danger fs-8 fw-bold my-2">متبقي: {{ $reg->remaining_amount }}</span>
                            </div>
                        </div>
                        @endforeach
                        @if($outstandingFeesStudents->isEmpty())
                        <div class="text-center text-muted py-5">لا يوجد رسوم مستحقة حالياً.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <!-- End Row 1 -->

        <!-- Row 2: Charts -->
        <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
            <div class="col-xl-6">
                <div class="card card-flush h-xl-100">
                    <div class="card-header pt-7">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-800">توزيع الطلاب جغرافياً</span>
                        </h3>
                    </div>
                    <div class="card-body pt-0">
                        <div id="kt_charts_widget_region" class="min-h-auto" style="height: 300px"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="card card-flush h-xl-100">
                    <div class="card-header pt-7">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-800">توزيع الطلاب حسب التخصص</span>
                        </h3>
                    </div>
                    <div class="card-body pt-0">
                        <div id="kt_charts_widget_major" class="min-h-auto" style="height: 300px"></div>
                    </div>
                </div>
            </div>
        </div>

        @php
            $daysOfWeek = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            $daysAr = [
                'Saturday' => 'السبت',
                'Sunday' => 'الأحد',
                'Monday' => 'الإثنين',
                'Tuesday' => 'الثلاثاء',
                'Wednesday' => 'الأربعاء',
                'Thursday' => 'الخميس',
                'Friday' => 'الجمعة'
            ];
            $currentDay = date('l');
        @endphp

        <!-- Row 3: Weekly Groups Schedule -->
        <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
            <div class="col-xl-12">
                <div class="card h-xl-100">
                    <div class="card-header position-relative py-0 border-bottom-2 d-flex justify-content-between align-items-center">
                        <ul class="nav nav-stretch nav-pills nav-pills-custom d-flex mt-3 overflow-auto">
                            @foreach($daysOfWeek as $day)
                            <li class="nav-item p-0 ms-0 me-8">
                                <a class="nav-link btn btn-color-muted px-0 show {{ $day == $currentDay ? 'active' : '' }}" data-bs-toggle="tab" href="#kt_table_widget_7_tab_content_{{ $day }}">
                                    <span class="nav-text fw-semibold fs-4 mb-3">{{ $daysAr[$day] }}</span>
                                    <span class="bullet-custom position-absolute z-index-2 w-100 h-2px top-100 bottom-n100 bg-primary rounded"></span>
                                </a>
                            </li>
                            @endforeach
                        </ul>
                        <div class="card-toolbar">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#kt_modal_enrolment" class="btn btn-sm btn-light-primary fw-bold" title="تشعيب ونقل الطلاب وتوزيعهم على المجموعات">
                                <i class="ki-duotone ki-people fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                تشعيب / نقل الطلاب
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="tab-content mb-2">
                            @foreach($daysOfWeek as $day)
                            <div class="tab-pane fade show {{ $day == $currentDay ? 'active' : '' }}" id="kt_table_widget_7_tab_content_{{ $day }}">
                                <div class="table-responsive">
                                    <table class="table table-striped table-row-bordered gy-5 gs-7">
                                        <thead>
                                            <tr class="fw-semibold fs-6 text-gray-800 fw-bold text-start">
                                                <th class="min-w-150px p-0 pb-3">الوقت</th>
                                                <th class="min-w-200px p-0 pb-3">المادة / المجموعة</th>
                                                <th class="min-w-100px p-0 pb-3">المعلم</th>
                                                <th class="min-w-80px p-0 pb-3 text-end">الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $daysMapToDb = [
                                                    'Saturday' => ['sat', 'saturday', 'Saturday', '6'],
                                                    'Sunday' => ['sun', 'sunday', 'Sunday', '0'],
                                                    'Monday' => ['mon', 'monday', 'Monday', '1'],
                                                    'Tuesday' => ['tue', 'tuesday', 'Tuesday', '2'],
                                                    'Wednesday' => ['wed', 'wednesday', 'Wednesday', '3'],
                                                    'Thursday' => ['thu', 'thursday', 'Thursday', '4'],
                                                    'Friday' => ['fri', 'friday', 'Friday', '5'],
                                                ];
                                                $dayGroups = $orderedGroups->filter(function($grp) use ($day, $daysMapToDb) {
                                                    if (!is_array($grp->days)) return false;
                                                    // Check if any of the group days matches the expected values for the current tab's day
                                                    $grpDaysLower = array_map('strtolower', $grp->days);
                                                    $expectedDays = array_map('strtolower', $daysMapToDb[$day]);
                                                    return count(array_intersect($grpDaysLower, $expectedDays)) > 0;
                                                });
                                            @endphp
                                            @foreach($dayGroups as $grp)
                                            <tr>
                                                <td class="fs-6 fw-bold text-gray-800 align-middle">
                                                    @if($grp->start_time && $grp->end_time)
                                                        <span class="badge badge-light-primary fs-7">
                                                            {{ \Carbon\Carbon::parse($grp->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($grp->end_time)->format('h:i A') }}
                                                        </span>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="fs-6 fw-bold text-gray-400 align-middle">
                                                    {{ $grp->subject?->name ?? '-' }}:
                                                    <a href="{{ route('groups.students', encrypt($grp->id)) }}" class="text-gray-800 text-hover-primary">{{ $grp->name }}</a>
                                                </td>
                                                <td class="fs-6 fw-bold text-gray-400 align-middle">
                                                    <span class="text-gray-800">{{ $grp->teacher?->name ?? '-' }}</span>
                                                </td>
                                                <td class="pe-0 text-end align-middle">
                                                    <a href="{{ route('groups.students', encrypt($grp->id)) }}" class="btn btn-sm btn-light btn-active-light-primary">تفاصيل</a>
                                                </td>
                                            </tr>
                                            @endforeach
                                            @if($dayGroups->isEmpty())
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-8">لا يوجد مجموعات مجدولة في هذا اليوم</td>
                                            </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 4: School Calendar -->
        <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
            <div class="col-xl-12">
                <div class="card card-flush h-xl-100">
                    <div class="card-header pt-7">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-800">تقويم المحاضرات</span>
                        </h3>
                    </div>
                    <div class="card-body">
                        <div id="kt_calendar_app"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/admin/plugins/custom/fullcalendar/fullcalendar.bundle.js') }}"></script>
{{-- Don't load ApexCharts from the CDN here: plugins.bundle.js (loaded on every
     admin page) already bundles its own ApexCharts build. Loading a second,
     different version from the CDN on top of it caused an internal API
     mismatch between the two ApexCharts instances — "e.put is not a function"
     in the console — since some shared internal state from the first load
     doesn't match what the second version's code expects. --}}
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Init FullCalendar
        var calendarEl = document.getElementById('kt_calendar_app');
        var rawEvents = @json($calendarEvents);
        
        var calendarEvents = [];
        rawEvents.forEach(function(ev) {
            calendarEvents.push({
                title: ev.title,
                startTime: ev.startTime,
                endTime: ev.endTime,
                daysOfWeek: ev.daysOfWeek,
                url: '/admin/groups/' + ev.groupId + '/edit' // Assuming such route exists or just '#'
            });
        });

        var calendar = new FullCalendar.Calendar(calendarEl, {
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'timeGridWeek,timeGridDay,listWeek'
            },
            initialView: 'timeGridWeek',
            locale: 'ar',
            events: calendarEvents,
            slotMinTime: '08:00:00',
            slotMaxTime: '22:00:00',
            allDaySlot: false,
        });
        calendar.render();

        // Region Chart
        var regionData = @json($regionDistribution);
        var regionLabels = regionData.map(d => d.name);
        var regionSeries = regionData.map(d => d.count);

        var regionOptions = {
            series: regionSeries,
            chart: {
                type: 'pie',
                height: 300
            },
            labels: regionLabels,
            theme: {
                monochrome: {
                    enabled: true,
                    color: '#009ef7'
                }
            },
            dataLabels: {
                enabled: true
            }
        };
        var regionChart = new ApexCharts(document.querySelector("#kt_charts_widget_region"), regionOptions);
        regionChart.render();

        // Major Chart
        var majorData = @json($studyBranchDistribution);
        var majorLabels = majorData.map(d => d.name);
        var majorSeries = majorData.map(d => d.count);

        var majorOptions = {
            series: majorSeries,
            chart: {
                type: 'donut',
                height: 300
            },
            labels: majorLabels,
            colors: ['#50cd89', '#f1416c', '#ffc700', '#7239ea', '#009ef7'],
            dataLabels: {
                enabled: true
            }
        };
        var majorChart = new ApexCharts(document.querySelector("#kt_charts_widget_major"), majorOptions);
        majorChart.render();
    });
</script>
@endpush
