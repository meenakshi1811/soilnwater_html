@php
    $railAds = collect($ads ?? [])->filter();
    $railId = $railId ?? ('storeAdRail'.uniqid());
@endphp

@if($railAds->isNotEmpty())
<aside class="vendor-store-ads-rail" aria-label="Sponsored listings">
    <div class="vendor-store-ads-rail__head">
        <p class="vendor-store-ads-rail__label mb-0">Sponsored</p>
        <span class="vendor-store-ads-rail__count">{{ $railAds->count() }} ads</span>
    </div>

    <div class="vendor-store-ads-stack">
        @foreach($railAds as $ad)
            @php
                $sizeClass = match ($loop->index % 4) {
                    0 => 'is-tall',
                    1 => 'is-standard',
                    2 => 'is-compact',
                    default => 'is-standard',
                };
            @endphp
            <a href="{{ route('frontend.ads.show', $ad) }}" class="vendor-store-ad-card {{ $sizeClass }}">
                <img src="{{ asset($ad->final_image) }}" alt="{{ $ad->title }}" loading="lazy">
            </a>
        @endforeach
    </div>
</aside>
@endif
