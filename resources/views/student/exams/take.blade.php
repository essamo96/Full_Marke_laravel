@extends('layouts.exam')

@section('title', 'تقديم الامتحان: ' . $exam->title)
@section('exam_title', $exam->title)

@section('exam_timer')
    <div class="d-flex align-items-center gap-3 flex-wrap justify-content-end">
        {{-- Autosave status: answers are saved on the device instantly and on the server whenever there is a connection. --}}
        <div class="d-flex align-items-center gap-2 text-white fs-7" id="examSaveBadge" aria-live="polite">
            <i id="examSaveIcon" class="bi bi-cloud-check-fill text-success"></i>
            <span id="examSaveStatus" class="opacity-75 d-none d-md-inline">تم حفظ إجاباتك</span>
        </div>

        <div class="text-white fs-7 d-none d-sm-block">
            أجبت على <span class="fw-bold" id="examProgress">0 / {{ $exam->questions->count() }}</span>
        </div>

        <div id="violationBadge" class="badge bg-danger rounded-pill px-3 py-2 d-none" style="font-size: 1rem;">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> المخالفات: <span id="violationCountSpan">0</span> / 3
        </div>

        @if($exam->duration_minutes)
            <div class="d-flex align-items-center gap-2 bg-dark rounded-pill px-4 py-2 border border-secondary" id="timerContainer">
                <i class="bi bi-stopwatch text-gold fs-4"></i>
                <span class="fw-bold fs-4 text-white" id="countdownTimer">--:--:--</span>
            </div>
        @else
            <div class="badge bg-success rounded-pill px-3 py-2">مفتوح الوقت</div>
        @endif
    </div>
@endsection

@section('content')

<!-- Entry Animation / Anti-cheat Gate -->
<div id="examIntroOverlay" class="position-fixed top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center text-center px-4" style="background: radial-gradient(circle at center, #1a1a1a 0%, #000 100%); z-index: 2000;">
    <div id="examIntroContent">
        <i class="bi bi-shield-lock-fill text-gold" style="font-size: 4rem;"></i>
        <h2 class="text-white fw-bold mt-4 mb-3">{{ $exam->title }}</h2>
        <p class="text-white opacity-75 mb-4" style="max-width: 480px;">
            هذا امتحان مراقب. سيتم تسجيل عدد مرات خروجك من الصفحة، ولا يجوز نسخ الأسئلة أو مغادرة وضع ملء الشاشة.
            بالضغط على "ابدأ الامتحان" أنت توافق على هذه الشروط.
        </p>
        <p class="text-white opacity-75 mb-5 fs-7" style="max-width: 480px;">
            <i class="bi bi-cloud-check me-1"></i>
            تُحفظ إجاباتك تلقائياً أثناء الامتحان، وإذا انقطع الإنترنت فسيتم إرسالها فور عودة الاتصال دون أن تتغير.
        </p>
        <div id="examResumeNote" class="alert alert-warning d-none mb-4" style="max-width: 480px;">
            هذه محاولة غير مكتملة — استعدنا إجاباتك المحفوظة والوقت المتبقي كما هو.
        </div>
        <button type="button" id="examStartBtn" class="btn btn-gold btn-lg px-8 rounded-pill fw-bold">
            <i class="bi bi-play-circle-fill me-2"></i> <span id="examStartBtnLabel">ابدأ الامتحان</span>
        </button>
    </div>
    <div id="examCountdownContent" class="d-none">
        <div id="examCountdownNumber" class="fw-bold text-gold" style="font-size: 8rem; line-height: 1;">3</div>
        <p class="text-white opacity-75 mt-3">استعد...</p>
    </div>
</div>

{{-- Shown while the exam is being handed in (and while waiting for the connection to come back to do so). --}}
<div id="examSubmitOverlay" class="d-none position-fixed top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center text-center px-4" style="background: rgba(0,0,0,.92); z-index: 2500;">
    <div class="spinner-border text-gold mb-4" style="width: 3rem; height: 3rem;" role="status"></div>
    <p id="examSubmitOverlayText" class="text-white fs-5 mb-0" style="max-width: 520px;">جارٍ تسليم الامتحان...</p>
</div>

<div id="examConnBanner" class="alert alert-warning d-none mb-4 text-center fw-semibold" role="status" style="position: sticky; top: 76px; z-index: 1030;"></div>

<div class="row justify-content-center" id="examContentWrapper" style="visibility: hidden;">
    <div class="col-lg-8">
        {{-- autocomplete="off": the browser must never "restore" radio buttons on its own; the engine restores them from the saved draft. --}}
        <form action="{{ route('student.exams.submit', $exam) }}" method="POST" id="examForm" autocomplete="off">
            @csrf
            <input type="hidden" name="auto_submitted" id="autoSubmittedField" value="0">

            <div class="glass-panel rounded-4 p-5 mb-4 text-center">
                <h2 class="text-white fw-bold mb-3">{{ $exam->title }}</h2>
                @if($exam->description)
                    <p class="text-white opacity-75 mb-0">{{ strip_tags($exam->description) }}</p>
                @endif
                <div class="mt-4 pt-4 border-top border-white/10 d-flex justify-content-center gap-4">
                    <div class="text-white">
                        <span class="opacity-50 block text-sm">إجمالي الأسئلة</span>
                        <span class="fw-bold fs-5">{{ $exam->questions->count() }}</span>
                    </div>
                    <div class="text-white">
                        <span class="opacity-50 block text-sm">العلامة الكلية</span>
                        <span class="fw-bold fs-5">{{ $exam->questions->sum('points') }}</span>
                    </div>
                </div>
            </div>

            @foreach($exam->questions as $index => $question)
                @if($index > 0)
                    <hr class="exam-question-divider">
                @endif
                <div class="glass-panel rounded-4 p-4 p-md-5 mb-4">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <h5 class="text-gold fw-bold m-0">سؤال {{ $index + 1 }}</h5>
                        <span class="badge bg-secondary text-white">{{ $question->points }} {{ $question->points > 1 ? 'نقاط' : 'نقطة' }}</span>
                    </div>

                    <div class="text-white fs-5 lh-lg mb-5 content-area no-select">
                        {!! $question->content !!}
                    </div>

                    @if($question->type === 'multiple_choice' || $question->type === 'true_false')
                        <div class="d-flex flex-column gap-3">
                            @foreach($question->options as $option)
                                <label class="custom-radio-card p-3 rounded-3 border border-white/10 d-flex align-items-center gap-3 cursor-pointer transition-all">
                                    <div class="form-check form-check-custom form-check-solid form-check-sm m-0">
                                        <input class="form-check-input" type="radio"
                                               name="answers[{{ $question->id }}]"
                                               data-qid="{{ $question->id }}"
                                               value="{{ $option->id }}">
                                    </div>
                                    <span class="text-white fs-6 no-select">{{ $option->option_text }}</span>
                                </label>
                            @endforeach
                        </div>
                    @elseif($question->type === 'essay')
                        <div>
                            <textarea name="answers[{{ $question->id }}]" data-qid="{{ $question->id }}" class="form-control bg-dark text-white border-secondary"
                                      rows="5" placeholder="اكتب إجابتك هنا..."></textarea>
                        </div>
                    @endif
                </div>
            @endforeach

            <div class="text-center mt-5 mb-10">
                <button type="button" id="submitExamBtn" class="btn btn-gold btn-lg px-8 rounded-pill fw-bold">
                    <i class="bi bi-send-check-fill me-2"></i> تسليم الامتحان
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    .custom-radio-card {
        background: rgba(255,255,255,0.02);
    }
    .custom-radio-card:hover {
        border-color: rgba(255,255,255,0.3) !important;
    }
    .custom-radio-card.is-selected {
        border-color: var(--accent-color) !important;
        background-color: rgba(197, 168, 128, 0.1);
    }
    .text-gold {
        color: var(--accent-color);
    }
    .btn-gold {
        background: var(--accent-color);
        color: #000;
        border: none;
    }
    .btn-gold:hover {
        background: #d4af37;
        color: #000;
    }
    .border-gold {
        border-color: var(--accent-color) !important;
    }
    /* Fix images in CKEditor content */
    .content-area img {
        max-width: 100%;
        height: auto;
        border-radius: 0.5rem;
    }
    .exam-question-divider {
        border: none;
        border-top: 2px dashed rgba(197, 168, 128, 0.25);
        margin: 2rem 0;
    }
    .no-select {
        user-select: none;
        -webkit-user-select: none;
    }
    /* Block text selection across the whole exam paper (question content,
       labels, badges...) while still allowing normal typing/selection inside
       the actual answer fields (radio labels' text stays unselectable too,
       since selecting an option is done by clicking, not by highlighting). */
    #examContentWrapper {
        user-select: none;
        -webkit-user-select: none;
    }
    #examContentWrapper textarea,
    #examContentWrapper input[type="text"] {
        user-select: text;
        -webkit-user-select: text;
    }
    #examIntroOverlay {
        animation: examIntroFadeIn 0.4s ease-out;
    }
    @keyframes examIntroFadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    #examIntroOverlay.exam-intro-hidden {
        animation: examIntroFadeOut 0.5s ease-in forwards;
    }
    @keyframes examIntroFadeOut {
        from { opacity: 1; }
        to { opacity: 0; visibility: hidden; }
    }
    /* The start button must stand out clearly on the dark intro screen. */
    #examStartBtn {
        background: linear-gradient(135deg, #f1d27a 0%, #d4af37 55%, #c5a880 100%);
        color: #111;
        font-size: 1.35rem;
        padding: 0.9rem 3rem;
        min-width: 260px;
        border: 2px solid rgba(255, 255, 255, 0.85);
        box-shadow: 0 0 0 0 rgba(212, 175, 55, 0.6), 0 8px 24px rgba(212, 175, 55, 0.35);
        animation: examStartPulse 2s ease-in-out infinite;
    }
    #examStartBtn:hover,
    #examStartBtn:focus-visible {
        color: #000;
        filter: brightness(1.08);
        transform: translateY(-2px);
    }
    #examStartBtn:focus-visible {
        outline: 3px solid #fff;
        outline-offset: 3px;
    }
    @keyframes examStartPulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(212, 175, 55, 0.55), 0 8px 24px rgba(212, 175, 55, 0.35); }
        50% { box-shadow: 0 0 0 12px rgba(212, 175, 55, 0), 0 8px 24px rgba(212, 175, 55, 0.35); }
    }
    @media (prefers-reduced-motion: reduce) {
        #examStartBtn { animation: none; }
    }
    #examSubmitOverlay.is-warning .spinner-border {
        color: #f59e0b !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset_ver('student/js/exam-taker.js') }}"></script>
<script>
    const EXAM = @json($examState);
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const VIOLATION_LIMIT = 3;
    let totalViolations = 0;
    let examStarted = false;
    let autoSubmitting = false;
    let leavingForResult = false;
    const pendingViolations = [];

    // SweetAlert comes from a CDN; if it can not load (bad connection) fall back to native dialogs
    // so the student can still submit.
    const hasSwal = () => typeof Swal !== 'undefined';

    const engine = ExamTaker.init({
        examId: EXAM.examId,
        studentId: EXAM.studentId,
        attemptId: EXAM.attemptId,
        draftUrl: EXAM.draftUrl,
        submitUrl: EXAM.submitUrl,
        questionIds: EXAM.questionIds,
        answers: EXAM.answers,
        started: EXAM.started,
        remainingSeconds: EXAM.remainingSeconds,
        csrf: csrfToken,
    }, {}, {
        onTimeUp() {
            autoSubmitting = true;
            engine.submit({ message: 'انتهى الوقت! جارٍ تسليم إجاباتك...' });
        },
        onSessionExpired() {
            if (hasSwal()) {
                Swal.fire({
                    title: 'انتهت الجلسة',
                    text: 'إجاباتك محفوظة على جهازك. سجّل الدخول مرة أخرى وافتح الامتحان لاستكماله.',
                    icon: 'warning',
                    confirmButtonText: 'تسجيل الدخول',
                    allowOutsideClick: false,
                }).then(() => { leavingForResult = true; window.location.href = '{{ route('student.login') }}'; });
            }
        },
        beforeNavigate() { leavingForResult = true; },
        // Violations the server has not acknowledged yet (made while offline) are attached to the submission.
        extraSubmitFields() { return { pending_violations: JSON.stringify(pendingViolations) }; },
    });

    function submitExamForm(options) {
        return engine.submit(options || {});
    }

    // Shown both when the student presses "submit" and when they try to leave the exam (back button).
    const SUBMIT_PROMPT = 'في حال انتهائك من حل جميع الأسئلة اضغط على "تأكيد التسليم".';
    let confirmOpen = false;

    function confirmSubmit() {
        if (confirmOpen || autoSubmitting || engine.isSubmitting()) return;

        const unanswered = EXAM.questionIds.length - ExamTaker.countAnswered(engine.getState(), EXAM.questionIds);
        const warning = unanswered > 0 ? `تنبيه: لم تجب على ${unanswered} سؤال بعد.` : '';

        if (!hasSwal()) {
            if (window.confirm(SUBMIT_PROMPT + (warning ? '\n' + warning : '') + '\n\nموافق = تأكيد التسليم، إلغاء = إلغاء التسليم')) submitExamForm();
            return;
        }

        confirmOpen = true;
        Swal.fire({
            title: 'تسليم الامتحان',
            text: SUBMIT_PROMPT + (warning ? ' ' + warning : ''),
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#c5a880',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'تأكيد التسليم',
            cancelButtonText: 'إلغاء التسليم',
            reverseButtons: false,
            focusCancel: true,
        }).then((result) => {
            confirmOpen = false;
            if (result.isConfirmed) {
                submitExamForm();
            }
        });
    }

    document.getElementById('submitExamBtn').addEventListener('click', confirmSubmit);

    // ---- anti-cheat violations (kept working while offline: they are queued and sent on reconnect) ----

    // Every violation gets a unique id so the server never counts one twice, even if it is both
    // flushed after reconnecting and attached to the final submission.
    const newViolationId = () => (window.crypto && crypto.randomUUID) ? crypto.randomUUID() : (Date.now().toString(36) + Math.random().toString(36).slice(2));

    function postViolation(violation) {
        return fetch(EXAM.violationUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(violation)
        }).then(res => {
            if (!res.ok) throw new Error('http ' + res.status);
            return res.json();
        });
    }

    let flushingViolations = false;
    function flushPendingViolations() {
        // navigator.onLine is only a hint, so just try; a failed attempt is retried on the next tick / online event
        if (!pendingViolations.length || flushingViolations) return;
        flushingViolations = true;
        const violation = pendingViolations[0];
        postViolation(violation).then(() => {
            // the server counted it (or had already counted it): drop it, even if more were queued meanwhile
            const i = pendingViolations.findIndex(v => v.id === violation.id);
            if (i !== -1) pendingViolations.splice(i, 1);
            flushingViolations = false;
            flushPendingViolations();
        }).catch(() => { flushingViolations = false; });
    }
    window.addEventListener('online', flushPendingViolations);
    setInterval(flushPendingViolations, 20000);

    function handleViolationTotal(total, message) {
        totalViolations = Math.max(totalViolations, total);

        if (totalViolations >= VIOLATION_LIMIT) {
            autoSubmitting = true;
            document.getElementById('autoSubmittedField').value = '1';
            submitExamForm({
                auto: true,
                message: 'تجاوزت الحد المسموح به لعدد مرات الخروج من صفحة الامتحان. جارٍ تسليم إجاباتك الآن.'
            });
            return;
        }

        // Show warning badge
        const badge = document.getElementById('violationBadge');
        const countSpan = document.getElementById('violationCountSpan');
        if (badge && countSpan) {
            badge.classList.remove('d-none');
            countSpan.textContent = totalViolations;
        }

        if (hasSwal()) {
            Swal.fire({
                title: 'تنبيه مراقبة',
                text: message + ' (' + totalViolations + ' من ' + VIOLATION_LIMIT + ')',
                icon: 'warning',
                confirmButtonText: 'أتعهد بعدم التكرار',
                confirmButtonColor: '#d33',
                allowOutsideClick: false
            });
        }
    }

    function reportViolation(type, message) {
        if (!examStarted || autoSubmitting || engine.isSubmitting()) return;

        const violation = { type: type, id: newViolationId() };
        postViolation(violation).then(data => {
            handleViolationTotal(data.total, message);
        }).catch(() => {
            // No connection: count it locally so going offline is not a loophole; it is reported on
            // reconnect or together with the submission, whichever comes first (the id prevents double counting).
            pendingViolations.push(violation);
            handleViolationTotal(totalViolations + 1, message);
        });
    }

    // Anti-cheat deterrents (best-effort — cannot fully block DevTools, printscreen, or force the tab to stay open)
    document.addEventListener('contextmenu', e => e.preventDefault());
    document.addEventListener('copy', e => { if (examStarted) e.preventDefault(); });
    document.addEventListener('cut', e => { if (examStarted) e.preventDefault(); });
    document.addEventListener('paste', e => {
        // Still allow pasting into the answer fields themselves (essay
        // textareas), just block pasting into anything else on the page.
        const tag = e.target.tagName;
        if (examStarted && tag !== 'TEXTAREA' && tag !== 'INPUT') e.preventDefault();
    });
    document.addEventListener('dragstart', e => { if (examStarted) e.preventDefault(); });
    document.addEventListener('selectstart', e => { if (examStarted) e.preventDefault(); });
    document.addEventListener('keydown', e => {
        const blockedKey = e.key === 'F12'
            || (e.ctrlKey && e.shiftKey && ['I', 'J', 'C', 'i', 'j', 'c'].includes(e.key))
            || (e.ctrlKey && ['u', 'U', 's', 'S', 'p', 'P'].includes(e.key))
            || (e.ctrlKey && e.key.toLowerCase() === 'a' && !['TEXTAREA', 'INPUT'].includes(e.target.tagName));
        if (blockedKey) e.preventDefault();
    });

    // Prevent loading from bfcache when user clicks back button
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            window.location.reload();
        }
    });

    // Trap the browser back/forward buttons: keep re-pushing the current entry so the
    // student never actually leaves, and offer to hand the exam in instead
    // ("تأكيد التسليم" submits, "إلغاء التسليم" returns to the questions).
    history.pushState(null, '', location.href);
    window.addEventListener('popstate', () => {
        if (examStarted && !autoSubmitting) {
            history.pushState(null, '', location.href);
            confirmSubmit();
        }
    });

    // Warn before closing/refreshing the tab while the exam is in progress
    // (answers are autosaved, so a refresh is safe, but a stray close is still worth a prompt).
    window.addEventListener('beforeunload', (e) => {
        if (examStarted && !autoSubmitting && !leavingForResult && !engine.isSubmitting()) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            reportViolation('tab_switch', 'تم تسجيل خروجك من صفحة الامتحان.');
        }
    });

    document.addEventListener('fullscreenchange', () => {
        if (examStarted && !document.fullscreenElement && !autoSubmitting && !engine.isSubmitting()) {
            reportViolation('fullscreen_exit', 'يجب البقاء في وضع ملء الشاشة أثناء الامتحان.');
            const el = document.documentElement;
            if (el.requestFullscreen) {
                el.requestFullscreen().catch(() => {});
            }
        }
    });

    // ---- timer face before the exam starts + resume wording ----

    const timerDisplay = document.getElementById('countdownTimer');
    if (timerDisplay && EXAM.remainingSeconds !== null) {
        timerDisplay.textContent = ExamTaker.formatClock(EXAM.remainingSeconds);
    }
    if (EXAM.started) {
        document.getElementById('examResumeNote').classList.remove('d-none');
        document.getElementById('examStartBtnLabel').textContent = 'استئناف الامتحان';
    }

    // Entry animation flow
    document.getElementById('examStartBtn').addEventListener('click', function () {
        const el = document.documentElement;
        if (el.requestFullscreen) {
            el.requestFullscreen().catch(() => {});
        }

        document.getElementById('examIntroContent').classList.add('d-none');
        const countdownEl = document.getElementById('examCountdownContent');
        const numberEl = document.getElementById('examCountdownNumber');
        countdownEl.classList.remove('d-none');

        let n = 3;
        numberEl.textContent = n;
        const countdown = setInterval(() => {
            n--;
            if (n <= 0) {
                clearInterval(countdown);
                beginExam();
            } else {
                numberEl.textContent = n;
            }
        }, 700);
    });

    function beginExam() {
        const overlay = document.getElementById('examIntroOverlay');
        overlay.classList.add('exam-intro-hidden');
        document.getElementById('examContentWrapper').style.visibility = 'visible';
        setTimeout(() => overlay.remove(), 500);

        examStarted = true;
        engine.start();
    }
</script>
@endpush
@endsection
