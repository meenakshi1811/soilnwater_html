@php
  $socialLinks = $educator->socialLinks();
  $compact = $compact ?? false;
@endphp

@if($socialLinks !== [])
  <div class="edu-contact-social {{ $compact ? 'edu-contact-social--compact' : '' }}">
    @unless($compact)
      <h3 class="edu-contact-social__title">
        <i class="fa-solid fa-share-nodes" aria-hidden="true"></i>
        Connect on social media
      </h3>
    @endunless
    <div class="edu-social-row" role="list">
      @foreach($socialLinks as $link)
        <a
          href="{{ $link['url'] }}"
          target="_blank"
          rel="noopener noreferrer"
          class="edu-social-btn edu-social-btn--{{ $link['brand'] }}"
          aria-label="{{ $link['label'] }}"
          title="{{ $link['label'] }}"
          role="listitem"
        >
          <i class="fa-brands {{ $link['icon'] }}" aria-hidden="true"></i>
        </a>
      @endforeach
    </div>
  </div>
@endif
