<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-5 pb-4 border-bottom">
    <div>
        <div class="fw-bold text-gray-800 fs-4">{{ $grade->student?->full_name_ar ?? $grade->student?->full_name_en }}</div>
        <div class="text-muted fs-7">{{ $grade->exam?->title ?? $grade->exam_name }} · {{ $grade->group?->name }}</div>
    </div>
    <div class="d-flex align-items-center gap-3">
        <span class="fs-2 fw-bold text-primary">{{ $grade->score }} / {{ $grade->max_score }}</span>
        @if($grade->admin_approved_at)
            <span class="badge badge-light-success">معتمدة</span>
        @elseif($grade->teacher_reviewed_at)
            <span class="badge badge-light-warning">بانتظار الاعتماد</span>
        @else
            <span class="badge badge-light-secondary">بانتظار المدرّس</span>
        @endif
    </div>
</div>

@if(($grade->tab_switch_count ?? 0) + ($grade->fullscreen_exit_count ?? 0) > 0)
    <div class="alert alert-warning py-3">
        مخالفات: تبويب {{ $grade->tab_switch_count ?? 0 }} · ملء الشاشة {{ $grade->fullscreen_exit_count ?? 0 }}
        @if($grade->auto_submitted) · تسليم تلقائي @endif
    </div>
@endif

@include('partials.exam-answers-review', ['grade' => $grade, 'showCorrect' => true, 'compact' => true])
