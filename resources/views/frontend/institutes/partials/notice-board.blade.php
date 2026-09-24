@php
  $notices = $notices ?? collect();
  $layout = $layout ?? 'grid';
  $showViewAll = $showViewAll ?? ! empty($viewAllUrl);
@endphp

<div
  class="sch-notices js-sch-notice-carousel sch-notices--{{ $layout }} {{ !empty($featured) ? 'sch-notices--featured' : '' }} {{ $notices->isEmpty() ? 'is-empty' : '' }}"
  id="schNoticeCarousel"
  data-notice-layout="{{ $layout }}"
>
  <div class="sch-notices__head">
    <div class="sch-notices__title-wrap">
      <span class="sch-notices__title-icon" aria-hidden="true"><i class="fa-solid fa-bullhorn"></i></span>
      <div class="sch-notices__title-copy">
        <h2 class="sch-notices__title">Notice Board</h2>
        @if(!empty($featured))
          <span class="sch-notices__badge">Latest updates</span>
        @endif
      </div>
    </div>
    <div class="sch-notices__actions">
      @if($showViewAll && !empty($viewAllUrl))
        <a href="{{ $viewAllUrl }}" class="sch-notices__view-all">{{ $viewAllLabel ?? 'View all notices' }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
      @endif
      @if($layout === 'grid' && $notices->count() > 1)
        <div class="sch-notices__nav">
          <button type="button" class="sch-notices__btn js-sch-notice-prev" aria-label="Previous notices">
            <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
          </button>
          <button type="button" class="sch-notices__btn js-sch-notice-next" aria-label="Next notices">
            <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
          </button>
        </div>
      @endif
    </div>
  </div>
  <div class="sch-notices__viewport">
    <div class="sch-notices__track js-sch-notice-track" id="schNoticeTrack">
      @foreach($notices as $notice)
        @include('frontend.institutes.partials.notice-item', [
            'notice' => $notice,
            'featured' => !empty($featured),
            'compact' => $layout === 'grid',
        ])
      @endforeach
    </div>
  </div>
</div>
