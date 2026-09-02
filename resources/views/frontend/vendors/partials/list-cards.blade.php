@forelse ($vendors as $vendor)
    @include('frontend.vendors.partials.card-data', ['vendor' => $vendor, 'hasLocation' => $hasLocation ?? false])
    <article class="vendors-list-card{{ $vendor->is_premium ? ' is-premium' : '' }}">
        <a href="{{ $storeUrl }}" class="vendors-list-card__logo-wrap" aria-label="View {{ $vendor->publicDisplayName() }} store">
            <img
                src="{{ $avatarImage }}"
                alt="{{ $vendor->publicDisplayName() }}"
                loading="lazy"
                onerror="this.onerror=null;this.src='{{ asset('assets/images/profile-placeholder.svg') }}';"
            >
        </a>
        <div class="vendors-list-card__content">
            <div class="vendors-list-card__top">
                <h3 class="vendors-list-card__name">
                    <a href="{{ $storeUrl }}">{{ $vendor->publicDisplayName() }}</a>
                    @if ($vendor->is_premium)
                        <i class="fa-solid fa-crown vendors-list-card__crown" aria-hidden="true" title="Premium vendor"></i>
                    @endif
                </h3>
                <span class="vendors-list-card__products">
                    <i class="fa-solid fa-box-open" aria-hidden="true"></i>{{ $vendor->products_count }} Products
                </span>
            </div>
            <div class="vendors-list-card__meta">
                <span class="vendors-list-card__category">{{ $categoryName }}</span>
                <span class="vendors-verified-badge vendors-verified-badge--sm"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Verified</span>
                <span class="vendors-list-card__location"><i class="fa-solid fa-location-dot" aria-hidden="true"></i>{{ $locationLabel }}</span>
                @if ($hasLocation && $vendor->nearest_distance_km !== null)
                    <span class="vendors-list-card__distance">{{ number_format($vendor->nearest_distance_km, 1) }} km</span>
                @endif
            </div>
        </div>
        <a href="{{ $storeUrl }}" class="vendors-list-card__action" aria-label="View {{ $vendor->publicDisplayName() }} store">
            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
        </a>
    </article>
@empty
    <div class="vendors-empty-state">
        <div class="vendors-empty-state__icon" aria-hidden="true"><i class="fa-solid fa-store"></i></div>
        <h3>No vendors found</h3>
        <p>Try adjusting your filters or search term.</p>
    </div>
@endforelse
