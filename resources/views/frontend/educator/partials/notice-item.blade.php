@php
  $compact = !empty($compact);
  $excerptLimit = $compact ? 95 : 180;
  $needsReadMore = $notice->needsReadMore($excerptLimit);
  $showReadMore = $needsReadMore || $compact;
  $imageUrl = $notice->imageUrl();
@endphp
<article
  class="edu-notice{{ $compact ? ' edu-notice--box' : '' }}{{ $imageUrl ? ' edu-notice--has-image' : '' }}"
  data-notice-id="{{ $notice->id }}"
  data-notice-title="{{ e($notice->displayTitle()) }}"
  data-notice-message="{{ e($notice->message) }}"
  data-notice-expires="{{ $notice->expires_at?->format('d M Y') }}"
  @if($imageUrl) data-notice-image="{{ $imageUrl }}" @endif
>
  @if($imageUrl)
    <div class="edu-notice__media">
      <img src="{{ $imageUrl }}" alt="{{ $notice->displayTitle() }}" loading="lazy" decoding="async">
    </div>
  @endif
  <div class="edu-notice__content">
    <div class="edu-notice__head">
      @if(!$imageUrl && (empty($featured) || $compact))
        <span class="edu-notice__icon" aria-hidden="true"><i class="fa-solid fa-bullhorn"></i></span>
      @endif
      <div class="edu-notice__head-copy">
        <h3 class="edu-notice__title">{{ $notice->displayTitle() }}</h3>
        <p class="edu-notice__meta mb-0">Valid until {{ $notice->expires_at?->format('d M Y') }}</p>
      </div>
    </div>
    <p class="edu-notice__text">
      @if($compact)
        {{ $needsReadMore ? $notice->excerpt($excerptLimit) : $notice->message }}
      @elseif(!empty($featured))
        {{ $needsReadMore ? $notice->excerpt($excerptLimit) : $notice->message }}
      @else
        &ldquo;{{ $needsReadMore ? $notice->excerpt($excerptLimit) : $notice->message }}&rdquo;
      @endif
    </p>
    @if($showReadMore)
      <button type="button" class="edu-notice__read-more js-edu-notice-read-more">
        Read more <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
      </button>
    @endif
  </div>
</article>
