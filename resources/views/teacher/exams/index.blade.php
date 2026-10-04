@extends('layouts.teacher')

@section('title', 'Exams | FULL MARK ACADEMY')
@section('page_title_en', 'Tests Management')
@section('page_title_ar', 'إدارة الامتحانات')

@section('content')
  @include('exams._styles')

  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h1 class="h3 fw-bold mb-0" style="color: var(--text-primary);" data-en="Exams" data-ar="الامتحانات">الامتحانات</h1>
    <a href="{{ route('teacher.exams.create') }}" class="tbtn tbtn--solid"><i class="bi bi-plus-lg"></i><span data-en="New Exam" data-ar="امتحان جديد">امتحان جديد</span></a>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger" dir="auto">{{ session('error') }}</div>
  @endif

  @if($exams->count())
    <div class="ex-toolbar mb-3">
      <label class="ex-search">
        <i class="bi bi-search"></i>
        <input type="search" id="exSearch" placeholder="ابحث باسم الامتحان أو المادة أو المجموعة..." autocomplete="off" aria-label="بحث في الامتحانات">
      </label>
      <div class="ex-filters" role="group" aria-label="تصفية الحالة">
        <button type="button" class="ex-pill is-active" data-status="all">الكل</button>
        <button type="button" class="ex-pill" data-status="published">منشور</button>
        <button type="button" class="ex-pill" data-status="draft">مسودة</button>
        <button type="button" class="ex-pill" data-status="completed">مكتمل</button>
      </div>
    </div>
  @endif

  <div class="ex-grid" id="exGrid">
    @forelse($exams as $exam)
      @php
        $statusMap = [
          'published' => ['منشور', 'ok'],
          'draft' => ['مسودة', 'warn'],
          'completed' => ['مكتمل', 'info'],
        ];
        [$statusLabel, $statusTone] = $statusMap[$exam->status] ?? [$exam->status, 'info'];
        $audienceLabel = match($exam->audience) { 'guests' => 'ضيوف', 'both' => 'طلاب + ضيوف', default => 'طلاب' };
      @endphp
      <article class="ex-card" data-status="{{ $exam->status }}"
               data-search="{{ \Illuminate\Support\Str::lower($exam->title.' '.($exam->subject->name ?? '').' '.($exam->group->name ?? '')) }}">
        <header class="ex-card__head">
          <h2 class="ex-card__title">{{ $exam->title }}</h2>
          <span class="ex-badge ex-badge--{{ $statusTone }}">{{ $statusLabel }}</span>
        </header>

        <div class="ex-card__meta">
          <span><i class="bi bi-journal-bookmark"></i> {{ $exam->subject->name ?? '-' }}</span>
          <span><i class="bi bi-people"></i> {{ $exam->group->name ?? '-' }}</span>
          <span><i class="bi bi-calendar-event"></i> {{ $exam->start_time?->format('Y-m-d H:i') ?? 'غير محدد' }}</span>
        </div>

        <div class="ex-card__stats">
          <span class="ex-stat"><strong>{{ $exam->questions_count }}</strong> سؤال</span>
          <span class="ex-stat"><strong>{{ $exam->grades_count }}</strong> تسليم</span>
          <span class="ex-stat"><i class="bi bi-person-badge"></i> {{ $audienceLabel }}</span>
        </div>

        <footer class="ex-card__actions">
          <a href="{{ route('teacher.grading.exam', $exam) }}" class="tbtn tbtn--solid tbtn--sm"><i class="bi bi-list-check"></i><span data-en="Results" data-ar="النتائج">النتائج</span></a>
          <a href="{{ route('teacher.exams.preview', $exam) }}" class="tbtn tbtn--preview tbtn--sm"><i class="bi bi-eye"></i><span data-en="Preview" data-ar="معاينة">معاينة</span></a>
          <a href="{{ route('teacher.exams.blank-pdf', $exam) }}" class="tbtn tbtn--pdf tbtn--sm"><i class="bi bi-file-earmark-pdf"></i><span>PDF</span></a>
          <a href="{{ route('teacher.exams.edit', $exam) }}" class="tbtn tbtn--edit tbtn--sm"><i class="bi bi-pencil"></i><span data-en="Edit" data-ar="تعديل">تعديل</span></a>
          @if($exam->allowsGuests())
            <span class="ex-card__sep" aria-hidden="true"></span>
            <button type="button" class="tbtn tbtn--neutral tbtn--sm" onclick="copyGuestLink(this)" data-link="{{ route('guest.exam.enter', $exam) }}" title="نسخ رابط الضيوف"><i class="bi bi-link-45deg"></i><span data-en="Guest link" data-ar="رابط الضيوف">رابط الضيوف</span></button>
            <button type="button" class="tbtn tbtn--neutral tbtn--sm tbtn--icon" onclick="showExamQr(this)" data-url="{{ route('qr.exam', $exam) }}" data-name="{{ $exam->title }}" data-link="{{ route('guest.exam.enter', $exam) }}" title="QR" aria-label="QR"><i class="bi bi-qr-code"></i></button>
          @endif
        </footer>
      </article>
    @empty
      <div class="ex-empty">
        <i class="bi bi-clipboard-check"></i>
        <p data-en="No exams yet." data-ar="لا توجد امتحانات بعد.">لا توجد امتحانات بعد.</p>
        <a href="{{ route('teacher.exams.create') }}" class="tbtn tbtn--solid"><i class="bi bi-plus-lg"></i><span>إنشاء أول امتحان</span></a>
      </div>
    @endforelse
  </div>
  <div class="ex-empty d-none" id="exNoMatch"><i class="bi bi-search"></i><p>لا توجد نتائج مطابقة.</p></div>

  <div class="mt-4">{{ $exams->links() }}</div>

  <!-- QR Code preview modal -->
  <div class="modal fade ex-modal" id="examQrModal" tabindex="-1" aria-labelledby="examQrModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-qr-code-scan me-2" style="color: var(--accent-color);"></i><span data-en="Guest access QR" data-ar="رمز QR لدخول الضيوف">رمز QR لدخول الضيوف</span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
        </div>
        <div class="modal-body text-center">
          <p class="ex-qr-name" id="examQrModalTitle"></p>
          <div class="ex-qr-frame">
            <img id="examQrModalImg" src="" alt="QR Code" width="240" height="240">
          </div>
          <p class="ex-qr-hint">وجّه كاميرا الجوال نحو الرمز لفتح صفحة الامتحان</p>
          <div class="ex-qr-link" id="examQrLink" dir="ltr"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="tbtn tbtn--neutral" id="examQrCopy"><i class="bi bi-link-45deg"></i><span>نسخ الرابط</span></button>
          <a id="examQrModalDownload" href="" download class="tbtn tbtn--solid"><i class="bi bi-download"></i><span data-en="Download" data-ar="تحميل">تحميل</span></a>
        </div>
      </div>
    </div>
  </div>

@endsection

@push('styles')
<style>
  .ex-toolbar { display: flex; flex-wrap: wrap; gap: .75rem; align-items: center; justify-content: space-between; }
  .ex-search { position: relative; flex: 1 1 280px; max-width: 460px; margin: 0; }
  .ex-search i { position: absolute; inset-inline-start: .9rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); }
  .ex-search input { width: 100%; min-height: 44px; padding: .5rem .9rem .5rem .9rem; padding-inline-start: 2.4rem;
    background: var(--input-bg); color: var(--text-primary); border: 1px solid var(--input-border); border-radius: 12px; }
  .ex-search input:focus { outline: none; border-color: var(--input-focus-border); box-shadow: 0 0 0 3px color-mix(in srgb, var(--input-focus-border) 25%, transparent); }
  .ex-filters { display: flex; flex-wrap: wrap; gap: .4rem; }
  .ex-pill { min-height: 40px; padding: .35rem .9rem; border-radius: 999px; font-weight: 600; font-size: .85rem; cursor: pointer;
    background: transparent; color: var(--text-secondary); border: 1px solid var(--card-border, var(--glass-border)); transition: background-color .18s, color .18s, border-color .18s; }
  .ex-pill:hover { border-color: var(--accent-color); color: var(--text-primary); }
  .ex-pill.is-active { background: var(--accent-color); border-color: var(--accent-color); color: #fff; }
  .ex-pill:focus-visible { outline: 3px solid color-mix(in srgb, var(--accent-color) 55%, transparent); outline-offset: 2px; }

  .ex-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 380px), 1fr)); gap: 1rem; }
  .ex-card { display: flex; flex-direction: column; gap: .85rem; padding: 1.1rem 1.2rem; border-radius: 16px;
    background: var(--card-bg, var(--bg-secondary)); border: 1px solid var(--card-border, var(--glass-border));
    box-shadow: var(--shadow-sm); transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease; }
  .ex-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); border-color: color-mix(in srgb, var(--accent-color) 45%, transparent); }
  .ex-card[hidden] { display: none; }
  .ex-card__head { display: flex; align-items: flex-start; justify-content: space-between; gap: .75rem; }
  .ex-card__title { font-size: 1.05rem; font-weight: 700; margin: 0; color: var(--text-primary); line-height: 1.5; overflow-wrap: anywhere; }
  .ex-badge { flex-shrink: 0; padding: .2rem .65rem; border-radius: 999px; font-size: .75rem; font-weight: 700; border: 1px solid transparent; }
  .ex-badge--ok { background: color-mix(in srgb, #22c55e 16%, transparent); border-color: color-mix(in srgb, #22c55e 45%, transparent); color: var(--text-primary); }
  .ex-badge--warn { background: color-mix(in srgb, #f59e0b 18%, transparent); border-color: color-mix(in srgb, #f59e0b 50%, transparent); color: var(--text-primary); }
  .ex-badge--info { background: color-mix(in srgb, var(--accent-color) 15%, transparent); border-color: color-mix(in srgb, var(--accent-color) 40%, transparent); color: var(--text-primary); }
  .ex-card__meta { display: flex; flex-wrap: wrap; gap: .4rem 1rem; color: var(--text-secondary); font-size: .85rem; }
  .ex-card__meta i, .ex-stat i { color: var(--accent-color); margin-inline-end: .25rem; }
  .ex-card__stats { display: flex; flex-wrap: wrap; gap: .5rem; }
  .ex-stat { padding: .25rem .65rem; border-radius: 8px; font-size: .8rem; color: var(--text-secondary);
    background: color-mix(in srgb, var(--text-muted) 10%, transparent); }
  .ex-stat strong { color: var(--text-primary); }
  .ex-card__actions { display: flex; flex-wrap: wrap; align-items: center; gap: .45rem; margin-top: auto; padding-top: .85rem; border-top: 1px solid var(--separator-color); }
  .ex-card__sep { width: 1px; align-self: stretch; background: var(--separator-color); margin: 0 .15rem; }
  .ex-modal .modal-content { background: var(--card-bg, var(--bg-secondary)); color: var(--text-primary); border: 1px solid var(--card-border, var(--glass-border));
    border-radius: 18px; box-shadow: var(--shadow-lg); }
  .ex-modal .modal-header, .ex-modal .modal-footer { border-color: var(--separator-color); }
  .ex-modal .modal-title { font-weight: 700; font-size: 1rem; color: var(--text-primary); }
  .ex-modal .modal-footer { justify-content: center; gap: .5rem; flex-wrap: wrap; }
  [data-theme="dark"] .ex-modal .btn-close, .theme-dark .ex-modal .btn-close { filter: invert(1); }
  .ex-modal.fade .modal-dialog { opacity: 0; transform: translateY(22px) scale(.97); transition: transform .28s cubic-bezier(.2,.8,.2,1), opacity .22s ease; }
  .ex-modal.show .modal-dialog { opacity: 1; transform: none; }
  .ex-qr-name { font-weight: 700; margin: 0 0 .9rem; overflow-wrap: anywhere; }
  .ex-qr-frame { display: inline-block; padding: 14px; background: #fff; border-radius: 16px; border: 1px solid var(--card-border, var(--glass-border)); box-shadow: var(--shadow-md); }
  .ex-qr-frame img { display: block; width: 240px; max-width: 100%; height: auto; }
  .ex-qr-hint { color: var(--text-muted); font-size: .85rem; margin: .9rem 0 .5rem; }
  .ex-qr-link { font-size: .75rem; color: var(--text-secondary); background: color-mix(in srgb, var(--text-muted) 10%, transparent);
    border-radius: 8px; padding: .4rem .6rem; overflow-wrap: anywhere; }
  @media (prefers-reduced-motion: reduce) { .ex-modal.fade .modal-dialog { transition: none; transform: none; } }
  .ex-empty { grid-column: 1 / -1; text-align: center; padding: 3rem 1rem; color: var(--text-muted); }
  .ex-empty i { font-size: 2.5rem; color: var(--accent-color); display: block; margin-bottom: .5rem; }
  @media (prefers-reduced-motion: reduce) { .ex-card, .ex-pill { transition: none; } .ex-card:hover { transform: none; } }
</style>
@endpush

@push('scripts')
<script>
  // Client-side filter: one pass over a handful of cards, rAF-batched, debounced.
  (function () {
    const grid = document.getElementById('exGrid');
    const input = document.getElementById('exSearch');
    if (!grid || !input) return;
    const cards = Array.from(grid.querySelectorAll('.ex-card'));
    const empty = document.getElementById('exNoMatch');
    let status = 'all', timer = null;

    function apply() {
      const q = input.value.trim().toLowerCase();
      let shown = 0;
      requestAnimationFrame(function () {
        cards.forEach(function (c) {
          const ok = (status === 'all' || c.dataset.status === status) && (!q || c.dataset.search.indexOf(q) !== -1);
          c.hidden = !ok;
          if (ok) shown++;
        });
        empty.classList.toggle('d-none', shown !== 0);
      });
    }
    input.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(apply, 120); });
    document.querySelectorAll('.ex-pill').forEach(function (b) {
      b.addEventListener('click', function () {
        document.querySelectorAll('.ex-pill').forEach(function (x) { x.classList.remove('is-active'); });
        b.classList.add('is-active');
        status = b.dataset.status;
        apply();
      });
    });
  })();

  function copyGuestLink(btn) {
    const link = btn.getAttribute('data-link');
    navigator.clipboard.writeText(link).then(() => {
      const label = btn.querySelector('span');
      const original = label.textContent;
      label.textContent = 'تم النسخ!';
      setTimeout(() => { label.textContent = original; }, 1500);
    });
  }

  function showExamQr(btn) {
    const url = btn.getAttribute('data-url');
    const name = btn.getAttribute('data-name');
    const link = btn.getAttribute('data-link') || '';
    document.getElementById('examQrModalTitle').textContent = name;
    document.getElementById('examQrModalImg').src = url + '?t=' + Date.now();
    document.getElementById('examQrLink').textContent = link;
    const dl = document.getElementById('examQrModalDownload');
    dl.setAttribute('href', url);
    dl.setAttribute('download', name.replace(/\s+/g, '_') + '_qr.svg');
    const copy = document.getElementById('examQrCopy');
    copy.onclick = function () {
      navigator.clipboard.writeText(link).then(function () {
        const label = copy.querySelector('span'); const old = label.textContent;
        label.textContent = 'تم النسخ!';
        setTimeout(function () { label.textContent = old; }, 1500);
      });
    };
    bootstrap.Modal.getOrCreateInstance(document.getElementById('examQrModal')).show();
  }
</script>
@endpush
