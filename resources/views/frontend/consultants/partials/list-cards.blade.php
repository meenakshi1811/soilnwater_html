@forelse ($consultants as $consultant)
    @php
        extract(\App\Support\ConsultantListingCard::data($consultant, $hasLocation ?? false));
    @endphp
    <article class="vendors-list-card{{ $consultant->is_premium ? ' is-premium' : '' }}">
        <a href="{{ $profileUrl }}" class="vendors-list-card__logo-wrap" aria-label="View {{ $consultant->publicDisplayName() }} profile">
            <img
                src="{{ $avatarImage }}"
                alt="{{ $consultant->publicDisplayName() }}"
                loading="lazy"
                onerror="this.onerror=null;this.src='{{ asset('assets/images/profile-placeholder.svg') }}';"
            >
        </a>
        <div class="vendors-list-card__content">
            <div class="vendors-list-card__top">
                <h3 class="vendors-list-card__name">
                    <a href="{{ $profileUrl }}">{{ $consultant->publicDisplayName() }}</a>
                    @if ($consultant->is_premium)
                        <i class="fa-solid fa-crown vendors-list-card__crown" aria-hidden="true" title="Premium consultant"></i>
                    @endif
                </h3>
                <span class="vendors-list-card__rating">
                    <i class="fa-solid fa-star" aria-hidden="true"></i>{{ number_format($ratingScore, 1) }}
                    <span>({{ number_format($ratingCount) }})</span>
                </span>
            </div>
            <div class="vendors-list-card__meta">
                <span class="vendors-list-card__category">{{ $categoryName }}</span>
                <span class="vendors-verified-badge vendors-verified-badge--sm"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Verified</span>
                <span class="vendors-list-card__location"><i class="fa-solid fa-location-dot" aria-hidden="true"></i>{{ $locationLabel }}</span>
                @if ($hasLocation && $consultant->nearest_distance_km !== null)
                    <span class="vendors-list-card__distance">{{ number_format($consultant->nearest_distance_km, 1) }} km</span>
                @endif
            </div>
        </div>
        <a href="{{ $profileUrl }}" class="vendors-list-card__action" aria-label="View {{ $consultant->publicDisplayName() }} profile">
            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
        </a>
    </article>
@empty
    <div class="vendors-empty-state">
        <div class="vendors-empty-state__icon" aria-hidden="true"><i class="fa-solid fa-user-tie"></i></div>
        <h3>No consultants found</h3>
        <p>Try adjusting your filters or search term.</p>
    </div>
@endforelse
