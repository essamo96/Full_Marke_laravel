@extends('admin.layout.mainLayouts.master')

@section('title', 'نتائج الامتحان: ' . $exam->title)

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('exams.view') }}" class="text-muted text-hover-primary">الامتحانات</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">نتائج الامتحان</li>
@endsection

@section('page-content')
<div id="kt_app_content" class="app-content flex-column-fluid">
    <div id="kt_app_content_container" class="app-container container-fluid">
        <div class="card card-flush">
            <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                <div class="card-title flex-column">
                    <h3 class="fw-bold mb-1">الطلاب المسجلين في مجموعة: {{ $exam->group->name }}</h3>
                    <div class="fs-6 fw-semibold text-muted">المادة: {{ $exam->subject->name }}</div>
                </div>
            </div>
            
            <div class="card-body pt-0">
                <form action="{{ route('exams.results', $exam->id) }}" method="GET" class="mb-7">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <input type="text" name="student_name" class="form-control form-control-solid" placeholder="ابحث باسم الطالب..." value="{{ request('student_name') }}">
                        </div>
                        <div class="col-md-4">
                            <select name="status" class="form-select form-select-solid">
                                <option value="">كل الحالات</option>
                                <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>تم التسليم</option>
                                <option value="not_submitted" {{ request('status') == 'not_submitted' ? 'selected' : '' }}>لم يسلم</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary me-2">بحث</button>
                            <a href="{{ route('exams.results', $exam->id) }}" class="btn btn-light">تفريغ</a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_exam_results_table">
                        <thead>
                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                <th>اسم الطالب</th>
                                <th>حالة التسليم</th>
                                <th>الدرجة</th>
                                <th>حالة الاعتماد</th>
                                <th>وقت البدء</th>
                                <th>الوقت المنقضي</th>
                            </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">
                            @forelse($students as $student)
                                @php
                                    $grade = $grades->get($student->id);
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="ms-5">
                                                <a href="{{ route('students.show', $student->id) }}" class="text-gray-800 text-hover-primary fs-5 fw-bold">{{ $student->full_name_ar }}</a>
                                                <div class="text-muted fs-7">{{ $student->phone }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($grade)
                                            <span class="badge badge-light-success">تم التسليم</span>
                                        @else
                                            <span class="badge badge-light-danger">لم يسلم</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($grade)
                                            <span class="fw-bold text-gray-800">{{ $grade->score }} / {{ $grade->max_score }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($grade && $grade->admin_approved_at)
                                            <span class="badge badge-light-success">معتمدة</span>
                                        @elseif($grade && $grade->teacher_reviewed_at)
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge badge-light-warning">بانتظار الاعتماد</span>
                                                <form method="POST" action="{{ route('exams.grades.approve', $grade) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-light-success">اعتماد</button>
                                                </form>
                                            </div>
                                        @elseif($grade)
                                            <span class="badge badge-light-secondary">بانتظار مراجعة المدرّس</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($grade && $grade->started_at)
                                            {{ $grade->started_at->format('Y-m-d h:i A') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($grade && $grade->time_taken_minutes !== null)
                                            {{ $grade->time_taken_minutes }} دقيقة
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">لا يوجد طلاب في هذه المجموعة.</td>
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
