@extends('layouts.student')

@section('title', 'My Cart | FULL MARK ACADEMY')
@section('page_title_en', 'My Cart')
@section('page_title_ar', 'سلة التسجيل')

@section('content')
  <h1 class="h3 fw-bold mb-4" style="color: var(--text-primary);" data-en="My Cart" data-ar="سلة التسجيل">My Cart</h1>

  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if ($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
  @endif

  @if ($items->isEmpty())
    <div class="glass-panel rounded-4 p-5 text-center opacity-75" data-en="Your cart is empty." data-ar="السلة فارغة حالياً.">Your cart is empty.</div>
  @else
    <div class="glass-panel rounded-4 p-4 mb-4">
      <table class="table align-middle mb-0">
        <thead>
          <tr class="text-muted text-uppercase fs-7">
            <th data-en="Subject" data-ar="المادة">Subject</th>
            <th data-en="Group" data-ar="المجموعة">Group</th>
            <th data-en="Fee" data-ar="الرسوم">Fee</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach ($items as $item)
            <tr>
              <td>
                <div class="fw-bold" style="color: var(--text-primary);">{{ $item->subject->name }}</div>
                <div class="text-muted fs-7">{{ $item->subject->program->title }}</div>
              </td>
              <td>
                <form method="POST" action="{{ route('student.cart.update-group', $item) }}" class="d-flex gap-2">
                  @csrf
                  <select name="group_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="" data-en="-- choose a group --" data-ar="-- اختر مجموعة --">-- choose a group --</option>
                    @foreach ($item->subject->groups as $group)
                      <option value="{{ $group->id }}" @selected($item->group_id === $group->id) @disabled(! $group->hasAvailableCapacity())>
                        {{ $group->name }}
                      </option>
                    @endforeach
                  </select>
                </form>
              </td>
              <td>{{ number_format($item->subject->fee, 2) }} <span data-en="(min" data-ar="(حد أدنى">(min</span> {{ number_format($item->subject->min_payment ?? $item->subject->fee, 2) }})</td>
              <td class="text-end">
                <form method="POST" action="{{ route('student.cart.destroy', $item) }}">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="glass-panel rounded-4 p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
      <div>
        <div class="text-muted fs-7" data-en="Full Total" data-ar="الإجمالي الكامل">Full Total</div>
        <div class="fs-3 fw-bold" style="color: var(--text-primary);">{{ number_format($totalFee, 2) }}</div>
        <div class="text-muted fs-7" data-en="Minimum Required" data-ar="الحد الأدنى المطلوب">Minimum Required</div>
        <div class="fs-5 fw-bold text-gold">{{ number_format($totalMinPayment, 2) }}</div>
      </div>
      <a href="{{ route('student.checkout') }}" class="btn btn-luxury px-5 py-3" data-en="Proceed to Payment" data-ar="المتابعة للدفع">Proceed to Payment</a>
    </div>
  @endif
@endsection
