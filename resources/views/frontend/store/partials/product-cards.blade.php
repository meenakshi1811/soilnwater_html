@php($resolvedStoreSlug = $storeSlug ?? request()->route('slug'))

@forelse($products as $product)
    <div class="col-6 col-md-4 col-lg-3">
        <div class="vendor-product-card shadow-sm border-0 h-100" style="background: linear-gradient(180deg,#ffffff 0%,#f8fbff 100%); border-radius:16px; overflow:hidden;">
            @php($image = is_array($product->images) ? ($product->images[0] ?? null) : null)
            <img src="{{ $image ? asset($image) : asset('assets/images/ad-sample.png') }}" alt="{{ $product->name }}" style="height:180px; width:100%; object-fit:cover;">
            <div class="card-body p-3">
                <h6 class="mb-2"><a href="{{ route('store.products.show', ['slug' => $resolvedStoreSlug, 'product' => $product->id]) }}" class="text-decoration-none fw-semibold text-dark">{{ $product->name }}</a></h6>
                <p class="price mb-1 fw-bold" style="color:#0d6efd;">₹{{ number_format((float) $product->final_price, 2) }}</p>
                <p class="moq mb-3"><span class="badge rounded-pill text-bg-success">In Stock: {{ number_format((int) $product->stock_quantity) }}</span></p>
                <a href="{{ route('store.products.show', ['slug' => $resolvedStoreSlug, 'product' => $product->id]) }}" class="btn btn-sm w-100 text-white border-0" style="background:linear-gradient(90deg,#0d6efd,#20c997); font-weight:600;">View Details</a>
            </div>
        </div>
    </div>
@empty
    <p class="text-center text-secondary">No approved products available yet.</p>
@endforelse
