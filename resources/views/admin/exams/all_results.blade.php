@extends('admin.layout.mainLayouts.master')

@section('title', 'نتائج الامتحانات الشاملة')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('exams.view') }}" class="text-muted text-hover-primary">@lang('app.exams')</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">جميع النتائج</li>
@endsection

@section('page-content')
<div class="card card-flush mt-5">
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <div class="card-title">
            <h3 class="fw-bold m-0">سجل نتائج الامتحانات لجميع الطلاب</h3>
        </div>
    </div>
    <div class="card-body pt-0">
        <form action="{{ route('exams_results.view') }}" method="GET" class="mb-7 mt-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-3">
                    <input type="text" name="student_name" class="form-control form-control-solid" placeholder="اسم الطالب..." value="{{ request('student_name') }}">
                </div>
                <div class="col-md-3">
                    <input type="text" name="exam_name" class="form-control form-control-solid" placeholder="اسم الامتحان..." value="{{ request('exam_name') }}">
                </div>
                <div class="col-md-3">
                    <select name="group_id" class="form-select form-select-solid" data-control="select2" data-placeholder="اختر المجموعة">
                        <option value="">كل المجموعات</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}" {{ request('group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary me-2">بحث</button>
                    <a href="{{ route('exams_results.view') }}" class="btn btn-light">تفريغ</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="w-10px pe-2">#</th>
                        <th class="min-w-150px">اسم الطالب</th>
                        <th class="min-w-150px">الامتحان</th>
                        <th class="min-w-125px">المجموعة</th>
                        <th class="min-w-100px text-center">الدرجة</th>
                        <th class="min-w-125px text-center">المدة المستغرقة</th>
                        <th class="min-w-125px text-end">تاريخ التسليم</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @forelse($grades as $grade)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            @if($grade->student)
                                <a href="{{ route('students.show', $grade->student->id) }}" class="text-gray-800 text-hover-primary mb-1">
                                    {{ $grade->student->full_name_ar ?? $grade->student->name }}
                                </a>
                            @else
                                <span class="text-muted">طالب محذوف</span>
                            @endif
                        </td>
                        <td>
                            @if($grade->exam)
                                <a href="{{ route('exams.edit', $grade->exam->id) }}" class="text-gray-800 text-hover-primary mb-1">
                                    {{ $grade->exam_name ?? $grade->exam->title }}
                                </a>
                            @else
                                {{ $grade->exam_name }}
                            @endif
                        </td>
                        <td>{{ $grade->group->name ?? '-' }}</td>
                        <td class="text-center">
                            <span class="badge {{ $grade->score >= ($grade->max_score / 2) ? 'badge-light-success' : 'badge-light-danger' }} fs-6">
                                {{ $grade->score }} / {{ $grade->max_score }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($grade->time_taken_minutes)
                                {{ $grade->time_taken_minutes }} دقيقة
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-end">
                            {{ $grade->created_at->format('Y-m-d H:i') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">لا توجد نتائج مسجلة حتى الآن.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($grades->hasPages())
            <div class="mt-4">
                {{ $grades->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
