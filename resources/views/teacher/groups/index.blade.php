@extends('layouts.teacher')

@section('title', 'My Groups | FULL MARK ACADEMY')
@section('page_title_en', 'Study Groups')
@section('page_title_ar', 'المجموعات الدراسية')

@section('content')

  <h1 class="h3 fw-bold mb-4" style="color: var(--text-primary);" data-en="Study Groups" data-ar="المجموعات الدراسية">المجموعات الدراسية</h1>

  <div class="row g-4">
    @forelse($groups as $group)
      <div class="col-md-6 col-xl-4">
        @include('teacher.groups._card', ['group' => $group])
      </div>
    @empty
      <div class="col-12">
        <div class="glass-panel rounded-4 p-5 text-center text-muted" data-en="No groups assigned to you yet." data-ar="لا توجد مجموعات مسندة إليك بعد.">لا توجد مجموعات مسندة إليك بعد.</div>
      </div>
    @endforelse
  </div>

@endsection

@push('styles')
<style>
.teacher-group-card-link:hover .teacher-group-card { border-color: var(--accent-color) !important; transform: translateY(-4px); box-shadow: 0 12px 30px rgba(197,168,128,0.12); }
.teacher-group-card { transition: all 0.3s ease; }
</style>
@endpush
