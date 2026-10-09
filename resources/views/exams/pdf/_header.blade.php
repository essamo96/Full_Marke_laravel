{{-- Academy letterhead. Expects $logo (absolute path|null), $docLabel (e.g. "نموذج امتحان"), $docDate. --}}
<table>
  <tr>
    <td style="width:60pt;">
      @if($logo)<img src="{{ $logo }}" style="height:46pt;">@endif
    </td>
    <td style="padding-right:8pt;">
      <div class="brand-name">أكاديمية العلامة الكاملة</div>
      <div class="brand-sub">FULL MARK ACADEMY</div>
    </td>
    <td style="text-align:left;" class="brand-sub">
      {{ $docLabel }}<br>{{ $docDate }}
    </td>
  </tr>
</table>
<div class="rule"></div>
