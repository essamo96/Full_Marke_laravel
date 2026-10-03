<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>نتيجة الامتحان - {{ $grade->exam_name }}</title>
  <style>
    @page { margin: 28px 32px; }
    * { box-sizing: border-box; }
    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 12px;
      color: #1f2937;
      /* DomPDF lacks native Arabic shaping; Ar-PHP glyphs expect LTR flow. */
      direction: ltr;
      text-align: right;
      line-height: 1.6;
    }
    .header {
      width: 100%;
      border-bottom: 2px solid #c9a227;
      padding-bottom: 14px;
      margin-bottom: 18px;
    }
    .header-table { width: 100%; border-collapse: collapse; }
    .header-table td { vertical-align: middle; }
    .logo { height: 62px; width: auto; }
    .brand {
      text-align: left;
      color: #6b7280;
      font-size: 11px;
    }
    .brand-title {
      color: #111827;
      font-size: 15px;
      font-weight: bold;
      margin-bottom: 2px;
    }
    h1 {
      font-size: 18px;
      margin: 0 0 10px;
      color: #111827;
    }
    .meta-box {
      width: 100%;
      background: #f8fafc;
      border: 1px solid #e5e7eb;
      border-radius: 6px;
      padding: 12px 14px;
      margin-bottom: 14px;
    }
    .meta-table { width: 100%; border-collapse: collapse; }
    .meta-table td { padding: 3px 0; vertical-align: top; }
    .meta-label { color: #6b7280; width: 90px; }
    .score-box {
      text-align: center;
      padding: 14px;
      margin: 0 0 18px;
      background: #fffbeb;
      border: 1px solid #f59e0b;
      border-radius: 6px;
    }
    .score-label {
      color: #92400e;
      font-size: 12px;
      margin-bottom: 4px;
    }
    .score-value {
      font-size: 26px;
      font-weight: bold;
      color: #111827;
    }
    h2 {
      font-size: 14px;
      margin: 0 0 10px;
      padding-bottom: 6px;
      border-bottom: 1px solid #e5e7eb;
      color: #111827;
    }
    .q {
      margin-bottom: 12px;
      padding: 12px;
      border: 1px solid #e5e7eb;
      border-radius: 6px;
      page-break-inside: avoid;
      background: #fff;
    }
    .q-head { margin-bottom: 8px; }
    .badge {
      display: inline-block;
      padding: 2px 8px;
      border-radius: 4px;
      font-size: 10px;
      margin-right: 6px;
    }
    .ok { background: #dcfce7; color: #166534; }
    .bad { background: #fee2e2; color: #991b1b; }
    .pending { background: #e5e7eb; color: #374151; }
    .points { color: #6b7280; font-size: 11px; }
    .q-content { margin: 8px 0 10px; }
    .opt {
      margin: 4px 0;
      padding: 6px 8px;
      border-radius: 4px;
      border: 1px solid transparent;
    }
    .opt-correct { background: #ecfdf5; border-color: #a7f3d0; }
    .opt-wrong { background: #fef2f2; border-color: #fecaca; }
    .opt-note { color: #6b7280; font-size: 10px; }
    .essay {
      background: #f9fafb;
      border: 1px dashed #d1d5db;
      border-radius: 4px;
      padding: 8px;
      margin-top: 6px;
    }
    .footer {
      margin-top: 22px;
      padding-top: 10px;
      border-top: 1px solid #e5e7eb;
      color: #9ca3af;
      font-size: 10px;
      text-align: center;
    }
  </style>
</head>
<body>
  @php
    $logoPath = public_path('site/images/logo_v2_gold.png');
    if (! is_file($logoPath)) {
        $logoPath = public_path('site/images/logo_v2_blue.png');
    }
    $logoSrc = null;
    if (is_file($logoPath)) {
        $logoSrc = 'data:image/png;base64,'.base64_encode((string) file_get_contents($logoPath));
    }
    $studentName = $grade->student?->full_name_ar ?: ($grade->student?->full_name_en ?: '—');
  @endphp

  <div class="header">
    <table class="header-table">
      <tr>
        <td class="brand" style="width: 70%; text-align: right;">
          <div class="brand-title">Full Mark Academy</div>
          <div>أكاديمية العلامة الكاملة</div>
          <div>نتيجة الامتحان</div>
        </td>
        <td style="width: 30%; text-align: left;">
          @if($logoSrc)
            <img class="logo" src="{{ $logoSrc }}" alt="Full Mark Academy">
          @endif
        </td>
      </tr>
    </table>
  </div>

  <h1>{{ $grade->exam_name }}</h1>

  <div class="meta-box">
    <table class="meta-table">
      <tr>
        <td class="meta-label">الطالب:</td>
        <td>{{ $studentName }}</td>
      </tr>
      <tr>
        <td class="meta-label">المجموعة:</td>
        <td>{{ $grade->group?->name ?? '—' }}</td>
      </tr>
      <tr>
        <td class="meta-label">التاريخ:</td>
        <td>{{ optional($grade->created_at)->format('Y-m-d H:i') }}</td>
      </tr>
    </table>
  </div>

  <div class="score-box">
    <div class="score-label">الدرجة النهائية</div>
    <div class="score-value">{{ $grade->score }} / {{ $grade->max_score }}</div>
  </div>

  <h2>تفاصيل الإجابات</h2>

  @foreach($grade->answers->sortBy(fn ($a) => $a->question?->sort_order ?? 0)->values() as $i => $answer)
    <div class="q">
      <div class="q-head">
        <strong>سؤال {{ $i + 1 }}</strong>
        @if($answer->is_correct === true)
          <span class="badge ok">صحيح</span>
        @elseif($answer->is_correct === false)
          <span class="badge bad">خاطئ</span>
        @else
          <span class="badge pending">قيد التصحيح</span>
        @endif
        <span class="points">({{ $answer->points_earned !== null ? $answer->points_earned : '؟' }} / {{ $answer->question?->points }})</span>
      </div>

      <div class="q-content">{!! strip_tags($answer->question?->content ?? '', '<br><p><b><strong><i><ul><ol><li>') !!}</div>

      @if($answer->question?->type === 'essay')
        <div><strong>إجابة الطالب:</strong></div>
        <div class="essay">{{ $answer->essay_answer ?: '—' }}</div>
      @else
        @foreach($answer->question?->options ?? [] as $option)
          @php
            $isSelected = $option->id === $answer->selected_option_id;
            $isCorrectOpt = (bool) $option->is_correct;
          @endphp
          <div class="opt {{ $isCorrectOpt ? 'opt-correct' : ($isSelected ? 'opt-wrong' : '') }}">
            {{ $isCorrectOpt ? '✓' : ($isSelected ? '✗' : '○') }}
            {!! strip_tags($option->option_text, '<b><strong><i>') !!}
            @if($isSelected)
              <span class="opt-note"> (إجابة الطالب)</span>
            @endif
            @if($isCorrectOpt && ! $isSelected)
              <span class="opt-note"> (الصحيحة)</span>
            @endif
          </div>
        @endforeach
      @endif
    </div>
  @endforeach

  <div class="footer">
    Full Mark Academy — تم إنشاء هذا الملف تلقائياً من نظام الأكاديمية
  </div>
</body>
</html>
