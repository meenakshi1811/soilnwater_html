@php
    $similarVendors = $similarVendors ?? collect();
@endphp

@if($similarVendors->isNotEmpty())
    <section class="vendor-store-section vendor-store-similar-vendors-section">
        <div class="container">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
                <div>
                    <p class="vendor-store-eyebrow mb-1">Similar stores</p>
                    <h2 class="vendor-store-section-title mb-0">Similar Vendors</h2>
                </div>
                <a href="{{ route('frontend.vendors.index') }}" class="btn btn-store-primary">View all vendors</a>
            </div>
            <div class="vendor-grid row row-cols-1 row-cols-sm-2 row-cols-xl-5 g-3 ad-slider auto-ad-slider vendor-store-similar-vendors-slider" data-show-arrows="true" data-pause-on-hover="false" data-show-dots="false" aria-label="Similar vendors slider">
                @foreach($similarVendors as $similarVendor)
                    <div class="col ad-slide">
                        <div class="vendor-card card h-100">
                            @php
                                $firstProduct = $similarVendor->products->first();
                                $productImages = is_array($firstProduct?->images) ? array_filter($firstProduct->images) : [];
                                $productImage = !empty($productImages) ? asset($productImages[0]) : null;
                                $bannerImage = $similarVendor->bannerSlides->first()?->image_path ? asset($similarVendor->bannerSlides->first()->image_path) : null;
                                $logoImage = $similarVendor->logo ? asset($similarVendor->logo) : null;
                                $vendorCardImage = $productImage ?? $bannerImage ?? $logoImage ?? 'https://images.unsplash.com/photo-1555529669-e69e7aa0ba9a?w=300&q=70';
                                $primaryBranch = $similarVendor->branches->first();
                            @endphp
                            <img src="{{ $vendorCardImage }}" alt="{{ $similarVendor->publicDisplayName() }}" loading="lazy">
                            <div class="vendor-card-body card-body d-flex flex-column">
                                <p>{{ $similarVendor->publicDisplayName() }} @if($similarVendor->is_premium)⭐@endif</p>
                                <div class="vendor-card-sub">{{ $primaryBranch?->city ?: ($similarVendor->city ?: 'Local Area') }} • {{ $similarVendor->products_count }} Products</div>
                                <a href="{{ route('store.show', $similarVendor->slug) }}" class="vendor-card-btn text-center text-decoration-none">View Store</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
