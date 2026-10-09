{{-- Page footer: document title on the right, page x / y on the left. Expects $footerTitle. --}}
<htmlpagefooter name="paperfooter">
  <div class="rule-thin"></div>
  <table class="foot"><tr>
    <td style="text-align:right;">{{ $footerTitle }}</td>
    <td style="text-align:left;" dir="ltr">{PAGENO} / {nbpg}</td>
  </tr></table>
</htmlpagefooter>
<sethtmlpagefooter name="paperfooter" value="on" />
