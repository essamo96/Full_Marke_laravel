@extends('layouts.student')

@section('title', 'النتائج')
@section('page_title_en', 'Results')
@section('page_title_ar', 'النتائج')

@section('content')
<div class="fade-in-up">
    <div class="mb-5 d-flex justify-content-between align-items-center">
        <h2 class="fw-bold mb-0 text-white" data-en="My Results" data-ar="نتائجي">نتائجي</h2>
    </div>

    <div class="row g-4">
        @forelse($results as $result)
            <div class="col-md-6 col-lg-4">
                <div class="glass-panel rounded-4 h-100 p-4 hover-elevate">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-gold/20 p-3 rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-award-fill text-gold fs-3"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-white mb-1">{{ $result->exam_name }}</h5>
                                <div class="text-white opacity-75 fs-7">{{ $result->group->name ?? 'بدون مجموعة' }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-dark bg-opacity-25 rounded-3 p-3 mb-3 d-flex justify-content-between align-items-center border border-white border-opacity-10">
                        <span class="text-white opacity-75">العلامة:</span>
                        <div class="d-flex align-items-baseline gap-1">
                            <span class="fs-2 fw-bold {{ ($result->score / max($result->max_score, 1)) >= 0.5 ? 'text-success' : 'text-danger' }}">
                                {{ $result->score }}
                            </span>
                            <span class="text-white opacity-50">/ {{ $result->max_score }}</span>
                        </div>
                    </div>
                    
                    @if($result->notes)
                        <div class="fs-8 text-white opacity-50 mb-3">
                            <i class="bi bi-info-circle me-1"></i> {{ $result->notes }}
                        </div>
                    @endif

                    <div class="pt-3 border-top border-white border-opacity-10 d-flex justify-content-between align-items-center">
                        <div class="fs-8 text-white opacity-50">
                            تاريخ التقديم: {{ $result->created_at->format('Y-m-d') }}
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="glass-panel rounded-4 p-5 text-center text-white opacity-75">
                    <i class="bi bi-journal-x fs-1 d-block mb-3"></i>
                    <h5 data-en="No results available currently" data-ar="لا يوجد نتائج متاحة حالياً">لا يوجد نتائج متاحة حالياً</h5>
                </div>
            </div>
        @endforelse
    </div>
    
    @if($results->hasPages())
        <div class="mt-5 d-flex justify-content-center">
            {{ $results->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
