@extends('layouts.student')

@section('title', 'الامتحانات')
@section('page_title_en', 'Exams')
@section('page_title_ar', 'الامتحانات')

@section('content')
<div class="fade-in-up">
    <div class="mb-5 d-flex justify-content-between align-items-center">
        <h2 class="fw-bold mb-0 text-white" data-en="My Exams" data-ar="الامتحانات الخاصة بي">الامتحانات الخاصة بي</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success bg-success text-white border-0 d-flex align-items-center gap-3">
            <i class="bi bi-check-circle-fill fs-3 text-white"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <div class="row g-4">
        @forelse($exams as $exam)
            @php
                $now = now();
                $isAvailable = true;
                $statusText = 'متاح الآن';
                $statusClass = 'bg-success';
                
                if($exam->start_time && $now->lt($exam->start_time)) {
                    $isAvailable = false;
                    $statusText = 'يبدأ لاحقاً';
                    $statusClass = 'bg-warning';
                } elseif($exam->end_time && $now->gt($exam->end_time)) {
                    $isAvailable = false;
                    $statusText = 'انتهى وقته';
                    $statusClass = 'bg-danger';
                }
            @endphp
            <div class="col-md-6 col-lg-4">
                <div class="glass-panel rounded-4 h-100 p-4 d-flex flex-column hover-elevate">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="badge {{ $statusClass }} rounded-pill px-3">{{ $statusText }}</span>
                        <div class="text-white opacity-75 fs-7">
                            <i class="bi bi-clock me-1"></i> {{ $exam->duration_minutes ? $exam->duration_minutes . ' دقيقة' : 'مفتوح' }}
                        </div>
                    </div>
                    
                    <h4 class="fw-bold text-white mb-1">{{ $exam->title }}</h4>
                    <p class="text-white opacity-75 fs-7 mb-4">{{ $exam->subject->name ?? '' }} - {{ $exam->group->name ?? '' }}</p>
                    
                    <div class="mt-auto pt-3 border-top border-white/10 d-flex justify-content-between align-items-center">
                        <div>
                            @if($exam->start_time)
                                <div class="fs-8 text-white opacity-50">تاريخ: {{ $exam->start_time->format('Y-m-d') }}</div>
                            @endif
                        </div>
                        <a href="{{ $isAvailable ? route('student.exams.take', $exam) : '#' }}" 
                           class="btn btn-sm btn-gold rounded-pill {{ !$isAvailable ? 'disabled opacity-50' : '' }}">
                            <i class="bi bi-play-circle-fill me-1"></i> بدء الامتحان
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="glass-panel rounded-4 p-5 text-center text-white opacity-75">
                    <i class="bi bi-journal-x fs-1 d-block mb-3"></i>
                    <h5 data-en="No exams available currently" data-ar="لا يوجد امتحانات متاحة حالياً">لا يوجد امتحانات متاحة حالياً</h5>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
