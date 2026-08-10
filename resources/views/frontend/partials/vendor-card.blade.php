@php
    $firstProduct = $vendor->products->first();
    $productImages = is_array($firstProduct?->images) ? array_filter($firstProduct->images) : [];
    $productImage = !empty($productImages) ? asset($productImages[0]) : null;
    $bannerImage = $vendor->bannerSlides->first()?->image_path ? asset($vendor->bannerSlides->first()->image_path) : null;
    $logoImage = $vendor->logo ? asset($vendor->logo) : null;
    $vendorCardImage = $productImage ?? $bannerImage ?? $logoImage ?? 'https://images.unsplash.com/photo-1555529669-e69e7aa0ba9a?w=300&q=70';
    $primaryBranch = $vendor->branches->first();
    $storeUrl = route('store.show', $vendor->slug);
    $hasLocation = $hasLocation ?? false;
@endphp

<div class="vendor-card card h-100{{ $vendor->is_premium ? ' is-premium-card' : '' }}">
    <a href="{{ $storeUrl }}" class="vendor-card-media" aria-label="View {{ $vendor->publicDisplayName() }} store">
        <img src="{{ $vendorCardImage }}" alt="{{ $vendor->publicDisplayName() }}" loading="lazy">
    </a>
    <div class="vendor-card-body card-body d-flex flex-column">
        <p class="vendor-card-name">
            {{ $vendor->publicDisplayName() }}
            @if($vendor->is_premium)
                @include('frontend.premium.partials.badge', ['size' => 'xs'])
            @endif
        </p>
        <div class="vendor-card-sub">
            {{ $primaryBranch?->city ?: ($vendor->city ?: 'Local Area') }} • {{ $vendor->products_count }} Products
            @if($hasLocation && $vendor->nearest_distance_km !== null)
                • {{ number_format($vendor->nearest_distance_km, 1) }} km
            @endif
        </div>
        <a href="{{ $storeUrl }}" class="vendor-card-btn text-center text-decoration-none">View Store</a>
    </div>
</div>
