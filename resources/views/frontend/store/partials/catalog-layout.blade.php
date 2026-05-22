<div class="vendor-store-catalog">
    <div class="row g-4 align-items-start">
        <div class="col-lg-3">
            @include('frontend.store.partials.category-sidebar', [
                'vendor' => $vendor,
                'vendorCategories' => $vendorCategories,
                'activeCategory' => $activeCategory ?? null,
                'activeSubcategory' => $activeSubcategory ?? null,
            ])
        </div>

        <div class="col-lg-{{ (! $vendor->is_premium && !empty($sidebarAds) && $sidebarAds->isNotEmpty()) ? '6' : '9' }}">
            <div class="vendor-store-results-bar">
                <p class="mb-0">
                    @if(method_exists($products, 'total'))
                        Showing {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }} of {{ $products->total() }} products
                    @else
                        {{ $products->count() }} {{ Str::plural('product', $products->count()) }}
                    @endif
                </p>
            </div>

            <div class="row g-3 g-md-4" id="store-products-grid">
                @include('frontend.store.partials.product-cards', [
                    'products' => $products,
                    'storeSlug' => $vendor->slug,
                ])
            </div>

            @if(method_exists($products, 'links'))
                <div class="mt-4 d-flex justify-content-center vendor-store-pagination">
                    {{ $products->withQueryString()->links() }}
                </div>
            @endif
        </div>

        @if(! $vendor->is_premium && !empty($sidebarAds) && $sidebarAds->isNotEmpty())
            <div class="col-lg-3">
                @include('frontend.store.partials.ads-rail', [
                    'ads' => $sidebarAds,
                    'railId' => $adsRailId ?? 'storeCatalogAds',
                ])
            </div>
        @endif
    </div>
</div>
