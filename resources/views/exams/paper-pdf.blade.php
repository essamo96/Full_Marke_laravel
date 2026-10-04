@php
    use App\Support\ExamPaper;
    $letters = ['أ', 'ب', 'ج', 'د', 'هـ', 'و'];
    $total = $exam->questions->sum('points');
    $ar = fn ($n) => strtr((string) $n, ['0' => '٠', '1' => '١', '2' => '٢', '3' => '٣', '4' => '٤', '5' => '٥', '6' => '٦', '7' => '٧', '8' => '٨', '9' => '٩']);
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<title>{{ $exam->title }}</title>
<style>
  body { font-family: dejavusans, sans-serif; font-size: 11pt; color: #1f2937; direction: rtl; text-align: right; line-height: 1.7; }
  table { border-collapse: collapse; width: 100%; }
  td { vertical-align: middle; }

  .brand-name { font-size: 15pt; font-weight: bold; color: #0f172a; }
  .brand-sub { font-size: 9pt; color: #64748b; }
  .rule { border-top: 2.5pt solid #0284c7; margin: 6pt 0 0; }
  .rule-thin { border-top: .6pt solid #cbd5e1; margin: 2pt 0 8pt; }

  .title { text-align: center; font-size: 17pt; font-weight: bold; color: #0f172a; margin: 10pt 0 8pt; }

  .info { border: .8pt solid #cbd5e1; margin-bottom: 8pt; }
  .info td { padding: 5pt 8pt; font-size: 10pt; border-bottom: .5pt solid #e2e8f0; }
  .info .k { color: #64748b; }
  .info .v { font-weight: bold; color: #0f172a; }

  .student { margin-bottom: 10pt; }
  .student td { padding: 6pt 4pt 0; font-size: 10pt; color: #475569; }
  .fill { border-bottom: .8pt dotted #64748b; height: 16pt; }

  .q { margin-bottom: 11pt; page-break-inside: avoid; }
  .q-head td { vertical-align: top; }
  .q-no { width: 24pt; font-weight: bold; color: #0284c7; font-size: 11pt; }
  .q-text { font-size: 11pt; font-weight: bold; color: #111827; }
  .q-pts { width: 54pt; text-align: left; font-size: 8.5pt; color: #64748b; }
  .opts { margin-top: 3pt; }
  .opts td { padding: 2.5pt 0; vertical-align: top; }
  .opt-l { width: 28pt; text-align: center; }
  .bub { text-align: center; font-size: 10.5pt; font-weight: bold; color: #0284c7; }
  .opt-t { font-size: 10.5pt; padding-right: 6pt; }
  .lines div { border-bottom: .6pt solid #94a3b8; height: 20pt; }
  .foot { font-size: 8.5pt; color: #64748b; }
</style>
</head>
<body>

<htmlpagefooter name="paperfooter">
  <div class="rule-thin"></div>
  <table class="foot"><tr>
    <td style="text-align:right;">{{ $exam->title }}</td>
    <td style="text-align:left;" dir="ltr">{PAGENO} / {nbpg}</td>
  </tr></table>
</htmlpagefooter>
<sethtmlpagefooter name="paperfooter" value="on" />

<table>
  <tr>
    <td style="width:60pt;">
      @if($logo)<img src="{{ $logo }}" style="height:46pt;">@endif
    </td>
    <td style="padding-right:8pt;">
      <div class="brand-name">أكاديمية فول مارك</div>
      <div class="brand-sub">FULL MARK ACADEMY</div>
    </td>
    <td style="text-align:left;" class="brand-sub">
      نموذج امتحان<br>{{ now()->format('Y/m/d') }}
    </td>
  </tr>
</table>
<div class="rule"></div>

<div class="title">{{ $exam->title }}</div>

<table class="info">
  <tr>
    <td><span class="k">المادة:</span> <span class="v">{{ $exam->subject->name ?? '-' }}</span></td>
    <td><span class="k">المجموعة:</span> <span class="v">{{ $exam->group->name ?? '-' }}</span></td>
  </tr>
  <tr>
    <td style="border-bottom:0;"><span class="k">المدة:</span> <span class="v">{{ $exam->duration_minutes ? $ar($exam->duration_minutes).' دقيقة' : 'غير محددة' }}</span></td>
    <td style="border-bottom:0;"><span class="k">الدرجة الكلية:</span> <span class="v">{{ $ar($total) }}</span> &nbsp; <span class="k">عدد الأسئلة:</span> <span class="v">{{ $ar($exam->questions->count()) }}</span></td>
  </tr>
</table>

<table class="student">
  <tr>
    <td style="width:34pt;">الاسم:</td><td class="fill" style="width:46%;"></td>
    <td style="width:12pt;"></td>
    <td style="width:44pt;">التاريخ:</td><td class="fill"></td>
  </tr>
</table>

@foreach($exam->questions as $i => $q)
  <div class="q">
    <table class="q-head"><tr>
      <td class="q-no">{{ $ar($i + 1) }}</td>
      <td class="q-text">{!! ExamPaper::plain($q->content) !!}</td>
      <td class="q-pts">({{ $ar($q->points) }} {{ $q->points > 2 ? 'درجات' : 'درجة' }})</td>
    </tr></table>

    @if($q->type === 'essay')
      <div class="lines" style="margin-right:24pt;"><div></div><div></div><div></div><div></div></div>
    @else
      <table class="opts" style="width:94%; margin-right:24pt;">
        @foreach($q->options as $k => $opt)
          <tr>
            <td class="opt-l"><span class="bub">{{ $letters[$k] ?? '' }}</span></td>
            @php $optHtml = ExamPaper::plain($opt->option_text); $isLtr = ! preg_match('/\p{Arabic}/u', strip_tags($optHtml)); @endphp
            <td class="opt-t"@if($isLtr) dir="ltr" style="text-align:right;"@endif>{!! $optHtml !!}</td>
          </tr>
        @endforeach
      </table>
    @endif
  </div>
@endforeach

</body>
</html>
