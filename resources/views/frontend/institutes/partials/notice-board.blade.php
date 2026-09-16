<div class="sch-notices js-sch-notice-carousel {{ ($notices ?? collect())->isEmpty() ? 'is-empty' : '' }}" id="schNoticeCarousel">
  <div class="sch-notices__head">
    <h2 class="sch-notices__title"><i class="fa-solid fa-bullhorn" aria-hidden="true"></i> Notice Board</h2>
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
        @include('frontend.institutes.partials.notice-item', ['notice' => $notice])
      @endforeach
    </div>
  </div>
</div>
