<div class="vendor-store-catalog">
    <div class="row g-4 align-items-start">
        <div class="col-12">
            @include('frontend.store.partials.category-sidebar', [
                'vendor' => $vendor,
                'vendorCategories' => $vendorCategories,
                'activeCategory' => $activeCategory ?? null,
                'activeSubcategory' => $activeSubcategory ?? null,
            ])
        </div>

        <div class="col-12">
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
                    'cardColumnClass' => 'col-6 col-md-4 col-xl-3',
                ])
            </div>

            @if(method_exists($products, 'links'))
                <div class="mt-4 d-flex justify-content-center vendor-store-pagination">
                    {{ $products->withQueryString()->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
