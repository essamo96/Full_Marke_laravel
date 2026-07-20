@extends('layouts.student')

@section('title', 'Registration Details | FULL MARK ACADEMY')
@section('page_title_en', 'Registration Details')
@section('page_title_ar', 'تفاصيل التسجيل')

@section('content')
  <h1 class="h3 fw-bold mb-4" style="color: var(--text-primary);">{{ $registration->registration_number }}</h1>

  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if ($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
  @endif

  <div class="glass-panel rounded-4 p-4 mb-4">
    <div class="row">
      <div class="col-md-6">
        <div class="text-muted fs-7" data-en="Subject" data-ar="المادة">Subject</div>
        <div class="fw-bold" style="color: var(--text-primary);">{{ $registration->subject?->name ?? '-' }}</div>
      </div>
      <div class="col-md-6">
        <div class="text-muted fs-7" data-en="Group" data-ar="المجموعة">Group</div>
        <div class="fw-bold" style="color: var(--text-primary);">{{ $registration->group?->name ?? '-' }}</div>
      </div>
    </div>
    <hr>
    <div class="progress mb-2" style="height: 10px;">
      @php($pct = $registration->fee_snapshot > 0 ? min(100, ($registration->amount_paid / $registration->fee_snapshot) * 100) : 0)
      <div class="progress-bar bg-success" style="width: {{ $pct }}%"></div>
    </div>
    <div class="d-flex justify-content-between fs-7">
      <span>{{ __('app.amount_paid') }}: {{ number_format($registration->amount_paid, 2) }} JOD</span>
      <span>{{ __('app.remaining') }}: {{ number_format($registration->remaining_amount, 2) }} JOD</span>
      <span>{{ __('app.total_fee') }}: {{ number_format($registration->fee_snapshot, 2) }} JOD</span>
    </div>
  </div>

  <div class="glass-panel rounded-4 p-4 mb-4">
    <h5 class="fw-bold mb-3" style="color: var(--text-primary);" data-en="Payment History" data-ar="سجل المدفوعات">Payment History</h5>
    <table class="table table-borderless text-white table-sm mb-0">
      <thead><tr class="text-muted"><th>#</th><th data-en="Amount" data-ar="المبلغ">Amount</th><th data-en="Status" data-ar="الحالة">Status</th><th data-en="Date" data-ar="التاريخ">Date</th></tr></thead>
      <tbody>
        @foreach ($registration->paymentItems as $item)
          <tr>
            <td>{{ $item->payment?->payment_number ?? '-' }}</td>
            <td>{{ number_format($item->allocated_amount, 2) }}</td>
            <td>{{ $item->payment?->status ?? '-' }}</td>
            <td>{{ $item->payment?->created_at?->format('Y-m-d') ?? '-' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  @if ($registration->remaining_amount > 0 && $registration->status !== 'cancelled')
    <div class="glass-panel rounded-4 p-4 mb-4">
      <h5 class="fw-bold mb-3" style="color: var(--text-primary);" data-en="Pay Remaining Balance" data-ar="سداد الباقي">Pay Remaining Balance</h5>
      <form method="POST" action="{{ route('student.registrations.pay-remaining', $registration) }}" enctype="multipart/form-data">
        @csrf
        <div class="row g-3">
          <div class="col-md-4">
            <input type="number" step="0.01" min="0.01" max="{{ $registration->remaining_amount }}" name="amount" class="form-control" placeholder="{{ __('app.amount') }}" required>
          </div>
          <div class="col-md-4">
            <select name="payment_method_id" class="form-select" required onchange="document.querySelectorAll('.pm-details-reg').forEach(e=>e.classList.add('d-none')); document.getElementById('pm-reg-'+this.value)?.classList.remove('d-none');">
              <option value="">-- {{ __('app.payment_methods') }} --</option>
              @foreach ($paymentMethods as $method)
                <option value="{{ $method->id }}">{{ $method->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <input type="file" name="receipt" class="form-control" accept="image/*,.pdf" required>
          </div>
        </div>
        
        <div class="row g-3 mt-1">
          <div class="col-12">
            @foreach ($paymentMethods as $method)
              <div id="pm-reg-{{ $method->id }}" class="pm-details-reg d-none mt-2 p-3 rounded-3 fs-7"
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
        </div>
        <button type="submit" class="btn btn-luxury mt-3" data-en="Submit Payment" data-ar="إرسال الدفعة">Submit Payment</button>
      </form>
    </div>
  @endif

  @if ($registration->status === 'fully_paid')
    <div class="glass-panel rounded-4 p-4">
      <h5 class="fw-bold mb-3" style="color: var(--text-primary);" data-en="Learning Resources" data-ar="الموارد التعليمية">Learning Resources</h5>
      @forelse (($registration->subject?->resources ?? collect())->where('is_active', true) as $resource)
        @php
            $isUrl = \Illuminate\Support\Str::startsWith($resource->url, ['http://', 'https://']);
            $resUrl = $isUrl ? $resource->url : route('student.resources');
        @endphp
        <a href="{{ $resUrl }}" target="_blank" class="d-flex align-items-center gap-2 mb-2 text-decoration-none">
          <i class="bi bi-{{ match($resource->type) { 'video' => 'play-circle', 'document' => 'file-earmark-text', 'zoom' => 'camera-video', default => 'link-45deg' } }}"></i>
          <span style="color: var(--text-primary);">{{ $resource->title }}</span>
        </a>
      @empty
        <p class="opacity-75" data-en="No resources available yet." data-ar="لا توجد موارد متاحة حالياً.">No resources available yet.</p>
      @endforelse
    </div>
  @endif
@endsection
