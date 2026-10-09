{{-- Page footer: document title on the right, the teacher's contact in the middle, page x / y on the left. Expects $footerTitle. --}}
<htmlpagefooter name="paperfooter">
  <div class="rule-thin"></div>
  <table class="foot"><tr>
    <td style="text-align:right; width:40%;">{{ $footerTitle }}</td>
    <td class="foot-contact" style="text-align:center; width:40%;">أ. اسامة الكفراوي &nbsp;|&nbsp; <span dir="ltr">0598659012</span></td>
    <td style="text-align:left; width:20%;" dir="ltr">{PAGENO} / {nbpg}</td>
  </tr></table>
</htmlpagefooter>
<sethtmlpagefooter name="paperfooter" value="on" />
