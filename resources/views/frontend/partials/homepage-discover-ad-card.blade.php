@php
    $ad = $ad ?? null;
    $meta = $meta ?? 'Sponsored';
@endphp
@if ($ad)
    <article
        class="homepage-discover-card homepage-discover-card--ad"
        role="link"
        tabindex="0"
        data-ad-id="{{ $ad->id }}"
        data-ad-url="{{ $ad->shareUrl() }}"
        data-ad-description="{{ $ad->short_description ?: 'Special marketplace ad available now.' }}"
    >
        <div class="homepage-discover-card__media">
            <img
                src="{{ asset($ad->final_image) }}"
                alt="{{ $ad->title }}"
                loading="lazy"
                decoding="async"
                data-ad-id="{{ $ad->id }}"
                data-ad-url="{{ $ad->shareUrl() }}"
                data-ad-description="{{ $ad->short_description ?: 'Special marketplace ad available now.' }}"
            >
            <span class="homepage-discover-card__ad-badge">Ad</span>
        </div>
        <div class="homepage-discover-card__body">
            <h3 class="homepage-discover-card__title">{{ $ad->title }}</h3>
            <p class="homepage-discover-card__meta">{{ $meta }}</p>
        </div>
    </article>
@endif
