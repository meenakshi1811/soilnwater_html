@php
    $href = $href ?? '#';
    $image = $image ?? asset('assets/images/vendor-card-placeholder.svg');
    $title = $title ?? '';
    $category = $category ?? '';
    $location = $location ?? '';
    $ratingScore = $ratingScore ?? null;
    $ratingCount = $ratingCount ?? null;
@endphp
<a href="{{ $href }}" class="homepage-discover-card">
    <div class="homepage-discover-card__media">
        <img src="{{ $image }}" alt="{{ $title }}" loading="lazy" decoding="async">
    </div>
    <div class="homepage-discover-card__body">
        <h3 class="homepage-discover-card__title">{{ $title }}</h3>
        @if ($category !== '')
            <p class="homepage-discover-card__meta">{{ $category }}</p>
        @endif
        @if ($location !== '')
            <p class="homepage-discover-card__loc">
                <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                {{ $location }}
            </p>
        @endif
        @if ($ratingScore !== null)
            <p class="homepage-discover-card__rating">
                <i class="fa-solid fa-star" aria-hidden="true"></i>
                {{ number_format((float) $ratingScore, 1) }}
                @if ($ratingCount !== null)
                    <span class="homepage-discover-card__rating-count">({{ (int) $ratingCount }})</span>
                @endif
            </p>
        @endif
    </div>
</a>
