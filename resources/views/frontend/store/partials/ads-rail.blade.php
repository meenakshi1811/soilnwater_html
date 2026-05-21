@php
    $railAds = collect($ads ?? [])->filter();
    $railId = $railId ?? ('storeAdRail'.uniqid());

    $normalizedAds = $railAds->map(function ($ad) {
        $w = (int) ($ad->adSize->width ?? 0);
        $h = (int) ($ad->adSize->height ?? 0);
        $ratio = ($w > 0 && $h > 0) ? ($w / $h) : 1;

        return compact('ad', 'w', 'h', 'ratio');
    });

    $sliderAds = $normalizedAds->filter(fn ($item) => $item['ratio'] >= 1)->take(3)->values();
    $stackAds = $normalizedAds->reject(function ($item) use ($sliderAds) {
        return $sliderAds->pluck('ad.id')->contains($item['ad']->id);
    })->values();
@endphp

@if($railAds->isNotEmpty())
<aside class="vendor-store-ads-rail" aria-label="Sponsored listings">
    <div class="vendor-store-ads-rail__head">
        <p class="vendor-store-ads-rail__label mb-0">Sponsored</p>
        <span class="vendor-store-ads-rail__count">{{ $railAds->count() }} ads</span>
    </div>

    @if($sliderAds->isNotEmpty())
        <div id="{{ $railId }}" class="carousel slide vendor-store-ads-carousel mb-2" data-bs-ride="carousel" data-bs-interval="4500">
            <div class="carousel-inner">
                @foreach($sliderAds as $index => $item)
                    @php($ad = $item['ad'])
                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                        <a href="{{ route('frontend.ads.show', $ad) }}" class="vendor-store-ad-card is-slider" style="--ad-ratio: {{ max(0.6, min(2, $item['ratio'])) }};">
                            <img src="{{ asset($ad->final_image) }}" alt="{{ $ad->title }}" loading="lazy">
                        </a>
                    </div>
                @endforeach
            </div>
            @if($sliderAds->count() > 1)
                <button class="carousel-control-prev" type="button" data-bs-target="#{{ $railId }}" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#{{ $railId }}" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                </button>
            @endif
        </div>
    @endif

    @if($stackAds->isNotEmpty())
        <div class="vendor-store-ads-stack">
            @foreach($stackAds as $item)
                @php
                    $sizeClass = $item['ratio'] < 0.9 ? 'is-tall' : ($item['ratio'] > 1.35 ? 'is-compact' : 'is-standard');
                @endphp
                <a href="{{ route('frontend.ads.show', $item['ad']) }}" class="vendor-store-ad-card {{ $sizeClass }}" style="--ad-ratio: {{ max(0.55, min(1.8, $item['ratio'])) }};">
                    <img src="{{ asset($item['ad']->final_image) }}" alt="{{ $item['ad']->title }}" loading="lazy">
                </a>
            @endforeach
        </div>
    @endif
</aside>
@endif
