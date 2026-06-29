@extends('layouts.admin')

@section('title', __('app.payments'))

@php($pageTitle = __('app.payments'))

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title"><h2>{{ __('app.payments') }}</h2></div>
        </div>
        <div class="card-body py-4">
            <form method="GET" class="row g-3 mb-5">
                <div class="col-md-3">
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control" placeholder="{{ __('app.start_date') }}">
                </div>
                <div class="col-md-3">
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control" placeholder="{{ __('app.end_date') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">{{ __('app.status') }}</option>
                        @foreach (['pending', 'confirmed', 'rejected'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="program_id" class="form-select">
                        <option value="">{{ __('app.program') }}</option>
                        @foreach ($programs as $program)
                            <option value="{{ $program->id }}" @selected(request('program_id') == $program->id)>{{ $program->title_ar }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">{{ __('app.search') ?? 'Filter' }}</button>
                </div>
            </form>

            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th>#</th>
                        <th>{{ __('app.student') }}</th>
                        <th>{{ __('app.amount') }}</th>
                        <th>{{ __('app.payment_methods') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th>{{ __('app.created_at') ?? 'Date' }}</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @foreach ($payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_number }}</td>
                            <td>{{ $payment->student->full_name_en }}</td>
                            <td>{{ number_format($payment->total_amount, 2) }}</td>
                            <td>{{ $payment->paymentMethod->name }}</td>
                            <td>
                                <span class="badge {{ match($payment->status) { 'confirmed' => 'badge-light-success', 'rejected' => 'badge-light-danger', default => 'badge-light-warning' } }}">
                                    {{ $payment->status }}
                                </span>
                            </td>
                            <td>{{ $payment->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $payments->links() }}
        </div>
    </div>
@endsection
