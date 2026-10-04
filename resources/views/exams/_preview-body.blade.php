{{-- Exam questions preview. $exam loaded with questions.options; optional $stats, $showCorrect --}}
@php $showCorrect = $showCorrect ?? false; $stats = $stats ?? null; @endphp
@include('exams._styles')
@foreach($exam->questions as $i => $q)
  @php
    $qStats = $stats[$q->id] ?? [];
    $total = array_sum($qStats);
  @endphp
  <div class="xp-card">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
      <h5 class="fw-bold m-0">سؤال {{ $i + 1 }}</h5>
      <span class="xp-pts">{{ $q->points }} درجة</span>
    </div>
    <div class="mb-3 fs-5">{!! $q->content !!}</div>
    @if($q->type === 'essay')
      <div class="text-muted"><i class="bi bi-pencil-square me-1"></i> سؤال مقالي</div>
    @else
      @foreach($q->options as $opt)
        @php
          $cnt = $qStats[$opt->id] ?? 0;
          $pct = $total ? round($cnt / $total * 100) : 0;
          $isOk = $showCorrect && $opt->is_correct;
        @endphp
        <div class="xp-opt {{ $isOk ? 'xp-opt--ok' : '' }}">
          <i class="bi xp-mark {{ $isOk ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
          <span>{!! $opt->option_text !!}</span>
          @if($isOk)<span class="fs-8 text-success">(الإجابة الصحيحة)</span>@endif
          @if($stats !== null)
            <span class="xp-count {{ $opt->is_correct ? 'xp-count--ok' : 'xp-count--bad' }}" title="عدد من اختار هذا الخيار">{{ $cnt }} ({{ $pct }}%)</span>
          @endif
        </div>
      @endforeach
    @endif
  </div>
@endforeach
@if($exam->questions->isEmpty())
  <div class="text-center text-muted py-5">لا توجد أسئلة في هذا الامتحان.</div>
@endif
