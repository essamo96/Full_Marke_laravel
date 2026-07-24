@extends('layouts.teacher')

@section('title', $subject->name . ' | FULL MARK ACADEMY')
@section('page_title_en', $subject->name)
@section('page_title_ar', $subject->name)

@push('styles')
<style>
.teacher-accordion .accordion-collapse { visibility: visible !important; }
.teacher-accordion .accordion-item { background: transparent; border: none; border-bottom: 1px solid rgba(255,255,255,0.05); }
.teacher-accordion .accordion-button { background: transparent; color: var(--text-primary); box-shadow: none; }
.teacher-accordion .accordion-button:not(.collapsed) { color: var(--accent-color); background: rgba(255,255,255,0.02); }
</style>
@endpush

@section('content')

  <h1 class="h3 fw-bold mb-4" style="color: var(--text-primary);">{{ $subject->name }}</h1>

  <div class="row g-4 mb-4">
    @forelse($groups as $group)
      @php($group->setRelation('subject', $subject))
      <div class="col-md-6 col-xl-4">
        @include('teacher.groups._card', ['group' => $group])
      </div>
    @empty
      <div class="col-12">
        <div class="glass-panel rounded-4 p-4 text-center text-muted" data-en="No groups assigned to you for this subject." data-ar="لا توجد مجموعات مسندة إليك لهذه المادة.">لا توجد مجموعات مسندة إليك لهذه المادة.</div>
      </div>
    @endforelse
  </div>


  <div class="mt-4">
    <a href="{{ route('teacher.content.manage', $subject) }}" class="btn btn-luxury" data-en="Manage Resources" data-ar="إدارة الموارد">إدارة الموارد</a>
  </div>

@endsection
