@extends('layouts.admin')

@section('title', __('app.students'))

@php($pageTitle = __('app.students'))

@section('content')
    <div class="card mb-5">
        <div class="card-header border-0 pt-6">
            <div class="card-title"><h2>{{ $student->full_name_en }} / {{ $student->full_name_ar }}</h2></div>
            <div class="card-toolbar">
                <a href="{{ route('students.view') }}" class="btn btn-light">{{ app()->getLocale() === 'ar' ? 'رجوع' : 'Back' }}</a>
            </div>
        </div>
        <div class="card-body py-4">
            <div class="row">
                <div class="col-md-2 text-center mb-4">
                    @if ($student->image)
                        <img src="{{ asset('storage/'.$student->image) }}" class="rounded w-100" alt="{{ $student->full_name_en }}">
                    @else
                        <span class="symbol symbol-100px"><span class="symbol-label bg-light-primary text-primary fw-bold fs-1">{{ mb_substr($student->full_name_en, 0, 1) }}</span></span>
                    @endif
                </div>
                <div class="col-md-10">
                    <table class="table table-row-dashed">
                        <tr><th>{{ __('app.email') }}</th><td>{{ $student->email }}</td></tr>
                        <tr><th>{{ __('app.phone') }}</th><td>{{ $student->phone }}</td></tr>
                        <tr><th>{{ __('app.branches') }}</th><td>{{ $student->branch?->name }}</td></tr>
                        <tr><th>{{ __('app.status') }}</th><td>{{ $student->status ? __('app.active') : __('app.inactive') }}</td></tr>
                        <tr><th>{{ app()->getLocale() === 'ar' ? 'العنوان' : 'Address' }}</th><td>{{ $student->address }}</td></tr>
                        @if ($student->guardian)
                            <tr><th>{{ app()->getLocale() === 'ar' ? 'ولي الأمر' : 'Guardian' }}</th><td>{{ $student->guardian->full_name }}</td></tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title"><h2>{{ __('app.registration_status') }}</h2></div>
        </div>
        <div class="card-body py-4">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th>{{ __('app.subjects') }}</th>
                        <th>{{ __('app.registration_status') }}</th>
                        <th>{{ __('app.payment_status') }}</th>
                        <th>{{ __('app.total_fee') }}</th>
                        <th>{{ __('app.amount_paid') }}</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @forelse ($student->registrations as $registration)
                        <tr>
                            <td>{{ $registration->subject?->name }}</td>
                            <td>{{ $registration->registration_status }}</td>
                            <td>{{ $registration->payment_status }}</td>
                            <td>{{ $registration->total_fee }}</td>
                            <td>{{ $registration->amount_paid }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">{{ app()->getLocale() === 'ar' ? 'لا توجد بيانات' : 'No data' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
