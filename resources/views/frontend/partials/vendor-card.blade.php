@php
    $firstProduct = $vendor->products->first();
    $productImages = is_array($firstProduct?->images) ? array_filter($firstProduct->images) : [];
    $productImage = !empty($productImages) ? asset($productImages[0]) : null;
    $bannerImage = $vendor->bannerSlides->first()?->image_path ? asset($vendor->bannerSlides->first()->image_path) : null;
    $logoImage = $vendor->logo ? asset($vendor->logo) : null;
    $vendorCardImage = $productImage ?? $bannerImage ?? $logoImage ?? 'https://images.unsplash.com/photo-1555529669-e69e7aa0ba9a?w=900&q=85&auto=format&fit=crop';
    $primaryBranch = $vendor->branches->first();
    $storeUrl = route('store.show', $vendor->slug);
    $hasLocation = $hasLocation ?? false;
    $vendorLocation = $primaryBranch?->city ?: ($vendor->city ?: 'Local Area');
    $vendorMeta = $vendorLocation.' • '.$vendor->products_count.' Products';

    if ($hasLocation && $vendor->nearest_distance_km !== null) {
        $vendorMeta .= ' • '.number_format($vendor->nearest_distance_km, 1).' km';
    }
@endphp

<article class="card h-100 shadow-sm border-0 offer-coupon-card vendor-offer-card{{ $vendor->is_premium ? ' is-premium-card' : '' }}">
    <a href="{{ $storeUrl }}" class="offer-coupon-image-wrap vendor-offer-card__media" aria-label="View {{ $vendor->publicDisplayName() }} store">
        <img src="{{ $vendorCardImage }}" alt="{{ $vendor->publicDisplayName() }}" class="offer-coupon-image" loading="lazy" decoding="async">
    </a>
    <div class="card-body d-flex flex-column gap-2">
        @if($vendor->is_premium)
            @include('frontend.premium.partials.badge', ['size' => 'xs'])
        @else
            <span class="badge text-bg-primary w-fit">{{ $vendor->products_count }} Products</span>
        @endif
        <h4 class="h6 mb-1 offer-coupon-title">{{ $vendor->publicDisplayName() }}</h4>
        <p class="small text-muted mb-2 offer-coupon-description">{{ $vendorMeta }}</p>
        <a href="{{ $storeUrl }}" class="vendor-offer-card__btn text-center text-decoration-none mt-auto">View Store</a>
    </div>
</article>
