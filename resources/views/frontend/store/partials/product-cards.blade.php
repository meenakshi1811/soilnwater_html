@php($resolvedStoreSlug = $storeSlug ?? request()->route('slug'))

@forelse($products as $product)
    <div class="col-6 col-md-4 col-lg-3">
        <div class="vendor-product-card">
            @php($image = is_array($product->images) ? ($product->images[0] ?? null) : null)
            <img src="{{ $image ? asset($image) : asset('assets/images/ad-sample.png') }}" alt="{{ $product->name }}">
            <div class="card-body">
                <h6 class="mb-1 small"><a href="{{ route('store.products.show', ['slug' => $resolvedStoreSlug, 'product' => $product->id]) }}" class="text-decoration-none">{{ $product->name }}</a></h6>
                <p class="price mb-0">₹{{ number_format((float) $product->final_price, 2) }}</p>
                <p class="moq mb-2">Stock: {{ number_format((int) $product->stock_quantity) }}</p>
                <a href="{{ route('store.products.show', ['slug' => $resolvedStoreSlug, 'product' => $product->id]) }}" class="btn btn-sm btn-outline-primary w-100">View Details</a>
            </div>
        </div>
    </div>
@empty
    <p class="text-center text-secondary">No approved products available yet.</p>
@endforelse
