@extends('layouts.student')

@section('title', 'My Registrations | FULL MARK ACADEMY')

@section('content')
  <h1 class="h3 fw-bold mb-4" style="color: var(--text-primary);" data-en="My Registrations" data-ar="طلباتي">My Registrations</h1>

  <div class="d-flex gap-2 mb-4">
    @foreach (['all' => 'All / الكل', 'active' => 'Active / نشط', 'pending' => 'Pending / معلق', 'cancelled' => 'Cancelled / مرفوض'] as $key => $label)
      <a href="{{ route('student.registrations', ['status' => $key]) }}"
         class="btn btn-sm {{ request('status', 'all') === $key ? 'btn-luxury' : 'btn-outline-secondary' }}">{{ $label }}</a>
    @endforeach
  </div>

  <div class="glass-panel rounded-4 p-4">
    <table class="table align-middle mb-0">
      <thead>
        <tr class="text-muted text-uppercase fs-7">
          <th>#</th>
          <th data-en="Subject" data-ar="المادة">Subject</th>
          <th data-en="Total" data-ar="الكلي">Total</th>
          <th data-en="Paid" data-ar="المدفوع">Paid</th>
          <th data-en="Remaining" data-ar="المتبقي">Remaining</th>
          <th data-en="Status" data-ar="الحالة">Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach ($registrations as $reg)
          <tr>
            <td>{{ $reg->registration_number }}</td>
            <td>{{ $reg->subject->name }} / {{ $reg->subject->program->title }}</td>
            <td>{{ number_format($reg->total_fee, 2) }}</td>
            <td>{{ number_format($reg->amount_paid, 2) }}</td>
            <td>{{ number_format($reg->remaining_amount, 2) }}</td>
            <td>
              <span class="badge {{ match($reg->registration_status) { 'active' => 'bg-success', 'cancelled' => 'bg-danger', default => 'bg-warning' } }}">
                {{ $reg->registration_status }}
              </span>
            </td>
            <td><a href="{{ route('student.registrations.show', $reg) }}" class="btn btn-sm btn-outline-primary" data-en="Details" data-ar="تفاصيل">Details</a></td>
          </tr>
        @endforeach
      </tbody>
    </table>
    {{ $registrations->links() }}
  </div>
@endsection
