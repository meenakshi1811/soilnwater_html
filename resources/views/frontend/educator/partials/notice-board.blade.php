<div class="edu-notices js-edu-notice-carousel {{ !empty($featured) ? 'edu-notices--featured' : '' }} {{ ($notices ?? collect())->isEmpty() ? 'is-empty' : '' }}" id="eduNoticeCarousel">
  <div class="edu-notices__head">
    <div class="edu-notices__title-wrap">
      <h2 class="edu-notices__title"><i class="fa-solid fa-bullhorn" aria-hidden="true"></i> Notice Board</h2>
      @if(!empty($featured))
        <span class="edu-notices__badge">Latest updates</span>
      @endif
    </div>
    <div class="edu-notices__nav">
      <button type="button" class="edu-notices__btn js-edu-notice-prev" aria-label="Previous notice">
        <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
      </button>
      <button type="button" class="edu-notices__btn js-edu-notice-next" aria-label="Next notice">
        <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
      </button>
    </div>
  </div>
  <div class="edu-notices__viewport">
    <div class="edu-notices__track js-edu-notice-track" id="eduNoticeTrack">
      @foreach($notices as $notice)
        @include('frontend.educator.partials.notice-item', ['notice' => $notice])
      @endforeach
    </div>
  </div>
</div>
