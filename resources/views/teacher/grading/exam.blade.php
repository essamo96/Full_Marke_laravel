@extends('layouts.teacher')

@section('title', $exam->title . ' | FULL MARK ACADEMY')
@section('page_title_en', 'Grading')
@section('page_title_ar', 'التصحيح')

@section('content')

  <h1 class="h3 fw-bold mb-1" style="color: var(--text-primary);">{{ $exam->title }}</h1>
  <p class="text-muted mb-4">{{ $exam->group->name ?? '' }} — {{ $exam->subject->name ?? '' }}</p>

  <div class="glass-panel rounded-4 p-0 overflow-hidden">
    <div class="table-responsive">
      <table class="table table-borderless text-white align-middle mb-0">
        <thead>
          <tr class="text-muted text-uppercase fs-7">
            <th data-en="Student" data-ar="الطالب">Student</th>
            <th data-en="Status" data-ar="الحالة">Status</th>
            <th data-en="Score" data-ar="العلامة">Score</th>
            <th data-en="Exits" data-ar="مرات الخروج">Exits</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse($students as $student)
            @php($grade = $grades->get($student->id))
            <tr>
              <td>{{ $student->full_name_ar ?? $student->full_name_en }}</td>
              <td>
                @if($grade)
                  <span class="badge bg-success">تم التقديم</span>
                @else
                  <span class="badge bg-secondary">لم يقدّم بعد</span>
                @endif
              </td>
              <td>{{ $grade ? $grade->score . ' / ' . $grade->max_score : '-' }}</td>
              <td>
                @if($grade)
                  @php($exits = $grade->tab_switch_count + $grade->fullscreen_exit_count)
                  @if($exits > 0)
                    <span class="badge {{ $exits >= 3 ? 'bg-danger' : 'bg-warning text-dark' }}">
                      <i class="bi bi-eye-fill me-1"></i>{{ $exits }}
                    </span>
                  @else
                    <span class="text-muted fs-7">0</span>
                  @endif
                  @if($grade->auto_submitted)
                    <span class="badge bg-danger ms-1" title="تسليم تلقائي بسبب مخالفة">تلقائي</span>
                  @endif
                @endif
              </td>
              <td>
                @if($grade)
                  <a href="{{ route('teacher.grading.show', $grade) }}" class="btn btn-sm btn-outline-primary" data-en="View Answers" data-ar="عرض الإجابات">عرض الإجابات</a>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-muted py-4" data-en="No students in this group." data-ar="لا يوجد طلاب في هذه المجموعة.">لا يوجد طلاب في هذه المجموعة.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

@endsection
