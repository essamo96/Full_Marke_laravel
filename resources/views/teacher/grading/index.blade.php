@extends('layouts.teacher')

@section('title', 'Grading | FULL MARK ACADEMY')
@section('page_title_en', 'Grading')
@section('page_title_ar', 'التصحيح')

@section('content')

  <h1 class="h3 fw-bold mb-4" style="color: var(--text-primary);" data-en="Grading" data-ar="التصحيح">التصحيح</h1>

  <div class="glass-panel rounded-4 p-0 overflow-hidden">
    <div class="table-responsive">
      <table class="table table-borderless text-white align-middle mb-0">
        <thead>
          <tr class="text-muted text-uppercase fs-7">
            <th data-en="Exam" data-ar="الامتحان">Exam</th>
            <th data-en="Group" data-ar="المجموعة">Group</th>
            <th data-en="Submissions" data-ar="عدد التقديمات">Submissions</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse($exams as $exam)
            <tr>
              <td>{{ $exam->title }}</td>
              <td>{{ $exam->group->name ?? '' }}</td>
              <td>{{ $submissionCounts[$exam->id] ?? 0 }}</td>
              <td><a href="{{ route('teacher.grading.exam', $exam) }}" class="btn btn-sm btn-outline-primary" data-en="Review" data-ar="مراجعة">مراجعة</a></td>
            </tr>
          @empty
            <tr><td colspan="4" class="text-center text-muted py-4" data-en="No exams yet." data-ar="لا توجد امتحانات بعد.">لا توجد امتحانات بعد.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

@endsection
