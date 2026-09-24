@php
  $inModal = ! empty($inModal);
  $imageUrl = $news['image'] ?? null;
@endphp
<article
  class="sch-news-card js-sch-news-open{{ $inModal ? ' sch-news-card--modal' : '' }}"
  data-notice-title="{{ e($news['title']) }}"
  data-notice-message="{{ e($news['message'] ?? '') }}"
  data-notice-expires="{{ e($news['expires'] ?? '') }}"
  data-notice-label="Article"
  @if($imageUrl) data-notice-image="{{ $imageUrl }}" @endif
  role="button"
  tabindex="0"
>
  <div class="sch-news-card__date">
    <strong>{{ $news['day'] }}</strong>
    <span>{{ $news['month'] }}</span>
  </div>
  <div class="sch-news-card__body">
    <h3 class="sch-news-card__title">{{ $news['title'] }}</h3>
    <p class="sch-news-card__excerpt">{{ $news['excerpt'] }}</p>
    <span class="sch-news-card__read-more">
      Read full article <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
    </span>
  </div>
</article>
