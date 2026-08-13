@extends('layouts.exam')

@section('title', 'دخول الامتحان: ' . $exam->title)
@section('exam_title', $exam->title)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-5">
        <div class="glass-panel rounded-4 p-5">
            <div class="text-center mb-4">
                <i class="bi bi-person-badge text-gold" style="font-size: 3rem;"></i>
                <h3 class="text-white fw-bold mt-3 mb-1">{{ $exam->title }}</h3>
                <p class="text-white opacity-75 mb-0">أنت تدخل هذا الامتحان كضيف غير مسجل في المنصة. الرجاء إدخال بياناتك قبل البدء.</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('guest.exam.register', $exam) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label text-white">الاسم الكامل</label>
                    <input type="text" name="guest_name" class="form-control bg-dark text-white border-secondary" value="{{ old('guest_name') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-white">رقم الجوال</label>
                    <input type="text" name="guest_phone" class="form-control bg-dark text-white border-secondary" value="{{ old('guest_phone') }}" required>
                </div>
                <div class="mb-4">
                    <label class="form-label text-white">البريد الإلكتروني (اختياري)</label>
                    <input type="email" name="guest_email" class="form-control bg-dark text-white border-secondary" value="{{ old('guest_email') }}">
                </div>

                <button type="submit" class="btn btn-gold btn-lg w-100 rounded-pill fw-bold">
                    <i class="bi bi-arrow-left-circle-fill me-2"></i> متابعة إلى الامتحان
                </button>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .text-gold { color: var(--accent-color); }
    .btn-gold { background: var(--accent-color); color: #000; border: none; }
    .btn-gold:hover { background: #d4af37; color: #000; }
</style>
@endpush
@endsection
