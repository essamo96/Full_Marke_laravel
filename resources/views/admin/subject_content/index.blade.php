@extends('admin.layout.mainLayouts.master')
@section('title', 'المحتوى التعليمي')

@section('breadcrumb')
    <li class="breadcrumb-item text-dark">المحتوى التعليمي</li>
@endsection

@section('page-content')
<div class="card mb-5 mb-xl-10">
    <div class="card-header border-0 pt-5">
        <h3 class="card-title align-items-start flex-column">
            <span class="text-muted mt-1 fw-semibold fs-6">اختر المادة لإدارة المحتوى التعليمي الخاص بها (الوحدات، الدروس، المرفقات)</span>
        </h3>
    </div>
    <div class="card-body py-3">
        <div class="table-responsive">
            <table id="kt_subject_content_table" class="table table-striped table-row-bordered gy-5 gs-7">
                <thead>
                                <tr class="fw-semibold fs-6 text-gray-800 fw-bold text-start">
                        <th class="ps-4 min-w-200px rounded-start">المادة</th>
                        <th class="min-w-150px">البرنامج</th>
                        <th class="min-w-100px  pe-4 rounded-end">إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subjects as $subject)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-45px me-5">
                                        <img src="{{ $subject->image ? (str_starts_with($subject->image, 'site/') ? asset($subject->image) : asset('storage/' . $subject->image)) : asset('assets/admin/media/avatars/blank.png') }}" alt="" />
                                    </div>
                                    <div class="d-flex justify-content-start flex-column">
                                        <a href="{{ route('subject_content.manage', \Illuminate\Support\Facades\Crypt::encrypt($subject->id)) }}" class="text-dark fw-bold text-hover-primary fs-6">{{ $subject->name_ar }}</a>
                                        <span class="text-muted fw-semibold text-muted d-block fs-7">{{ $subject->name_en }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-dark fw-bold d-block fs-6">{{ $subject->program ? $subject->program->name_ar : '-' }}</span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('subject_content.manage', \Illuminate\Support\Facades\Crypt::encrypt($subject->id)) }}" class="btn btn-sm btn-light-primary">
                                    إدارة المحتوى <i class="ki-duotone ki-folder ms-2 fs-5"><span class="path1"></span><span class="path2"></span></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-5">لا توجد مواد مسجلة</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
