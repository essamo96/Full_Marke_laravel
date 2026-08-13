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
              <td class="d-flex gap-2">
                <a href="{{ route('teacher.exams.edit', $exam) }}" class="btn btn-sm btn-outline-primary" data-en="Edit" data-ar="تعديل">تعديل</a>
                @if($exam->allowsGuests())
                  <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copyGuestLink(this)" data-link="{{ route('guest.exam.enter', $exam) }}" data-en="Copy Guest Link" data-ar="نسخ رابط الضيوف">نسخ رابط الضيوف</button>
                  <button type="button" class="btn btn-sm btn-outline-secondary" onclick="showExamQr(this)" data-url="{{ route('qr.exam', $exam) }}" data-name="{{ $exam->title }}" data-en="QR Code" data-ar="رمز QR">رمز QR</button>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted py-4" data-en="No exams yet." data-ar="لا توجد امتحانات بعد.">لا توجد امتحانات بعد.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-4">{{ $exams->links() }}</div>

  <!-- QR Code preview modal -->
  <div class="modal fade" id="examQrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content glass-panel text-center">
        <div class="modal-header">
          <h5 class="modal-title" id="examQrModalTitle" style="color: var(--text-primary);">QR Code</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body py-8">
          <img id="examQrModalImg" src="" alt="QR Code" class="rounded" style="width: 260px; height: 260px; background: #fff; padding: 10px;">
          <a id="examQrModalDownload" href="" download class="btn btn-luxury d-block mt-4 mx-auto" style="max-width: 220px;">
            <i class="bi bi-download me-1"></i> تحميل
          </a>
        </div>
      </div>
    </div>
  </div>

@endsection

@push('scripts')
<script>
  function copyGuestLink(btn) {
    const link = btn.getAttribute('data-link');
    navigator.clipboard.writeText(link).then(() => {
      const original = btn.textContent;
      btn.textContent = 'تم النسخ!';
      setTimeout(() => { btn.textContent = original; }, 1500);
    });
  }

  function showExamQr(btn) {
    const url = btn.getAttribute('data-url');
    const name = btn.getAttribute('data-name');
    document.getElementById('examQrModalTitle').textContent = name;
    document.getElementById('examQrModalImg').src = url + '?t=' + Date.now();
    const dl = document.getElementById('examQrModalDownload');
    dl.setAttribute('href', url);
    dl.setAttribute('download', name.replace(/\s+/g, '_') + '_qr.svg');
    new bootstrap.Modal(document.getElementById('examQrModal')).show();
  }
</script>
@endpush
