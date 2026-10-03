@php
    $href = $href ?? '#';
    $image = $image ?? asset('assets/images/vendor-card-placeholder.svg');
    $title = $title ?? '';
    $category = $category ?? '';
    $location = $location ?? '';
    $ratingScore = $ratingScore ?? null;
    $ratingCount = $ratingCount ?? null;
@endphp
<a href="{{ $href }}" class="homepage-vendors-mobile-card">
    <div class="homepage-vendors-mobile-card__media">
        <img src="{{ $image }}" alt="{{ $title }}" loading="lazy" decoding="async">
    </div>
    <div class="homepage-vendors-mobile-card__body">
        <h3 class="homepage-vendors-mobile-card__title">{{ $title }}</h3>
        @if ($category !== '')
            <p class="homepage-vendors-mobile-card__category">{{ $category }}</p>
        @endif
        <div class="homepage-vendors-mobile-card__foot">
            @if ($location !== '')
                <span class="homepage-vendors-mobile-card__loc">
                    <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                    {{ $location }}
                </span>
            @endif
            @if ($ratingScore !== null)
                <span class="homepage-vendors-mobile-card__rating">
                    <i class="fa-solid fa-star" aria-hidden="true"></i>
                    {{ number_format((float) $ratingScore, 1) }}
                    @if ($ratingCount !== null)
                        <span class="homepage-vendors-mobile-card__rating-count">({{ (int) $ratingCount }})</span>
                    @endif
                </span>
            @endif
        </div>
    </div>
</a>
