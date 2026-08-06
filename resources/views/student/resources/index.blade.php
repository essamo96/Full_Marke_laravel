@extends('layouts.student')

@section('title', 'Learning Resources | FULL MARK ACADEMY')
@section('page_title_en', 'Resources')
@section('page_title_ar', 'الموارد التعليمية')

@section('content')
  <h1 class="h3 fw-bold mb-4" style="color: var(--text-primary);" data-en="Learning Resources" data-ar="الموارد التعليمية">Learning Resources</h1>

  <style>
    .resource-card {
      background: var(--bg-secondary);
      border: 1px solid var(--separator-color);
      transition: border-color .2s ease, transform .2s ease, box-shadow .2s ease;
      min-width: 0;
    }
    .resource-card:not(:disabled):hover {
      border-color: var(--accent-color);
      transform: translateY(-2px);
      box-shadow: 0 6px 18px rgba(0,0,0,.12);
    }
    .resource-card.has-download {
      padding-inline-end: 64px !important;
    }
    .resource-card .resource-text {
      min-width: 0;
      overflow-wrap: anywhere;
      text-align: start;
    }
    .resource-icon-circle {
      width: 44px; height: 44px;
      background: rgba(197,168,128,0.12);
      color: var(--accent-color);
      border: 1px solid rgba(197,168,128,0.25);
    }
    .resource-download-btn {
      position: absolute;
      top: 50%;
      inset-inline-end: 24px;
      transform: translateY(-50%);
      z-index: 10;
      width: 40px; height: 40px;
      background: rgba(197,168,128,0.15);
      color: var(--accent-color);
      text-decoration: none;
      transition: background .2s ease;
    }
    .resource-download-btn:hover {
      background: rgba(197,168,128,0.3);
      color: var(--accent-color);
    }

    /* Viewer size modes — student-controlled small/medium/large display for
       every secure viewer modal (video, document, image, link). */
    .viewer-modal.size-sm { max-width: 420px; }
    .viewer-modal.size-md { max-width: 800px; }
    /* "large" uses Bootstrap's own .modal-fullscreen class (added/removed in
       JS) so it genuinely fills the entire viewport — no margins, no rounded
       corners — instead of just being a wide dialog. */
    .viewer-modal.modal-fullscreen .modal-content { border-radius: 0; }
    .viewer-modal.modal-fullscreen #lessonVideoContainer,
    .viewer-modal.modal-fullscreen #lessonLinkEmbedWrap {
      height: 100% !important;
      aspect-ratio: unset !important;
    }
    .viewer-modal.modal-fullscreen #lessonDocumentContainer,
    .viewer-modal.modal-fullscreen #lessonImageContainer {
      height: 100% !important;
      min-height: 0 !important;
    }
    .viewer-modal.modal-fullscreen .modal-body {
      display: flex;
      flex-direction: column;
      height: calc(100vh - 80px);
    }
    .viewer-size-toggle {
      display: flex;
      align-items: center;
      gap: 4px;
      margin-inline-end: 12px;
    }
    .viewer-size-toggle button {
      width: 30px; height: 30px;
      display: flex; align-items: center; justify-content: center;
      background: transparent;
      border: 1px solid var(--separator-color);
      border-radius: 6px;
      color: var(--text-secondary);
      transition: background .15s ease, color .15s ease;
    }
    .viewer-size-toggle button.active {
      background: var(--accent-color);
      color: #000;
      border-color: var(--accent-color);
    }
  </style>

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

        @foreach($subject->units as $unit)
          @php $unitResourceCount = $unit->lessons->sum(fn($l) => $l->resources->count()); @endphp
          @continue($unitResourceCount === 0)
          <div class="mb-3">
            <h6 class="fw-bold mb-2 d-flex align-items-center gap-2" style="color: var(--text-primary);">
              <i class="bi bi-folder2-open" style="color: var(--accent-color);"></i>
              {{ $unit->name_ar ?? $unit->name_en }}
            </h6>

            @foreach($unit->lessons as $lesson)
              @continue($lesson->resources->isEmpty())
              <div class="rounded-3 p-3 mb-3" style="background: var(--bg-secondary); border: 1px solid var(--separator-color);">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                  <span class="fw-semibold d-flex align-items-center gap-2" style="color: var(--text-primary);">
                    <i class="bi bi-journal-text opacity-75"></i>
                    {{ $lesson->name_ar ?? $lesson->name_en }}
                  </span>
                  <span class="badge rounded-pill px-3 py-1" style="background: rgba(197,168,128,0.1); color: var(--accent-color); font-size: 0.75rem;">{{ $lesson->resources->count() }} <span data-en="resource(s)" data-ar="مورد">مورد</span></span>
                </div>
                <div class="row g-3">
                  @foreach($lesson->resources as $resource)
                    @include('student.resources.parts.resource-card', ['resource' => $resource])
                  @endforeach
                </div>
              </div>
            @endforeach
          </div>
        @endforeach
      </div>
    @endforeach
  @endif

  <!-- Fullscreen is redirected from the <video> to #lessonVideoContainer (see
       keepOverlayInFullscreen in student-video-player.js) so the watermark
       canvas — a sibling of the video, not a child — stays visible when zoomed. -->
  <style>
    #lessonVideoContainer:fullscreen,
    #lessonVideoContainer:-webkit-full-screen {
      width: 100vw;
      height: 100vh;
      aspect-ratio: auto;
      border-radius: 0;
    }
    #lessonVideoContainer:fullscreen #lessonVideoEl,
    #lessonVideoContainer:-webkit-full-screen #lessonVideoEl {
      object-fit: contain;
    }

    /* Hides the modal's title/size-toggle/close bar while the video is
       fullscreen (see keepOverlayInFullscreen in student-video-player.js for
       why it would otherwise still paint on top of the fullscreen video). */
    #lessonVideoModal.video-is-fullscreen .modal-header {
      display: none !important;
    }
  </style>

  <!-- Secure lesson video modal -->
  <div class="modal fade" id="lessonVideoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog viewer-modal size-md modal-dialog-centered">
      <div class="modal-content glass-panel">
        <div class="modal-header border-0">
          <h5 class="modal-title fw-bold" id="lessonVideoTitle" style="color: var(--text-primary);"></h5>
          @include('student.resources.parts.viewer-size-toggle')
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body pt-0">
          <div id="lessonVideoContainer" style="position: relative; width: 100%; aspect-ratio: 16/9; background: #000; border-radius: 12px; overflow: hidden;">
            <video id="lessonVideoEl" style="width: 100%; height: 100%; object-fit: contain;" controls playsinline></video>
          </div>
          <p id="lessonVideoError" class="text-danger mt-3 mb-0 d-none"></p>
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

  <!-- External link modal (YouTube embed, or a gated "open" action for non-embeddable links like Zoom) -->
  <div class="modal fade" id="lessonLinkModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog viewer-modal size-md modal-dialog-centered">
      <div class="modal-content glass-panel">
        <div class="modal-header border-0">
          <h5 class="modal-title fw-bold" id="lessonLinkTitle" style="color: var(--text-primary);"></h5>
          @include('student.resources.parts.viewer-size-toggle')
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body pt-0" oncontextmenu="return false;">
          <div id="lessonLinkEmbedWrap" class="d-none" style="position: relative; width: 100%; aspect-ratio: 16/9; background: #000; border-radius: 12px; overflow: hidden;">
            <iframe id="lessonLinkIframe" style="width: 100%; height: 100%; border: 0;" allow="encrypted-media; picture-in-picture" allowfullscreen oncontextmenu="return false;"></iframe>
          </div>
          <div id="lessonLinkOpenWrap" class="d-none text-center py-4">
            <p class="opacity-75 mb-3" style="color: var(--text-primary);">هذا الرابط يفتح في نافذة خارجية (مثل Zoom).</p>
            <button type="button" id="lessonLinkOpenBtn" class="btn btn-glass px-4 py-2">فتح الرابط</button>
          </div>
          <div id="lessonLinkLoading" class="text-center opacity-75 py-4" style="color: var(--text-primary);">جاري التحقق من صلاحية الوصول...</div>
          <p id="lessonLinkError" class="text-danger mt-3 mb-0 d-none"></p>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script src="{{ asset('assets/vendor/hlsjs/hls.min.js') }}"></script>
  <script src="{{ asset('assets/js/secure-watermark.js') }}"></script>
  <script src="{{ asset('assets/js/student-video-player.js') }}"></script>
  <script src="{{ asset('assets/vendor/pdfjs/pdf.min.js') }}"></script>
  <script src="{{ asset('assets/js/student-document-viewer.js') }}"></script>
  <script src="{{ asset('assets/js/student-image-viewer.js') }}"></script>
  <script>
    // Disable right-click context menu on the entire page
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });

    // Small/medium/large display size for every secure viewer modal. The
    // watermark canvas already re-fits itself via ResizeObserver whenever its
    // container's pixel size changes, and right-click blocking is applied on
    // the video/document/image/iframe elements themselves — not the modal
    // dialog — so switching sizes here never drops either protection.
    var VIEWER_SIZE_KEY = 'lessonViewerSize';

    function applyViewerSize(dialog, size) {
        dialog.classList.remove('size-sm', 'size-md', 'size-lg', 'modal-fullscreen');
        dialog.classList.add('size-' + size);
        // "large" = Bootstrap's real fullscreen modal (100vw/100vh, no margins),
        // not just a wider dialog, so the "enlarge" button actually fills the screen.
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
        fileUrl: '{{ url('student/resources') }}/' + resourceId + '/file',
        studentName: @json(auth()->guard('student')->user()->name),
        studentPhotoUrl: @json(auth()->guard('student')->user()->photo_url),
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

    var lessonLinkModalEl = document.getElementById('lessonLinkModal');
    var lessonLinkModal = new bootstrap.Modal(lessonLinkModalEl);

    function openLessonLink(resourceId, title) {
      var embedWrap = document.getElementById('lessonLinkEmbedWrap');
      var openWrap = document.getElementById('lessonLinkOpenWrap');
      var loadingEl = document.getElementById('lessonLinkLoading');
      var errorEl = document.getElementById('lessonLinkError');
      var iframe = document.getElementById('lessonLinkIframe');

      embedWrap.classList.add('d-none');
      openWrap.classList.add('d-none');
      errorEl.classList.add('d-none');
      loadingEl.classList.remove('d-none');
      document.getElementById('lessonLinkTitle').textContent = title;
      lessonLinkModal.show();

      // Load the secure embed view
      iframe.src = '{{ url('student/secure-embed') }}/' + resourceId;
      
      iframe.onload = function() {
        loadingEl.classList.add('d-none');
        embedWrap.classList.remove('d-none');
      };
    }

    lessonLinkModalEl.addEventListener('hidden.bs.modal', function () {
      document.getElementById('lessonLinkIframe').src = 'about:blank';
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
        fileUrl: '{{ url('student/resources') }}/' + resourceId + '/file',
        studentName: @json(auth()->guard('student')->user()->name),
        studentPhotoUrl: @json(auth()->guard('student')->user()->photo_url),
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

    var lessonVideoDestroy = null;
    var lessonVideoModalEl = document.getElementById('lessonVideoModal');
    var lessonVideoModal = new bootstrap.Modal(lessonVideoModalEl);

    function openLessonVideo(resourceId, title) {
      document.getElementById('lessonVideoTitle').textContent = title;
      document.getElementById('lessonVideoError').classList.add('d-none');
      lessonVideoModal.show();

      lessonVideoDestroy = mountSecureVideoPlayer({
        resourceId: resourceId,
        container: document.getElementById('lessonVideoContainer'),
        videoEl: document.getElementById('lessonVideoEl'),
        startUrl: '{{ url('student/videos') }}/' + resourceId + '/start',
        studentName: @json(auth()->guard('student')->user()->name),
        studentPhotoUrl: @json(auth()->guard('student')->user()->photo_url),
        csrfToken: '{{ csrf_token() }}',
        onError: function (message) {
          var errorEl = document.getElementById('lessonVideoError');
          errorEl.textContent = message;
          errorEl.classList.remove('d-none');
        },
      });
    }

    lessonVideoModalEl.addEventListener('hidden.bs.modal', function () {
      if (lessonVideoDestroy) {
        lessonVideoDestroy();
        lessonVideoDestroy = null;
      }
    });
  </script>
@endpush
