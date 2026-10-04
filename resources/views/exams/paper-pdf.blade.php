<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>{{ $exam->title }}</title>
  <style>
    @page { margin: 28px 32px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; direction: ltr; text-align: right; line-height: 1.7; }
    h1 { font-size: 18px; margin: 0 0 6px; }
    .meta { border-bottom: 2px solid #c9a227; padding-bottom: 10px; margin-bottom: 16px; color: #4b5563; }
    .fields { width: 100%; margin-bottom: 14px; }
    .fields td { padding: 4px 0; border-bottom: 1px dotted #9ca3af; }
    .q { margin-bottom: 14px; page-break-inside: avoid; }
    .q-head { font-weight: bold; margin-bottom: 4px; }
    .pts { color: #6b7280; font-weight: normal; font-size: 11px; }
    .opt { margin: 2px 18px 2px 0; }
    .box { display: inline-block; width: 11px; height: 11px; border: 1px solid #374151; margin-left: 6px; }
    .lines div { border-bottom: 1px solid #9ca3af; height: 22px; }
  </style>
</head>
<body>
  <h1>{{ $exam->title }}</h1>
  <div class="meta">
    @if($exam->subject) {{ $exam->subject->name }} @endif
    @if($exam->duration_minutes) | المدة: {{ $exam->duration_minutes }} دقيقة @endif
    | الدرجة الكلية: {{ $exam->questions->sum('points') }}
  </div>
  <table class="fields"><tr><td>الاسم:</td><td>التاريخ:</td></tr></table>

  @foreach($exam->questions as $i => $q)
    <div class="q">
      <div class="q-head">{{ $i + 1 }}) {!! strip_tags($q->content, '<b><strong><i><em><u><br><sub><sup>') !!}
        <span class="pts">({{ $q->points }} درجة)</span></div>
      @if($q->type === 'essay')
        <div class="lines"><div></div><div></div><div></div><div></div></div>
      @else
        @foreach($q->options as $opt)
          <div class="opt"><span class="box"></span> {!! strip_tags($opt->option_text, '<b><strong><i><em><u><br><sub><sup>') !!}</div>
        @endforeach
      @endif
    </div>
  @endforeach
</body>
</html>
