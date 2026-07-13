@extends('admin.layout.mainLayouts.master')
@section('title')
    التسجيلات
@stop

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('registrations.view') }}" class="text-muted text-hover-primary">التسجيلات</a>
    </li>
@endsection

@section('page-content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title w-100 mb-0 row">
                            <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                <form action="{{ route('registrations.view') }}" method="GET" class="d-flex w-100">
                                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm form-control-solid me-2 w-200px" placeholder="بحث برقم التسجيل أو اسم الطالب">
                                    <select name="status" class="form-select form-select-sm form-select-solid me-2 w-150px">
                                        <option value="">كل الحالات</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                        <option value="partially_paid" {{ request('status') == 'partially_paid' ? 'selected' : '' }}>مدفوع جزئياً</option>
                                        <option value="fully_paid" {{ request('status') == 'fully_paid' ? 'selected' : '' }}>مدفوع بالكامل</option>
                                    </select>
                                    <select name="program_id" class="form-select form-select-sm form-select-solid me-2 w-200px">
                                        <option value="">كل البرامج</option>
                                        @foreach($programs as $program)
                                            <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? $program->name_ar : $program->name_en }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary">تصفية</button>
                                    <a href="{{ route('registrations.view') }}" class="btn btn-sm btn-light ms-2">إلغاء</a>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card-body py-4">
                        @include('admin.layout.masterLayouts.error')
                        <table class="table table-striped table-row-bordered gy-5 gs-7">
                            <thead>
                                <tr class="fw-semibold fs-6 text-gray-800 fw-bold text-start">
                                    <th>رقم التسجيل</th>
                                    <th>الطالب</th>
                                    <th>المادة</th>
                                    <th>المجموعة</th>
                                    <th>المبلغ المتبقي</th>
                                    <th>الحالة</th>
                                    <th>التاريخ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($registrations as $registration)
                                    <tr>
                                        <td>{{ $registration->registration_number }}</td>
                                        <td>
                                            <div class="fw-bold">{{ $registration->student?->name }}</div>
                                            <div class="text-muted small">{{ $registration->student?->email }}</div>
                                        </td>
                                        <td>{{ app()->getLocale() == 'ar' ? $registration->subject?->name_ar : $registration->subject?->name_en }}</td>
                                        <td>{{ $registration->group?->name ?? '-' }}</td>
                                        <td>{{ number_format($registration->remaining_amount, 2) }}</td>
                                        <td>
                                            @if($registration->status === 'fully_paid')
                                                <span class="badge badge-light-success">مدفوع بالكامل</span>
                                            @elseif($registration->status === 'partially_paid')
                                                <span class="badge badge-light-warning">مدفوع جزئياً</span>
                                            @else
                                                <span class="badge badge-light-secondary">قيد الانتظار</span>
                                            @endif
                                        </td>
                                        <td>{{ $registration->created_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">لا توجد تسجيلات</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="mt-4">
                            {{ $registrations->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
