@php
    $hubUrl = route('community.index', ['hub' => $hubKey]);
@endphp

<article
    class="card h-100 shadow-sm border-0 offer-coupon-card vendor-offer-card community-hub-offer-card"
    style="--hub-accent: {{ $hub['accent'] }};"
>
    <a href="{{ $hubUrl }}" class="offer-coupon-image-wrap vendor-offer-card__media community-hub-offer-card__media" aria-label="Browse {{ $hub['label'] }}">
        <span class="community-hub-offer-card__icon" aria-hidden="true">
            <i class="fa-solid {{ $hub['icon'] }}"></i>
        </span>
    </a>
    <div class="card-body d-flex flex-column gap-2">
        <span class="badge text-bg-primary w-fit">Community</span>
        <h4 class="h6 mb-1 offer-coupon-title">{{ $hub['label'] }}</h4>
        <p class="small text-muted mb-2 offer-coupon-description">{{ $hub['tagline'] }}</p>
        <a href="{{ $hubUrl }}" class="vendor-offer-card__btn text-center text-decoration-none mt-auto">Explore</a>
    </div>
</article>
