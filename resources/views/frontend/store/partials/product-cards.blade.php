@php($resolvedStoreSlug = $storeSlug ?? request()->route('slug'))

@forelse($products as $product)
    <div class="col-6 col-md-6 col-xl-6">
        <article class="vendor-product-card h-100">
            @php($image = is_array($product->images) ? ($product->images[0] ?? null) : null)
            <a href="{{ route('store.products.show', ['slug' => $resolvedStoreSlug, 'product' => $product->id]) }}" class="vendor-product-card__image-wrap">
                <img src="{{ $image ? asset($image) : asset('assets/images/ad-sample.png') }}" alt="{{ $product->name }}" loading="lazy">
            </a>
            <div class="vendor-product-card__body">
                <h3 class="vendor-product-card__title">
                    <a href="{{ route('store.products.show', ['slug' => $resolvedStoreSlug, 'product' => $product->id]) }}">{{ $product->name }}</a>
                </h3>
                @if($product->brand)
                    <p class="vendor-product-card__brand">{{ $product->brand }}</p>
                @endif
                <p class="vendor-product-card__price">₹{{ number_format((float) $product->final_price, 2) }}</p>
                <p class="vendor-product-card__meta">Min. order: 1 · Stock: {{ number_format((int) $product->stock_quantity) }}</p>
                <a href="{{ route('store.products.show', ['slug' => $resolvedStoreSlug, 'product' => $product->id]) }}" class="vendor-product-card__btn">View Details</a>
            </div>
        </article>
    </div>
@empty
    <div class="col-12">
        <div class="vendor-store-empty-products">
            <p class="mb-0">No products found in this category yet.</p>
        </div>
    </div>
@endforelse
