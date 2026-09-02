@php
    $firstProduct = $vendor->products->first();
    $productImages = is_array($firstProduct?->images) ? array_filter($firstProduct->images) : [];
    $productImage = ! empty($productImages) ? asset($productImages[0]) : null;
    $bannerImage = $vendor->bannerSlides->first()?->image_path ? asset($vendor->bannerSlides->first()->image_path) : null;
    $logoImage = $vendor->logo ? asset($vendor->logo) : null;
    $coverImage = $bannerImage ?? $productImage ?? $logoImage ?? asset('assets/images/vendor-card-placeholder.svg');
    $avatarImage = $logoImage ?? $productImage ?? asset('assets/images/profile-placeholder.svg');
    $primaryBranch = $vendor->branches->first();
    $storeUrl = route('store.show', $vendor->slug);
    $hasLocation = $hasLocation ?? false;
    $vendorLocation = $primaryBranch?->city ?: ($vendor->city ?: 'Local Area');
    $vendorState = $primaryBranch?->state ?: ($vendor->state ?? null);
    $locationLabel = $vendorState ? $vendorLocation.', '.$vendorState : $vendorLocation;
    $categoryName = $firstProduct?->category?->name
        ?: $firstProduct?->subcategory?->name
        ?: 'General';
    $featuredLabel = filled($firstProduct?->name)
        ? $firstProduct->name
        : (\Illuminate\Support\Str::limit(strip_tags((string) $vendor->description), 72) ?: null);
    $serviceTags = $vendor->products
        ->take(3)
        ->pluck('name')
        ->filter()
        ->values()
        ->all();
@endphp
