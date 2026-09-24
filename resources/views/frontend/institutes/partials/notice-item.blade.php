@php
  $compact = !empty($compact);
  $excerptLimit = $compact ? 95 : 180;
  $needsReadMore = $notice->needsReadMore($excerptLimit);
  $showReadMore = $needsReadMore || $compact;
  $imageUrl = $notice->imageUrl();
@endphp
<article
  class="sch-notice{{ $compact ? ' sch-notice--box' : '' }}{{ $imageUrl ? ' sch-notice--has-image' : '' }}"
  data-notice-id="{{ $notice->id }}"
  data-notice-title="{{ e($notice->displayTitle()) }}"
  data-notice-message="{{ e($notice->message) }}"
  data-notice-expires="{{ $notice->expires_at?->format('d M Y') }}"
  @if($imageUrl) data-notice-image="{{ $imageUrl }}" @endif
>
  @if($imageUrl)
    <div class="sch-notice__media">
      <img src="{{ $imageUrl }}" alt="{{ $notice->displayTitle() }}" loading="lazy" decoding="async">
    </div>
  @endif
  <div class="sch-notice__content">
    <div class="sch-notice__head">
      @if(!$imageUrl && (empty($featured) || $compact))
        <span class="sch-notice__icon" aria-hidden="true"><i class="fa-solid fa-bullhorn"></i></span>
      @endif
      <div class="sch-notice__head-copy">
        <h3 class="sch-notice__title">{{ $notice->displayTitle() }}</h3>
        <p class="sch-notice__meta mb-0">Valid until {{ $notice->expires_at?->format('d M Y') }}</p>
      </div>
    </div>
    <p class="sch-notice__text">
      @if($compact)
        {{ $needsReadMore ? $notice->excerpt($excerptLimit) : $notice->message }}
      @elseif(!empty($featured))
        {{ $needsReadMore ? $notice->excerpt($excerptLimit) : $notice->message }}
      @else
        &ldquo;{{ $needsReadMore ? $notice->excerpt($excerptLimit) : $notice->message }}&rdquo;
      @endif
    </p>
    @if($showReadMore)
      <button type="button" class="sch-notice__read-more js-sch-notice-read-more">
        Read more <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
      </button>
    @endif
  </div>
</article>
