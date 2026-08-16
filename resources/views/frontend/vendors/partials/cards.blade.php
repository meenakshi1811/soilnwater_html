@forelse ($vendors as $vendor)
    @php
        $firstProduct = $vendor->products->first();
        $productImages = is_array($firstProduct?->images) ? array_filter($firstProduct->images) : [];
        $productImage = ! empty($productImages) ? asset($productImages[0]) : null;
        $bannerImage = $vendor->bannerSlides->first()?->image_path ? asset($vendor->bannerSlides->first()->image_path) : null;
        $logoImage = $vendor->logo ? asset($vendor->logo) : null;
        $coverImage = $bannerImage ?? $productImage ?? $logoImage ?? 'https://images.unsplash.com/photo-1555529669-e69e7aa0ba9a?w=900&q=85&auto=format&fit=crop';
        $avatarImage = $logoImage ?? $productImage ?? asset('assets/images/profile-placeholder.svg');
        $primaryBranch = $vendor->branches->first();
        $storeUrl = route('store.show', $vendor->slug);
        $hasLocation = $hasLocation ?? false;
        $vendorLocation = $primaryBranch?->city ?: ($vendor->city ?: 'Local Area');
        $vendorState = $primaryBranch?->state ?: ($vendor->state ?? null);
        $locationLabel = $vendorState ? $vendorLocation.', '.$vendorState : $vendorLocation;
        $featuredLabel = filled($firstProduct?->name)
            ? $firstProduct->name
            : (\Illuminate\Support\Str::limit(strip_tags((string) $vendor->description), 56) ?: null);
    @endphp
    <div class="col">
        <a
            href="{{ $storeUrl }}"
            class="vendor-store-card text-decoration-none{{ $vendor->is_premium ? ' is-premium-card' : '' }}"
            aria-label="View {{ $vendor->publicDisplayName() }} store"
        >
            <div class="vendor-store-card__hero">
                <img src="{{ $coverImage }}" alt="{{ $vendor->publicDisplayName() }}" class="vendor-store-card__cover" loading="lazy" decoding="async">
                <div class="vendor-store-card__shade" aria-hidden="true"></div>

                @if ($vendor->is_premium)
                    <span class="vendor-store-card__premium-tag">
                        <i class="fa-solid fa-crown me-1" aria-hidden="true"></i>Premium
                    </span>
                @endif

                <span class="vendor-store-card__product-tag">
                    <i class="fa-solid fa-box-open me-1" aria-hidden="true"></i>{{ $vendor->products_count }} Products
                </span>

                <div class="vendor-store-card__avatar">
                    <img
                        src="{{ $avatarImage }}"
                        alt="{{ $vendor->publicDisplayName() }}"
                        loading="lazy"
                        onerror="this.onerror=null;this.src='{{ asset('assets/images/profile-placeholder.svg') }}';"
                    >
                </div>
            </div>

            <div class="vendor-store-card__panel">
                <div class="vendor-store-card__title-row">
                    <h3 class="vendor-store-card__name">{{ $vendor->publicDisplayName() }}</h3>
                    @if ($vendor->is_premium)
                        @include('frontend.premium.partials.badge', ['size' => 'xs'])
                    @endif
                </div>

                <div class="vendor-store-card__meta">
                    <span class="vendor-store-card__chip">
                        <i class="fa-solid fa-location-dot" aria-hidden="true"></i>{{ $locationLabel }}
                    </span>
                    @if ($hasLocation && $vendor->nearest_distance_km !== null)
                        <span class="vendor-store-card__chip vendor-store-card__chip--distance">
                            <i class="fa-solid fa-route" aria-hidden="true"></i>{{ number_format($vendor->nearest_distance_km, 1) }} km
                        </span>
                    @endif
                </div>

                @if ($featuredLabel)
                    <p class="vendor-store-card__featured">
                        <span>Featured</span>
                        {{ $featuredLabel }}
                    </p>
                @endif

                <span class="vendor-store-card__cta">
                    Visit Store
                    <i class="fa-solid fa-arrow-right-long" aria-hidden="true"></i>
                </span>
            </div>
        </a>
    </div>
@empty
    <div class="col-12 vendor-empty-state">
        <div class="vendor-empty-state-card">
            <div class="vendor-empty-state-icon" aria-hidden="true">
                <i class="fa-solid fa-store"></i>
            </div>
            <div class="vendor-empty-state-content">
                <h3 class="vendor-empty-state-title mb-1">No vendors found</h3>
                <p class="vendor-empty-state-text mb-0">
                    We could not find any matching vendors right now. Try a different search term or check back later.
                </p>
            </div>
        </div>
    </div>
@endforelse
