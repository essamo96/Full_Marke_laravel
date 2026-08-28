@extends('layouts.student')

@section('title', 'Learning Resources | FULL MARK ACADEMY')
@section('page_title_en', 'Resources')
@section('page_title_ar', 'الموارد التعليمية')

@section('content')
  <h1 class="h3 fw-bold mb-4" style="color: var(--text-primary);" data-en="Learning Resources" data-ar="الموارد التعليمية">Learning Resources</h1>

@push('styles')
  <link rel="stylesheet" href="{{ asset_ver('assets/css/student-resources.css') }}">
  <link rel="stylesheet" href="{{ asset_ver('assets/css/curriculum-accordion.css') }}">
@endpush

  @if($subjects->isEmpty())
    <div class="glass-panel rounded-4 p-5 text-center">
      <i class="bi bi-collection-play fs-1 mb-3 d-block" style="color: var(--accent-color);"></i>
      <p class="opacity-75 mb-3" data-en="No learning resources have been published for your subjects yet." data-ar="لا توجد موارد تعليمية منشورة لموادك حتى الآن.">No learning resources have been published for your subjects yet.</p>
      <a href="{{ route('student.registrations') }}" class="btn btn-glass px-4 py-2" data-en="View My Subjects" data-ar="عرض موادي">View My Subjects</a>
    </div>
  @else
    @foreach($subjects as $subject)
      @php $subjectResourceCount = $subject->units->sum(fn($u) => $u->lessons->sum(fn($l) => $l->resources->count())); @endphp
      <div class="glass-panel rounded-4 p-4 mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
          <h5 class="fw-bold mb-0 border-start border-4 ps-3" style="color: var(--text-primary); border-color: var(--accent-color) !important;">{{ $subject->name }}</h5>
          <span class="badge rounded-pill px-3 py-2" style="background: rgba(197,168,128,0.12); color: var(--accent-color);">{{ $subjectResourceCount }} <span data-en="resource(s)" data-ar="مورد">مورد</span></span>
        </div>

        <div class="accordion curriculum-accordion" id="accordion_subject_{{ $subject->id }}">
          @foreach($subject->units as $unitIndex => $unit)
            @php $unitResourceCount = $unit->lessons->sum(fn($l) => $l->resources->count()); @endphp
            @continue($unitResourceCount === 0)
            
            <div class="unit-card">
              <button type="button" class="unit-toggle" data-bs-toggle="collapse" data-bs-target="#unitPanel{{ $unit->id }}" aria-expanded="{{ $unitIndex === 0 ? 'true' : 'false' }}">
                <span class="unit-num">{{ $unitIndex + 1 }}</span>
                <span class="flex-grow-1">
                  <span class="unit-title d-block">{{ $unit->name_ar ?? $unit->name_en }}</span>
                  <span class="unit-meta d-block">{{ $unit->lessons->count() }} <span data-en="lesson(s)" data-ar="درس">درس</span> · {{ $unitResourceCount }} <span data-en="resource(s)" data-ar="مورد">مورد</span></span>
                </span>
                <i class="bi bi-chevron-down"></i>
              </button>
              
              <div id="unitPanel{{ $unit->id }}" class="collapse {{ $unitIndex === 0 ? 'show' : '' }}">
                @foreach($unit->lessons as $lesson)
                  @continue($lesson->resources->isEmpty())
                  <div class="lesson-block">
                    <button type="button" class="lesson-toggle collapsed" data-bs-toggle="collapse" data-bs-target="#lessonPanel{{ $lesson->id }}" aria-expanded="false">
                      <i class="bi bi-journal-text" style="color: var(--accent-color);"></i>
                      <span>{{ $lesson->name_ar ?? $lesson->name_en }}</span>
                      <span class="lesson-count">{{ $lesson->resources->count() }}</span>
                      <i class="bi bi-chevron-down"></i>
                    </button>
                    <div id="lessonPanel{{ $lesson->id }}" class="collapse">
                      <div class="lesson-resources">
                        <div class="row g-3">
                          @foreach($lesson->resources as $resource)
                            @include('student.resources.parts.resource-card', ['resource' => $resource])
                          @endforeach
                        </div>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          @endforeach
        </div>
      </div>
    @endforeach
  @endif

  <!-- Fullscreen is redirected to #lessonMediaContainer so the watermark stays visible. -->
  <style>
    #lessonMediaContainer:fullscreen,
    #lessonMediaContainer:-webkit-full-screen,
    #lessonMediaContainer .smp-root:fullscreen,
    #lessonMediaContainer .smp-root:-webkit-full-screen {
      width: 100vw;
      height: 100vh;
      aspect-ratio: auto;
      border-radius: 0;
    }
    #lessonMediaModal.video-is-fullscreen .modal-header {
      display: none !important;
    }
  </style>

  <!-- Unified protected media modal (uploaded MP4 + YouTube + Drive) -->
  <div class="modal fade" id="lessonMediaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog viewer-modal size-md modal-dialog-centered">
      <div class="modal-content glass-panel">
        <div class="modal-header border-0">
          <h5 class="modal-title fw-bold" id="lessonMediaTitle" style="color: var(--text-primary);"></h5>
          @include('student.resources.parts.viewer-size-toggle')
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body pt-0" oncontextmenu="return false;">
          <div id="lessonMediaContainer" style="position: relative; width: 100%; aspect-ratio: 16/9; background: #000; border-radius: 12px; overflow: hidden;"></div>
          <p id="lessonMediaError" class="text-danger mt-3 mb-0 d-none"></p>
        </div>
      </div>
    </div>
  </div>

  <!-- Secure document (PDF) modal -->
  <div class="modal fade" id="lessonDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog viewer-modal size-md modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content glass-panel">
        <div class="modal-header border-0">
          <h5 class="modal-title fw-bold" id="lessonDocumentTitle" style="color: var(--text-primary);"></h5>
          @include('student.resources.parts.viewer-size-toggle')
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body pt-0">
          <div id="lessonDocumentContainer" style="position: relative; width: 100%; height: 70vh; background: #1a1a1a; border-radius: 12px; overflow-y: auto; padding: 10px;">
            <div class="text-center text-white-50 py-5" id="lessonDocumentLoading">جاري تحميل الملف الآمن...</div>
          </div>
          <p id="lessonDocumentError" class="text-danger mt-3 mb-0 d-none"></p>
        </div>
      </div>
    </div>
  </div>

  <!-- Secure image modal -->
  <div class="modal fade" id="lessonImageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog viewer-modal size-md modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content glass-panel">
        <div class="modal-header border-0">
          <h5 class="modal-title fw-bold" id="lessonImageTitle" style="color: var(--text-primary);"></h5>
          @include('student.resources.parts.viewer-size-toggle')
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body pt-0">
          <div id="lessonImageContainer" style="position: relative; width: 100%; min-height: 40vh; background: #1a1a1a; border-radius: 12px; overflow: auto; padding: 10px; display: flex; align-items: center; justify-content: center;">
            <div class="text-center text-white-50 py-5" id="lessonImageLoading">جاري تحميل الصورة الآمنة...</div>
          </div>
          <p id="lessonImageError" class="text-danger mt-3 mb-0 d-none"></p>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script src="{{ asset_ver('assets/js/secure-watermark.js') }}"></script>
  <script src="{{ asset_ver('assets/js/student-secure-media-player.js') }}"></script>
  <script src="{{ asset_ver('assets/vendor/pdfjs/pdf.min.js') }}"></script>
  <script src="{{ asset_ver('assets/js/student-document-viewer.js') }}"></script>
  <script src="{{ asset_ver('assets/js/student-image-viewer.js') }}"></script>
  <script>
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });

    var VIEWER_SIZE_KEY = 'lessonViewerSize';

    function applyViewerSize(dialog, size) {
        dialog.classList.remove('size-sm', 'size-md', 'size-lg', 'modal-fullscreen');
        dialog.classList.add('size-' + size);
        if (size === 'lg') dialog.classList.add('modal-fullscreen');
        var modalEl = dialog.closest('.modal');
        if (!modalEl) return;
        modalEl.querySelectorAll('.viewer-size-btn').forEach(function (btn) {
            btn.classList.toggle('active', btn.dataset.size === size);
        });
    }

    document.querySelectorAll('.viewer-modal').forEach(function (dialog) {
        var savedSize = localStorage.getItem(VIEWER_SIZE_KEY) || 'md';
        applyViewerSize(dialog, savedSize);
    });

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.viewer-size-btn');
        if (!btn) return;
        var dialog = btn.closest('.viewer-modal');
        if (!dialog) return;
        var size = btn.dataset.size;
        applyViewerSize(dialog, size);
        localStorage.setItem(VIEWER_SIZE_KEY, size);
    });

    var studentMediaOpts = {
      studentName: @json(auth()->guard('student')->user()->name),
      studentPhotoUrl: @json(auth()->guard('student')->user()->photo_url),
      csrfToken: '{{ csrf_token() }}',
    };

    var lessonMediaDestroy = null;
    var lessonMediaModalEl = document.getElementById('lessonMediaModal');
    var lessonMediaModal = new bootstrap.Modal(lessonMediaModalEl);

    function openProtectedMedia(resourceId, title) {
      document.getElementById('lessonMediaTitle').textContent = title;
      document.getElementById('lessonMediaError').classList.add('d-none');
      if (lessonMediaDestroy) {
        lessonMediaDestroy();
        lessonMediaDestroy = null;
      }
      lessonMediaModal.show();

      lessonMediaDestroy = mountSecureMediaPlayer({
        container: document.getElementById('lessonMediaContainer'),
        resourceId: resourceId,
        resolveUrl: '{{ url('student/resources') }}/' + encodeURIComponent(resourceId) + '/resolve',
        startUrl: '{{ url('student/videos') }}/' + encodeURIComponent(resourceId) + '/start',
        studentName: studentMediaOpts.studentName,
        studentPhotoUrl: studentMediaOpts.studentPhotoUrl,
        csrfToken: studentMediaOpts.csrfToken,
        onError: function (message) {
          var errorEl = document.getElementById('lessonMediaError');
          errorEl.textContent = message;
          errorEl.classList.remove('d-none');
        },
      });
    }

    function openLessonVideo(resourceId, title) { openProtectedMedia(resourceId, title); }
    function openLessonLink(resourceId, title) { openProtectedMedia(resourceId, title); }

    lessonMediaModalEl.addEventListener('hidden.bs.modal', function () {
      if (lessonMediaDestroy) {
        lessonMediaDestroy();
        lessonMediaDestroy = null;
      }
    });

    var lessonImageDestroy = null;
    var lessonImageModalEl = document.getElementById('lessonImageModal');
    var lessonImageModal = new bootstrap.Modal(lessonImageModalEl);

    function openLessonImage(resourceId, title) {
      var container = document.getElementById('lessonImageContainer');
      var errorEl = document.getElementById('lessonImageError');
      container.innerHTML = '<div class="text-center text-white-50 py-5" id="lessonImageLoading">جاري تحميل الصورة الآمنة...</div>';
      errorEl.classList.add('d-none');
      document.getElementById('lessonImageTitle').textContent = title;
      lessonImageModal.show();

      lessonImageDestroy = mountSecureImageViewer({
        container: container,
        fileUrl: '{{ url('student/resources') }}/' + encodeURIComponent(resourceId) + '/file',
        studentName: studentMediaOpts.studentName,
        studentPhotoUrl: studentMediaOpts.studentPhotoUrl,
        onLoaded: function () {
          var loadingEl = document.getElementById('lessonImageLoading');
          if (loadingEl) loadingEl.remove();
        },
        onError: function (message) {
          errorEl.textContent = message;
          errorEl.classList.remove('d-none');
        },
      });
    }

    lessonImageModalEl.addEventListener('hidden.bs.modal', function () {
      if (lessonImageDestroy) {
        lessonImageDestroy();
        lessonImageDestroy = null;
      }
    });

    var lessonDocumentDestroy = null;
    var lessonDocumentModalEl = document.getElementById('lessonDocumentModal');
    var lessonDocumentModal = new bootstrap.Modal(lessonDocumentModalEl);

    function openLessonDocument(resourceId, title) {
      var container = document.getElementById('lessonDocumentContainer');
      var errorEl = document.getElementById('lessonDocumentError');
      container.innerHTML = '<div class="text-center text-white-50 py-5" id="lessonDocumentLoading">جاري تحميل الملف الآمن...</div>';
      errorEl.classList.add('d-none');
      document.getElementById('lessonDocumentTitle').textContent = title;
      lessonDocumentModal.show();

      lessonDocumentDestroy = mountSecureDocumentViewer({
        container: container,
        fileUrl: '{{ url('student/resources') }}/' + encodeURIComponent(resourceId) + '/file',
        studentName: studentMediaOpts.studentName,
        studentPhotoUrl: studentMediaOpts.studentPhotoUrl,
        onLoaded: function () {
          var loadingEl = document.getElementById('lessonDocumentLoading');
          if (loadingEl) loadingEl.remove();
        },
        onError: function (message) {
          errorEl.textContent = message;
          errorEl.classList.remove('d-none');
        },
      });
    }

    lessonDocumentModalEl.addEventListener('hidden.bs.modal', function () {
      if (lessonDocumentDestroy) {
        lessonDocumentDestroy();
        lessonDocumentDestroy = null;
      }
    });
  </script>
@endpush
