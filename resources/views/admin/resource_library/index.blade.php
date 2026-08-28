@extends('layouts.admin')

@section('title', 'مكتبة المرفقات')

@push('styles')
  <link rel="stylesheet" href="{{ asset_ver('assets/css/admin-resources.css') }}">
  <link rel="stylesheet" href="{{ asset_ver('assets/css/curriculum-accordion.css') }}">
@endpush

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h3 fw-bold mb-0 text-primary"><i class="bi bi-collection-play me-2 text-gold"></i> مكتبة المرفقات وتوزيع الموارد</h1>
</div>

<div class="glass-panel rounded-4 p-4 mb-4" style="border: 1px solid var(--separator-color);">
  <form action="{{ route('admin.resource-library.index') }}" method="GET" class="row g-3 align-items-end">
    <div class="col-md-5">
      <label class="form-label text-muted">اختر المادة الدراسية لعرض مكتبة المرفقات:</label>
      <select name="subject_id" class="form-select bg-dark text-light border-secondary" onchange="this.form.submit()">
        <option value="">-- اختر المادة --</option>
        @foreach($subjects as $subject)
          <option value="{{ $subject->id }}" {{ $selectedSubjectId == $subject->id ? 'selected' : '' }}>
            {{ $subject->name }} ({{ $subject->program->title ?? '' }})
          </option>
        @endforeach
      </select>
    </div>
  </form>
</div>

@if($selectedSubject)
  
  @if($groups->isEmpty())
    <div class="alert alert-warning text-center">لا توجد مجموعات مسجلة في هذه المادة حالياً.</div>
  @else
    
    <div class="accordion admin-accordion" id="groupsAccordion">
      @foreach($groups as $groupIndex => $group)
        <div class="glass-panel rounded-4 mb-4 overflow-hidden" style="border: 1px solid var(--separator-color);">
          <div class="accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button fw-bold {{ $groupIndex === 0 ? '' : 'collapsed' }} fs-5" type="button" data-bs-toggle="collapse" data-bs-target="#groupPanel{{ $group->id }}">
                <i class="bi bi-people-fill text-gold me-2"></i> مجموعة: {{ $group->name }}
              </button>
            </h2>
            <div id="groupPanel{{ $group->id }}" class="accordion-collapse collapse {{ $groupIndex === 0 ? 'show' : '' }}">
              <div class="accordion-body p-4 pt-2">
                
                @forelse($units as $unitIndex => $unit)
                  
                  @php 
                    // Calculate resources available for THIS group in this unit
                    $groupUnitResourceCount = 0;
                    foreach($unit->lessons as $lesson) {
                        $groupUnitResourceCount += $lesson->resources->filter(function($r) use ($group) {
                            $gids = is_string($r->group_ids) ? json_decode($r->group_ids, true) : $r->group_ids;
                            return empty($gids) || in_array($group->id, $gids);
                        })->count();
                    }
                  @endphp
                  
                  @continue($groupUnitResourceCount === 0)
                  
                  <div class="unit-card mb-3">
                    <div class="unit-toggle" data-bs-toggle="collapse" data-bs-target="#unit_{{ $group->id }}_{{ $unit->id }}" aria-expanded="{{ $unitIndex === 0 ? 'true' : 'false' }}">
                      <span class="unit-num">{{ $unitIndex + 1 }}</span>
                      <span class="flex-grow-1">
                        <span class="unit-title d-block">{{ $unit->name_ar ?? $unit->name_en }}</span>
                        <span class="unit-meta d-block">{{ $unit->lessons->count() }} درس · {{ $groupUnitResourceCount }} مورد لهذه المجموعة</span>
                      </span>
                      <i class="bi bi-chevron-down"></i>
                    </div>
                    
                    <div id="unit_{{ $group->id }}_{{ $unit->id }}" class="collapse {{ $unitIndex === 0 ? 'show' : '' }}">
                      @forelse($unit->lessons as $lesson)
                        @php
                            $groupLessonResources = $lesson->resources->filter(function($r) use ($group) {
                                $gids = is_string($r->group_ids) ? json_decode($r->group_ids, true) : $r->group_ids;
                                return empty($gids) || in_array($group->id, $gids);
                            });
                        @endphp
                        
                        @continue($groupLessonResources->isEmpty())
                        
                        <div class="lesson-block">
                          <div class="lesson-toggle collapsed" data-bs-toggle="collapse" data-bs-target="#lesson_{{ $group->id }}_{{ $lesson->id }}" aria-expanded="false">
                            <i class="bi bi-journal-text" style="color: var(--accent-color);"></i>
                            <span class="flex-grow-1">{{ $lesson->name_ar ?? $lesson->name_en }}</span>
                            <div class="d-flex gap-2 align-items-center me-3">
                              <span class="lesson-count">{{ $groupLessonResources->count() }} مرفق</span>
                            </div>
                            <i class="bi bi-chevron-down"></i>
                          </div>

                          <div id="lesson_{{ $group->id }}_{{ $lesson->id }}" class="collapse">
                            <div class="lesson-resources mt-2">
                              <div class="row g-3 mb-3">
                                @foreach($groupLessonResources as $resource)
                                  @php
                                    $resourceType = $resource->type ?? 'link';
                                    $iconMap = [
                                      'video' => 'bi-play-circle-fill',
                                      'document' => 'bi-file-earmark-pdf-fill',
                                      'image' => 'bi-image-fill',
                                      'link' => 'bi-link-45deg',
                                      'zoom' => 'bi-camera-video-fill',
                                    ];
                                    $icon = $iconMap[$resourceType] ?? 'bi-link-45deg';
                                    $title = match($resourceType) {
                                      'video' => 'فيديو',
                                      'document' => 'ملف',
                                      'image' => 'صورة',
                                      'zoom' => 'Zoom',
                                      default => 'رابط',
                                    };
                                  @endphp
                                  
                                  <div class="col-12 col-md-6 col-xl-4">
                                    <div class="admin-resource-card">
                                      <div class="d-flex align-items-start gap-3 mb-3">
                                        <span class="admin-resource-icon {{ $resourceType }}"><i class="{{ $icon }}"></i></span>
                                        <div>
                                          <div class="fw-bold text-primary">{{ $resource->title }}</div>
                                          <div class="admin-resource-badge mt-1">{{ $title }}</div>
                                        </div>
                                      </div>

                                      <div class="mt-auto pt-2 border-top border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                                        <a href="{{ route('admin.subject_content.manage', $selectedSubject) }}" target="_blank" class="btn btn-sm btn-outline-gold rounded-pill px-3 fs-8">
                                          <i class="bi bi-pencil-square me-1"></i> تعديل المورد
                                        </a>
                                      </div>
                                    </div>
                                  </div>
                                @endforeach
                              </div>
                            </div>
                          </div>
                        </div>
                      @empty
                        <div class="text-center text-muted py-3">لا توجد دروس في هذه الوحدة.</div>
                      @endforelse
                    </div>
                  </div>
                @empty
                  <div class="text-center text-muted py-4">
                    <i class="bi bi-folder-x display-4 d-block mb-3 opacity-25"></i>
                    لا توجد موارد لهذه المجموعة في هذا المنهج.
                  </div>
                @endforelse
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>

  @endif

@endif

@endsection
