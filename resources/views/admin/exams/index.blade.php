@extends('admin.layout.mainLayouts.master')

@section('title', __('app.exams'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('exams.view') }}" class="text-muted text-hover-primary">@lang('app.exams')</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">@lang('app.view')</li>
@endsection

@section('page-content')
<div class="card card-flush mt-5">
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <div class="card-toolbar">
            <a href="{{ route('exams.create') }}" class="btn btn-primary">
                <i class="ki-duotone ki-plus fs-2"></i>إضافة امتحان جديد
            </a>
        </div>
    </div>
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="w-10px pe-2">#</th>
                        <th class="min-w-125px">عنوان الامتحان</th>
                        <th class="min-w-125px">المادة / المجموعة</th>
                        <th class="min-w-125px">المواعيد</th>
                        <th class="min-w-125px">الحالة</th>
                        <th class="text-end min-w-70px">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @forelse($exams as $exam)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $exam->title }}</td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="text-gray-800">{{ $exam->subject->name ?? '-' }}</span>
                                <span class="text-muted fs-7">{{ $exam->group->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td>
                            @if($exam->start_time && $exam->end_time)
                                <div><span class="badge badge-light-success mb-1">يبدأ: {{ $exam->start_time->format('Y-m-d H:i') }}</span></div>
                                <div><span class="badge badge-light-danger">ينتهي: {{ $exam->end_time->format('Y-m-d H:i') }}</span></div>
                            @else
                                <span class="badge badge-light-secondary">غير محدد</span>
                            @endif
                        </td>
                        <td>
                            @if($exam->status === 'published')
                                <span class="badge badge-light-success">منشور</span>
                            @elseif($exam->status === 'completed')
                                <span class="badge badge-light-primary">مكتمل</span>
                            @else
                                <span class="badge badge-light-warning">مسودة</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('exams.results', $exam) }}" class="btn btn-icon btn-bg-light btn-active-color-info btn-sm me-1" title="نتائج الامتحان">
                                <i class="ki-duotone ki-chart-simple fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                            </a>
                            <a href="{{ route('exams.edit', $exam) }}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" title="تعديل">
                                <i class="ki-duotone ki-pencil fs-2"><span class="path1"></span><span class="path2"></span></i>
                            </a>
                            <form action="{{ route('exams.destroy', $exam) }}" method="POST" class="d-inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" onclick="return confirm('هل أنت متأكد من الحذف؟')">
                                    <i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">لا يوجد امتحانات مضافة.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $exams->links() }}
        </div>
    </div>
</div>
@endsection
