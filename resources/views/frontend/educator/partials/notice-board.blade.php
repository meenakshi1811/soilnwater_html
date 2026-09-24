@php
  $notices = $notices ?? collect();
  $layout = $layout ?? 'grid';
  $showViewAll = $showViewAll ?? ! empty($viewAllUrl);
@endphp

<div
  class="edu-notices js-edu-notice-carousel edu-notices--{{ $layout }} {{ !empty($featured) ? 'edu-notices--featured' : '' }} {{ $notices->isEmpty() ? 'is-empty' : '' }}"
  id="eduNoticeCarousel"
  data-notice-layout="{{ $layout }}"
>
  <div class="edu-notices__head">
    <div class="edu-notices__title-wrap">
      <span class="edu-notices__title-icon" aria-hidden="true"><i class="fa-solid fa-bullhorn"></i></span>
      <div class="edu-notices__title-copy">
        <h2 class="edu-notices__title">Notice Board</h2>
        @if(!empty($featured))
          <span class="edu-notices__badge">Latest updates</span>
        @endif
      </div>
    </div>
    <div class="edu-notices__actions">
      @if($showViewAll && !empty($viewAllUrl))
        <a href="{{ $viewAllUrl }}" class="edu-notices__view-all">{{ $viewAllLabel ?? 'View all notices' }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
      @endif
      @if($layout === 'grid' && $notices->count() > 1)
        <div class="edu-notices__nav">
          <button type="button" class="edu-notices__btn js-edu-notice-prev" aria-label="Previous notices">
            <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
          </button>
          <button type="button" class="edu-notices__btn js-edu-notice-next" aria-label="Next notices">
            <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
          </button>
        </div>
      @endif
    </div>
  </div>
  <div class="edu-notices__viewport">
    <div class="edu-notices__track js-edu-notice-track" id="eduNoticeTrack">
      @foreach($notices as $notice)
        @include('frontend.educator.partials.notice-item', [
            'notice' => $notice,
            'featured' => !empty($featured),
            'compact' => $layout === 'grid',
        ])
      @endforeach
    </div>
  </div>
</div>
