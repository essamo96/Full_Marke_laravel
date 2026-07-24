@extends('layouts.exam')

@section('title', 'تقديم الامتحان: ' . $exam->title)
@section('exam_title', $exam->title)

@section('exam_timer')
    <div class="d-flex align-items-center gap-3">
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
        <p class="text-white opacity-75 mb-5" style="max-width: 480px;">
            هذا امتحان مراقب. سيتم تسجيل عدد مرات خروجك من الصفحة، ولا يجوز نسخ الأسئلة أو مغادرة وضع ملء الشاشة.
            بالضغط على "ابدأ الامتحان" أنت توافق على هذه الشروط.
        </p>
        <button type="button" id="examStartBtn" class="btn btn-gold btn-lg px-8 rounded-pill fw-bold">
            <i class="bi bi-play-circle-fill me-2"></i> ابدأ الامتحان
        </button>
    </div>
    <div id="examCountdownContent" class="d-none">
        <div id="examCountdownNumber" class="fw-bold text-gold" style="font-size: 8rem; line-height: 1;">3</div>
        <p class="text-white opacity-75 mt-3">استعد...</p>
    </div>
</div>

<div class="row justify-content-center" id="examContentWrapper" style="visibility: hidden;">
    <div class="col-lg-8">
        <form action="{{ route('student.exams.submit', $exam) }}" method="POST" id="examForm" x-data="examEngine()">
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
                                <label class="custom-radio-card p-3 rounded-3 border d-flex align-items-center gap-3 cursor-pointer transition-all"
                                       :class="answers[{{ $question->id }}] == {{ $option->id }} ? 'border-gold bg-gold/10' : 'border-white/10 hover:border-white/30'">

                                    <div class="form-check form-check-custom form-check-solid form-check-sm m-0">
                                        <input class="form-check-input" type="radio"
                                               name="answers[{{ $question->id }}]"
                                               value="{{ $option->id }}"
                                               x-model="answers[{{ $question->id }}]" required>
                                    </div>
                                    <span class="text-white fs-6 no-select">{{ $option->option_text }}</span>
                                </label>
                            @endforeach
                        </div>
                    @elseif($question->type === 'essay')
                        <div>
                            <textarea name="answers[{{ $question->id }}]" class="form-control bg-dark text-white border-secondary"
                                      rows="5" placeholder="اكتب إجابتك هنا..." required></textarea>
                        </div>
                    @endif
                </div>
            @endforeach

            <div class="text-center mt-5 mb-10">
                <button type="submit" class="btn btn-gold btn-lg px-8 rounded-pill fw-bold"
                        @click.prevent="confirmSubmit">
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
    .bg-gold\/10 {
        background-color: rgba(197, 168, 128, 0.1);
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
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('examEngine', () => ({
            answers: {},

            confirmSubmit() {
                Swal.fire({
                    title: 'هل أنت متأكد من تسليم الامتحان؟',
                    text: 'لن تتمكن من تعديل إجاباتك بعد التسليم.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#c5a880',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'نعم، قم بالتسليم',
                    cancelButtonText: 'إلغاء'
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitExamForm();
                    }
                });
            }
        }));
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const violationUrl = '{{ route("student.exams.violation", $exam) }}';
    const VIOLATION_LIMIT = 3;
    let totalViolations = 0;
    let examStarted = false;
    let autoSubmitting = false;

    function submitExamForm() {
        document.getElementById('examForm').submit();
    }

    function reportViolation(type, message) {
        if (!examStarted || autoSubmitting) return;

        fetch(violationUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ type: type })
        }).then(res => res.json()).then(data => {
            totalViolations = data.total;

            if (totalViolations >= VIOLATION_LIMIT) {
                autoSubmitting = true;
                document.getElementById('autoSubmittedField').value = '1';
                Swal.fire({
                    title: 'تم إنهاء الامتحان',
                    text: 'تجاوزت الحد المسموح به لعدد مرات الخروج من صفحة الامتحان. سيتم تسليم إجاباتك الآن.',
                    icon: 'error',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    timer: 3500
                }).then(() => submitExamForm());
                return;
            }

            // Show warning badge
            const badge = document.getElementById('violationBadge');
            const countSpan = document.getElementById('violationCountSpan');
            if (badge && countSpan) {
                badge.classList.remove('d-none');
                countSpan.textContent = totalViolations;
            }

            Swal.fire({
                title: 'تنبيه مراقبة',
                text: message + ' (' + totalViolations + ' من ' + VIOLATION_LIMIT + ')',
                icon: 'warning',
                confirmButtonText: 'أتعهد بعدم التكرار',
                confirmButtonColor: '#d33',
                allowOutsideClick: false
            });
        }).catch(() => {});
    }

    // Anti-cheat deterrents (best-effort — cannot fully block DevTools or force the tab to stay open)
    document.addEventListener('contextmenu', e => e.preventDefault());
    document.addEventListener('copy', e => e.preventDefault());
    document.addEventListener('cut', e => e.preventDefault());
    document.addEventListener('selectstart', e => { if (examStarted) e.preventDefault(); });
    document.addEventListener('keydown', e => {
        const blockedKey = e.key === 'F12'
            || (e.ctrlKey && e.shiftKey && ['I', 'J', 'C', 'i', 'j', 'c'].includes(e.key))
            || (e.ctrlKey && ['u', 'U', 's', 'S'].includes(e.key));
        if (blockedKey) e.preventDefault();
    });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            reportViolation('tab_switch', 'تم تسجيل خروجك من صفحة الامتحان.');
        }
    });

    document.addEventListener('fullscreenchange', () => {
        if (examStarted && !document.fullscreenElement && !autoSubmitting) {
            reportViolation('fullscreen_exit', 'يجب البقاء في وضع ملء الشاشة أثناء الامتحان.');
            const el = document.documentElement;
            if (el.requestFullscreen) {
                el.requestFullscreen().catch(() => {});
            }
        }
    });

    // Removed beforeunload listener as requested

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
        startTimer();
    }

    @if($exam->duration_minutes)
        let durationSeconds = {{ $exam->duration_minutes * 60 }};
        const timerDisplay = document.getElementById('countdownTimer');
        let timerInterval = null;

        function updateTimer() {
            let h = Math.floor(durationSeconds / 3600);
            let m = Math.floor((durationSeconds % 3600) / 60);
            let s = durationSeconds % 60;

            h = h < 10 ? '0' + h : h;
            m = m < 10 ? '0' + m : m;
            s = s < 10 ? '0' + s : s;

            timerDisplay.textContent = h + ':' + m + ':' + s;

            if (durationSeconds <= 300) {
                timerDisplay.classList.remove('text-white');
                timerDisplay.classList.add('text-danger');
                timerDisplay.classList.add('animate-pulse');
            }

            if (durationSeconds <= 0) {
                clearInterval(timerInterval);
                autoSubmitting = true;
                Swal.fire({
                    title: 'انتهى الوقت!',
                    text: 'سيتم تسليم إجاباتك تلقائياً.',
                    icon: 'info',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    timer: 3000
                }).then(() => submitExamForm());
            }

            durationSeconds--;
        }

        function startTimer() {
            updateTimer();
            timerInterval = setInterval(updateTimer, 1000);
        }
    @else
        function startTimer() {}
    @endif
</script>
@endpush
@endsection
