<div class="sch-notices js-sch-notice-carousel {{ !empty($featured) ? 'sch-notices--featured' : '' }} {{ ($notices ?? collect())->isEmpty() ? 'is-empty' : '' }}" id="schNoticeCarousel">
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
    <div class="sch-notices__nav">
      <button type="button" class="sch-notices__btn js-sch-notice-prev" aria-label="Previous notice">
        <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
      </button>
      <button type="button" class="sch-notices__btn js-sch-notice-next" aria-label="Next notice">
        <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
      </button>
    </div>
  </div>
  <div class="sch-notices__viewport">
    <div class="sch-notices__track js-sch-notice-track" id="schNoticeTrack">
      @foreach($notices as $notice)
        @include('frontend.institutes.partials.notice-item', ['notice' => $notice, 'featured' => !empty($featured)])
      @endforeach
    </div>
  </div>
</div>
