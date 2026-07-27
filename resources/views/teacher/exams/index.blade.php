@extends('layouts.teacher')

@section('title', 'Exams | FULL MARK ACADEMY')
@section('page_title_en', 'Tests Management')
@section('page_title_ar', 'إدارة الامتحانات')

@section('content')

  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h1 class="h3 fw-bold mb-0" style="color: var(--text-primary);" data-en="Exams" data-ar="الامتحانات">الامتحانات</h1>
    <a href="{{ route('teacher.exams.create') }}" class="btn btn-luxury" data-en="New Exam" data-ar="امتحان جديد">امتحان جديد</a>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="glass-panel rounded-4 p-0 overflow-hidden">
    <div class="table-responsive">
      <table class="table table-borderless align-middle mb-0">
        <thead>
          <tr class="text-muted text-uppercase fs-7">
            <th data-en="Title" data-ar="العنوان">Title</th>
            <th data-en="Subject" data-ar="المادة">Subject</th>
            <th data-en="Group" data-ar="المجموعة">Group</th>
            <th data-en="Start" data-ar="البدء">Start</th>
            <th data-en="Status" data-ar="الحالة">Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse($exams as $exam)
            <tr>
              <td>{{ $exam->title }}</td>
              <td>{{ $exam->subject->name ?? '' }}</td>
              <td>{{ $exam->group->name ?? '' }}</td>
              <td>{{ $exam->start_time?->format('Y-m-d H:i') }}</td>
              <td><span class="badge bg-gold text-dark">{{ $exam->status }}</span></td>
              <td><a href="{{ route('teacher.exams.edit', $exam) }}" class="btn btn-sm btn-outline-primary" data-en="Edit" data-ar="تعديل">تعديل</a></td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted py-4" data-en="No exams yet." data-ar="لا توجد امتحانات بعد.">لا توجد امتحانات بعد.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-4">{{ $exams->links() }}</div>

@endsection
