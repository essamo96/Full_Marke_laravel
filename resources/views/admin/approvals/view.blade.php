@extends('layouts.admin')

@section('title', __('app.pending_approvals'))

@php($pageTitle = __('app.pending_approvals'))

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title"><h2>{{ __('app.pending_approvals') }}</h2></div>
        </div>
        <div class="card-body py-4">
            @if ($payments->isEmpty())
                <div class="text-center text-muted py-10">{{ __('app.no_pending_requests') }}</div>
            @endif

            @foreach ($payments as $payment)
                <div class="border rounded p-5 mb-4">
                    <div class="d-flex justify-content-between flex-wrap gap-3 mb-3">
                        <div>
                            <div class="fw-bold fs-5">{{ $payment->student->full_name_en }} ({{ $payment->student->full_name_ar }})</div>
                            <div class="text-muted fs-7">{{ $payment->payment_number }} — {{ $payment->created_at->format('Y-m-d H:i') }}</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold fs-5">{{ number_format($payment->total_amount, 2) }}</div>
                            <div class="text-muted fs-7">{{ $payment->paymentMethod->name }}</div>
                        </div>
                    </div>

                    <table class="table table-sm mb-3">
                        <thead>
                            <tr class="text-muted fs-7 text-uppercase">
                                <th>{{ __('app.subject') }}</th>
                                <th>{{ __('app.total_fee') }}</th>
                                <th>{{ __('app.amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payment->items as $item)
                                <tr>
                                    <td>{{ $item->registration->subject->name_ar }}</td>
                                    <td>{{ number_format($item->registration->total_fee, 2) }}</td>
                                    <td>{{ number_format($item->allocated_amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <a href="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('payments.receipt', now()->addMinutes(30), ['payment' => $payment->id]) }}" target="_blank" class="btn btn-sm btn-light-info">
                            <i class="ki-duotone ki-eye fs-3"></i> {{ __('app.view_receipt') }}
                        </a>

                        <div class="d-flex gap-2">
                            <form action="{{ route('approvals.confirm') }}" method="POST">
                                @csrf
                                <input type="hidden" name="id" value="{{ \Illuminate\Support\Facades\Crypt::encrypt($payment->id) }}">
                                <button type="submit" class="btn btn-sm btn-success">{{ __('app.confirm') }}</button>
                            </form>

                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $payment->id }}">
                                {{ __('app.reject') }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="rejectModal{{ $payment->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('approvals.reject') }}" method="POST">
                                @csrf
                                <input type="hidden" name="id" value="{{ \Illuminate\Support\Facades\Crypt::encrypt($payment->id) }}">
                                <div class="modal-header">
                                    <h5 class="modal-title">{{ __('app.reject') }} — {{ $payment->payment_number }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <label class="form-label required">{{ __('app.rejection_reason') }}</label>
                                    <textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                                    <button type="submit" class="btn btn-danger">{{ __('app.reject') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach

            {{ $payments->links() }}
        </div>
    </div>
@endsection
