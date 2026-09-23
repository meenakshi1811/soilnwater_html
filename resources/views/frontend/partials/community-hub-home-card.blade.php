@php
    $hubUrl = route('community.index', ['hub' => $hubKey]);
@endphp

<a
    href="{{ $hubUrl }}"
    class="card h-100 shadow-sm border-0 offer-coupon-card vendor-offer-card community-hub-offer-card community-hub-offer-card--compact text-decoration-none text-reset"
    style="--hub-accent: {{ $hub['accent'] }};"
    aria-label="Browse {{ $hub['label'] }}"
>
    <span class="offer-coupon-image-wrap vendor-offer-card__media community-hub-offer-card__media">
        <span class="community-hub-offer-card__icon" aria-hidden="true">
            <i class="fa-solid {{ $hub['icon'] }}"></i>
        </span>
    </span>
    <span class="card-body d-flex flex-column gap-1">
        <span class="h6 mb-0 offer-coupon-title">{{ $hub['label'] }}</span>
        <span class="small text-muted offer-coupon-description">{{ $hub['tagline'] }}</span>
        <span class="community-hub-offer-card__cta">Explore <i class="fa-solid fa-arrow-right-long" aria-hidden="true"></i></span>
    </span>
</a>
