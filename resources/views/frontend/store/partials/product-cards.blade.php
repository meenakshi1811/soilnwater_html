@php($resolvedStoreSlug = $storeSlug ?? request()->route('slug'))
@php($cardStyle = $cardStyle ?? 'default')
@php($isFeaturedStyle = $cardStyle === 'featured')
@php($cardColumnClass = $cardColumnClass ?? ($isFeaturedStyle ? 'col-6 col-md-4 col-lg-3 col-xl-2-4' : 'col-6 col-md-6 col-xl-4'))

@forelse($products as $product)
    <div class="{{ $cardColumnClass }}">
        <article class="vendor-product-card {{ $isFeaturedStyle ? 'vendor-product-card--featured h-100' : 'h-100' }}">
            @php($image = is_array($product->images) ? ($product->images[0] ?? null) : null)
            <a href="{{ route('store.products.show', ['slug' => $resolvedStoreSlug, 'product' => $product->id]) }}" class="vendor-product-card__image-wrap">
                <img src="{{ $image ? asset($image) : asset('assets/images/logo_soilnwater.webp') }}" alt="{{ $product->name }}" loading="lazy" @class(['vendor-product-card__image--placeholder' => ! $image])>
            </a>
            <div class="vendor-product-card__body">
                <h3 class="vendor-product-card__title">
                    <a href="{{ route('store.products.show', ['slug' => $resolvedStoreSlug, 'product' => $product->id]) }}">{{ $product->name }}</a>
                </h3>
                @if($product->brand)
                    <p class="vendor-product-card__brand">{{ $product->brand }}</p>
                @endif
                @php($finalPrice = (float) $product->final_price)
                @if($isFeaturedStyle)
                    <a href="{{ route('store.products.show', ['slug' => $resolvedStoreSlug, 'product' => $product->id]) }}" class="vendor-product-card__btn vendor-product-card__btn--featured">View Details</a>
                @else
                    <p class="vendor-product-card__price">₹{{ number_format($finalPrice, 2) }}</p>
                    <p class="vendor-product-card__meta">Min. order: 1 · Stock: {{ number_format((int) $product->stock_quantity) }}</p>
                    <a href="{{ route('store.products.show', ['slug' => $resolvedStoreSlug, 'product' => $product->id]) }}" class="vendor-product-card__btn">View Details</a>
                @endif
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
