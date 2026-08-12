@php
    $supportingAds = collect($ads ?? [])->filter()->values()->take(6);
    $placementId = $placementId ?? 'serviceProviderSupportingAds';
    $gridColumns = min(max($supportingAds->count(), 1), 3);
@endphp

@if($supportingAds->isNotEmpty())
<aside class="service-provider-inline-ads" aria-labelledby="{{ $placementId }}Title">
    <div class="container px-3 px-lg-4">
        <div class="service-provider-inline-ads__head">
            <span><i class="fa-solid fa-rectangle-ad"></i> Sponsored recommendations</span>
            <small id="{{ $placementId }}Title">Selected for this page</small>
        </div>
        <div class="service-provider-inline-ads__grid service-provider-inline-ads__grid--{{ $gridColumns }}">
            @foreach($supportingAds as $ad)
                @include('frontend.service_provider.partials.compact-ad-card', [
                    'ad' => $ad,
                    'variant' => 'strip',
                ])
            @endforeach
        </div>
    </div>
</aside>
@endif
