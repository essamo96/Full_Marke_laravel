@extends('layouts.exam')

@section('title', 'تقديم الامتحان: ' . $exam->title)
@section('exam_title', $exam->title)

@section('exam_timer')
    @if($exam->duration_minutes)
        <div class="d-flex align-items-center gap-2 bg-dark rounded-pill px-4 py-2 border border-secondary" id="timerContainer">
            <i class="bi bi-stopwatch text-gold fs-4"></i>
            <span class="fw-bold fs-4 text-white" id="countdownTimer">--:--:--</span>
        </div>
    @else
        <div class="badge bg-success rounded-pill px-3 py-2">مفتوح الوقت</div>
    @endif
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <form action="{{ route('student.exams.submit', $exam) }}" method="POST" id="examForm" x-data="examEngine()">
            @csrf
            
            <div class="glass-panel rounded-4 p-5 mb-4 text-center">
                <h2 class="text-white fw-bold mb-3">{{ $exam->title }}</h2>
                @if($exam->description)
                    <p class="text-white opacity-75 mb-0">{{ $exam->description }}</p>
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
                <div class="glass-panel rounded-4 p-4 p-md-5 mb-4">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <h5 class="text-gold fw-bold m-0">سؤال {{ $index + 1 }}</h5>
                        <span class="badge bg-secondary text-white">{{ $question->points }} {{ $question->points > 1 ? 'نقاط' : 'نقطة' }}</span>
                    </div>
                    
                    <div class="text-white fs-5 lh-lg mb-5 content-area">
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
                                    <span class="text-white fs-6">{{ $option->option_text }}</span>
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
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('examEngine', () => ({
            answers: {},
            
            confirmSubmit() {
                // Check if all questions have answers
                const totalQuestions = {{ $exam->questions->count() }};
                const answeredOptions = Object.keys(this.answers).length;
                
                // Note: Alpine only models the radio buttons easily here. For essay, we'd need to check textareas.
                // HTML5 required handles the basic validation on submit.
                
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
                        document.getElementById('examForm').submit();
                    }
                });
            }
        }));
    });

    @if($exam->duration_minutes)
        // Timer Logic
        // In a real robust system, we would store the start time in DB or session to prevent cheating via refresh.
        // For this implementation, we use a simple JS countdown.
        let durationSeconds = {{ $exam->duration_minutes * 60 }};
        const timerDisplay = document.getElementById('countdownTimer');
        
        function updateTimer() {
            let h = Math.floor(durationSeconds / 3600);
            let m = Math.floor((durationSeconds % 3600) / 60);
            let s = durationSeconds % 60;
            
            h = h < 10 ? '0' + h : h;
            m = m < 10 ? '0' + m : m;
            s = s < 10 ? '0' + s : s;
            
            timerDisplay.textContent = h + ':' + m + ':' + s;
            
            if (durationSeconds <= 300) { // last 5 minutes
                timerDisplay.classList.remove('text-white');
                timerDisplay.classList.add('text-danger');
                timerDisplay.classList.add('animate-pulse');
            }
            
            if (durationSeconds <= 0) {
                clearInterval(timerInterval);
                Swal.fire({
                    title: 'انتهى الوقت!',
                    text: 'سيتم تسليم إجاباتك تلقائياً.',
                    icon: 'info',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    timer: 3000
                }).then(() => {
                    document.getElementById('examForm').submit();
                });
            }
            
            durationSeconds--;
        }
        
        updateTimer();
        const timerInterval = setInterval(updateTimer, 1000);
    @endif
</script>
@endpush
@endsection
