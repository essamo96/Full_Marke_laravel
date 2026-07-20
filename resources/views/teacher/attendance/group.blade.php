@extends('layouts.teacher')

@section('title', 'Attendance | ' . $group->name)
@section('page_title_en', 'Attendance')
@section('page_title_ar', 'الحضور والغياب')

@section('content')

  <h1 class="h3 fw-bold mb-4" style="color: var(--text-primary);">{{ $group->name }} — <span data-en="Attendance" data-ar="الحضور والغياب">الحضور والغياب</span></h1>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <form method="GET" action="{{ route('teacher.attendance.show', $group) }}" class="d-flex gap-2 align-items-center mb-4">
    <input type="date" name="date" value="{{ $date }}" class="form-control" style="max-width: 220px;" onchange="this.form.submit()">
  </form>

  <form method="POST" action="{{ route('teacher.attendance.store', $group) }}">
    @csrf
    <input type="hidden" name="date" value="{{ $date }}">

    <div class="glass-panel rounded-4 p-0 overflow-hidden">
      <div class="table-responsive">
        <table class="table table-borderless text-white align-middle mb-0">
          <thead>
            <tr class="text-muted text-uppercase fs-7">
              <th data-en="Student" data-ar="الطالب">Student</th>
              <th data-en="Present" data-ar="حاضر">حاضر</th>
              <th data-en="Absent" data-ar="غائب">غائب</th>
              <th data-en="Late" data-ar="متأخر">متأخر</th>
              <th data-en="Excused" data-ar="معذور">معذور</th>
            </tr>
          </thead>
          <tbody>
            @forelse($roster as $reg)
              @php($current = $existing->get($reg->student_id)?->status ?? 'present')
              <tr>
                <td>{{ $reg->student?->full_name_ar ?? $reg->student?->full_name_en }}</td>
                @foreach(['present' => 'حاضر', 'absent' => 'غائب', 'late' => 'متأخر', 'excused' => 'معذور'] as $status => $label)
                  <td>
                    <input type="radio" name="records[{{ $reg->student_id }}]" value="{{ $status }}" {{ $current === $status ? 'checked' : '' }}>
                  </td>
                @endforeach
              </tr>
            @empty
              <tr><td colspan="5" class="text-center text-muted py-4" data-en="No students in this group." data-ar="لا يوجد طلاب في هذه المجموعة.">لا يوجد طلاب في هذه المجموعة.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    @if($roster->isNotEmpty())
      <button type="submit" class="btn btn-luxury mt-4" data-en="Save Attendance" data-ar="حفظ الحضور">حفظ الحضور</button>
    @endif
  </form>

@endsection
