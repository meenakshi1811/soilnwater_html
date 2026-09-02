@forelse ($premiumVendors as $vendor)
    @php
        extract(\App\Support\VendorListingCard::data($vendor, $hasLocation ?? false));
    @endphp
    <article class="vendors-premium-card{{ $vendor->is_premium ? ' is-premium' : '' }}">
        <div class="vendors-premium-card__media">
            <img src="{{ $coverImage }}" alt="{{ $vendor->publicDisplayName() }}" class="vendors-premium-card__cover" loading="lazy" decoding="async">
            @if ($vendor->is_premium)
                <span class="vendors-premium-card__badge">
                    <i class="fa-solid fa-crown" aria-hidden="true"></i> Premium
                </span>
            @endif
            <button type="button" class="vendors-premium-card__wishlist" aria-label="Save {{ $vendor->publicDisplayName() }}">
                <i class="fa-regular fa-heart" aria-hidden="true"></i>
            </button>
            <div class="vendors-premium-card__avatar">
                <img
                    src="{{ $avatarImage }}"
                    alt="{{ $vendor->publicDisplayName() }}"
                    loading="lazy"
                    onerror="this.onerror=null;this.src='{{ asset('assets/images/profile-placeholder.svg') }}';"
                >
            </div>
        </div>
        <div class="vendors-premium-card__body">
            <h3 class="vendors-premium-card__name">{{ $vendor->publicDisplayName() }}</h3>
            <div class="vendors-premium-card__meta">
                <span><i class="fa-solid fa-location-dot" aria-hidden="true"></i>{{ $locationLabel }}</span>
            </div>
            <div class="vendors-premium-card__rating-row">
                <span class="vendors-rating">
                    <i class="fa-solid fa-star" aria-hidden="true"></i>{{ number_format($ratingScore, 1) }}
                </span>
                <span class="vendors-rating-count">({{ number_format($ratingCount) }})</span>
                <span class="vendors-verified-badge"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Verified</span>
            </div>
            @if ($featuredLabel)
                <p class="vendors-premium-card__desc">{{ $featuredLabel }}</p>
            @endif
            @if (! empty($serviceTags))
                <div class="vendors-premium-card__tags">
                    @foreach ($serviceTags as $tag)
                        <span>{{ $tag }}</span>
                    @endforeach
                </div>
            @endif
            <a href="{{ $storeUrl }}" class="vendors-premium-card__cta">View Store</a>
        </div>
    </article>
@empty
    <div class="vendors-empty-inline">
        <p>No premium vendors available right now.</p>
    </div>
@endforelse
