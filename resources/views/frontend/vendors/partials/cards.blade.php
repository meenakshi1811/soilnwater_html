@forelse ($vendors as $vendor)
    @php
        extract(\App\Support\VendorListingCard::data($vendor, $hasLocation ?? false));
    @endphp
    <article class="vendors-compact-card{{ $vendor->is_premium ? ' is-premium' : '' }}">
        <a href="{{ $storeUrl }}" class="vendors-compact-card__logo" aria-label="View {{ $vendor->publicDisplayName() }} store">
            <img
                src="{{ $avatarImage }}"
                alt="{{ $vendor->publicDisplayName() }}"
                loading="lazy"
                onerror="this.onerror=null;this.src='{{ asset('assets/images/profile-placeholder.svg') }}';"
            >
        </a>
        <div class="vendors-compact-card__content">
            <h3 class="vendors-compact-card__name">
                <a href="{{ $storeUrl }}">{{ $vendor->publicDisplayName() }}</a>
            </h3>
            <div class="vendors-compact-card__rating">
                <span class="vendors-rating"><i class="fa-solid fa-star" aria-hidden="true"></i>{{ number_format($ratingScore, 1) }}</span>
                <span class="vendors-rating-count">({{ number_format($ratingCount) }})</span>
            </div>
            <div class="vendors-compact-card__meta">
                <span class="vendors-compact-card__category">{{ $categoryName }}</span>
                <span class="vendors-verified-badge vendors-verified-badge--sm"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Verified</span>
            </div>
            <p class="vendors-compact-card__location"><i class="fa-solid fa-location-dot" aria-hidden="true"></i>{{ $locationLabel }}</p>
        </div>
    </article>
@empty
    <div class="vendors-empty-state">
        <div class="vendors-empty-state__icon" aria-hidden="true"><i class="fa-solid fa-store"></i></div>
        <h3>No vendors found</h3>
        <p>Try adjusting your filters or search term.</p>
    </div>
@endforelse
