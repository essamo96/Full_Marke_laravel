@extends('layouts.student')

@section('title', 'My Registrations | FULL MARK ACADEMY')
@section('page_title_en', 'My Registrations')
@section('page_title_ar', 'طلباتي')

@section('content')
  <h1 class="h3 fw-bold mb-4" style="color: var(--text-primary);" data-en="My Registrations" data-ar="طلباتي">My Registrations</h1>

  <div class="d-flex gap-2 mb-4 flex-wrap">
    @foreach (['all' => 'All / الكل', 'pending' => 'Pending / معلق', 'partially_paid' => 'Partial / جزئي', 'fully_paid' => 'Paid / مدفوع', 'cancelled' => 'Cancelled / مرفوض'] as $key => $label)
      <a href="{{ route('student.registrations', ['status' => $key]) }}"
         class="btn btn-sm {{ request('status', 'all') === $key ? 'btn-luxury' : 'btn-outline-secondary' }}">{{ $label }}</a>
    @endforeach
  </div>

  <div class="glass-panel rounded-4 p-4">
    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    @if($registrations->isEmpty())
      <div class="text-center py-8 text-muted">
        <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
        <p data-en="No registrations yet." data-ar="لا توجد تسجيلات حتى الآن.">No registrations yet.</p>
        <a href="{{ url('/programs') }}" class="btn btn-luxury btn-sm mt-2" data-en="Browse Programs" data-ar="استعرض البرامج">Browse Programs</a>
      </div>
    @else
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead>
            <tr class="text-muted text-uppercase fs-7">
              <th>#</th>
              <th data-en="Subject / Program" data-ar="المادة / البرنامج">Subject / Program</th>
              <th data-en="Total" data-ar="الكلي">Total</th>
              <th data-en="Paid" data-ar="المدفوع">Paid</th>
              <th data-en="Remaining" data-ar="المتبقي">المتبقي</th>
              <th data-en="Status" data-ar="الحالة">Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach ($registrations as $reg)
              <tr>
                <td class="text-muted fs-7">{{ $reg->registration_number }}</td>
                <td>
                  <div class="fw-bold">{{ $reg->subject?->name }}</div>
                  <div class="text-muted fs-7">{{ $reg->subject?->program?->title }}</div>
                  @if($reg->group)
                    <div class="text-muted fs-7">
                      <i class="bi bi-people-fill me-1"></i>{{ $reg->group->name }}
                      @if($reg->group->days)
                        — {{ implode(', ', (array)$reg->group->days) }}
                      @endif
                    </div>
                  @endif
                </td>
                <td>{{ number_format($reg->fee_snapshot, 2) }} JOD</td>
                <td class="text-success fw-bold">{{ number_format($reg->amount_paid, 2) }} JOD</td>
                <td class="{{ $reg->remaining_amount > 0 ? 'text-danger' : 'text-success' }} fw-bold">
                  {{ number_format($reg->remaining_amount, 2) }} JOD
                </td>
                <td>
                  @php
                    $statusMap = [
                      'pending' => ['label' => 'معلق', 'class' => 'bg-warning text-dark'],
                      'partially_paid' => ['label' => 'جزئي', 'class' => 'bg-info text-white'],
                      'fully_paid' => ['label' => 'مدفوع', 'class' => 'bg-success text-white'],
                      'cancelled' => ['label' => 'ملغي', 'class' => 'bg-danger text-white'],
                    ];
                    $s = $statusMap[$reg->status] ?? ['label' => $reg->status, 'class' => 'bg-secondary text-white'];
                  @endphp
                  <span class="badge {{ $s['class'] }}">{{ $s['label'] }}</span>
                </td>
                <td>
                  <a href="{{ route('student.registrations.show', $reg) }}"
                     class="btn btn-sm btn-outline-primary"
                     data-en="Details" data-ar="تفاصيل">Details</a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="mt-4">
        {{ $registrations->links() }}
      </div>
    @endif
  </div>
@endsection
