@extends('admin.layout.mainLayouts.master')
@section('title', 'تفاصيل الطالب')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route($active_menu . '.view') }}" class="text-muted text-hover-primary">@lang('app.' . $active_menu)</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">تفاصيل الطالب</li>
@endsection

@section('page-content')
<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <h3>{{ app()->getLocale() == 'ar' ? $student->full_name_ar : $student->full_name_en }}</h3>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>@lang('app.email'):</strong> {{ $student->email }}</p>
                <p><strong>@lang('app.phone'):</strong> {{ $student->phone }}</p>
                <p><strong>الهوية:</strong> {{ $student->national_id }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>الفرع:</strong> {{ $student->branch ? (app()->getLocale() == 'ar' ? $student->branch->name_ar : $student->branch->name_en) : '-' }}</p>
                <p><strong>الجنس:</strong> {{ $student->gender == 'M' ? 'ذكر' : 'أنثى' }}</p>
                <p><strong>تاريخ الميلاد:</strong> {{ $student->date_of_birth ? $student->date_of_birth->format('Y-m-d') : '-' }}</p>
            </div>
        </div>
        </div>

        <h4 class="mt-8 mb-4 border-bottom pb-2">التسجيلات الدراسية</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="bg-light">
                    <tr>
                        <th>رقم التسجيل</th>
                        <th>المادة</th>
                        <th>الرسوم المقررة</th>
                        <th>المبلغ المدفوع</th>
                        <th>المتبقي</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($student->registrations as $reg)
                        <tr>
                            <td>{{ $reg->registration_number ?? $reg->id }}</td>
                            <td>{{ $reg->subject ? (app()->getLocale() == 'ar' ? $reg->subject->name_ar : $reg->subject->name_en) : '-' }}</td>
                            <td>{{ number_format($reg->fee_snapshot, 2) }}</td>
                            <td>{{ number_format($reg->amount_paid, 2) }}</td>
                            <td class="text-danger fw-bold">{{ number_format($reg->fee_snapshot - $reg->amount_paid, 2) }}</td>
                            <td>
                                @if($reg->status === 'fully_paid')
                                    <span class="badge badge-success">مدفوع بالكامل</span>
                                @elseif($reg->status === 'partially_paid')
                                    <span class="badge badge-info">دفع جزئي</span>
                                @else
                                    <span class="badge badge-warning">معلق</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">لا توجد تسجيلات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <h4 class="mt-8 mb-4 border-bottom pb-2">الدفعات المالية</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="bg-light">
                    <tr>
                        <th>رقم الدفعة</th>
                        <th>المبلغ</th>
                        <th>طريقة الدفع</th>
                        <th>التاريخ</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($student->payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_number ?? $payment->id }}</td>
                            <td>{{ number_format($payment->amount, 2) }}</td>
                            <td>{{ $payment->paymentMethod ? (app()->getLocale() == 'ar' ? $payment->paymentMethod->name_ar : $payment->paymentMethod->name_en) : 'بنكي' }}</td>
                            <td>{{ $payment->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                @if($payment->status === 'confirmed')
                                    <span class="badge badge-success">مؤكد</span>
                                @elseif($payment->status === 'rejected')
                                    <span class="badge badge-danger">مرفوض</span>
                                @else
                                    <span class="badge badge-warning">معلق</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">لا توجد دفعات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            <a href="{{ route('students.view') }}" class="btn btn-secondary">رجوع</a>
        </div>
    </div>
</div>
@endsection
