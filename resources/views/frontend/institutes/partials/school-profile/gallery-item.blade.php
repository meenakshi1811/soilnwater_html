@php
  $type = ($item['type'] ?? 'image') === 'video' ? 'video' : 'image';
  $url = $item['url'] ?? '';
@endphp
@if($type === 'video')
  <button
    type="button"
    class="sch-gallery-item sch-gallery-item--video js-sch-gallery-open"
    data-gallery-type="video"
    data-gallery-src="{{ $url }}"
    aria-label="Play gallery video"
  >
    <span class="sch-gallery-item__video-placeholder" aria-hidden="true">
      <i class="fa-solid fa-circle-play"></i>
      <span>Play video</span>
    </span>
  </button>
@else
  <button
    type="button"
    class="sch-gallery-item js-sch-gallery-open"
    data-gallery-type="image"
    data-gallery-src="{{ $url }}"
    aria-label="View gallery photo"
  >
    <img src="{{ $url }}" alt="Campus gallery image" loading="lazy" decoding="async">
  </button>
@endif
