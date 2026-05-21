@php
    $railAds = collect($ads ?? [])->filter();
    $railId = $railId ?? ('storeAdRail'.uniqid());
@endphp

@if($railAds->isNotEmpty())
<aside class="vendor-store-ads-rail" aria-label="Sponsored listings">
    <p class="vendor-store-ads-rail__label">Sponsored</p>
    @if($railAds->count() > 1)
        <div id="{{ $railId }}" class="carousel slide vendor-store-ads-carousel" data-bs-ride="carousel" data-bs-interval="5000">
            <div class="carousel-inner">
                @foreach($railAds as $i => $ad)
                    <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                        <a href="{{ route('frontend.ads.show', $ad) }}" class="vendor-store-ad-card">
                            <img src="{{ asset($ad->final_image) }}" alt="{{ $ad->title }}" loading="lazy">
                        </a>
                    </div>
                @endforeach
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#{{ $railId }}" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#{{ $railId }}" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
            </button>
        </div>
    @else
        @php($ad = $railAds->first())
        <a href="{{ route('frontend.ads.show', $ad) }}" class="vendor-store-ad-card">
            <img src="{{ asset($ad->final_image) }}" alt="{{ $ad->title }}" loading="lazy">
        </a>
    @endif
</aside>
@endif
