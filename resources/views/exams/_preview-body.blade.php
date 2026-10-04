{{-- Exam questions preview. $exam loaded with questions.options; optional $stats, $showCorrect --}}
@php $showCorrect = $showCorrect ?? false; $stats = $stats ?? null; @endphp
@foreach($exam->questions as $i => $q)
  @php
    $qStats = $stats[$q->id] ?? [];
    $total = array_sum($qStats);
  @endphp
  <div class="border rounded p-4 mb-4" style="background:#fff;color:#212529;">
    <div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
      <h5 class="fw-bold m-0">سؤال {{ $i + 1 }}</h5>
      <span class="badge bg-secondary">{{ $q->points }} درجة</span>
    </div>
    <div class="mb-3 fs-5">{!! $q->content !!}</div>
    @if($q->type === 'essay')
      <div class="text-muted">سؤال مقالي</div>
    @else
      @foreach($q->options as $opt)
        @php
          $cnt = $qStats[$opt->id] ?? 0;
          $pct = $total ? round($cnt / $total * 100) : 0;
          $isOk = $showCorrect && $opt->is_correct;
        @endphp
        <div class="d-flex align-items-center gap-2 p-3 rounded border mb-2"
             style="{{ $isOk ? 'background:rgba(40,167,69,.15);border-color:rgba(40,167,69,.5)!important;' : '' }}">
          <i class="bi {{ $isOk ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }}"></i>
          <span class="{{ $isOk ? 'fw-bold text-success' : '' }}">{!! $opt->option_text !!}</span>
          @if($isOk)<span class="fs-8 text-success">(الإجابة الصحيحة)</span>@endif
          @if($stats !== null)
            <span class="ms-auto badge {{ $opt->is_correct ? 'bg-success' : 'bg-danger' }}">{{ $cnt }} ({{ $pct }}%)</span>
          @endif
        </div>
      @endforeach
    @endif
  </div>
@endforeach
@if($exam->questions->isEmpty())
  <div class="text-center text-muted py-5">لا توجد أسئلة في هذا الامتحان.</div>
@endif
