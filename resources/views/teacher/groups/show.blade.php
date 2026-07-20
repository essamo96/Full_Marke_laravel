@extends('layouts.teacher')

@section('title', $group->name . ' | FULL MARK ACADEMY')
@section('page_title_en', $group->name)
@section('page_title_ar', $group->name)

@section('content')

  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h1 class="h3 fw-bold mb-0" style="color: var(--text-primary);">{{ $group->name }}</h1>
    <div class="d-flex gap-2 flex-wrap">
      <a href="{{ route('teacher.attendance.show', $group) }}" class="btn btn-luxury btn-sm" data-en="Take Attendance" data-ar="أخذ الحضور">أخذ الحضور</a>
      <a href="{{ route('teacher.exams.create', ['group_id' => $group->id]) }}" class="btn btn-glass btn-sm" data-en="Schedule Exam" data-ar="جدولة امتحان">جدولة امتحان</a>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="text-muted fs-7 mb-4">
    {{ $group->subject->name ?? '' }} — {{ $group->subject->program->title ?? '' }}
    &middot; {{ $group->days ? implode(', ', (array) $group->days) : '-' }}
    @if($group->start_time) — {{ $group->start_time }} - {{ $group->end_time }} @endif
  </div>

  <div class="row g-4">
    <!-- Roster -->
    <div class="col-xl-7">
      <h3 class="h5 fw-bold mb-3" style="color: var(--text-primary);" data-en="Students" data-ar="الطلاب">الطلاب</h3>
      <div class="glass-panel rounded-4 p-0 overflow-hidden">
        <div class="table-responsive">
          <table class="table table-borderless text-white align-middle mb-0">
            <thead>
              <tr class="text-muted text-uppercase fs-7">
                <th data-en="Student" data-ar="الطالب">Student</th>
                <th data-en="Phone" data-ar="الهاتف">Phone</th>
                <th data-en="Status" data-ar="الحالة">Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse($roster as $reg)
                <tr>
                  <td>{{ $reg->student?->full_name_ar ?? $reg->student?->full_name_en }}</td>
                  <td>{{ $reg->student?->phone }}</td>
                  <td><span class="badge bg-gold text-dark">{{ $reg->status }}</span></td>
                </tr>
              @empty
                <tr><td colspan="3" class="text-center text-muted py-4" data-en="No students yet." data-ar="لا يوجد طلاب بعد.">لا يوجد طلاب بعد.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Group Notes -->
    <div class="col-xl-5">
      <h3 class="h5 fw-bold mb-3" style="color: var(--text-primary);" data-en="Group Notes" data-ar="ملاحظات المجموعة">ملاحظات المجموعة</h3>
      <div class="glass-panel rounded-4 p-4 mb-3">
        <form method="POST" action="{{ route('teacher.group-notes.store', $group) }}">
          @csrf
          <div class="mb-3">
            <input type="text" name="title" class="form-control" placeholder="العنوان" required maxlength="255">
          </div>
          <div class="mb-3">
            <textarea name="content" class="form-control" rows="3" placeholder="نص الملاحظة" required></textarea>
          </div>
          <button type="submit" class="btn btn-luxury btn-sm" data-en="Post Note" data-ar="نشر الملاحظة">نشر الملاحظة</button>
        </form>
      </div>

      <div class="d-flex flex-column gap-3">
        @forelse($notes as $note)
          <div class="glass-panel rounded-4 p-3" style="border: 1px solid var(--separator-color);">
            <div class="fw-bold fs-7 mb-1" style="color: var(--accent-color);">{{ $note->title }}</div>
            <div class="text-sm" style="color: var(--text-primary);">{{ $note->content }}</div>
            <div class="text-muted fs-7 mt-2">{{ $note->created_at->diffForHumans() }}</div>
          </div>
        @empty
          <div class="text-muted text-center py-4" data-en="No notes yet." data-ar="لا توجد ملاحظات بعد.">لا توجد ملاحظات بعد.</div>
        @endforelse
      </div>
    </div>
  </div>

@endsection
