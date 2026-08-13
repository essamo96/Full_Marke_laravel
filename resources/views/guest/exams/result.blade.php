@extends('layouts.exam')

@section('title', 'نتيجتك: ' . $submission->exam->title)
@section('exam_title', $submission->exam->title)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="glass-panel rounded-4 p-5 text-center">
            <i class="bi bi-award-fill text-gold" style="font-size: 3.5rem;"></i>
            <h3 class="text-white fw-bold mt-3 mb-1">{{ $submission->exam->title }}</h3>
            <p class="text-white opacity-75 mb-4">مقدَّم باسم: {{ $submission->guest_name }}</p>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-warning">{{ session('error') }}</div>
            @endif

            <div class="bg-dark bg-opacity-25 rounded-3 p-4 mb-4 d-flex justify-content-between align-items-center border border-white border-opacity-10">
                <span class="text-white opacity-75">العلامة:</span>
                <div class="d-flex align-items-baseline gap-1">
                    <span class="fs-1 fw-bold {{ ($submission->score / max($submission->max_score, 1)) >= 0.5 ? 'text-success' : 'text-danger' }}">
                        {{ $submission->score }}
                    </span>
                    <span class="text-white opacity-50">/ {{ $submission->max_score }}</span>
                </div>
            </div>

            @if($submission->notes)
                <div class="fs-8 text-white opacity-50 mb-3">
                    <i class="bi bi-info-circle me-1"></i> {{ $submission->notes }}
                </div>
            @endif

            <div class="fs-8 text-white opacity-50">
                وقت التسليم: {{ $submission->created_at->format('Y-m-d H:i') }}
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .text-gold { color: var(--accent-color); }
</style>
@endpush
@endsection
