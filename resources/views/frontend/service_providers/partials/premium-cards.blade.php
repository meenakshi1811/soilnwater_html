@forelse ($premiumServiceProviders as $service_provider)
    @php
        extract(\App\Support\ServiceProviderListingCard::data($service_provider, $hasLocation ?? false));
    @endphp
    <article class="vendors-premium-card{{ $service_provider->is_premium ? ' is-premium' : '' }}">
        <div class="vendors-premium-card__media">
            <img src="{{ $coverImage }}" alt="{{ $service_provider->publicDisplayName() }}" class="vendors-premium-card__cover" loading="lazy" decoding="async">
            @if ($service_provider->is_premium)
                <span class="vendors-premium-card__badge">
                    <i class="fa-solid fa-crown" aria-hidden="true"></i> Premium
                </span>
            @endif
            <div class="vendors-premium-card__avatar">
                <img
                    src="{{ $avatarImage }}"
                    alt="{{ $service_provider->publicDisplayName() }}"
                    loading="lazy"
                    onerror="this.onerror=null;this.src='{{ asset('assets/images/profile-placeholder.svg') }}';"
                >
            </div>
        </div>
        <div class="vendors-premium-card__body">
            <h3 class="vendors-premium-card__name">{{ $service_provider->publicDisplayName() }}</h3>
            <span class="vendors-premium-card__category">{{ $categoryName }}</span>
            <div class="vendors-premium-card__meta">
                <span><i class="fa-solid fa-location-dot" aria-hidden="true"></i>{{ $locationLabel }}</span>
            </div>
            <p class="vendors-premium-card__product-heading">
                <span class="vendors-premium-card__product-label">Offers:</span>
                {{ $featuredLabel ?: $categoryName }}
            </p>
            <div class="vendors-premium-card__rating-row">
                <span class="vendors-rating">
                    <i class="fa-solid fa-star" aria-hidden="true"></i>{{ number_format($ratingScore, 1) }}
                </span>
                <span class="vendors-rating-count">({{ number_format($ratingCount) }})</span>
                <span class="vendors-verified-badge"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Verified</span>
            </div>
            <a href="{{ $profileUrl }}" class="vendors-premium-card__cta">View Profile</a>
        </div>
    </article>
@empty
    <div class="vendors-empty-inline">
        <p>No premium service providers available right now.</p>
    </div>
@endforelse
