@php
  $excerptLimit = 180;
  $needsReadMore = $notice->needsReadMore($excerptLimit);
@endphp
<article
  class="edu-notice"
  data-notice-id="{{ $notice->id }}"
  data-notice-title="{{ e($notice->displayTitle()) }}"
  data-notice-message="{{ e($notice->message) }}"
  data-notice-expires="{{ $notice->expires_at?->format('d M Y') }}"
>
  <div class="edu-notice__head">
    <span class="edu-notice__icon" aria-hidden="true"><i class="fa-solid fa-bullhorn"></i></span>
    <div>
      <h3 class="edu-notice__title">{{ $notice->displayTitle() }}</h3>
      <p class="edu-notice__meta mb-0">Valid until {{ $notice->expires_at?->format('d M Y') }}</p>
    </div>
  </div>
  <p class="edu-notice__text">&ldquo;{{ $needsReadMore ? $notice->excerpt($excerptLimit) : $notice->message }}&rdquo;</p>
  @if($needsReadMore)
    <button type="button" class="edu-notice__read-more js-edu-notice-read-more">
      Read more <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
    </button>
  @endif
</article>
