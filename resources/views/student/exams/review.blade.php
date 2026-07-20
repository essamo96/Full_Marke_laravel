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

    <div class="mt-4 d-flex justify-content-center gap-4 no-print">
      <button type="button" class="btn btn-glass" onclick="window.print()">
        <i class="bi bi-printer-fill me-1"></i> طباعة / تحميل النتيجة
      </button>
      <a href="{{ route('student.results.index') }}" class="btn btn-gold">
        <i class="bi bi-arrow-right me-1"></i> رجوع للنتائج
      </a>
    </div>
  </div>

  @php($orderedAnswers = $grade->answers->sortBy(fn($a) => $a->question?->sort_order ?? 0)->values())

  @forelse($orderedAnswers as $index => $answer)
    <div class="glass-panel rounded-4 p-4 p-md-5 mb-4">
      <div class="d-flex justify-content-between align-items-start mb-4">
        <h5 class="text-gold fw-bold m-0">سؤال {{ $index + 1 }}</h5>
        <span class="badge {{ $answer->is_correct === true ? 'bg-success' : ($answer->is_correct === false ? 'bg-danger' : 'bg-secondary') }} text-white">
          {{ $answer->points_earned !== null ? $answer->points_earned : '؟' }} / {{ $answer->question?->points }}
        </span>
      </div>

      <div class="text-white fs-5 lh-lg mb-4 content-area">
        {!! $answer->question?->content !!}
      </div>

      @if($answer->question?->type === 'essay')
        <div class="rounded-3 p-3 mb-2" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
          <div class="text-white opacity-50 fs-8 mb-1">إجابتك:</div>
          <div class="text-white">{{ $answer->essay_answer ?: 'لم تُجب' }}</div>
        </div>
        @if($answer->points_earned === null)
          <div class="text-warning fs-7"><i class="bi bi-hourglass-split me-1"></i> بانتظار التصحيح اليدوي من المدرّس</div>
        @endif
      @else
        <div class="d-flex flex-column gap-2">
          @foreach($answer->question?->options ?? [] as $option)
            @php
              $isSelected = $option->id === $answer->selected_option_id;
              $isCorrectOpt = $option->is_correct;
            @endphp
            <div class="d-flex align-items-center gap-2 p-3 rounded-3 border"
                 style="{{ $isCorrectOpt ? 'background: rgba(40,167,69,0.15); border-color: rgba(40,167,69,0.4) !important;' : ($isSelected ? 'background: rgba(220,53,69,0.15); border-color: rgba(220,53,69,0.4) !important;' : 'border-color: rgba(255,255,255,0.08) !important;') }}">
              @if($isCorrectOpt) <i class="bi bi-check-circle-fill text-success"></i>
              @elseif($isSelected) <i class="bi bi-x-circle-fill text-danger"></i>
              @else <i class="bi bi-circle text-white opacity-25"></i> @endif
              <span class="text-white">{{ $option->option_text }}</span>
              @if($isSelected) <span class="fs-8 text-white opacity-50 ms-auto">(إجابتك)</span> @endif
            </div>
          @endforeach
        </div>
      @endif
    </div>
  @empty
    <div class="glass-panel rounded-4 p-5 text-center text-white opacity-75">
      لا تتوفر تفاصيل الإجابات لهذا التقديم.
    </div>
  @endforelse

</div>
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
