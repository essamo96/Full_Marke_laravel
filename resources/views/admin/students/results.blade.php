@extends('admin.layout.mainLayouts.master')

@section('title', 'نتائج امتحانات الطالب: ' . (app()->getLocale() == 'ar' ? $student->full_name_ar : $student->full_name_en))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('students.view') }}" class="text-muted text-hover-primary">الطلاب</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">نتائج الطالب</li>
@endsection

@section('page-content')
<div id="kt_app_content" class="app-content flex-column-fluid">
    <div id="kt_app_content_container" class="app-container container-fluid">
        <div class="card card-flush">
            <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                <div class="card-title flex-column">
                    <h3 class="fw-bold mb-1">نتائج امتحانات الطالب: {{ app()->getLocale() == 'ar' ? $student->full_name_ar : $student->full_name_en }}</h3>
                </div>
            </div>
            
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                <th>تاريخ الامتحان</th>
                                <th>اسم الامتحان</th>
                                <th>المجموعة</th>
                                <th>النتيجة</th>
                                <th>الوقت المستغرق</th>
                                <th>ملاحظات</th>
                            </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">
                            @forelse($grades as $grade)
                                <tr>
                                    <td>{{ $grade->created_at->format('Y-m-d') }}</td>
                                    <td>{{ $grade->exam_name }}</td>
                                    <td>
                                        @if($grade->group)
                                            <span class="badge badge-light-primary">{{ $grade->group->name }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-bold {{ $grade->score >= ($grade->max_score/2) ? 'text-success' : 'text-danger' }}">
                                            {{ $grade->score }} / {{ $grade->max_score }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($grade->time_taken_minutes !== null)
                                            {{ $grade->time_taken_minutes }} دقيقة
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $grade->notes ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">لا توجد نتائج امتحانات لهذا الطالب حتى الآن.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
