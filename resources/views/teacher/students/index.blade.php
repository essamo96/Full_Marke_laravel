@extends('layouts.teacher')

@section('title', 'My Students | FULL MARK ACADEMY')
@section('page_title_en', 'Students')
@section('page_title_ar', 'الطلاب')

@section('content')

  <h1 class="h3 fw-bold mb-4" style="color: var(--text-primary);" data-en="Students" data-ar="الطلاب">الطلاب</h1>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="row g-4">
    @forelse($registrations as $reg)
      @php($student = $reg->student)
      <div class="col-md-6 col-xl-4">
        <div class="glass-panel rounded-4 p-4 h-100" style="border: 1px solid var(--separator-color);">
          <div class="d-flex align-items-center gap-3 mb-3">
            <img src="{{ $student && $student->image ? asset('storage/'.$student->image) : asset('assets/admin/media/avatars/blank.png') }}"
                 alt="{{ $student->full_name_ar ?? '' }}" class="rounded-circle" style="width: 56px; height: 56px; object-fit: cover; border: 2px solid var(--separator-color);">
            <div>
              <h6 class="fw-bold mb-0" style="color: var(--text-primary);">{{ $student->full_name_ar ?? $student->full_name_en }}</h6>
              <div class="text-muted fs-8">{{ $reg->group->name ?? '' }}</div>
            </div>
          </div>

          <div class="text-muted fs-7 mb-3">
            <i class="bi bi-book me-1"></i>{{ $reg->subject->name ?? '' }}
          </div>

          <div class="d-flex align-items-center justify-content-between mb-3">
            <span class="badge {{ $reg->group_status === 'suspended' ? 'bg-danger' : ($reg->group_status === 'deferred' ? 'bg-warning text-dark' : 'bg-success') }}">
              {{ \App\Models\Registration::groupStatusLabel($reg->group_status) }}
            </span>
            <span class="text-muted fs-8">{{ $student->phone ?? '' }}</span>
          </div>

          <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('teacher.groups.show', $reg->group) }}" class="btn btn-sm btn-outline-primary flex-grow-1" data-en="View Details" data-ar="عرض التفاصيل">عرض التفاصيل</a>
            <button type="button" class="btn btn-sm btn-luxury flex-grow-1" data-bs-toggle="modal" data-bs-target="#noteModal{{ $student->id }}">
              <i class="bi bi-bell-fill me-1"></i> <span data-en="Note / Alert" data-ar="ملاحظة/تنبيه">ملاحظة/تنبيه</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Note/Alert Modal -->
      <div class="modal fade" id="noteModal{{ $student->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content glass-panel">
            <div class="modal-header">
              <h5 class="modal-title">إرسال ملاحظة إلى {{ $student->full_name_ar ?? $student->full_name_en }}</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('teacher.student-notes.store') }}">
              @csrf
              <input type="hidden" name="student_id" value="{{ $student->id }}">
              <div class="modal-body">
                <div class="mb-3">
                  <input type="text" name="title" class="form-control" placeholder="العنوان" required maxlength="255">
                </div>
                <div class="mb-3">
                  <textarea name="content" class="form-control" rows="3" placeholder="نص الملاحظة" required></textarea>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="is_alert" value="1" id="isAlert{{ $student->id }}">
                  <label class="form-check-label text-muted fs-7" for="isAlert{{ $student->id }}">
                    تنبيه مهم (يظهر بلون مختلف على العام)
                  </label>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-glass" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-luxury">إرسال</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    @empty
      <div class="col-12">
        <div class="glass-panel rounded-4 p-5 text-center text-muted" data-en="No students in your groups yet." data-ar="لا يوجد طلاب في مجموعاتك بعد.">لا يوجد طلاب في مجموعاتك بعد.</div>
      </div>
    @endforelse
  </div>

@endsection
