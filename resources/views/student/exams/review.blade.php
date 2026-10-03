@extends('layouts.student')

@section('title', 'نتيجة الامتحان | ' . ($grade->exam_name ?? ''))
@section('page_title_en', 'Exam Review')
@section('page_title_ar', 'مراجعة الامتحان')

@section('content')
<div class="fade-in-up" id="examReviewPrintArea">

  @if (session('success'))
    <div class="alert alert-success no-print">{{ session('success') }}</div>
  @endif

  <div class="glass-panel rounded-4 p-4 p-md-5 mb-4 text-center">
    <h2 class="text-white fw-bold mb-2">{{ $grade->exam_name }}</h2>
    <p class="text-white opacity-75 mb-4">{{ $grade->group->name ?? '' }}</p>

    <div class="d-inline-flex align-items-baseline gap-2 px-5 py-3 rounded-4" style="background: rgba(197,168,128,0.1);">
      <span class="fs-1 fw-bold {{ ($grade->score / max($grade->max_score, 1)) >= 0.5 ? 'text-success' : 'text-danger' }}">{{ $grade->score }}</span>
      <span class="text-white opacity-50 fs-4">/ {{ $grade->max_score }}</span>
    </div>

    @if($grade->auto_submitted)
      <div class="alert alert-warning mt-4 mb-0">{{ $grade->notes }}</div>
    @endif

    @if($grade->admin_approved_at)
      <div class="mt-3"><span class="badge bg-success">النتيجة معتمدة</span></div>
    @elseif($grade->teacher_reviewed_at)
      <div class="mt-3"><span class="badge bg-warning text-dark">بانتظار اعتماد الإدارة</span></div>
    @endif

    <div class="mt-4 d-flex justify-content-center gap-3 flex-wrap no-print">
      @if($canReview)
        <a href="{{ route('student.results.pdf', $grade) }}" class="btn btn-glass">
          <i class="bi bi-file-earmark-pdf-fill me-1"></i> تحميل PDF
        </a>
        <button type="button" class="btn btn-outline-light" onclick="toggleReviewSection()">
          <i class="bi bi-search me-1"></i> إظهار / إخفاء المراجعة
        </button>
      @endif
      <a href="{{ route('student.results.index') }}" class="btn btn-gold">
        <i class="bi bi-arrow-right me-1"></i> رجوع للنتائج
      </a>
    </div>
  </div>

  @if($canReview)
    <div id="mistakesReviewSection">
      <div class="glass-panel rounded-4 p-3 mb-4 no-print">
        <div class="text-white fw-bold mb-1"><i class="bi bi-list-check me-2"></i>مراجعة الإجابات والأخطاء</div>
        <div class="text-white opacity-75 fs-7">الإجابة الصحيحة بالأخضر، إجابتك الخاطئة بالأحمر.</div>
      </div>
      @include('partials.exam-answers-review', ['grade' => $grade, 'showCorrect' => true, 'compact' => false])
    </div>
  @else
    <div class="glass-panel rounded-4 p-5 text-center text-white opacity-75">
      <i class="bi bi-lock-fill fs-1 d-block mb-3"></i>
      مراجعة تفاصيل الإجابات غير مفعّلة لهذا الامتحان من قبل الإدارة. يمكنك الاطلاع على الدرجة النهائية فقط.
    </div>
  @endif

</div>
@push('scripts')
<script>
    if (window.history && window.history.pushState) {
        window.history.pushState('forward', null, window.location.href);
        window.addEventListener('popstate', function () {
            window.history.pushState('forward', null, window.location.href);
        });
    }

    function toggleReviewSection() {
        const section = document.getElementById('mistakesReviewSection');
        if (!section) return;
        section.style.display = (section.style.display === 'none') ? 'block' : 'none';
        if (section.style.display !== 'none') {
            section.scrollIntoView({ behavior: 'smooth' });
        }
    }
</script>
@endpush
@endsection

@push('styles')
<style>
  .text-gold { color: var(--accent-color); }
  .btn-gold { background: var(--accent-color); color: #000; border: none; }
  .btn-gold:hover { background: #d4af37; color: #000; }
  .content-area img { max-width: 100%; height: auto; border-radius: 0.5rem; }
  @media print {
    body * { visibility: hidden; }
    #examReviewPrintArea, #examReviewPrintArea * { visibility: visible; }
    #examReviewPrintArea { position: absolute; inset: 0; width: 100%; color: #000 !important; background: #fff !important; }
    #examReviewPrintArea .glass-panel { background: #fff !important; border: 1px solid #ccc !important; box-shadow: none !important; }
    #examReviewPrintArea * { color: #000 !important; }
    .no-print { display: none !important; }
  }
</style>
@endpush
