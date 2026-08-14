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
  $cardClass = 'resource-card d-flex align-items-center gap-3 p-3 rounded-3 text-decoration-none h-100 w-100 border-0 text-start'
      . ($resource->allow_download ? ' has-download' : '');
@endphp
<div class="col-md-6 col-xxl-4 position-relative">
  @if($isVideo)
    <button type="button"
            class="{{ $cardClass }}"
            {{ ($isProcessing || $isFailed) ? 'disabled' : '' }}
            @if(!$isProcessing && !$isFailed)
              onclick="openLessonVideo(@js($resource->getRouteKey()), @js($resource->title))"
            @endif>
      <div class="resource-icon-circle rounded-circle d-flex align-items-center justify-content-center flex-shrink-0">
        <i class="bi bi-{{ $icon }} fs-5"></i>
      </div>
      <div class="flex-grow-1 resource-text">
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
            class="{{ $cardClass }}"
            onclick="openLessonDocument(@js($resource->getRouteKey()), @js($resource->title))">
      <div class="resource-icon-circle rounded-circle d-flex align-items-center justify-content-center flex-shrink-0">
        <i class="bi bi-{{ $icon }} fs-5"></i>
      </div>
      <div class="flex-grow-1 resource-text">
        <div class="fw-bold" style="color: var(--text-primary);">{{ $resource->title }}</div>
        @if($resource->description)
          <div class="text-xs opacity-75">{{ strip_tags(html_entity_decode($resource->description)) }}</div>
        @endif
      </div>
    </button>
  @elseif($resource->type === 'document')
    <a href="{{ route('student.resources.file', $resource) }}" target="_blank" rel="noopener" class="{{ $cardClass }}">
      <div class="resource-icon-circle rounded-circle d-flex align-items-center justify-content-center flex-shrink-0">
        <i class="bi bi-{{ $icon }} fs-5"></i>
      </div>
      <div class="flex-grow-1 resource-text">
        <div class="fw-bold" style="color: var(--text-primary);">{{ $resource->title }}</div>
        @if($resource->description)
          <div class="text-xs opacity-75">{{ strip_tags(html_entity_decode($resource->description)) }}</div>
        @endif
      </div>
      <i class="bi bi-box-arrow-up-right opacity-50 flex-shrink-0"></i>
    </a>
  @elseif($isImage)
    <button type="button"
            class="{{ $cardClass }}"
            onclick="openLessonImage(@js($resource->getRouteKey()), @js($resource->title))">
      <div class="resource-icon-circle rounded-circle d-flex align-items-center justify-content-center flex-shrink-0">
        <i class="bi bi-image-fill fs-5"></i>
      </div>
      <div class="flex-grow-1 resource-text">
        <div class="fw-bold" style="color: var(--text-primary);">{{ $resource->title }}</div>
        @if($resource->description)
          <div class="text-xs opacity-75">{{ strip_tags(html_entity_decode($resource->description)) }}</div>
        @endif
      </div>
    </button>
  @elseif($isLinkLike)
    <button type="button"
            class="{{ $cardClass }}"
            onclick="openLessonLink(@js($resource->getRouteKey()), @js($resource->title))">
      <div class="resource-icon-circle rounded-circle d-flex align-items-center justify-content-center flex-shrink-0">
        <i class="bi bi-{{ $icon }} fs-5"></i>
      </div>
      <div class="flex-grow-1 resource-text">
        <div class="fw-bold" style="color: var(--text-primary);">{{ $resource->title }}</div>
        @if($resource->description)
          <div class="text-xs opacity-75">{{ strip_tags(html_entity_decode($resource->description)) }}</div>
        @endif
      </div>
    </button>
  @endif

  @if($resource->allow_download)
    <a href="{{ route('student.resources.download', $resource) }}" target="_blank" class="resource-download-btn d-flex align-items-center justify-content-center rounded-circle" title="تحميل">
      <i class="bi bi-download fs-5"></i>
    </a>
  @endif
</div>
