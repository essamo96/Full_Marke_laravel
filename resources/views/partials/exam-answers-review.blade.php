{{-- Shared answer review block for student / teacher / admin modal --}}
@php
  $orderedAnswers = $grade->answers->sortBy(fn ($a) => $a->question?->sort_order ?? 0)->values();
  $showCorrect = $showCorrect ?? true;
  $compact = $compact ?? false;
@endphp

@forelse($orderedAnswers as $index => $answer)
  <div class="{{ $compact ? 'border rounded p-4 mb-4' : 'glass-panel rounded-4 p-4 p-md-5 mb-4' }}" style="{{ $compact ? 'background: var(--bs-gray-100, #f9f9f9);' : '' }}">
    <div class="d-flex justify-content-between align-items-start mb-3 gap-2 flex-wrap">
      <h5 class="fw-bold m-0 {{ $compact ? 'text-gray-800' : 'text-gold' }}">سؤال {{ $index + 1 }}</h5>
      <div class="d-flex align-items-center gap-2">
        @if($answer->is_correct === true)
          <span class="badge bg-success">صحيح</span>
        @elseif($answer->is_correct === false)
          <span class="badge bg-danger">خاطئ</span>
        @else
          <span class="badge bg-secondary">مقال / قيد التصحيح</span>
        @endif
        <span class="badge {{ $compact ? 'badge-light-primary' : 'bg-gold text-dark' }}">
          {{ $answer->points_earned !== null ? $answer->points_earned : '؟' }} / {{ $answer->question?->points }}
        </span>
      </div>
    </div>

    <div class="fs-5 lh-lg mb-4 content-area {{ $compact ? 'text-gray-800' : 'text-white' }}">
      {!! $answer->question?->content ?? '' !!}
    </div>

    @if($answer->question?->type === 'essay')
      <div class="rounded-3 p-3 mb-2" style="{{ $compact ? 'background:#fff;border:1px solid #e4e6ef;' : 'background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);' }}">
        <div class="opacity-75 fs-8 mb-1 {{ $compact ? 'text-muted' : 'text-white' }}">إجابة الطالب:</div>
        <div class="{{ $compact ? 'text-gray-800' : 'text-white' }}">{!! $answer->essay_answer ?: 'لم تُجب' !!}</div>
      </div>
      @if($answer->points_earned === null)
        <div class="text-warning fs-7"><i class="bi bi-hourglass-split me-1"></i> بانتظار التصحيح اليدوي</div>
      @endif
    @else
      <div class="d-flex flex-column gap-2">
        @foreach($answer->question?->options ?? [] as $option)
          @php
            $isSelected = $option->id === $answer->selected_option_id;
            $isCorrectOpt = (bool) $option->is_correct;
            $highlightCorrect = $showCorrect && $isCorrectOpt;
            $highlightWrong = $isSelected && ! $isCorrectOpt;
          @endphp
          <div class="d-flex align-items-center gap-2 p-3 rounded-3 border"
               style="@if($highlightCorrect) background: rgba(40,167,69,0.15); border-color: rgba(40,167,69,0.45) !important;
                      @elseif($highlightWrong) background: rgba(220,53,69,0.15); border-color: rgba(220,53,69,0.45) !important;
                      @else border-color: rgba(0,0,0,0.08) !important; @endif">
            @if($highlightCorrect) <i class="bi bi-check-circle-fill text-success fs-5"></i>
            @elseif($highlightWrong) <i class="bi bi-x-circle-fill text-danger fs-5"></i>
            @elseif($isSelected) <i class="bi bi-dot fs-3 text-primary"></i>
            @else <i class="bi bi-circle text-muted opacity-50 fs-5"></i> @endif

            <span class="@if($highlightCorrect) text-success fw-bold @elseif($highlightWrong) text-danger fw-bold @elseif($compact) text-gray-800 @else text-white @endif">
              {!! $option->option_text !!}
            </span>

            @if($isSelected)
              <span class="fs-8 ms-auto opacity-75 {{ $highlightCorrect ? 'text-success' : 'text-danger' }}">(إجابة الطالب)</span>
            @elseif($highlightCorrect)
              <span class="fs-8 ms-auto text-success opacity-75">(الإجابة الصحيحة)</span>
            @endif
          </div>
        @endforeach
      </div>
    @endif
  </div>
@empty
  <div class="{{ $compact ? 'text-muted text-center py-8' : 'glass-panel rounded-4 p-5 text-center text-white opacity-75' }}">
    لا تتوفر تفاصيل الإجابات لهذا التقديم.
  </div>
@endforelse
