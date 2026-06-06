@php
    $supportingAds = collect($ads ?? [])->filter()->values();
@endphp

@if($supportingAds->isNotEmpty())
<section class="vendor-store-section service-provider-supporting-ads-section" aria-labelledby="serviceProviderSupportingAdsTitle">
    <div class="container px-3 px-lg-4">
        <div class="service-provider-supporting-ads__head">
            <div>
                <p class="vendor-store-eyebrow mb-1">Sponsored</p>
                <h2 class="vendor-store-section-title mb-0" id="serviceProviderSupportingAdsTitle">Featured Ads</h2>
            </div>
            <span class="service-provider-supporting-ads__count">{{ $supportingAds->count() }} {{ \Illuminate\Support\Str::plural('ad', $supportingAds->count()) }}</span>
        </div>

        <div class="service-provider-supporting-ads__grid">
            @foreach($supportingAds as $ad)
                @php
                    $width = (int) ($ad->adSize->width ?? 0);
                    $height = (int) ($ad->adSize->height ?? 0);
                    $ratio = ($width > 0 && $height > 0) ? $width / $height : 1;
                    $formatClass = $ratio > 1.2 ? 'is-horizontal' : ($ratio < 0.85 ? 'is-vertical' : 'is-standard');
                @endphp
                <a href="{{ route('frontend.ads.show', $ad) }}" class="service-provider-supporting-ad {{ $formatClass }}">
                    <img src="{{ asset($ad->final_image) }}" alt="{{ $ad->title }}" loading="lazy" decoding="async">
                    <span class="visually-hidden">View {{ $ad->title }}</span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif
