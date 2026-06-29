@extends('layouts.admin')

@section('title', __('app.financial_reports'))

@php($pageTitle = __('app.financial_reports'))

@section('content')
    <div class="row g-5 mb-5">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted fs-7 text-uppercase">{{ __('app.total_revenue') }}</div>
                    <div class="fs-2 fw-bold text-success">{{ number_format($totalRevenue, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted fs-7 text-uppercase">{{ __('app.total_outstanding') }}</div>
                    <div class="fs-2 fw-bold text-danger">{{ number_format($totalOutstanding, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-5">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h3 class="card-title">{{ __('app.programs') }}</h3></div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead><tr><th>{{ __('app.program') }}</th><th>{{ __('app.amount') }}</th></tr></thead>
                        <tbody>
                            @foreach ($revenueByProgram as $row)
                                <tr><td>{{ $row->title_ar }}</td><td>{{ number_format($row->revenue, 2) }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h3 class="card-title">{{ __('app.payment_methods') }}</h3></div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead><tr><th>{{ __('app.payment_methods') }}</th><th>{{ __('app.amount') }}</th></tr></thead>
                        <tbody>
                            @foreach ($revenueByMethod as $row)
                                <tr><td>{{ $row->name_ar }}</td><td>{{ number_format($row->revenue, 2) }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h3 class="card-title">{{ __('app.financial_reports') }} (6m)</h3></div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead><tr><th>{{ __('app.month') ?? 'Month' }}</th><th>{{ __('app.amount') }}</th></tr></thead>
                        <tbody>
                            @foreach ($monthlyRevenue as $row)
                                <tr><td>{{ $row->month }}</td><td>{{ number_format($row->revenue, 2) }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h3 class="card-title">{{ __('app.student') }} — {{ __('app.remaining') }}</h3></div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead><tr><th>{{ __('app.student') }}</th><th>{{ __('app.subject') }}</th><th>{{ __('app.remaining') }}</th></tr></thead>
                        <tbody>
                            @foreach ($debtors->take(20) as $reg)
                                <tr>
                                    <td>{{ $reg->student->full_name_en }}</td>
                                    <td>{{ $reg->subject->name_ar }}</td>
                                    <td>{{ number_format($reg->remaining_amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
