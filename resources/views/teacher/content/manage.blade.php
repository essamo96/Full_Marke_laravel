@extends('layouts.teacher')

@section('title', 'إدارة المحتوى - ' . $subject->name)
@section('page_title_en', 'Manage Content')
@section('page_title_ar', 'إدارة المحتوى')

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/vendor/resumable/resumable.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script src="{{ asset('assets/js/secure-watermark.js') }}"></script>
@endpush

@section('content')

@push('styles')
  <link rel="stylesheet" href="{{ asset_ver('assets/css/teacher-content.css') }}">
  <link rel="stylesheet" href="{{ asset_ver('assets/css/curriculum-accordion.css') }}">
@endpush

  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h1 class="h3 fw-bold mb-0" style="color: var(--text-primary);">{{ $subject->name }}</h1>
    <button type="button" class="btn btn-luxury" onclick="openUnitModal()">
      <i class="bi bi-plus-lg me-1"></i> <span data-en="Add Unit" data-ar="إضافة وحدة">إضافة وحدة</span>
    </button>
  </div>

  @if($groups->count() > 0)
    <ul class="nav nav-pills mb-4 flex-wrap gap-2">
      <li class="nav-item">
        <a class="btn btn-sm {{ is_null($selectedGroupId) ? 'btn-luxury' : 'btn-outline-primary' }}" href="{{ route('teacher.content.manage', $subject) }}">
          محتوى مشترك (كل مجموعاتي)
        </a>
      </li>
      @foreach($groups as $group)
        <li class="nav-item">
          <a class="btn btn-sm {{ $selectedGroupId === $group->id ? 'btn-luxury' : 'btn-outline-primary' }}" href="{{ route('teacher.content.manage', $subject) }}?group={{ $group->id }}">
            {{ $group->name }}
          </a>
        </li>
      @endforeach
    </ul>
  @endif

  <div class="accordion teacher-accordion teacher-content-accordion" id="unitsAccordion" data-sortable="units">
    @forelse($units as $unitIndex => $unit)
      <div class="unit-card mb-3" data-id="{{ $unit->id }}">
        <div class="unit-toggle" data-bs-toggle="collapse" data-bs-target="#unit_{{ $unit->id }}" aria-expanded="{{ $unitIndex === 0 ? 'true' : 'false' }}">
          <span class="unit-num">{{ $unitIndex + 1 }}</span>
          <span class="flex-grow-1">
            <span class="unit-title d-block">{{ $unit->name_ar ?? $unit->name_en }}</span>
            <span class="unit-meta d-block">{{ $unit->lessons->count() }} <span data-en="lesson(s)" data-ar="درس">درس</span></span>
          </span>
          <div class="d-flex gap-2 ms-auto me-3 align-items-center">
            <button type="button" class="btn btn-sm btn-outline-primary" title="تعديل الوحدة" onclick="event.stopPropagation(); openEditUnitModal('{{ $unit->getRouteKey() }}', {{ json_encode(['name_ar' => $unit->name_ar, 'name_en' => $unit->name_en, 'group_ids' => $unit->groups->pluck('id'), 'is_shared' => $unit->is_shared]) }})"><i class="bi bi-pencil"></i></button>
            <button type="button" class="btn btn-sm btn-outline-success" title="إضافة درس" onclick="event.stopPropagation(); openLessonModal('{{ $unit->getRouteKey() }}')"><i class="bi bi-plus-lg"></i></button>
            <button type="button" class="btn btn-sm btn-outline-danger" title="حذف الوحدة" onclick="event.stopPropagation(); deleteUnit('{{ $unit->getRouteKey() }}')"><i class="bi bi-trash"></i></button>
          </div>
          <i class="bi bi-chevron-down"></i>
        </div>
        
        <div id="unit_{{ $unit->id }}" class="collapse {{ $unitIndex === 0 ? 'show' : '' }} teacher-content-collapse" data-sortable="lessons">
          @forelse($unit->lessons as $lesson)
            <div class="lesson-block" data-id="{{ $lesson->id }}">
              <div class="lesson-toggle collapsed" data-bs-toggle="collapse" data-bs-target="#lesson_res_{{ $lesson->id }}" aria-expanded="false">
                <i class="bi bi-journal-text" style="color: var(--accent-color);"></i>
                <span class="flex-grow-1">{{ $lesson->name_ar ?? $lesson->name_en }}</span>
                
                <div class="d-flex gap-2 align-items-center me-3">
                  <button type="button" class="btn btn-sm btn-outline-primary" title="تعديل الدرس" onclick="event.stopPropagation(); openEditLessonModal('{{ $lesson->getRouteKey() }}', {{ json_encode(['name_ar' => $lesson->name_ar, 'name_en' => $lesson->name_en, 'group_ids' => $lesson->groups->pluck('id'), 'is_shared' => $lesson->is_shared]) }})"><i class="bi bi-pencil"></i></button>
                  <span class="lesson-count">{{ $lesson->resources->count() }} مرفق</span>
                  <button type="button" class="btn btn-sm btn-outline-danger" onclick="event.stopPropagation(); deleteLesson('{{ $lesson->getRouteKey() }}')"><i class="bi bi-trash"></i></button>
                </div>
                <i class="bi bi-chevron-down"></i>
              </div>

              <div id="lesson_res_{{ $lesson->id }}" class="collapse teacher-content-collapse">
                <div class="lesson-resources mt-2">
                  <div class="row g-3 mb-3" data-sortable="resources">
                    @forelse($lesson->resources as $resource)
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
                      <div class="col-12 col-md-6 col-xl-4" data-id="{{ $resource->id }}">
                        <div class="teacher-resource-card h-100">
                          <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                            <div class="d-flex align-items-center gap-2">
                              <span class="teacher-resource-icon {{ $resourceType }}"><i class="{{ $icon }}"></i></span>
                              <div>
                                <div class="fw-bold" style="color: var(--text-primary);">{{ $resource->title }}</div>
                                <div class="teacher-resource-badge mt-1">{{ $title }}</div>
                              </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="openEditResourceModal('{{ $resource->getRouteKey() }}', {{ json_encode(['title' => $resource->title, 'type' => $resource->type, 'url' => $resource->url, 'description' => $resource->description, 'allow_download' => $resource->allow_download, 'group_ids' => $resource->groups->pluck('id'), 'is_shared' => $resource->is_shared]) }})"><i class="bi bi-pencil"></i></button>
                             <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteResource('{{ $resource->getRouteKey() }}')"><i class="bi bi-trash"></i></button>
                          </div>

                          <div class="small text-muted mb-3">
                            @if($resource->isExternalLink())
                              <span>رابط خارجي</span>
                            @elseif($resource->processing_status === 'processing')
                              <span class="text-warning">جاري المعالجة...</span>
                            @elseif($resource->processing_status === 'failed')
                              <span class="text-danger">فشلت المعالجة</span>
                            @else
                              <span class="text-success">متاح للطلاب</span>
                            @endif
                          </div>

                          <div class="d-flex flex-wrap gap-2 mt-auto">
                            @if($resource->isExternalLink())
                              <button type="button" class="btn btn-sm btn-outline-primary w-100"
                                      onclick="openProtectedViewer('link', @js($resource->url), @js($resource->title))">فتح الرابط</button>
                            @elseif($resource->type === 'document' || $resource->isImage())
                              <button type="button" class="btn btn-sm btn-outline-primary w-100"
                                      onclick="openProtectedViewer('{{ $resource->isImage() ? 'image' : 'document' }}', @js(route('teacher.content.view-file', $resource)), @js($resource->title))">فتح الملف</button>
                            @elseif($resource->type === 'video' && $resource->isReady())
                              <button type="button" class="btn btn-sm btn-outline-primary w-100"
                                      onclick="openProtectedViewer('video', @js(route('teacher.content.view-file', $resource)), @js($resource->title))">مشاهدة الفيديو</button>
                            @elseif($resource->type === 'video')
                              <span class="btn btn-sm btn-outline-secondary disabled w-100">الفيديو قيد المعالجة</span>
                            @else
                              <span class="btn btn-sm btn-outline-secondary disabled w-100">لا يوجد محتوى</span>
                            @endif
                          </div>
                        </div>
                      </div>
                    @empty
                      <div class="col-12">
                        <div class="text-center text-muted py-3" data-en="No resources for this lesson." data-ar="لا توجد مرفقات لهذا الدرس.">لا توجد مرفقات لهذا الدرس.</div>
                      </div>
                    @endforelse
                  </div>
                  
                  <div class="d-flex flex-wrap gap-2 pt-2 border-top border-secondary border-opacity-25">
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="selectVideoForLesson('{{ $lesson->getRouteKey() }}')">
                      <i class="bi bi-cloud-arrow-up me-1"></i> رفع فيديو
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="openResourceModal('{{ $lesson->getRouteKey() }}')">
                      <i class="bi bi-plus-lg me-1"></i> إضافة مرفق (PDF / رابط)
                    </button>
                  </div>
                </div>
              </div>
            </div>
          @empty
            <div class="text-center text-muted py-3" data-en="No lessons in this unit." data-ar="لا توجد دروس في هذه الوحدة.">لا توجد دروس في هذه الوحدة.</div>
          @endforelse
        </div>
      </div>
    @empty
      <div class="glass-panel rounded-4 p-5 text-center text-muted" data-en="No content for this subject yet." data-ar="لا يوجد محتوى لهذه المادة حتى الآن.">لا يوجد محتوى لهذه المادة حتى الآن.</div>
    @endforelse
  </div>

  <!-- Add Unit Modal -->
  <div class="modal fade teacher-content-modal" tabindex="-1" id="modal_add_unit">
    <div class="modal-dialog">
      <div class="modal-content glass-panel">
        <div class="modal-header">
          <h5 class="modal-title" data-en="Add Unit" data-ar="إضافة وحدة تعليمية جديدة">إضافة وحدة تعليمية جديدة</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form id="form_add_unit">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">اسم الوحدة بالعربية</label>
              <input type="text" name="name_ar" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">اسم الوحدة بالإنجليزية</label>
              <input type="text" name="name_en" class="form-control">
            </div>
            <div class="mb-3">
              <div class="form-check form-switch">
                <input type="hidden" name="is_shared" value="0">
                <input class="form-check-input is-shared-checkbox" type="checkbox" value="1" name="is_shared" {{ $selectedGroupId ? '' : 'checked' }}>
                <label class="form-check-label">محتوى عام لجميع المجموعات (Shared)</label>
              </div>
            </div>
            <div class="mb-3 group-selection-container" style="{{ $selectedGroupId ? '' : 'display: none;' }}">
              <label class="form-label">المجموعات المستهدفة</label>
              <select name="group_ids[]" id="unit_groups" class="form-select" data-control="select2" data-placeholder="اختر المجموعات..." multiple="multiple">
                @foreach($groups as $group)
                  <option value="{{ $group->id }}" {{ $selectedGroupId == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-glass" data-bs-dismiss="modal">إلغاء</button>
            <button type="submit" class="btn btn-luxury">حفظ</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Add Lesson Modal -->
  <div class="modal fade teacher-content-modal" tabindex="-1" id="modal_add_lesson">
    <div class="modal-dialog">
      <div class="modal-content glass-panel">
        <div class="modal-header">
          <h5 class="modal-title" data-en="Add Lesson" data-ar="إضافة درس جديد">إضافة درس جديد</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form id="form_add_lesson">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">اسم الدرس بالعربية</label>
              <input type="text" name="name_ar" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">اسم الدرس بالإنجليزية</label>
              <input type="text" name="name_en" class="form-control">
            </div>
            <div class="mb-3">
              <div class="form-check form-switch">
                <input type="hidden" name="is_shared" value="0">
                <input class="form-check-input is-shared-checkbox" type="checkbox" value="1" name="is_shared" {{ $selectedGroupId ? '' : 'checked' }}>
                <label class="form-check-label">محتوى عام لجميع المجموعات (Shared)</label>
              </div>
            </div>
            <div class="mb-3 group-selection-container" style="{{ $selectedGroupId ? '' : 'display: none;' }}">
              <label class="form-label">المجموعات المستهدفة</label>
              <select name="group_ids[]" id="lesson_groups" class="form-select" data-control="select2" data-placeholder="اختر المجموعات..." multiple="multiple">
                @foreach($groups as $group)
                  <option value="{{ $group->id }}" {{ $selectedGroupId == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-glass" data-bs-dismiss="modal">إلغاء</button>
            <button type="submit" class="btn btn-luxury">حفظ</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Add Resource Modal -->
  <div class="modal fade teacher-content-modal" tabindex="-1" id="modal_add_resource">
    <div class="modal-dialog">
      <div class="modal-content glass-panel">
        <div class="modal-header">
          <h5 class="modal-title" data-en="Add Resource" data-ar="إضافة مرفق تعليمي">إضافة مرفق تعليمي</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form id="form_add_resource" enctype="multipart/form-data">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">عنوان المرفق</label>
              <input type="text" name="title" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">نوع المرفق</label>
              <select name="type" id="resource_type" class="form-select" required>
                <option value="video">فيديو</option>
                <option value="document">ملف / PDF</option>
                <option value="image">صورة</option>
                <option value="link">رابط خارجي (يوتيوب، إلخ)</option>
                <option value="zoom">رابط Zoom</option>
              </select>
            </div>
            <div class="mb-3">
              <div class="form-check form-switch">
                <input type="hidden" name="is_shared" value="0">
                <input class="form-check-input is-shared-checkbox" type="checkbox" value="1" name="is_shared" {{ $selectedGroupId ? '' : 'checked' }}>
                <label class="form-check-label">محتوى عام لجميع المجموعات (Shared)</label>
              </div>
            </div>
            <div class="mb-3 group-selection-container" style="{{ $selectedGroupId ? '' : 'display: none;' }}">
              <label class="form-label">المجموعات المستهدفة</label>
              <select name="group_ids[]" id="resource_groups" class="form-select" data-control="select2" data-placeholder="اختر المجموعات..." multiple="multiple">
                @foreach($groups as $group)
                  <option value="{{ $group->id }}" {{ $selectedGroupId == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="mb-3" id="resource_video_field">
              <label class="form-label">رفع فيديو</label>
              <div class="d-flex align-items-center gap-3 flex-wrap">
                <input type="file" id="resource_video_input" class="d-none" accept="video/*">
                <button type="button" id="resource_video_browse" class="btn btn-outline-primary btn-sm">اختر ملف الفيديو</button>
                <span id="resource_video_filename" class="text-muted fs-7"></span>
              </div>
              <div class="progress mt-3 d-none" id="resource_video_progress_wrap" style="height: 8px;">
                <div class="progress-bar" id="resource_video_progress" role="progressbar" style="width: 0%"></div>
              </div>
              <div class="form-text text-muted fs-7">يدعم الرفع المجزّأ (Chunked) القابل للاستئناف.</div>
            </div>
            <div class="mb-3" id="resource_document_field">
              <label class="form-label">رفع ملف (PDF / مستند)</label>
              <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx">
            </div>
            <div class="mb-3 d-none" id="resource_image_field">
              <label class="form-label">رفع صورة</label>
              <input type="file" name="file" class="form-control" accept=".jpg,.jpeg,.png,.webp,.gif">
            </div>
            <div class="mb-3" id="resource_url_field">
              <label class="form-label">رابط خارجي</label>
              <input type="text" name="url" class="form-control" placeholder="https://...">
            </div>
            <input type="hidden" name="uploaded_path" id="resource_uploaded_path">
            <input type="hidden" name="original_filename" id="resource_original_filename">
            <div class="mb-3">
              <label class="form-label">وصف مختصر</label>
              <textarea name="description" class="form-control" rows="2"></textarea>
            </div>
            <div class="mb-3">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" value="1" id="teacher_allow_download_check" name="allow_download">
                <label class="form-check-label" for="teacher_allow_download_check">
                  السماح للطلاب بالتحميل
                </label>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-glass" data-bs-dismiss="modal">إلغاء</button>
            <button type="submit" class="btn btn-luxury">حفظ</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  
  <!-- Edit Unit Modal -->
  <div class="modal fade teacher-content-modal" tabindex="-1" id="modal_edit_unit">
    <div class="modal-dialog">
      <div class="modal-content glass-panel">
        <div class="modal-header">
          <h5 class="modal-title">تعديل وحدة تعليمية</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form id="form_edit_unit">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">اسم الوحدة بالعربية</label>
              <input type="text" name="name_ar" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">اسم الوحدة بالإنجليزية</label>
              <input type="text" name="name_en" class="form-control">
            </div>
                        <div class="mb-3">
              <div class="form-check form-switch">
                <input type="hidden" name="is_shared" value="0">
                <input class="form-check-input is-shared-checkbox" type="checkbox" value="1" name="is_shared" checked>
                <label class="form-check-label">محتوى عام لجميع المجموعات (Shared)</label>
              </div>
            </div>
            <div class="mb-3 group-selection-container" style="display: none;">
              <label class="form-label">المجموعات (تترك فارغة لحفظها كمسودة)</label>
              <select name="group_ids[]" class="form-select" data-control="select2" multiple="multiple">
                @foreach($groups as $group)
                  <option value="{{ $group->id }}">{{ $group->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-glass" data-bs-dismiss="modal">إلغاء</button>
            <button type="submit" class="btn btn-luxury">حفظ</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Lesson Modal -->
  <div class="modal fade teacher-content-modal" tabindex="-1" id="modal_edit_lesson">
    <div class="modal-dialog">
      <div class="modal-content glass-panel">
        <div class="modal-header">
          <h5 class="modal-title">تعديل درس</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form id="form_edit_lesson">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">اسم الدرس بالعربية</label>
              <input type="text" name="name_ar" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">اسم الدرس بالإنجليزية</label>
              <input type="text" name="name_en" class="form-control">
            </div>
                        <div class="mb-3">
              <div class="form-check form-switch">
                <input type="hidden" name="is_shared" value="0">
                <input class="form-check-input is-shared-checkbox" type="checkbox" value="1" name="is_shared" checked>
                <label class="form-check-label">محتوى عام لجميع المجموعات (Shared)</label>
              </div>
            </div>
            <div class="mb-3 group-selection-container" style="display: none;">
              <label class="form-label">المجموعات (تترك فارغة لحفظها كمسودة)</label>
              <select name="group_ids[]" class="form-select" data-control="select2" multiple="multiple">
                @foreach($groups as $group)
                  <option value="{{ $group->id }}">{{ $group->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-glass" data-bs-dismiss="modal">إلغاء</button>
            <button type="submit" class="btn btn-luxury">حفظ</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Resource Modal -->
  <div class="modal fade teacher-content-modal" tabindex="-1" id="modal_edit_resource">
    <div class="modal-dialog">
      <div class="modal-content glass-panel">
        <div class="modal-header">
          <h5 class="modal-title">تعديل مرفق</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form id="form_edit_resource" enctype="multipart/form-data">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">عنوان المرفق</label>
              <input type="text" name="title" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">نوع المرفق</label>
              <select name="type" class="form-select" disabled>
                <option value="video">فيديو</option>
                <option value="document">ملف / PDF</option>
                <option value="image">صورة</option>
                <option value="link">رابط خارجي</option>
                <option value="zoom">رابط Zoom</option>
              </select>
              <input type="hidden" name="type">
            </div>
                        <div class="mb-3">
              <div class="form-check form-switch">
                <input type="hidden" name="is_shared" value="0">
                <input class="form-check-input is-shared-checkbox" type="checkbox" value="1" name="is_shared" checked>
                <label class="form-check-label">محتوى عام لجميع المجموعات (Shared)</label>
              </div>
            </div>
            <div class="mb-3 group-selection-container" style="display: none;">
              <label class="form-label">المجموعات (تترك فارغة لحفظها كمسودة)</label>
              <select name="group_ids[]" class="form-select" data-control="select2" multiple="multiple">
                @foreach($groups as $group)
                  <option value="{{ $group->id }}">{{ $group->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="mb-3 edit_resource_url_field">
              <label class="form-label">رابط خارجي</label>
              <input type="text" name="url" class="form-control">
            </div>
            <div class="mb-3">
              <label class="form-label">وصف مختصر</label>
              <textarea name="description" class="form-control" rows="2"></textarea>
            </div>
            <div class="mb-3">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" value="1" id="edit_allow_download_check" name="allow_download">
                <label class="form-check-label" for="edit_allow_download_check">
                  السماح للطلاب بالتحميل
                </label>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-glass" data-bs-dismiss="modal">إلغاء</button>
            <button type="submit" class="btn btn-luxury">حفظ</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <!-- Protected viewer: keeps resource URLs out of the address bar and blocks
       the usual save/right-click/new-tab routes to the underlying file. -->
  <div class="modal fade" tabindex="-1" id="modal_protected_viewer" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content glass-panel protected-viewer">
        <div class="modal-header">
          <h5 class="modal-title" id="protected_viewer_title">معاينة</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
        </div>
        <div class="modal-body p-0">
          <div class="protected-viewer__stage" id="protected_viewer_stage">
            <div class="protected-viewer__shield" aria-hidden="true"></div>
          </div>
        </div>
        <div class="modal-footer justify-content-between">
          <span class="text-muted fs-7"><i class="bi bi-shield-lock me-1"></i> محتوى محمي — يُمنع التحميل أو النسخ</span>
          <button type="button" class="btn btn-glass" data-bs-dismiss="modal">إغلاق</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Floating upload monitor: percentage, transfer rate and time remaining.
       Lives outside the modal so progress stays visible if the modal is closed. -->
  <div id="upload_monitor" class="upload-monitor glass-panel" hidden aria-live="polite">
    <div class="upload-monitor__head">
      <span class="upload-monitor__icon"><i class="bi bi-cloud-arrow-up-fill"></i></span>
      <div class="upload-monitor__titles">
        <div class="upload-monitor__name" id="upload_monitor_name">—</div>
        <div class="upload-monitor__state" id="upload_monitor_state">جارٍ التحضير…</div>
      </div>
      <button type="button" class="upload-monitor__close" id="upload_monitor_close" title="إخفاء" aria-label="إخفاء">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <div class="upload-monitor__bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" id="upload_monitor_bar_wrap">
      <div class="upload-monitor__fill" id="upload_monitor_fill" style="width:0%"></div>
    </div>

    <div class="upload-monitor__stats">
      <div class="upload-monitor__stat">
        <span class="upload-monitor__stat-label">النسبة</span>
        <span class="upload-monitor__stat-value" id="upload_monitor_pct">0%</span>
      </div>
      <div class="upload-monitor__stat">
        <span class="upload-monitor__stat-label">السرعة</span>
        <span class="upload-monitor__stat-value" id="upload_monitor_speed">—</span>
      </div>
      <div class="upload-monitor__stat">
        <span class="upload-monitor__stat-label">الوقت المتبقي</span>
        <span class="upload-monitor__stat-value" id="upload_monitor_eta">—</span>
      </div>
    </div>

    <div class="upload-monitor__foot">
      <span id="upload_monitor_size">—</span>
      <button type="button" class="upload-monitor__cancel" id="upload_monitor_cancel">إلغاء الرفع</button>
    </div>
  </div>

@endsection

@push('styles')
<style>
  /* ---- Protected viewer ---- */
  .protected-viewer__stage {
    position: relative;
    width: 100%;
    aspect-ratio: 16 / 9;
    max-height: 78vh;
    background: #000;
    overflow: hidden;
    border-radius: 0 0 0 0;
  }
  .protected-viewer__stage > video,
  .protected-viewer__stage > iframe,
  .protected-viewer__stage > img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    border: 0;
    z-index: 1;
  }
  .protected-viewer__stage > img { object-fit: contain; }

  /* Blocks the "open in new window" affordance that Drive/YouTube render in
     the top corner of their embeds. */
  .protected-viewer__shield {
    position: absolute;
    top: 0;
    inset-inline-end: 0;
    width: 84px;
    height: 74px;
    z-index: 30;
    background: transparent;
    cursor: not-allowed;
  }

  /* Defence-in-depth against drag-to-desktop and selection copying. */
  .protected-viewer__stage,
  .protected-viewer__stage * {
    -webkit-user-select: none;
    user-select: none;
    -webkit-touch-callout: none;
  }
  .protected-viewer__stage img,
  .protected-viewer__stage video { -webkit-user-drag: none; }

  /* ---- Upload monitor ---- */
  .upload-monitor {
    position: fixed;
    inset-inline-start: 24px;
    bottom: 24px;
    width: min(360px, calc(100vw - 32px));
    z-index: 1090; /* above Bootstrap modals (1055) so it stays visible */
    padding: 16px 18px;
    border-radius: 16px;
    border: 1px solid var(--glass-border);
    background: var(--glass-bg, var(--card-bg));
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    box-shadow: var(--shadow-lg);
    color: var(--text-primary);
    animation: uploadMonitorIn 0.25s ease;
  }
  .upload-monitor[hidden] { display: none; }

  @keyframes uploadMonitorIn {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  .upload-monitor__head { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
  .upload-monitor__icon {
    flex: 0 0 auto; width: 34px; height: 34px; border-radius: 10px;
    display: grid; place-items: center;
    background: var(--accent-glow); color: var(--accent-color); font-size: 1rem;
  }
  .upload-monitor__titles { min-width: 0; flex: 1 1 auto; }
  .upload-monitor__name {
    font-weight: 700; font-size: 0.85rem; line-height: 1.3;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
  }
  .upload-monitor__state { font-size: 0.72rem; color: var(--text-muted); margin-top: 2px; }
  .upload-monitor__close {
    flex: 0 0 auto; background: none; border: 0; padding: 4px;
    color: var(--text-muted); font-size: 0.75rem; line-height: 1; cursor: pointer;
  }
  .upload-monitor__close:hover { color: var(--text-primary); }

  .upload-monitor__bar {
    height: 8px; border-radius: 999px; overflow: hidden;
    background: var(--separator-color); margin-bottom: 12px;
  }
  .upload-monitor__fill {
    height: 100%; width: 0;
    background: var(--accent-gradient, var(--accent-color));
    border-radius: 999px;
    transition: width 0.25s ease;
  }

  .upload-monitor__stats { display: flex; gap: 8px; margin-bottom: 10px; }
  .upload-monitor__stat {
    flex: 1 1 0; min-width: 0; text-align: center;
    padding: 7px 4px; border-radius: 10px;
    background: var(--input-bg); border: 1px solid var(--separator-color);
  }
  .upload-monitor__stat-label {
    display: block; font-size: 0.62rem; color: var(--text-muted);
    margin-bottom: 3px; white-space: nowrap;
  }
  .upload-monitor__stat-value {
    display: block; font-size: 0.8rem; font-weight: 700;
    font-variant-numeric: tabular-nums; white-space: nowrap;
  }

  .upload-monitor__foot {
    display: flex; align-items: center; justify-content: space-between; gap: 8px;
    font-size: 0.7rem; color: var(--text-muted); font-variant-numeric: tabular-nums;
  }
  .upload-monitor__cancel {
    background: none; border: 0; padding: 0; cursor: pointer;
    font-size: 0.7rem; font-weight: 700; color: #ef4444;
  }
  .upload-monitor__cancel:hover { text-decoration: underline; }
  .upload-monitor__cancel[hidden] { display: none; }

  .upload-monitor.is-done .upload-monitor__fill { background: #22c55e; }
  .upload-monitor.is-error .upload-monitor__fill { background: #ef4444; }

  @media (max-width: 575.98px) {
    .upload-monitor { inset-inline: 16px; width: auto; bottom: 16px; }
  }
</style>
@endpush

@push('scripts')
<script>
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const unitsStoreUrl = '{{ route('teacher.content.store-unit', $subject) }}';
  const unitsBaseUrl = '{{ url('teacher/content/units') }}';
  const lessonsBaseUrl = '{{ url('teacher/content/lessons') }}';
  const resourcesBaseUrl = '{{ url('teacher/content/resources') }}';
  const chunkUploadUrl = '{{ route('teacher.content.upload-chunk') }}';

  /**
   * Protected resource viewer.
   *
   * Resources used to open with target="_blank", which put the signed file URL
   * straight into the address bar where it could be copied, bookmarked or
   * saved. Everything now renders inside this modal instead, with the usual
   * grab routes (context menu, drag, download control, picture-in-picture,
   * Ctrl+S) disabled and an identifying watermark burned over the frame.
   *
   * This raises the effort required to copy content; it cannot make a stream
   * that the browser must decode unrippable, and screen recording is always
   * possible. The watermark is what makes a leak traceable.
   */
  const protectedViewerModal = new bootstrap.Modal(document.getElementById('modal_protected_viewer'));
  const protectedViewerStage = document.getElementById('protected_viewer_stage');
  let protectedViewerWatermarkDestroy = null;
  let protectedViewerFullscreenCleanup = null;

  // Native video fullscreen makes the <video> itself the fullscreen element,
  // which would leave the watermark canvas (a sibling, not a child) behind on
  // the page — same fix as the student-side player, so the watermark and the
  // right-click block on the video element both survive going fullscreen.
  function keepWatermarkInFullscreen(container, videoEl) {
    var modal = container.closest('.modal');

    function onFullscreenChange() {
      var fsEl = document.fullscreenElement || document.webkitFullscreenElement;

      if (modal) {
        modal.classList.toggle('video-is-fullscreen', fsEl === container);
      }

      if (fsEl !== videoEl) return;
      var exit = document.exitFullscreen
        ? document.exitFullscreen()
        : (document.webkitExitFullscreen ? Promise.resolve(document.webkitExitFullscreen()) : Promise.resolve());
      Promise.resolve(exit).catch(function () {}).then(function () {
        var request = container.requestFullscreen || container.webkitRequestFullscreen;
        if (request) request.call(container).catch(function () {});
      });
    }
    document.addEventListener('fullscreenchange', onFullscreenChange);
    document.addEventListener('webkitfullscreenchange', onFullscreenChange);
    return function destroy() {
      document.removeEventListener('fullscreenchange', onFullscreenChange);
      document.removeEventListener('webkitfullscreenchange', onFullscreenChange);
      if (modal) modal.classList.remove('video-is-fullscreen');
    };
  }

  function openProtectedViewer(kind, url, title) {
    document.getElementById('protected_viewer_title').textContent = title || 'معاينة';

    // Drop any previously rendered media so its stream stops immediately.
    protectedViewerStage.querySelectorAll('video, iframe, img').forEach(el => el.remove());
    if (protectedViewerWatermarkDestroy) { protectedViewerWatermarkDestroy(); protectedViewerWatermarkDestroy = null; }
    if (protectedViewerFullscreenCleanup) { protectedViewerFullscreenCleanup(); protectedViewerFullscreenCleanup = null; }

    let node;
    if (kind === 'video') {
      node = document.createElement('video');
      node.src = url;
      node.controls = true;
      node.playsInline = true;
      node.controlsList = 'nodownload noplaybackrate noremoteplayback';
      node.disablePictureInPicture = true;
      node.setAttribute('disableRemotePlayback', '');
    } else if (kind === 'image') {
      node = document.createElement('img');
      node.src = url;
      node.alt = title || '';
    } else {
      node = document.createElement('iframe');
      node.src = toEmbeddableUrl(url);
      node.setAttribute('allow', 'autoplay; encrypted-media; picture-in-picture');
      node.setAttribute('allowfullscreen', '');
      node.setAttribute('referrerpolicy', 'no-referrer');
      // sandbox keeps the embed from navigating the opener or spawning tabs
      node.setAttribute('sandbox', 'allow-scripts allow-same-origin allow-presentation');
    }

    node.addEventListener('contextmenu', e => e.preventDefault());
    node.addEventListener('dragstart', e => e.preventDefault());
    protectedViewerStage.insertBefore(node, protectedViewerStage.firstChild);

    // Same canonical roam-then-lock watermark as the student side, so a leak
    // is traceable back to whoever previewed it regardless of which side saw it.
    protectedViewerWatermarkDestroy = mountSecureWatermark(
      protectedViewerStage,
      @json(auth('teacher')->user()?->name ?? 'معلّم'),
      null
    );

    if (kind === 'video') {
      protectedViewerFullscreenCleanup = keepWatermarkInFullscreen(protectedViewerStage, node);
    }

    protectedViewerModal.show();
  }

  /** Normalises Drive/YouTube share links into their no-chrome embed form. */
  function toEmbeddableUrl(url) {
    let m;
    if ((m = url.match(/drive\.google\.com\/file\/d\/([\w-]+)/))) {
      return 'https://drive.google.com/file/d/' + m[1] + '/preview';
    }
    if ((m = url.match(/drive\.google\.com\/(?:open|uc)\?(?:export=\w+&)?id=([\w-]+)/))) {
      return 'https://drive.google.com/file/d/' + m[1] + '/preview';
    }
    if ((m = url.match(/youtube\.com\/watch\?(?:.*&)?v=([\w-]+)/))) {
      return 'https://www.youtube-nocookie.com/embed/' + m[1] + '?rel=0&modestbranding=1&iv_load_policy=3&playsinline=1';
    }
    if ((m = url.match(/youtu\.be\/([\w-]+)/))) {
      return 'https://www.youtube-nocookie.com/embed/' + m[1] + '?rel=0&modestbranding=1&iv_load_policy=3&playsinline=1';
    }
    if ((m = url.match(/youtube\.com\/(?:shorts|live|embed)\/([\w-]+)/))) {
      return 'https://www.youtube-nocookie.com/embed/' + m[1] + '?rel=0&modestbranding=1&iv_load_policy=3&playsinline=1';
    }
    return url;
  }

  // Stop playback and release the source as soon as the viewer closes.
  document.getElementById('modal_protected_viewer').addEventListener('hidden.bs.modal', function () {
    protectedViewerStage.querySelectorAll('video').forEach(v => { v.pause(); v.removeAttribute('src'); v.load(); });
    protectedViewerStage.querySelectorAll('video, iframe, img').forEach(el => el.remove());
    if (protectedViewerWatermarkDestroy) { protectedViewerWatermarkDestroy(); protectedViewerWatermarkDestroy = null; }
    if (protectedViewerFullscreenCleanup) { protectedViewerFullscreenCleanup(); protectedViewerFullscreenCleanup = null; }
  });

  // Suppress the context menu across the resource area so the file URL cannot
  // be lifted via "copy link address" / "save video as".
  document.addEventListener('contextmenu', function (e) {
    if (e.target.closest('.protected-viewer__stage, .resource-card, video, img')) e.preventDefault();
  });

  // Ctrl/Cmd+S inside the viewer would otherwise offer to save the page.
  document.addEventListener('keydown', function (e) {
    const viewerOpen = document.getElementById('modal_protected_viewer').classList.contains('show');
    if (viewerOpen && (e.ctrlKey || e.metaKey) && ['s', 'u'].includes(e.key.toLowerCase())) {
      e.preventDefault();
    }
  });

  /**
   * Floating upload monitor.
   *
   * Reports percentage, transfer rate and estimated time remaining for both
   * the chunked (Resumable.js) video upload and the plain file upload in the
   * resource form. The rate is smoothed with an exponential moving average
   * because raw chunk deltas swing wildly and make the ETA jump around.
   */
  const uploadMonitor = (function () {
    const el = document.getElementById('upload_monitor');
    const nameEl = document.getElementById('upload_monitor_name');
    const stateEl = document.getElementById('upload_monitor_state');
    const fillEl = document.getElementById('upload_monitor_fill');
    const barEl = document.getElementById('upload_monitor_bar_wrap');
    const pctEl = document.getElementById('upload_monitor_pct');
    const speedEl = document.getElementById('upload_monitor_speed');
    const etaEl = document.getElementById('upload_monitor_eta');
    const sizeEl = document.getElementById('upload_monitor_size');
    const cancelBtn = document.getElementById('upload_monitor_cancel');
    const closeBtn = document.getElementById('upload_monitor_close');

    let total = 0;
    let lastLoaded = 0;
    let lastTime = 0;
    let rate = 0;        // bytes/sec, smoothed
    let onCancel = null;
    let hideTimer = null;

    function formatBytes(bytes) {
      if (!bytes || bytes < 0) return '0 B';
      const units = ['B', 'KB', 'MB', 'GB'];
      const i = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
      const value = bytes / Math.pow(1024, i);
      return value.toFixed(value >= 10 || i === 0 ? 0 : 1) + ' ' + units[i];
    }

    function formatDuration(seconds) {
      if (!isFinite(seconds) || seconds < 0) return '—';
      seconds = Math.round(seconds);
      if (seconds < 60) return seconds + ' ثانية';
      const m = Math.floor(seconds / 60);
      const s = seconds % 60;
      if (m < 60) return s ? `${m} د ${s} ث` : `${m} دقيقة`;
      const h = Math.floor(m / 60);
      return `${h} س ${m % 60} د`;
    }

    function show() {
      clearTimeout(hideTimer);
      el.classList.remove('is-done', 'is-error');
      el.hidden = false;
    }

    return {
      start(fileName, totalBytes, cancelFn) {
        total = totalBytes || 0;
        lastLoaded = 0;
        lastTime = Date.now();
        rate = 0;
        onCancel = cancelFn || null;

        nameEl.textContent = fileName || 'ملف';
        nameEl.title = fileName || '';
        stateEl.textContent = 'جارٍ الرفع…';
        pctEl.textContent = '0%';
        speedEl.textContent = '—';
        etaEl.textContent = '—';
        sizeEl.textContent = total ? '0 B / ' + formatBytes(total) : '—';
        fillEl.style.width = '0%';
        barEl.setAttribute('aria-valuenow', '0');
        cancelBtn.hidden = !onCancel;
        show();
      },

      update(loadedBytes, totalBytes) {
        if (totalBytes) total = totalBytes;

        const now = Date.now();
        const elapsed = (now - lastTime) / 1000;

        // Only recompute the rate on a meaningful interval; sub-100ms deltas
        // are mostly noise and produce nonsense speeds.
        if (elapsed >= 0.25) {
          const instant = (loadedBytes - lastLoaded) / elapsed;
          rate = rate ? rate * 0.7 + instant * 0.3 : instant;
          lastLoaded = loadedBytes;
          lastTime = now;
        }

        const pct = total ? Math.min(100, Math.floor((loadedBytes / total) * 100)) : 0;
        fillEl.style.width = pct + '%';
        barEl.setAttribute('aria-valuenow', String(pct));
        pctEl.textContent = pct + '%';
        speedEl.textContent = rate > 0 ? formatBytes(rate) + '/ث' : '—';
        etaEl.textContent = rate > 0 && total
          ? formatDuration((total - loadedBytes) / rate)
          : '—';
        sizeEl.textContent = total
          ? formatBytes(loadedBytes) + ' / ' + formatBytes(total)
          : formatBytes(loadedBytes);
      },

      /** Upload finished; the resource record may still be saving. */
      finishing(message) {
        stateEl.textContent = message || 'اكتمل الرفع، جارٍ الحفظ…';
        fillEl.style.width = '100%';
        pctEl.textContent = '100%';
        etaEl.textContent = '—';
        cancelBtn.hidden = true;
      },

      done(message) {
        el.classList.add('is-done');
        stateEl.textContent = message || 'تم الرفع بنجاح';
        fillEl.style.width = '100%';
        pctEl.textContent = '100%';
        speedEl.textContent = '—';
        etaEl.textContent = '—';
        cancelBtn.hidden = true;
        hideTimer = setTimeout(() => { el.hidden = true; }, 4000);
      },

      error(message) {
        show();
        el.classList.add('is-error');
        stateEl.textContent = message || 'فشل الرفع';
        speedEl.textContent = '—';
        etaEl.textContent = '—';
        cancelBtn.hidden = true;
      },

      hide() {
        clearTimeout(hideTimer);
        el.hidden = true;
      },

      _cancel() {
        if (onCancel) onCancel();
        onCancel = null;
        this.hide();
      },
    };
  })();

  document.getElementById('upload_monitor_close').addEventListener('click', () => uploadMonitor.hide());
  document.getElementById('upload_monitor_cancel').addEventListener('click', () => uploadMonitor._cancel());

  // Guard against losing an in-flight upload by navigating away.
  let uploadInFlight = false;
  window.addEventListener('beforeunload', function (e) {
    if (!uploadInFlight) return;
    e.preventDefault();
    e.returnValue = '';
  });

  let modalAddUnit, modalAddLesson, modalAddResource;
    let modalEditUnit, modalEditLesson, modalEditResource;
  document.addEventListener('change', function(e) {
    if (e.target.matches('.is-shared-checkbox')) {
        const container = e.target.closest('form').querySelector('.group-selection-container');
        if (container) {
            container.style.display = e.target.checked ? 'none' : 'block';
        }
    }
});
let editUnitId = null;
  let editLessonId = null;
  let editResourceId = null;

  document.addEventListener('DOMContentLoaded', function () {
    modalEditUnit = new bootstrap.Modal(document.getElementById('modal_edit_unit'));
    modalEditLesson = new bootstrap.Modal(document.getElementById('modal_edit_lesson'));
    modalEditResource = new bootstrap.Modal(document.getElementById('modal_edit_resource'));

    // Initialize Sortable for units
    const unitsAccordion = document.getElementById('unitsAccordion');
    if (unitsAccordion) {
      new Sortable(unitsAccordion, {
        animation: 150,
        handle: '.unit-toggle',
        onEnd: function (evt) {
          let order = Array.from(unitsAccordion.children).map(el => el.getAttribute('data-id')).filter(Boolean);
          $.post(unitsBaseUrl + '/reorder', { _token: csrfToken, order: order });
        }
      });
    }

    // Initialize Sortable for lessons
    document.querySelectorAll('[data-sortable="lessons"]').forEach(el => {
      new Sortable(el, {
        animation: 150,
        handle: '.lesson-toggle',
        onEnd: function (evt) {
          let order = Array.from(el.children).map(child => child.getAttribute('data-id')).filter(Boolean);
          $.post(lessonsBaseUrl + '/reorder', { _token: csrfToken, order: order });
        }
      });
    });

    // Initialize Sortable for resources
    document.querySelectorAll('[data-sortable="resources"]').forEach(el => {
      new Sortable(el, {
        animation: 150,
        onEnd: function (evt) {
          let order = Array.from(el.children).map(child => child.getAttribute('data-id')).filter(Boolean);
          $.post(resourcesBaseUrl + '/reorder', { _token: csrfToken, order: order });
        }
      });
    });
  });

  function setSharedCheckbox(form, isShared) {
    const sharedCb = form.querySelector('.is-shared-checkbox');
    if (!sharedCb) return;
    sharedCb.checked = !!isShared;
    $(sharedCb).trigger('change');
  }

  function openEditUnitModal(unitId, data) {
    editUnitId = unitId;
    const form = document.getElementById('form_edit_unit');
    form.name_ar.value = data.name_ar || '';
    form.name_en.value = data.name_en || '';
    setSharedCheckbox(form, data.is_shared);
    $(form).find('select[name="group_ids[]"]').val(data.group_ids || []).trigger('change');
    modalEditUnit.show();
  }

  function openEditLessonModal(lessonId, data) {
    editLessonId = lessonId;
    const form = document.getElementById('form_edit_lesson');
    form.name_ar.value = data.name_ar || '';
    form.name_en.value = data.name_en || '';
    setSharedCheckbox(form, data.is_shared);
    $(form).find('select[name="group_ids[]"]').val(data.group_ids || []).trigger('change');
    modalEditLesson.show();
  }

  function openEditResourceModal(resourceId, data) {
    editResourceId = resourceId;
    const form = document.getElementById('form_edit_resource');
    form.title.value = data.title || '';
    form.type.value = data.type;
    form.querySelector('input[name="type"]').value = data.type;
    form.url.value = data.url || '';
    form.description.value = data.description || '';
    form.allow_download.checked = data.allow_download;
    setSharedCheckbox(form, data.is_shared);
    $(form).find('select[name="group_ids[]"]').val(data.group_ids || []).trigger('change');
    
    if (data.type === 'link' || data.type === 'zoom') {
        $(form).find('.edit_resource_url_field').show();
    } else {
        $(form).find('.edit_resource_url_field').hide();
    }
    
    modalEditResource.show();
  }

  $('#form_edit_unit').on('submit', function (e) {
    e.preventDefault();
    $.ajax({
      url: unitsBaseUrl + '/' + editUnitId,
      type: 'PUT',
      data: $(this).serialize() + '&_token=' + csrfToken,
      success: function () { location.reload(); },
      error: function () { Swal.fire('خطأ', 'حدث خطأ، يرجى التأكد من البيانات.', 'error'); }
    });
  });

  $('#form_edit_lesson').on('submit', function (e) {
    e.preventDefault();
    $.ajax({
      url: lessonsBaseUrl + '/' + editLessonId,
      type: 'PUT',
      data: $(this).serialize() + '&_token=' + csrfToken,
      success: function () { location.reload(); },
      error: function () { Swal.fire('خطأ', 'حدث خطأ، يرجى التأكد من البيانات.', 'error'); }
    });
  });

  $('#form_edit_resource').on('submit', function (e) {
    e.preventDefault();
    $.ajax({
      url: resourcesBaseUrl + '/' + editResourceId,
      type: 'POST', // Laravel needs _method=PUT for multipart forms, or we can just send PUT here for non-files
      data: $(this).serialize() + '&_token=' + csrfToken + '&_method=PUT',
      success: function () { location.reload(); },
      error: function () { Swal.fire('خطأ', 'حدث خطأ، يرجى التأكد من البيانات.', 'error'); }
    });
  });
  let currentUnitId = null;
  let currentLessonId = null;
  let pendingLessonId = null;
  let videoUploadResumable = null;
  let videoUploadDone = false;

  document.addEventListener('DOMContentLoaded', function () {
    modalAddUnit = new bootstrap.Modal(document.getElementById('modal_add_unit'));
    modalAddLesson = new bootstrap.Modal(document.getElementById('modal_add_lesson'));
    modalAddResource = new bootstrap.Modal(document.getElementById('modal_add_resource'));

    document.getElementById('resource_type').addEventListener('change', toggleResourceFields);
    toggleResourceFields();
    initVideoResumable();

    @if(isset($processingResources) && count($processingResources) > 0)
      @foreach($processingResources as $resId)
        pollResourceProgress('{{ $resId }}');
      @endforeach
    @endif
  });

  function pollResourceProgress(resourceId) {
    const pollInterval = setInterval(() => {
      $.get(resourcesBaseUrl + '/' + resourceId + '/progress', function (data) {
        if (data.status === 'ready' || data.percentage >= 100) {
          clearInterval(pollInterval);
          location.reload();
        } else if (data.status === 'failed') {
          clearInterval(pollInterval);
        }
      });
    }, 3000);
  }

  function toggleResourceFields() {
    const type = document.getElementById('resource_type').value;
    const documentField = document.getElementById('resource_document_field');
    const imageField = document.getElementById('resource_image_field');
    document.getElementById('resource_video_field').classList.toggle('d-none', type !== 'video');
    documentField.classList.toggle('d-none', type !== 'document');
    imageField.classList.toggle('d-none', type !== 'image');
    document.getElementById('resource_url_field').classList.toggle('d-none', type !== 'link' && type !== 'zoom');

    // document/image inputs share name="file" — a hidden-but-enabled input
    // still rides along in FormData(form) and corrupts the "file" field into
    // an array, so disable whichever one isn't active.
    documentField.querySelector('input[name="file"]').disabled = (type !== 'document');
    imageField.querySelector('input[name="file"]').disabled = (type !== 'image');
  }

  function initVideoResumable() {
    const videoInput = document.getElementById('resource_video_input');
    const videoBrowse = document.getElementById('resource_video_browse');

    videoUploadResumable = new Resumable({
      target: chunkUploadUrl,
      chunkSize: 5 * 1024 * 1024,
      simultaneousUploads: 3,
      testChunks: false,
      maxChunkRetries: 8,
      chunkRetryInterval: 3000,
      query: { _token: csrfToken },
    });

    videoBrowse.addEventListener('click', function () {
      videoInput.click();
    });

    videoInput.addEventListener('change', function () {
      if (!videoInput.files || !videoInput.files.length) return;
      const file = videoInput.files[0];
      videoUploadResumable.addFile(file);
      document.getElementById('resource_video_filename').textContent = file.name;
    });

    videoUploadResumable.on('fileAdded', function (file) {
      videoUploadDone = false;
      uploadInFlight = true;
      document.getElementById('resource_uploaded_path').value = '';
      document.getElementById('resource_video_filename').textContent = file.fileName;
      document.getElementById('resource_video_progress_wrap').classList.remove('d-none');
      document.getElementById('resource_video_progress').style.width = '0%';

      uploadMonitor.start(file.fileName, file.size, function () {
        videoUploadResumable.cancel();
        uploadInFlight = false;
        videoUploadDone = false;
        document.getElementById('resource_video_progress_wrap').classList.add('d-none');
        document.getElementById('resource_video_filename').textContent = '';
        document.getElementById('resource_video_input').value = '';
      });

      videoUploadResumable.upload();
    });

    videoUploadResumable.on('fileProgress', function (file) {
      const ratio = videoUploadResumable.progress();
      const pct = Math.floor(ratio * 100);
      document.getElementById('resource_video_progress').style.width = pct + '%';
      uploadMonitor.update(Math.round(ratio * file.size), file.size);
    });

    videoUploadResumable.on('fileSuccess', function (file, response) {
      const data = JSON.parse(response);
      document.getElementById('resource_uploaded_path').value = data.path;
      document.getElementById('resource_original_filename').value = data.original_filename;
      document.getElementById('resource_video_progress').style.width = '100%';
      videoUploadDone = true;
      uploadMonitor.finishing();

      const targetLessonId = pendingLessonId || currentLessonId;
      if (!targetLessonId) {
        uploadInFlight = false;
        uploadMonitor.error('لم يتم تحديد درس لربط الفيديو.');
        Swal.fire('تنبيه', 'لم يتم تحديد درس لربط الفيديو.', 'warning');
        return;
      }

      const enteredTitle = (document.querySelector('#form_add_resource [name="title"]').value || '').trim();
      const fallbackTitle = (data.original_filename || file.fileName || file.name || 'فيديو جديد').replace(/\.[^/.]+$/, '');

      const formData = new FormData();
      formData.append('_token', csrfToken);
      formData.append('title', enteredTitle || fallbackTitle);
      formData.append('type', 'video');
      formData.append('uploaded_path', data.path);
      formData.append('original_filename', data.original_filename || file.fileName || file.name || 'video');
      formData.append('description', '');
      formData.append('allow_download', 0);

      $.ajax({
        url: lessonsBaseUrl + '/' + targetLessonId + '/resources',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function () {
          uploadInFlight = false;
          uploadMonitor.done('تم رفع الفيديو وحفظه');
          location.reload();
        },
        error: function (xhr) {
          uploadInFlight = false;
          const message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'حدث خطأ أثناء حفظ الفيديو.';
          uploadMonitor.error(message);
          Swal.fire('خطأ', message, 'error');
        }
      });
    });

    videoUploadResumable.on('fileError', function () {
      uploadInFlight = false;
      uploadMonitor.error('تعذّر رفع الفيديو — سيعاد المحاولة تلقائيًا');
      Swal.fire('خطأ', 'تعذّر رفع الفيديو. سيتم إعادة المحاولة تلقائيًا عند استعادة الاتصال.', 'error');
    });
  }

  function selectVideoForLesson(lessonId) {
    pendingLessonId = lessonId;
    currentLessonId = lessonId;
    document.getElementById('resource_video_input').click();
  }

  function openUnitModal() {
    $('#form_add_unit')[0].reset();
    modalAddUnit.show();
  }

  function openLessonModal(unitId) {
    currentUnitId = unitId;
    $('#form_add_lesson')[0].reset();
    modalAddLesson.show();
  }

  function openResourceModal(lessonId) {
    currentLessonId = lessonId;
    $('#form_add_resource')[0].reset();
    document.getElementById('resource_uploaded_path').value = '';
    document.getElementById('resource_original_filename').value = '';
    document.getElementById('resource_video_filename').textContent = '';
    document.getElementById('resource_video_progress_wrap').classList.add('d-none');
    videoUploadDone = false;
    if (videoUploadResumable) videoUploadResumable.files = [];
    document.getElementById('resource_video_input').value = '';
    toggleResourceFields();
    modalAddResource.show();
  }

  function confirmDelete(callback) {
    Swal.fire({
      text: 'هل أنت متأكد من عملية الحذف؟ لا يمكن التراجع عن هذا الإجراء.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'نعم، احذف!',
      cancelButtonText: 'إلغاء',
    }).then(function (result) {
      if (result.isConfirmed) callback();
    });
  }

  $('#form_add_unit').on('submit', function (e) {
    e.preventDefault();
    $.ajax({
      url: unitsStoreUrl, type: 'POST',
      data: $(this).serialize() + '&_token=' + csrfToken,
      success: function () { location.reload(); },
      error: function () { Swal.fire('خطأ', 'حدث خطأ، يرجى التأكد من البيانات.', 'error'); }
    });
  });

  $('#form_add_lesson').on('submit', function (e) {
    e.preventDefault();
    $.ajax({
      url: unitsBaseUrl + '/' + currentUnitId + '/lessons', type: 'POST',
      data: $(this).serialize() + '&_token=' + csrfToken,
      success: function () { location.reload(); },
      error: function () { Swal.fire('خطأ', 'حدث خطأ، يرجى التأكد من البيانات.', 'error'); }
    });
  });

  $('#form_add_resource').on('submit', function (e) {
    e.preventDefault();
    const type = document.getElementById('resource_type').value;
    if (type === 'video' && !videoUploadDone) {
      Swal.fire('تنبيه', 'يرجى الانتظار حتى ينتهي رفع الفيديو قبل الحفظ.', 'warning');
      return;
    }
    const formData = new FormData(this);
    formData.append('_token', csrfToken);

    // Documents and images go up as one request, so progress comes from the
    // XHR upload event rather than Resumable's chunk callbacks.
    const picked = this.querySelector('#resource_document_field input[name="file"]:not([disabled])')
      || this.querySelector('#resource_image_field input[name="file"]:not([disabled])');
    const pickedFile = picked && picked.files && picked.files[0];
    let monitored = false;
    let activeXhr = null;

    if (pickedFile) {
      monitored = true;
      uploadInFlight = true;
      uploadMonitor.start(pickedFile.name, pickedFile.size, function () {
        if (activeXhr) activeXhr.abort();
        uploadInFlight = false;
      });
    }

    $.ajax({
      url: lessonsBaseUrl + '/' + currentLessonId + '/resources', type: 'POST',
      data: formData, contentType: false, processData: false,
      xhr: function () {
        const xhr = $.ajaxSettings.xhr();
        activeXhr = xhr;
        if (monitored && xhr.upload) {
          xhr.upload.addEventListener('progress', function (e) {
            if (e.lengthComputable) uploadMonitor.update(e.loaded, e.total);
          });
          xhr.upload.addEventListener('load', function () { uploadMonitor.finishing(); });
        }
        return xhr;
      },
      success: function () {
        if (monitored) { uploadInFlight = false; uploadMonitor.done(); }
        location.reload();
      },
      error: function (xhr, textStatus) {
        uploadInFlight = false;
        if (textStatus === 'abort') return; // user cancelled from the monitor
        const message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'حدث خطأ، يرجى التأكد من البيانات.';
        if (monitored) uploadMonitor.error(message);
        Swal.fire('خطأ', message, 'error');
      }
    });
  });

  function deleteWithSharedGuard(url) {
    confirmDelete(function () {
      $.ajax({
        url: url,
        type: 'DELETE',
        data: { _token: csrfToken },
        success: function () { location.reload(); },
        error: function (xhr) {
          if (xhr.status === 409 && xhr.responseJSON && xhr.responseJSON.code === 'shared_content') {
            Swal.fire({
              title: 'محتوى مشترك',
              text: xhr.responseJSON.message || 'الحذف سيزيله من كل المجموعات.',
              icon: 'warning',
              showCancelButton: true,
              confirmButtonText: 'نعم، احذف من الكل',
              cancelButtonText: 'إلغاء',
              confirmButtonColor: '#dc3545'
            }).then(function (result) {
              if (!result.isConfirmed) return;
              $.ajax({
                url: url + (url.indexOf('?') >= 0 ? '&' : '?') + 'confirm_shared_delete=1',
                type: 'DELETE',
                data: { _token: csrfToken, confirm_shared_delete: 1 },
                success: function () { location.reload(); },
                error: function () { Swal.fire('خطأ', 'تعذر الحذف', 'error'); }
              });
            });
            return;
          }
          Swal.fire('خطأ', (xhr.responseJSON && xhr.responseJSON.message) || 'تعذر الحذف', 'error');
        }
      });
    });
  }

  function deleteUnit(unitId) {
    deleteWithSharedGuard(unitsBaseUrl + '/' + unitId + '{{ $selectedGroupId ? '?detach_group_id='.$selectedGroupId : '' }}');
  }

  function deleteLesson(lessonId) {
    deleteWithSharedGuard(lessonsBaseUrl + '/' + lessonId + '{{ $selectedGroupId ? '?detach_group_id='.$selectedGroupId : '' }}');
  }

  function deleteResource(resourceId) {
    deleteWithSharedGuard(resourcesBaseUrl + '/' + resourceId + '{{ $selectedGroupId ? '?detach_group_id='.$selectedGroupId : '' }}');
  }
</script>
@endpush
