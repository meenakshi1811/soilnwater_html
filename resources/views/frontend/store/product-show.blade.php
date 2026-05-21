@extends('frontend.layouts.app')
@section('title', $product->name.' - '.$vendor->publicDisplayName())
@section('content')
@php
$primaryImage = is_array($product->images) ? ($product->images[0] ?? null) : null;
$galleryImages = collect(is_array($product->images) ? $product->images : [])->filter()->values();
$hasLocation = $product->latitude !== null && $product->longitude !== null;
$hasAds = ($topGroups->flatten()->count() + $sideGroups->flatten()->count() + $bottomGroups->flatten()->count()) > 0;
$adFrameStyle = function ($ad) {
    $w = (int) ($ad->adSize->width ?? 0);
    $h = (int) ($ad->adSize->height ?? 0);

    if ($w > 0 && $h > 0) {
        return 'width:min(100%, '.$w.'px);aspect-ratio:'.$w.' / '.$h.';';
    }

    return 'width:100%;';
};
@endphp
<div class="container py-4 py-lg-5">
  <div class="d-flex justify-content-between align-items-center mb-4"><a href="{{ route('store.show', $vendor->slug) }}#products" class="btn btn-sm btn-outline-secondary">← Back to products</a><span class="badge text-bg-light border">{{ $vendor->publicDisplayName() }}</span></div>

  @if($hasAds)
  <section class="ad-zone mb-4">
    <div class="ad-zone__title">Sponsored</div>
    @foreach($topGroups as $gi => $group)
      @php($id = 'topAdCarousel'.$gi)
      <div class="mb-3">
        @if($group->count() > 1)
        <div id="{{ $id }}" class="carousel slide" data-bs-ride="carousel">
          <div class="carousel-inner rounded-3 overflow-hidden border shadow-sm bg-white">
            @foreach($group as $i => $ad)
            <div class="carousel-item {{ $i===0 ? 'active' : '' }}"><a href="{{ route('frontend.ads.show', $ad) }}" class="ad-link"><img src="{{ asset($ad->final_image) }}" class="d-block w-100" style="{{ $adFrameStyle($ad) }}" alt="{{ $ad->title }}"></a></div>
            @endforeach
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#{{ $id }}" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
          <button class="carousel-control-next" type="button" data-bs-target="#{{ $id }}" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
        </div>
        @else
        @php($ad = $group->first())<a href="{{ route('frontend.ads.show', $ad) }}" class="ad-link d-block rounded-3 overflow-hidden border shadow-sm"><img src="{{ asset($ad->final_image) }}" class="img-fluid w-100" style="{{ $adFrameStyle($ad) }}" alt="{{ $ad->title }}"></a>
        @endif
      </div>
    @endforeach
  </section>
  @endif

  <div class="row g-4">
    <aside class="col-lg-2">
      @foreach($sideGroups->take(1) as $gi => $group)
        @php($id = 'leftAdCarousel'.$gi)
        @if($group->count() > 1)
        <div id="{{ $id }}" class="carousel slide mb-3" data-bs-ride="carousel"><div class="carousel-inner rounded-3 overflow-hidden border shadow-sm bg-white">@foreach($group as $i => $ad)<div class="carousel-item {{ $i===0?'active':'' }}"><a href="{{ route('frontend.ads.show', $ad) }}" class="ad-link"><img src="{{ asset($ad->final_image) }}" class="d-block w-100" style="{{ $adFrameStyle($ad) }}" alt="{{ $ad->title }}"></a></div>@endforeach</div></div>
        @else
        @php($ad = $group->first())<a href="{{ route('frontend.ads.show', $ad) }}" class="ad-link d-block rounded-3 overflow-hidden border shadow-sm mb-3"><img src="{{ asset($ad->final_image) }}" class="img-fluid w-100" style="{{ $adFrameStyle($ad) }}" alt="{{ $ad->title }}"></a>
        @endif
      @endforeach
    </aside>
    <main class="col-lg-8">
      <div class="card border-0 shadow-sm mb-4"><div class="card-body p-3 p-lg-4"><img src="{{ $primaryImage ? asset($primaryImage) : asset('assets/images/ad-sample.png') }}" class="img-fluid rounded-3 mb-3 border" alt="{{ $product->name }}"><h1 class="h3 mb-1">{{ $product->name }}</h1><p class="text-muted mb-3">{{ $product->brand ?: 'Brand not specified' }}</p><div class="row g-2 mb-3"><div class="col-sm-6"><div class="p-2 border rounded-3 bg-light"><strong>Price:</strong> ₹{{ number_format((float) $product->final_price, 2) }}</div></div><div class="col-sm-6"><div class="p-2 border rounded-3 bg-light"><strong>Stock:</strong> {{ number_format((int) $product->stock_quantity) }}</div></div></div><p class="mb-0">{!! nl2br(e($product->description ?: 'No description available.')) !!}</p><button class="btn btn-primary mt-4" data-bs-toggle="modal" data-bs-target="#enquiryModal">Send Enquiry</button></div></div>
      @if($galleryImages->count() > 1)<div class="card border-0 shadow-sm mb-4"><div class="card-header bg-white border-0 pb-0"><h2 class="h5 mb-0">Product Gallery</h2></div><div class="card-body"><div class="row g-2">@foreach($galleryImages->slice(1) as $image)<div class="col-6 col-md-4"><img src="{{ asset($image) }}" class="img-fluid rounded-3 border" alt="{{ $product->name }} image" loading="lazy"></div>@endforeach</div></div></div>@endif
      <div class="card border-0 shadow-sm"><div class="card-header bg-white"><h2 class="h5 mb-0">Product Location</h2></div><div class="card-body">@if($hasLocation)<div id="productLocationMap" style="height:360px;" class="rounded-3 border"></div>@else <p class="text-muted mb-0">Location is not available for this product.</p>@endif</div></div>
    </main>
    <aside class="col-lg-2">
      @foreach($sideGroups->slice(1,1) as $gi => $group)
        @php($id = 'rightAdCarousel'.$gi)
        @if($group->count() > 1)
        <div id="{{ $id }}" class="carousel slide mb-3" data-bs-ride="carousel"><div class="carousel-inner rounded-3 overflow-hidden border shadow-sm bg-white">@foreach($group as $i => $ad)<div class="carousel-item {{ $i===0?'active':'' }}"><a href="{{ route('frontend.ads.show', $ad) }}" class="ad-link"><img src="{{ asset($ad->final_image) }}" class="d-block w-100" style="{{ $adFrameStyle($ad) }}" alt="{{ $ad->title }}"></a></div>@endforeach</div></div>
        @else
        @php($ad = $group->first())<a href="{{ route('frontend.ads.show', $ad) }}" class="ad-link d-block rounded-3 overflow-hidden border shadow-sm mb-3"><img src="{{ asset($ad->final_image) }}" class="img-fluid w-100" style="{{ $adFrameStyle($ad) }}" alt="{{ $ad->title }}"></a>
        @endif
      @endforeach
    </aside>
  </div>

  @foreach($bottomGroups as $gi => $group)
    @php($id = 'bottomAdCarousel'.$gi)
    <div class="mt-4">
      @if($group->count() > 1)
      <div id="{{ $id }}" class="carousel slide" data-bs-ride="carousel"><div class="carousel-inner rounded-3 overflow-hidden border shadow-sm bg-white">@foreach($group as $i => $ad)<div class="carousel-item {{ $i===0?'active':'' }}"><a href="{{ route('frontend.ads.show', $ad) }}" class="ad-link"><img src="{{ asset($ad->final_image) }}" class="d-block w-100" style="{{ $adFrameStyle($ad) }}" alt="{{ $ad->title }}"></a></div>@endforeach</div><button class="carousel-control-prev" type="button" data-bs-target="#{{ $id }}" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button><button class="carousel-control-next" type="button" data-bs-target="#{{ $id }}" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button></div>
      @else
      @php($ad = $group->first())<a href="{{ route('frontend.ads.show', $ad) }}" class="ad-link d-block rounded-3 overflow-hidden border shadow-sm"><img src="{{ asset($ad->final_image) }}" class="img-fluid w-100" style="{{ $adFrameStyle($ad) }}" alt="{{ $ad->title }}"></a>
      @endif
    </div>
  @endforeach
</div>
@includeWhen(true, 'frontend.store.partials.enquiry-modal')
@endsection
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<style>.ad-zone__title{font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:.6rem}.ad-link{display:flex;justify-content:center;align-items:center;background:#fff}.ad-link img{display:block;object-fit:contain;transition:transform .25s ease}.ad-link:hover img{transform:scale(1.01)}</style>
@endpush
@push('scripts')
@auth
<script>document.getElementById('enquiryForm')?.addEventListener('submit', async function(e){e.preventDefault();const fd = new FormData(this);const res = await fetch("{{ route('store.products.enquiry', [$vendor->slug, $product->id]) }}", {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}, body:fd});const data = await res.json();alert(data.message || 'Done');if(res.ok){ bootstrap.Modal.getInstance(document.getElementById('enquiryModal')).hide(); }});</script>
@endauth
@if($hasLocation)
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>document.addEventListener('DOMContentLoaded', function () {const lat = {{ (float) $product->latitude }};const lng = {{ (float) $product->longitude }};const map = L.map('productLocationMap').setView([lat, lng], 14);L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 19, attribution: '&copy; OpenStreetMap contributors'}).addTo(map);L.marker([lat, lng]).addTo(map).bindPopup(@json($product->name)).openPopup();});</script>
@endif
@endpush
