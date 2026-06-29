@extends('layouts.student')

@section('title', 'Checkout | FULL MARK ACADEMY')

@section('content')
  <h1 class="h3 fw-bold mb-4" style="color: var(--text-primary);" data-en="Complete Payment" data-ar="إتمام الدفع">Complete Payment</h1>

  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="glass-panel rounded-4 p-4 mb-4">
    <h5 class="fw-bold mb-3" style="color: var(--text-primary);" data-en="Order Summary" data-ar="ملخص الطلب">Order Summary</h5>
    <ul class="list-unstyled mb-3">
      @foreach ($items as $item)
        <li class="d-flex justify-content-between mb-1">
          <span>{{ $item->subject->name }}</span>
          <span>{{ number_format($item->subject->fee, 2) }}</span>
        </li>
      @endforeach
    </ul>
    <div class="d-flex justify-content-between fw-bold">
      <span data-en="Total" data-ar="الإجمالي">Total</span>
      <span>{{ number_format($totalFee, 2) }}</span>
    </div>
    <div class="d-flex justify-content-between text-gold fw-bold">
      <span data-en="Minimum Required" data-ar="الحد الأدنى">Minimum Required</span>
      <span>{{ number_format($totalMinPayment, 2) }}</span>
    </div>
  </div>

  <div class="glass-panel rounded-4 p-4">
    <form method="POST" action="{{ route('student.checkout.store') }}" enctype="multipart/form-data">
      @csrf
      @foreach ($items as $item)
        <input type="hidden" name="cart_item_ids[]" value="{{ $item->id }}">
      @endforeach

      <div class="mb-4">
        <label class="form-label fw-bold" data-en="Amount Paid" data-ar="المبلغ المدفوع">Amount Paid</label>
        <input type="number" step="0.01" min="{{ $totalMinPayment }}" max="{{ $totalFee }}" name="amount" class="form-control" required>
        <div class="form-text">{{ __('app.min_payment') }}: {{ number_format($totalMinPayment, 2) }} — {{ __('app.total_fee') }}: {{ number_format($totalFee, 2) }}</div>
      </div>

      <div class="mb-4">
        <label class="form-label fw-bold" data-en="Payment Method" data-ar="طريقة الدفع">Payment Method</label>
        <select name="payment_method_id" class="form-select" required onchange="document.querySelectorAll('.pm-details').forEach(e=>e.classList.add('d-none')); document.getElementById('pm-'+this.value)?.classList.remove('d-none');">
          <option value="">-- {{ __('app.payment_methods') }} --</option>
          @foreach ($paymentMethods as $method)
            <option value="{{ $method->id }}">{{ $method->name }}</option>
          @endforeach
        </select>
        @foreach ($paymentMethods as $method)
          <div id="pm-{{ $method->id }}" class="pm-details d-none mt-2 p-3 rounded" style="background: var(--bg-secondary);">{{ $method->details }}</div>
        @endforeach
      </div>

      <div class="mb-4">
        <label class="form-label fw-bold" data-en="Payment Receipt" data-ar="إشعار الدفع">Payment Receipt</label>
        <input type="file" name="receipt" class="form-control" accept="image/*,.pdf" required>
        <div class="form-text" data-en="PNG, JPG, PDF — up to 5MB" data-ar="PNG, JPG, PDF — حتى 5MB">PNG, JPG, PDF — up to 5MB</div>
      </div>

      <div class="mb-4">
        <label class="form-label" data-en="Notes (optional)" data-ar="ملاحظات (اختياري)">Notes (optional)</label>
        <textarea name="notes" class="form-control" rows="2"></textarea>
      </div>

      <button type="submit" class="btn btn-luxury w-100 py-3" data-en="Confirm & Submit" data-ar="تأكيد وإرسال الطلب">Confirm &amp; Submit</button>
    </form>
  </div>
@endsection
