@forelse ($vendors as $vendor)
    @include('frontend.vendors.partials.card-data', ['vendor' => $vendor, 'hasLocation' => $hasLocation ?? false])
    <article class="vendors-grid-card{{ $vendor->is_premium ? ' is-premium' : '' }}">
        <a href="{{ $storeUrl }}" class="vendors-grid-card__media" aria-label="View {{ $vendor->publicDisplayName() }} store">
            <img src="{{ $coverImage }}" alt="{{ $vendor->publicDisplayName() }}" loading="lazy" decoding="async">
            @if ($vendor->is_premium)
                <span class="vendors-grid-card__premium"><i class="fa-solid fa-crown" aria-hidden="true"></i></span>
            @endif
        </a>
        <div class="vendors-grid-card__body">
            <div class="vendors-grid-card__logo">
                <img
                    src="{{ $avatarImage }}"
                    alt=""
                    loading="lazy"
                    onerror="this.onerror=null;this.src='{{ asset('assets/images/profile-placeholder.svg') }}';"
                >
            </div>
            <h3 class="vendors-grid-card__name">
                <a href="{{ $storeUrl }}">{{ $vendor->publicDisplayName() }}</a>
            </h3>
            <div class="vendors-grid-card__meta">
                <span class="vendors-grid-card__products">
                    <i class="fa-solid fa-box-open" aria-hidden="true"></i>{{ $vendor->products_count }} Products
                </span>
                <span class="vendors-verified-badge vendors-verified-badge--sm"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Verified</span>
            </div>
            <span class="vendors-grid-card__category">{{ $categoryName }}</span>
            <p class="vendors-grid-card__location"><i class="fa-solid fa-location-dot" aria-hidden="true"></i>{{ $locationLabel }}</p>
            <a href="{{ $storeUrl }}" class="vendors-grid-card__cta">View Store</a>
        </div>
    </article>
@empty
    <div class="vendors-empty-state">
        <div class="vendors-empty-state__icon" aria-hidden="true"><i class="fa-solid fa-store"></i></div>
        <h3>No vendors found</h3>
        <p>Try adjusting your filters or search term.</p>
    </div>
@endforelse
