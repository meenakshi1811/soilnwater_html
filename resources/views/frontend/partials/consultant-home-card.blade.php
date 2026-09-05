@php
    extract(\App\Support\ConsultantListingCard::data($consultant, $hasLocation ?? false));
    $metaLine = $locationLabel.' • '.number_format((int) ($consultant->services_count ?? 0)).' Services';
    if ($hasLocation && $consultant->nearest_distance_km !== null) {
        $metaLine .= ' • '.number_format($consultant->nearest_distance_km, 1).' km';
    }
@endphp

<a href="{{ $profileUrl }}" class="card h-100 shadow-sm border-0 offer-coupon-card homepage-profile-card text-decoration-none text-reset{{ $consultant->is_premium ? ' is-premium-card' : '' }}">
    <div class="offer-coupon-image-wrap">
        <img
            src="{{ $coverImage }}"
            alt="{{ $consultant->publicDisplayName() }}"
            class="offer-coupon-image"
            loading="lazy"
            decoding="async"
            onerror="this.onerror=null;this.src='{{ asset('assets/images/vendor-card-placeholder.svg') }}';"
        >
    </div>
    <div class="card-body d-flex flex-column gap-2">
        @if ($consultant->is_premium)
            @include('frontend.premium.partials.badge', ['size' => 'xs'])
        @else
            <span class="badge text-bg-primary w-fit">{{ number_format((int) ($consultant->services_count ?? 0)) }} Services</span>
        @endif
        <h4 class="h6 mb-1 offer-coupon-title">{{ $consultant->publicDisplayName() }}</h4>
        <p class="small text-muted mb-2 offer-coupon-description">{{ $metaLine }}</p>
    </div>
</a>
