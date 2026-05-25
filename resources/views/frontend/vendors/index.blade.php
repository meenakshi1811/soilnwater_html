@extends('frontend.layouts.app')

@section('content')
<div class="main-wrap">
  <div class="main-col">
    <section class="sec">
      <div class="sec-head">
        <div class="sec-title"><span class="icon"><i class="fa-solid fa-store"></i></span> All Vendors</div>
      </div>
      <p class="small text-muted mb-3">
        @if($hasLocation)
          Showing nearby premium vendors first, then nearby normal vendors.
        @else
          Showing latest premium vendors first, followed by latest normal vendors.
        @endif
      </p>

      <div class="vendor-grid row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-3">
        @forelse($vendors as $vendor)
          @php
            $primaryBranch = $vendor->branches->first();
            $firstProduct = $vendor->products->first();
            $productImages = is_array($firstProduct?->images) ? array_filter($firstProduct->images) : [];
            $productImage = !empty($productImages) ? asset($productImages[0]) : null;
            $bannerImage = $vendor->bannerSlides->first()?->image_path ? asset($vendor->bannerSlides->first()->image_path) : null;
            $logoImage = $vendor->logo ? asset($vendor->logo) : null;
            $vendorCardImage = $productImage ?? $bannerImage ?? $logoImage ?? 'https://images.unsplash.com/photo-1555529669-e69e7aa0ba9a?w=300&q=70';
          @endphp
          <div class="col">
            <div class="vendor-card card h-100">
              <img src="{{ $vendorCardImage }}" alt="{{ $vendor->publicDisplayName() }}">
              <div class="vendor-card-body card-body d-flex flex-column">
                <p>{{ $vendor->publicDisplayName() }} @if($vendor->is_premium)⭐@endif</p>
                <div class="vendor-card-sub">
                  {{ $primaryBranch?->city ?: ($vendor->city ?: 'Local Area') }} • {{ $vendor->products_count }} Products
                  @if($hasLocation && $vendor->nearest_distance_km !== null)
                    • {{ number_format($vendor->nearest_distance_km, 1) }} km
                  @endif
                </div>
                <a href="{{ route('store.show', $vendor->slug) }}" class="vendor-card-btn text-center text-decoration-none">View Store</a>
              </div>
            </div>
          </div>
        @empty
          <div class="col-12"><p class="text-muted">No vendors found.</p></div>
        @endforelse
      </div>

      <div class="mt-4">
        {{ $vendors->links() }}
      </div>
    </section>
  </div>
</div>
@endsection
