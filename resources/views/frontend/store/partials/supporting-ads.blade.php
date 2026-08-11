@php
    $supportingAds = collect($ads ?? [])->filter()->values();
    $placementId = $placementId ?? 'vendorSupportingAds';
@endphp

@if($supportingAds->isNotEmpty())
<aside class="service-provider-inline-ads" aria-labelledby="{{ $placementId }}Title">
    <div class="container px-3 px-lg-4">
        <div class="service-provider-inline-ads__head">
            <span><i class="fa-solid fa-rectangle-ad"></i> Sponsored recommendations</span>
            <small id="{{ $placementId }}Title">Selected for this store</small>
        </div>
        <div class="service-provider-inline-ads__grid service-provider-inline-ads__grid--{{ min($supportingAds->count(), 2) }}">
            @foreach($supportingAds as $ad)
                @include('frontend.store.partials.compact-ad-card', [
                    'ad' => $ad,
                    'variant' => 'strip',
                ])
            @endforeach
        </div>
    </div>
</aside>
@endif
