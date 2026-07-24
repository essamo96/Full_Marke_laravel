@extends('layouts.student')

@section('title', 'Learning Resources | FULL MARK ACADEMY')
@section('page_title_en', 'Resources')
@section('page_title_ar', 'الموارد التعليمية')

@section('content')
  <h1 class="h3 fw-bold mb-4" style="color: var(--text-primary);" data-en="Learning Resources" data-ar="الموارد التعليمية">Learning Resources</h1>

  @if($resourcesBySubject->isEmpty())
    <div class="glass-panel rounded-4 p-5 text-center">
      <i class="bi bi-collection-play fs-1 mb-3 d-block" style="color: var(--accent-color);"></i>
      <p class="opacity-75 mb-3" data-en="No learning resources have been published for your subjects yet." data-ar="لا توجد موارد تعليمية منشورة لموادك حتى الآن.">No learning resources have been published for your subjects yet.</p>
      <a href="{{ route('student.registrations') }}" class="btn btn-glass px-4 py-2" data-en="View My Subjects" data-ar="عرض موادي">View My Subjects</a>
    </div>
  @else
    @foreach($resourcesBySubject as $subjectId => $resources)
      @php $subject = $resources->first()->subject; @endphp
      <div class="glass-panel rounded-4 p-4 mb-4">
        <h5 class="fw-bold mb-3" style="color: var(--text-primary);">{{ $subject->name }}</h5>
        <div class="row g-3">
          @foreach($resources as $resource)
            @php
              $icon = match($resource->type) {
                'video' => 'play-circle-fill',
                'document' => 'file-earmark-text-fill',
                'image' => 'image-fill',
                'zoom' => 'camera-video-fill',
                default => 'link-45deg',
              };
              $isVideo = $resource->isVideo();
              $isFailed = $isVideo && $resource->processing_status === 'failed';
              $isProcessing = $isVideo && ! $isFailed && ! $resource->isReady();
              $isPdf = $resource->type === 'document' && strtolower(pathinfo($resource->url ?? '', PATHINFO_EXTENSION)) === 'pdf';
              $isImage = $resource->isImage();
              $isLinkLike = $resource->isExternalLink();
            @endphp
            <div class="col-md-6 position-relative">
              @if($isVideo)
                <button type="button"
                        class="d-flex align-items-center gap-3 p-3 rounded-3 text-decoration-none h-100 w-100 border-0 text-start"
                        style="background: var(--bg-secondary); border: 1px solid var(--separator-color);"
                        {{ ($isProcessing || $isFailed) ? 'disabled' : '' }}
                        @if(!$isProcessing && !$isFailed)
                          onclick="openLessonVideo('{{ $resource->getRouteKey() }}', '{{ addslashes($resource->title) }}')"
                        @endif>
                  <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; background: rgba(197,168,128,0.1); color: var(--accent-color);">
                    <i class="bi bi-{{ $icon }} fs-5"></i>
                  </div>
                  <div class="flex-grow-1" dir="auto" style="padding-left: 50px;">
                    <div class="fw-bold" style="color: var(--text-primary);">{{ $resource->title }}</div>
                    @if($isProcessing)
                      <div class="text-xs opacity-75" data-en="Still processing — check back soon" data-ar="جاري تجهيز الفيديو، حاول لاحقًا">جاري تجهيز الفيديو، حاول لاحقًا</div>
                    @elseif($isFailed)
                      <div class="text-xs text-danger" data-en="Processing failed" data-ar="تعذّرت معالجة الفيديو">تعذّرت معالجة الفيديو</div>
                    @elseif($resource->description)
                      <div class="text-xs opacity-75">{{ strip_tags(html_entity_decode($resource->description)) }}</div>
                    @endif
                  </div>
                </button>
              @elseif($resource->type === 'document' && $isPdf)
                <button type="button"
                        class="d-flex align-items-center gap-3 p-3 rounded-3 text-decoration-none h-100 w-100 border-0 text-start"
                        style="background: var(--bg-secondary); border: 1px solid var(--separator-color);"
                        onclick="openLessonDocument('{{ $resource->getRouteKey() }}', '{{ addslashes($resource->title) }}')">
                  <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; background: rgba(197,168,128,0.1); color: var(--accent-color);">
                    <i class="bi bi-{{ $icon }} fs-5"></i>
                  </div>
                  <div class="flex-grow-1" dir="auto" style="padding-left: 50px;">
                    <div class="fw-bold" style="color: var(--text-primary);">{{ $resource->title }}</div>
                    @if($resource->description)
                      <div class="text-xs opacity-75">{{ strip_tags(html_entity_decode($resource->description)) }}</div>
                    @endif
                  </div>
                </button>
              @elseif($resource->type === 'document')
                <a href="{{ route('student.resources.file', $resource) }}" target="_blank" rel="noopener" class="d-flex align-items-center gap-3 p-3 rounded-3 text-decoration-none h-100" style="background: var(--bg-secondary); border: 1px solid var(--separator-color);">
                  <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; background: rgba(197,168,128,0.1); color: var(--accent-color);">
                    <i class="bi bi-{{ $icon }} fs-5"></i>
                  </div>
                  <div class="flex-grow-1" dir="auto" style="padding-left: 50px;">
                    <div class="fw-bold" style="color: var(--text-primary);">{{ $resource->title }}</div>
                    @if($resource->description)
                      <div class="text-xs opacity-75">{{ strip_tags(html_entity_decode($resource->description)) }}</div>
                    @endif
                  </div>
                  <i class="bi bi-box-arrow-up-right opacity-50" style="margin-left: 50px;"></i>
                </a>
              @elseif($isImage)
                <button type="button"
                        class="d-flex align-items-center gap-3 p-3 rounded-3 text-decoration-none h-100 w-100 border-0 text-start"
                        style="background: var(--bg-secondary); border: 1px solid var(--separator-color);"
                        onclick="openLessonImage('{{ $resource->getRouteKey() }}', '{{ addslashes($resource->title) }}')">
                  <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; background: rgba(197,168,128,0.1); color: var(--accent-color);">
                    <i class="bi bi-image-fill fs-5"></i>
                  </div>
                  <div class="flex-grow-1" dir="auto" style="padding-left: 50px;">
                    <div class="fw-bold" style="color: var(--text-primary);">{{ $resource->title }}</div>
                    @if($resource->description)
                      <div class="text-xs opacity-75">{{ strip_tags(html_entity_decode($resource->description)) }}</div>
                    @endif
                  </div>
                </button>
              @elseif($isLinkLike)
                <button type="button"
                        class="d-flex align-items-center gap-3 p-3 rounded-3 text-decoration-none h-100 w-100 border-0 text-start"
                        style="background: var(--bg-secondary); border: 1px solid var(--separator-color);"
                        onclick="openLessonLink('{{ $resource->getRouteKey() }}', '{{ addslashes($resource->title) }}')">
                  <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; background: rgba(197,168,128,0.1); color: var(--accent-color);">
                    <i class="bi bi-{{ $icon }} fs-5"></i>
                  </div>
                  <div class="flex-grow-1" dir="auto" style="padding-left: 50px;">
                    <div class="fw-bold" style="color: var(--text-primary);">{{ $resource->title }}</div>
                    @if($resource->description)
                      <div class="text-xs opacity-75">{{ strip_tags(html_entity_decode($resource->description)) }}</div>
                    @endif
                  </div>
                </button>
              @endif

              @if($resource->allow_download)
                <a href="{{ route('student.resources.download', $resource) }}" target="_blank" class="position-absolute d-flex align-items-center justify-content-center rounded-circle" style="top: 50%; left: 1.5rem; transform: translateY(-50%); z-index: 10; width: 40px; height: 40px; background: rgba(197,168,128,0.15); color: var(--accent-color); text-decoration: none; transition: background 0.2s;" title="تحميل">
                  <i class="bi bi-download fs-5"></i>
                </a>
              @endif
            </div>
          @endforeach
        </div>
      </div>
    @endforeach
  @endif

  <!-- Secure lesson video modal -->
  <div class="modal fade" id="lessonVideoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content" style="background: var(--bg-secondary); border: 1px solid var(--separator-color);">
        <div class="modal-header border-0">
          <h5 class="modal-title fw-bold" id="lessonVideoTitle" style="color: var(--text-primary);"></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body pt-0">
          <div id="lessonVideoContainer" style="position: relative; width: 100%; aspect-ratio: 16/9; background: #000; border-radius: 12px; overflow: hidden;">
            <video id="lessonVideoEl" style="width: 100%; height: 100%;" controls playsinline></video>
          </div>
          <p id="lessonVideoError" class="text-danger mt-3 mb-0 d-none"></p>
        </div>
      </div>
    </div>
  </div>

  <!-- Secure document (PDF) modal -->
  <div class="modal fade" id="lessonDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content" style="background: var(--bg-secondary); border: 1px solid var(--separator-color);">
        <div class="modal-header border-0">
          <h5 class="modal-title fw-bold" id="lessonDocumentTitle" style="color: var(--text-primary);"></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body pt-0">
          <div id="lessonDocumentContainer" style="position: relative; width: 100%; min-height: 60vh; background: #1a1a1a; border-radius: 12px; overflow: auto; padding: 10px;">
            <div class="text-center text-white-50 py-5" id="lessonDocumentLoading">جاري تحميل الملف الآمن...</div>
          </div>
          <p id="lessonDocumentError" class="text-danger mt-3 mb-0 d-none"></p>
        </div>
      </div>
    </div>
  </div>

  <!-- Secure image modal -->
  <div class="modal fade" id="lessonImageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content" style="background: var(--bg-secondary); border: 1px solid var(--separator-color);">
        <div class="modal-header border-0">
          <h5 class="modal-title fw-bold" id="lessonImageTitle" style="color: var(--text-primary);"></h5>
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
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content" style="background: var(--bg-secondary); border: 1px solid var(--separator-color);">
        <div class="modal-header border-0">
          <h5 class="modal-title fw-bold" id="lessonLinkTitle" style="color: var(--text-primary);"></h5>
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
  <script src="{{ asset('assets/js/student-video-player.js') }}"></script>
  <script src="{{ asset('assets/vendor/pdfjs/pdf.min.js') }}"></script>
  <script src="{{ asset('assets/js/student-document-viewer.js') }}"></script>
  <script src="{{ asset('assets/js/student-image-viewer.js') }}"></script>
  <script>
    // Disable right-click context menu on the entire page
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
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
        studentPhotoUrl: @json(auth()->guard('student')->user()->image ? asset('storage/' . auth()->guard('student')->user()->image) : null),
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
        studentPhotoUrl: @json(auth()->guard('student')->user()->image ? asset('storage/' . auth()->guard('student')->user()->image) : null),
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
        studentPhotoUrl: @json(auth()->guard('student')->user()->image ? asset('storage/' . auth()->guard('student')->user()->image) : null),
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
