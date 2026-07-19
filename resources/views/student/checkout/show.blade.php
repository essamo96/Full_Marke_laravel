@extends('layouts.student')

@section('title', 'Checkout | FULL MARK ACADEMY')
@section('page_title_en', 'Checkout')
@section('page_title_ar', 'إتمام الدفع')

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

    @foreach ($items as $item)
      <div class="border rounded-3 p-3 mb-3" style="background: var(--glass-bg);">
        <div class="d-flex align-items-center gap-3 mb-2">
          @if($item->subject->image)
            <img src="{{ str_starts_with($item->subject->image, 'site/') ? asset($item->subject->image) : asset('storage/' . $item->subject->image) }}"
                 class="rounded-circle" style="width:45px; height:45px; object-fit:cover;" alt="">
          @else
            <div class="rounded-circle bg-primary bg-opacity-20 d-flex align-items-center justify-content-center" style="width:45px;height:45px;">
              <i class="bi bi-book text-primary"></i>
            </div>
          @endif
          <div>
            <div class="fw-bold" style="color: var(--text-primary);">{{ $item->subject->name }}</div>
            <div class="text-muted fs-7">
              <span>Min: {{ number_format($item->subject->min_payment ?? $item->subject->fee, 2) }} JOD</span>
              <span class="ms-2">Total: {{ number_format($item->subject->fee, 2) }} JOD</span>
            </div>
          </div>
        </div>

        {{-- Group Selection --}}
        @if($item->subject->groups->isNotEmpty())
          <div class="mt-2">
            <label class="form-label fs-7 fw-semibold" style="color: var(--text-muted);">
              <i class="bi bi-people-fill me-1"></i>
              <span data-en="Select Group (Optional)" data-ar="اختر المجموعة (اختياري)">Select Group (Optional)</span>
            </label>
            <select name="group_ids[{{ $item->id }}]" class="form-select form-select-sm group-selector"
                    style="background: var(--input-bg); color: var(--text-primary); border-color: var(--input-border);"
                    data-item="{{ $item->id }}">
              <option value="">-- <span data-en="No group / Later" data-ar="بدون مجموعة / لاحقاً">No group / Later</span> --</option>
              @foreach($item->subject->groups as $group)
                <option value="{{ $group->id }}"
                  {{ $item->group_id == $group->id ? 'selected' : '' }}
                  {{ !$group->hasAvailableCapacity() ? 'disabled' : '' }}>
                  {{ $group->name }}
                  @if($group->days) — {{ is_array($group->days) ? implode(', ', $group->days) : $group->days }} @endif
                  @if($group->start_time) | {{ \Carbon\Carbon::parse($group->start_time)->format('h:i A') }}–{{ \Carbon\Carbon::parse($group->end_time)->format('h:i A') }} @endif
                  @if(!$group->hasAvailableCapacity()) (ممتلئة) @endif
                </option>
              @endforeach
            </select>
          </div>
        @else
          {{-- hidden input to preserve item id without group --}}
          <input type="hidden" name="group_ids[{{ $item->id }}]" value="">
        @endif
      </div>
    @endforeach

    <div class="d-flex justify-content-between fw-bold border-top pt-3 mt-2">
      <span data-en="Total Fee" data-ar="إجمالي الرسوم">Total Fee</span>
      <span style="color: var(--accent-color);">{{ number_format($totalFee, 2) }} JOD</span>
    </div>
    <div class="d-flex justify-content-between fw-semibold text-success">
      <span data-en="Minimum Required" data-ar="الحد الأدنى للدفع">Minimum Required</span>
      <span>{{ number_format($totalMinPayment, 2) }} JOD</span>
    </div>
  </div>

  <div class="glass-panel rounded-4 p-4">
    <form method="POST" action="{{ route('student.checkout.store') }}" enctype="multipart/form-data" id="checkoutForm">
      @csrf
      @foreach ($items as $item)
        <input type="hidden" name="cart_item_ids[]" value="{{ $item->id }}">
      @endforeach
      {{-- Group IDs injected dynamically by JS below --}}

      <div class="mb-4">
        <label class="form-label fw-bold" data-en="Amount Paid" data-ar="المبلغ المدفوع">Amount Paid (JOD)</label>
        <input type="number" step="0.01" min="{{ $totalMinPayment }}" max="{{ $totalFee }}"
               name="amount" class="form-control" required
               value="{{ old('amount', $totalMinPayment) }}"
               style="background: var(--input-bg); color: var(--text-primary); border-color: var(--input-border);">
        <div class="form-text" style="color: var(--text-muted);">
          Min: {{ number_format($totalMinPayment, 2) }} JOD — Max: {{ number_format($totalFee, 2) }} JOD
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label fw-bold" data-en="Payment Method" data-ar="طريقة الدفع">Payment Method</label>
        <select name="payment_method_id" class="form-select" required
                style="background: var(--input-bg); color: var(--text-primary); border-color: var(--input-border);"
                onchange="document.querySelectorAll('.pm-details').forEach(e=>e.classList.add('d-none')); document.getElementById('pm-'+this.value)?.classList.remove('d-none');">
          <option value="">-- {{ __('app.payment_methods') }} --</option>
          @foreach ($paymentMethods as $method)
            <option value="{{ $method->id }}">{{ $method->name }}</option>
          @endforeach
        </select>
        @foreach ($paymentMethods as $method)
          <div id="pm-{{ $method->id }}" class="pm-details d-none mt-2 p-3 rounded-3 fs-7"
               style="background: var(--bg-secondary); color: var(--text-secondary);">
               
               @if(!empty($method->credentials) && is_array($method->credentials))
                 <div class="mb-3">
                    <strong class="d-block mb-2" data-en="Payment Credentials:" data-ar="بيانات الاعتماد:">بيانات الاعتماد:</strong>
                    <ul class="list-unstyled ms-3">
                      @foreach($method->credentials as $cred)
                        <li class="mb-1"><span class="fw-bold">{{ $cred['name'] ?? '' }}:</span> <span dir="ltr">{{ $cred['value'] ?? '' }}</span></li>
                      @endforeach
                    </ul>
                 </div>
               @endif

               @if($method->details)
                 <div class="mt-2 text-wrap">{!! $method->details !!}</div>
               @endif
          </div>
        @endforeach
      </div>

      <div class="mb-4">
        <label class="form-label fw-bold" data-en="Payment Receipt" data-ar="إشعار الدفع">Payment Receipt</label>
        <input type="file" name="receipt" class="form-control" accept="image/*,.pdf" required
               style="background: var(--input-bg); color: var(--text-primary); border-color: var(--input-border);">
        <div class="form-text" style="color: var(--text-muted);" data-en="PNG, JPG, PDF — up to 5MB" data-ar="PNG, JPG, PDF — حتى 5MB">PNG, JPG, PDF — up to 5MB</div>
      </div>

      <div class="mb-4">
        <label class="form-label" data-en="Notes (optional)" data-ar="ملاحظات (اختياري)">Notes (optional)</label>
        <textarea name="notes" class="form-control" rows="2"
                  style="background: var(--input-bg); color: var(--text-primary); border-color: var(--input-border);"></textarea>
      </div>

      <button type="submit" class="btn btn-luxury w-100 py-3" data-en="Confirm &amp; Submit" data-ar="تأكيد وإرسال الطلب">Confirm &amp; Submit</button>
    </form>
  </div>
@endsection

@push('scripts')
<script>
// Sync group selectors into checkout form as hidden inputs on submit
document.getElementById('checkoutForm').addEventListener('submit', function() {
  document.querySelectorAll('.group-selector').forEach(function(sel) {
    if (sel.value) {
      const hidden = document.createElement('input');
      hidden.type = 'hidden';
      hidden.name = sel.name;
      hidden.value = sel.value;
      // Already set via sel name attribute group_ids[itemId]
    }
  });
});
</script>
@endpush
