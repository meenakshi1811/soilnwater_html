@extends('frontend.layouts.app')

@section('title', $product->name.' - '.$vendor->publicDisplayName())

@section('content')
@php
    $primaryImage = is_array($product->images) ? ($product->images[0] ?? null) : null;
    $galleryImages = collect(is_array($product->images) ? $product->images : [])->filter()->values();
    $hasLocation = $product->latitude !== null && $product->longitude !== null;
@endphp

<div class="container py-4 py-lg-5">
    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
        <a href="{{ route('store.show', $vendor->slug) }}#products" class="btn btn-sm btn-outline-secondary">← Back to products</a>
        <span class="badge text-bg-light border">{{ $vendor->publicDisplayName() }}</span>
    </div>

    @if($adsTop->isNotEmpty())
        <div class="row g-3 mb-4">
            @foreach($adsTop as $ad)
                <div class="col-12 col-md-6">
                    <a href="{{ route('frontend.ads.show', $ad) }}" class="d-block rounded-3 overflow-hidden shadow-sm border bg-white">
                        <img src="{{ asset($ad->final_image) }}" class="img-fluid w-100" alt="{{ $ad->title }}" loading="lazy">
                    </a>
                </div>
            @endforeach
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-3 p-lg-4">
                    <img src="{{ $primaryImage ? asset($primaryImage) : asset('assets/images/ad-sample.png') }}" class="img-fluid rounded-3 mb-3 border" alt="{{ $product->name }}">
                    <h1 class="h3 mb-1">{{ $product->name }}</h1>
                    <p class="text-muted mb-3">{{ $product->brand ?: 'Brand not specified' }}</p>

                    <div class="row g-2 mb-3">
                        <div class="col-sm-6"><div class="p-2 border rounded-3 bg-light"><strong>Price:</strong> ₹{{ number_format((float) $product->final_price, 2) }}</div></div>
                        <div class="col-sm-6"><div class="p-2 border rounded-3 bg-light"><strong>Stock:</strong> {{ number_format((int) $product->stock_quantity) }}</div></div>
                    </div>

                    <p class="mb-0">{!! nl2br(e($product->description ?: 'No description available.')) !!}</p>

                    <button class="btn btn-primary mt-4" data-bs-toggle="modal" data-bs-target="#enquiryModal">Send Enquiry</button>
                </div>
            </div>

            @if($adsInline->isNotEmpty())
                <div class="row g-3 mb-4">
                    @foreach($adsInline as $ad)
                        <div class="col-12 col-md-6">
                            <a href="{{ route('frontend.ads.show', $ad) }}" class="d-block rounded-3 overflow-hidden shadow-sm border bg-white">
                                <img src="{{ asset($ad->final_image) }}" class="img-fluid w-100" alt="{{ $ad->title }}" loading="lazy">
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($galleryImages->count() > 1)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pb-0"><h2 class="h5 mb-0">Product Gallery</h2></div>
                    <div class="card-body">
                        <div class="row g-2">
                            @foreach($galleryImages->slice(1) as $image)
                                <div class="col-6 col-md-4"><img src="{{ asset($image) }}" class="img-fluid rounded-3 border" alt="{{ $product->name }} image" loading="lazy"></div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h2 class="h5 mb-0">Product Location</h2></div>
                <div class="card-body">
                    @if($hasLocation)
                        <div id="productLocationMap" style="height: 360px;" class="rounded-3 border"></div>
                        <p class="small text-muted mt-2 mb-0">Pinned at: {{ number_format((float)$product->latitude, 6) }}, {{ number_format((float)$product->longitude, 6) }}</p>
                    @else
                        <p class="text-muted mb-0">Location is not available for this product.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            @if($adsSidebar->isNotEmpty())
                <div class="position-sticky" style="top:90px;">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white"><small class="fw-semibold text-uppercase">Sponsored</small></div>
                        <div class="card-body p-2">
                            @foreach($adsSidebar as $ad)
                                <a href="{{ route('frontend.ads.show', $ad) }}" class="d-block rounded-3 overflow-hidden border mb-2">
                                    <img src="{{ asset($ad->final_image) }}" class="img-fluid w-100" alt="{{ $ad->title }}" loading="lazy">
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@includeWhen(true, 'frontend.store.partials.enquiry-modal')
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
@endpush

@push('scripts')
@auth
<script>
document.getElementById('enquiryForm')?.addEventListener('submit', async function(e){
 e.preventDefault();
 const fd = new FormData(this);
 const res = await fetch("{{ route('store.products.enquiry', [$vendor->slug, $product->id]) }}", {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}, body:fd});
 const data = await res.json();
 alert(data.message || 'Done');
 if(res.ok){ bootstrap.Modal.getInstance(document.getElementById('enquiryModal')).hide(); }
});
</script>
@endauth

@if($hasLocation)
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const lat = {{ (float) $product->latitude }};
    const lng = {{ (float) $product->longitude }};
    const map = L.map('productLocationMap').setView([lat, lng], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    L.marker([lat, lng]).addTo(map).bindPopup(@json($product->name)).openPopup();
});
</script>
@endif
@endpush
