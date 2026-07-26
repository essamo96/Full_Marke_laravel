@extends('layouts.teacher')

@section('title', 'إدارة المحتوى - ' . $subject->name)
@section('page_title_en', 'Manage Content')
@section('page_title_ar', 'إدارة المحتوى')

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/vendor/resumable/resumable.js') }}"></script>
@endpush

@section('content')

  <style>
    /* Color/border/background rules for these modals now come from the
       shared .glass-panel + .modal-content.glass-panel rules in
       landing.css, which read the active theme's CSS variables — the
       hardcoded rgba(255,255,255,...) values previously here made the
       modal invisible/illegible under theme-light. */
    .teacher-content-modal .form-control,
    .teacher-content-modal .form-select {
      box-shadow: none;
    }

    .teacher-content-modal .btn-luxury {
      box-shadow: 0 12px 30px var(--accent-glow);
    }

    .teacher-content-modal {
      z-index: 1060;
    }

    .teacher-content-modal .modal-backdrop {
      z-index: 1050;
    }

    .teacher-content-accordion .teacher-content-collapse {
      visibility: visible !important;
    }

    .teacher-content-accordion .teacher-content-collapse:not(.show) {
      display: none;
    }

    .teacher-content-accordion .teacher-content-collapse.show {
      display: block;
    }

    .teacher-resource-card {
      background: rgba(255,255,255,0.08);
      border: 1px solid rgba(255,255,255,0.12);
      border-radius: 1rem;
      padding: 1rem;
      transition: all 0.2s ease;
      backdrop-filter: blur(14px);
      height: 100%;
    }

    .teacher-resource-card:hover {
      transform: translateY(-2px);
      border-color: rgba(255,255,255,0.24);
      box-shadow: 0 10px 24px rgba(0,0,0,0.16);
    }

    .teacher-resource-icon {
      width: 46px;
      height: 46px;
      border-radius: 14px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.15rem;
      color: white;
      flex-shrink: 0;
    }

    .teacher-resource-icon.video { background: linear-gradient(135deg, #ff6b6b, #ff3d71); }
    .teacher-resource-icon.document { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .teacher-resource-icon.image { background: linear-gradient(135deg, #10b981, #059669); }
    .teacher-resource-icon.link { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
    .teacher-resource-icon.zoom { background: linear-gradient(135deg, #f59e0b, #d97706); }

    .teacher-resource-badge {
      border-radius: 999px;
      padding: 0.35rem 0.7rem;
      font-size: 0.78rem;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      background: rgba(255,255,255,0.12);
      color: var(--text-primary);
    }
  </style>

  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h1 class="h3 fw-bold mb-0" style="color: var(--text-primary);">{{ $subject->name }}</h1>
    <button type="button" class="btn btn-luxury" onclick="openUnitModal()">
      <i class="bi bi-plus-lg me-1"></i> <span data-en="Add Unit" data-ar="إضافة وحدة">إضافة وحدة</span>
    </button>
  </div>

  <div class="accordion teacher-accordion teacher-content-accordion" id="unitsAccordion">
    @forelse($units as $unit)
      <div class="glass-panel rounded-4 p-3 mb-3">
        <div class="d-flex justify-content-between align-items-center" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#unit_{{ $unit->id }}">
          <h5 class="fw-bold mb-0" style="color: var(--text-primary);">{{ $unit->name_ar ?? $unit->name_en }}</h5>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-success" title="إضافة درس" onclick="event.stopPropagation(); openLessonModal({{ $unit->id }})"><i class="bi bi-plus-lg"></i></button>
            <button type="button" class="btn btn-sm btn-outline-danger" title="حذف الوحدة" onclick="event.stopPropagation(); deleteUnit({{ $unit->id }})"><i class="bi bi-trash"></i></button>
          </div>
        </div>
        <div id="unit_{{ $unit->id }}" class="collapse mt-3 teacher-content-collapse">
          @forelse($unit->lessons as $lesson)
            <div class="rounded-3 p-3 mb-2" style="background: var(--bg-secondary);">
              <div class="d-flex justify-content-between align-items-center">
                <span class="fw-medium" style="color: var(--text-primary);">{{ $lesson->name_ar ?? $lesson->name_en }}</span>
                <div class="d-flex gap-2 align-items-center">
                  <span class="badge bg-gold text-dark">{{ $lesson->resources->count() }} مرفق</span>
                  <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#lesson_res_{{ $lesson->id }}">إدارة المرفقات</button>
                  <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteLesson({{ $lesson->id }})"><i class="bi bi-trash"></i></button>
                </div>
              </div>
              <div id="lesson_res_{{ $lesson->id }}" class="collapse mt-3 teacher-content-collapse">
                <div class="row g-3 mb-3">
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
                    <div class="col-12 col-md-6 col-xl-4">
                      <div class="teacher-resource-card">
                        <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                          <div class="d-flex align-items-center gap-2">
                            <span class="teacher-resource-icon {{ $resourceType }}"><i class="{{ $icon }}"></i></span>
                            <div>
                              <div class="fw-bold" style="color: var(--text-primary);">{{ $resource->title }}</div>
                              <div class="teacher-resource-badge mt-1">{{ $title }}</div>
                            </div>
                          </div>
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

                        <div class="d-flex flex-wrap gap-2">
                          @if($resource->isExternalLink())
                            <a href="{{ $resource->url }}" target="_blank" class="btn btn-sm btn-outline-primary">فتح الرابط</a>
                          @elseif($resource->type === 'document' || $resource->isImage())
                            <a href="{{ route('teacher.content.view-file', $resource) }}" target="_blank" class="btn btn-sm btn-outline-primary">فتح الملف</a>
                          @elseif($resource->type === 'video')
                            <a href="{{ route('teacher.content.view-file', $resource) }}" target="_blank" class="btn btn-sm btn-outline-primary">مشاهدة الفيديو</a>
                          @else
                            <span class="btn btn-sm btn-outline-secondary disabled">لا يوجد محتوى</span>
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
                <div class="d-flex flex-wrap gap-2">
                  <button type="button" class="btn btn-sm btn-outline-success" onclick="selectVideoForLesson({{ $lesson->id }})">
                    <i class="bi bi-cloud-arrow-up me-1"></i> رفع فيديو
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-primary" onclick="openResourceModal({{ $lesson->id }})">
                    <i class="bi bi-plus-lg me-1"></i> إضافة مرفق (PDF / رابط)
                  </button>
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

@endsection

@push('scripts')
<script>
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const unitsStoreUrl = '{{ route('teacher.content.store-unit', $subject) }}';
  const unitsBaseUrl = '{{ url('teacher/content/units') }}';
  const lessonsBaseUrl = '{{ url('teacher/content/lessons') }}';
  const resourcesBaseUrl = '{{ url('teacher/content/resources') }}';
  const chunkUploadUrl = '{{ route('teacher.content.upload-chunk') }}';

  let modalAddUnit, modalAddLesson, modalAddResource;
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
    document.getElementById('resource_video_field').classList.toggle('d-none', type !== 'video');
    document.getElementById('resource_document_field').classList.toggle('d-none', type !== 'document');
    document.getElementById('resource_image_field').classList.toggle('d-none', type !== 'image');
    document.getElementById('resource_url_field').classList.toggle('d-none', type !== 'link' && type !== 'zoom');
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
      document.getElementById('resource_uploaded_path').value = '';
      document.getElementById('resource_video_filename').textContent = file.fileName;
      document.getElementById('resource_video_progress_wrap').classList.remove('d-none');
      document.getElementById('resource_video_progress').style.width = '0%';
      videoUploadResumable.upload();
    });

    videoUploadResumable.on('fileProgress', function () {
      const pct = Math.floor(videoUploadResumable.progress() * 100);
      document.getElementById('resource_video_progress').style.width = pct + '%';
    });

    videoUploadResumable.on('fileSuccess', function (file, response) {
      const data = JSON.parse(response);
      document.getElementById('resource_uploaded_path').value = data.path;
      document.getElementById('resource_original_filename').value = data.original_filename;
      document.getElementById('resource_video_progress').style.width = '100%';
      videoUploadDone = true;

      const targetLessonId = pendingLessonId || currentLessonId;
      if (!targetLessonId) {
        Swal.fire('تنبيه', 'لم يتم تحديد درس لربط الفيديو.', 'warning');
        return;
      }

      const formData = new FormData();
      formData.append('_token', csrfToken);
      formData.append('title', (data.original_filename || file.fileName || file.name || 'فيديو جديد').replace(/\.[^/.]+$/, ''));
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
        success: function () { location.reload(); },
        error: function (xhr) {
          const message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'حدث خطأ أثناء حفظ الفيديو.';
          Swal.fire('خطأ', message, 'error');
        }
      });
    });

    videoUploadResumable.on('fileError', function () {
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
    $.ajax({
      url: lessonsBaseUrl + '/' + currentLessonId + '/resources', type: 'POST',
      data: formData, contentType: false, processData: false,
      success: function () { location.reload(); },
      error: function (xhr) {
        const message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'حدث خطأ، يرجى التأكد من البيانات.';
        Swal.fire('خطأ', message, 'error');
      }
    });
  });

  function deleteUnit(unitId) {
    confirmDelete(function () {
      $.ajax({ url: unitsBaseUrl + '/' + unitId, type: 'DELETE', data: { _token: csrfToken }, success: function () { location.reload(); } });
    });
  }

  function deleteLesson(lessonId) {
    confirmDelete(function () {
      $.ajax({ url: lessonsBaseUrl + '/' + lessonId, type: 'DELETE', data: { _token: csrfToken }, success: function () { location.reload(); } });
    });
  }

  function deleteResource(resourceId) {
    confirmDelete(function () {
      $.ajax({ url: resourcesBaseUrl + '/' + resourceId, type: 'DELETE', data: { _token: csrfToken }, success: function () { location.reload(); } });
    });
  }
</script>
@endpush
