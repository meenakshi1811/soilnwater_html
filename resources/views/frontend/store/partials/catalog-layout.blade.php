@php
    $breadcrumbParts = collect();
    if (!empty($activeSubcategory)) {
        $breadcrumbParts = collect([$activeCategory?->name, $activeSubcategory->name])->filter();
    } elseif (!empty($activeCategory)) {
        $breadcrumbParts = collect([$activeCategory->name]);
    }
@endphp

<div class="vendor-store-catalog">
    <div class="row g-4">
        <div class="col-lg-3">
            @include('frontend.store.partials.category-sidebar', [
                'vendor' => $vendor,
                'vendorCategories' => $vendorCategories,
                'activeCategory' => $activeCategory ?? null,
                'activeSubcategory' => $activeSubcategory ?? null,
            ])
        </div>
        <div class="col-lg="{{ !empty($sidebarAds) && $sidebarAds->isNotEmpty() ? '6' : '9' }}">
            @if($breadcrumbParts->isNotEmpty())
                <nav class="vendor-store-breadcrumb" aria-label="breadcrumb">
                    <a href="{{ route('store.products.index', $vendor->slug) }}">All products</a>
                    @foreach($breadcrumbParts as $part)
                        <span class="mx-1">›</span>
                        <span>{{ $part }}</span>
                    @endforeach
                </nav>
            @endif

            <div class="row g-3 g-md-4" id="store-products-grid">
                @include('frontend.store.partials.product-cards', [
                    'products' => $products,
                    'storeSlug' => $vendor->slug,
                ])
            </div>

            @if(method_exists($products, 'links'))
                <div class="mt-4 d-flex justify-content-center">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
        @if(!empty($sidebarAds) && $sidebarAds->isNotEmpty())
            <div class="col-lg-3">
                @include('frontend.store.partials.ads-rail', [
                    'ads' => $sidebarAds,
                    'railId' => $adsRailId ?? 'storeCatalogAds',
                ])
            </div>
        @endif
    </div>
</div>
