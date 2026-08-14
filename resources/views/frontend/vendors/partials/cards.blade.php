@forelse ($vendors as $vendor)
    @php
        $firstProduct = $vendor->products->first();
        $productImages = is_array($firstProduct?->images) ? array_filter($firstProduct->images) : [];
        $productImage = ! empty($productImages) ? asset($productImages[0]) : null;
        $bannerImage = $vendor->bannerSlides->first()?->image_path ? asset($vendor->bannerSlides->first()->image_path) : null;
        $logoImage = $vendor->logo ? asset($vendor->logo) : null;
        $vendorCardImage = $productImage ?? $bannerImage ?? $logoImage ?? 'https://images.unsplash.com/photo-1555529669-e69e7aa0ba9a?w=600&q=70';
        $primaryBranch = $vendor->branches->first();
        $storeUrl = route('store.show', $vendor->slug);
        $hasLocation = $hasLocation ?? false;
        $vendorLocation = $primaryBranch?->city ?: ($vendor->city ?: 'Local Area');
    @endphp
    <div class="col">
        <article class="vendor-market-card card h-100 border-0 shadow-sm{{ $vendor->is_premium ? ' is-premium-card' : '' }}">
            <a href="{{ $storeUrl }}" class="vendor-market-card__media" aria-label="View {{ $vendor->publicDisplayName() }} store">
                <img src="{{ $vendorCardImage }}" alt="{{ $vendor->publicDisplayName() }}" class="vendor-market-card__image" loading="lazy">
                <span class="vendor-market-card__count">{{ $vendor->products_count }} Products</span>
            </a>
            <div class="vendor-market-card__body card-body d-flex flex-column">
                <div class="vendor-market-card__head">
                    <h3 class="vendor-market-card__name">
                        <a href="{{ $storeUrl }}" class="text-decoration-none">{{ $vendor->publicDisplayName() }}</a>
                    </h3>
                    @if ($vendor->is_premium)
                        @include('frontend.premium.partials.badge', ['size' => 'xs'])
                    @endif
                </div>
                <p class="vendor-market-card__meta mb-0">
                    <i class="fa-solid fa-location-dot me-1" aria-hidden="true"></i>
                    {{ $vendorLocation }}
                    @if ($hasLocation && $vendor->nearest_distance_km !== null)
                        <span class="vendor-market-card__distance">• {{ number_format($vendor->nearest_distance_km, 1) }} km away</span>
                    @endif
                </p>
                <a href="{{ $storeUrl }}" class="vendor-market-card__btn mt-auto text-center text-decoration-none">View Store</a>
            </div>
        </article>
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
