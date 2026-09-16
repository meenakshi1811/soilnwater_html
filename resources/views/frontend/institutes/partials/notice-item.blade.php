@php
  $excerptLimit = 180;
  $needsReadMore = $notice->needsReadMore($excerptLimit);
@endphp
<article
  class="sch-notice"
  data-notice-id="{{ $notice->id }}"
  data-notice-title="{{ e($notice->displayTitle()) }}"
  data-notice-message="{{ e($notice->message) }}"
  data-notice-expires="{{ $notice->expires_at?->format('d M Y') }}"
>
  <div class="sch-notice__head">
    <span class="sch-notice__icon" aria-hidden="true"><i class="fa-solid fa-bullhorn"></i></span>
    <div>
      <h3 class="sch-notice__title">{{ $notice->displayTitle() }}</h3>
      <p class="sch-notice__meta mb-0">Valid until {{ $notice->expires_at?->format('d M Y') }}</p>
    </div>
  </div>
  <p class="sch-notice__text">&ldquo;{{ $needsReadMore ? $notice->excerpt($excerptLimit) : $notice->message }}&rdquo;</p>
  @if($needsReadMore)
    <button type="button" class="sch-notice__read-more js-sch-notice-read-more">
      Read more <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
    </button>
  @endif
</article>
