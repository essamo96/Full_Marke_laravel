@php
    use App\Support\ArabicMpdf;
    use App\Support\ExamPaper;

    $letters = ['أ', 'ب', 'ج', 'د', 'هـ', 'و'];
    $ar = fn ($n) => ArabicMpdf::digits($n);
    // 5.00 -> 5, 2.50 -> 2.5 (marks may be fractional once an essay is graded by hand)
    $num = fn ($n) => $ar(rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.') ?: '0');

    $studentName = $grade->student?->full_name_ar ?: ($grade->student?->full_name_en ?: '—');
    $answers = $grade->answers->sortBy(fn ($a) => $a->question?->sort_order ?? 0)->values();
    $percent = (float) $grade->max_score > 0 ? round(((float) $grade->score / (float) $grade->max_score) * 100) : 0;
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<title>نتيجة الامتحان - {{ $grade->exam_name }}</title>
<style>
@include('exams.pdf._styles')

  .score { border: .8pt solid #7dd3fc; background-color: #f0f9ff; margin-bottom: 12pt; }
  .score td { text-align: center; padding: 7pt 6pt; }
  .score .k { font-size: 9pt; color: #64748b; }
  .score .big { font-size: 18pt; font-weight: bold; color: #0f172a; }

  .section { font-size: 12.5pt; font-weight: bold; color: #0f172a; border-bottom: .6pt solid #cbd5e1; padding-bottom: 3pt; margin: 4pt 0 9pt; }

  .q { margin-bottom: 11pt; page-break-inside: avoid; }
  .q-head td { vertical-align: top; }
  .q-no { width: 24pt; font-weight: bold; color: #0284c7; font-size: 11pt; }
  .q-text { font-size: 11pt; font-weight: bold; color: #111827; }
  .q-state { width: 100pt; text-align: left; font-size: 8.5pt; color: #64748b; }
  .ok { color: #15803d; font-weight: bold; }
  .bad { color: #b91c1c; font-weight: bold; }
  .wait { color: #b45309; font-weight: bold; }

  .opts { margin-top: 3pt; }
  .opts td { padding: 2.5pt 0; vertical-align: top; }
  .opt-l { width: 28pt; text-align: center; }
  .bub { text-align: center; font-size: 10.5pt; font-weight: bold; color: #0284c7; }
  .opt-t { font-size: 10.5pt; padding-right: 6pt; }
  .opt-tag { width: 92pt; text-align: left; font-size: 8.5pt; padding-left: 4pt; }
  .row-correct td { background-color: #ecfdf5; }
  .row-wrong td { background-color: #fef2f2; }
  .row-correct .bub { color: #15803d; }
  .row-wrong .bub { color: #b91c1c; }

  .essay-lbl { font-size: 9pt; color: #64748b; margin-top: 2pt; }
  .essay { border: .7pt dashed #94a3b8; background-color: #f8fafc; padding: 6pt 8pt; margin-top: 2pt; font-size: 10.5pt; }
</style>
</head>
<body>

@include('exams.pdf._footer', ['footerTitle' => 'نتيجة الامتحان — '.$grade->exam_name.' — '.$studentName])

@include('exams.pdf._header', ['logo' => $logo, 'docLabel' => 'نتيجة الامتحان', 'docDate' => optional($grade->created_at)->format('Y/m/d')])

<div class="title">{{ $grade->exam_name }}</div>

<table class="info">
  <tr>
    <td><span class="k">الطالب:</span> <span class="v">{{ $studentName }}</span></td>
    <td><span class="k">المجموعة:</span> <span class="v">{{ $grade->group?->name ?? '-' }}</span></td>
  </tr>
  <tr>
    <td style="border-bottom:0;"><span class="k">تاريخ التسليم:</span> <span class="v" dir="ltr">{{ $ar(optional($grade->created_at)->format('Y/m/d H:i')) }}</span></td>
    <td style="border-bottom:0;"><span class="k">المدة المستغرقة:</span> <span class="v">{{ $grade->time_taken_minutes !== null ? $ar($grade->time_taken_minutes).' دقيقة' : '-' }}</span></td>
  </tr>
</table>

<table class="score">
  <tr>
    <td style="width:50%;"><div class="k">الدرجة النهائية</div><div class="big">{{ $num($grade->score) }} من {{ $num($grade->max_score) }}</div></td>
    <td style="width:50%;"><div class="k">النسبة المئوية</div><div class="big">{{ $ar($percent) }}٪</div></td>
  </tr>
</table>

<div class="section">تفاصيل الإجابات</div>

@foreach($answers as $i => $answer)
  @php
      $q = $answer->question;
      $isEssay = $q?->type === 'essay';
      $answered = $isEssay ? filled($answer->essay_answer) : $answer->selected_option_id !== null;
  @endphp
  <div class="q">
    <table class="q-head"><tr>
      <td class="q-no">{{ $ar($i + 1) }}</td>
      <td class="q-text">{!! ExamPaper::plain($q?->content) !!}</td>
      <td class="q-state">
        @if($answer->is_correct === true)
          <span class="ok">صحيحة</span>
        @elseif($answer->is_correct === false)
          <span class="bad">{{ $answered ? 'خاطئة' : 'بدون إجابة' }}</span>
        @else
          <span class="wait">{{ $answered ? 'بانتظار التصحيح' : 'بدون إجابة' }}</span>
        @endif
        <br>{{ $answer->points_earned !== null ? $num($answer->points_earned) : '؟' }} من {{ $num($q?->points ?? 0) }} درجة
      </td>
    </tr></table>

    @if($isEssay)
      <div style="margin-right:24pt;">
        <div class="essay-lbl">إجابتك:</div>
        <div class="essay">{!! $answered ? ExamPaper::plain($answer->essay_answer) : '—' !!}</div>
      </div>
    @else
      <table class="opts" style="width:94%; margin-right:24pt;">
        @foreach(($q?->options ?? []) as $k => $opt)
          @php
              $isSelected = (int) $opt->id === (int) $answer->selected_option_id;
              $isCorrectOpt = (bool) $opt->is_correct;
              $optHtml = ExamPaper::plain($opt->option_text);
              $isLtr = ! preg_match('/\p{Arabic}/u', strip_tags($optHtml));
          @endphp
          <tr class="{{ $isCorrectOpt ? 'row-correct' : ($isSelected ? 'row-wrong' : '') }}">
            <td class="opt-l"><span class="bub">{{ $letters[$k] ?? '' }}</span></td>
            <td class="opt-t"@if($isLtr) dir="ltr" style="text-align:right;"@endif>{!! $optHtml !!}</td>
            <td class="opt-tag">
              @if($isSelected && $isCorrectOpt)
                <span class="ok">إجابتك (صحيحة)</span>
              @elseif($isSelected)
                <span class="bad">إجابتك</span>
              @elseif($isCorrectOpt)
                <span class="ok">الإجابة الصحيحة</span>
              @endif
            </td>
          </tr>
        @endforeach
      </table>
    @endif
  </div>
@endforeach

</body>
</html>
