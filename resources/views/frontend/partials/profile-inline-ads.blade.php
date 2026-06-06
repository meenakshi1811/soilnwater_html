@php
    $supportingAds = collect($ads ?? [])->filter()->values();
    $placementId = $placementId ?? 'serviceProviderSupportingAds';
@endphp

@if($supportingAds->isNotEmpty())
<aside class="marketplace-profile-inline-ads" aria-labelledby="{{ $placementId }}Title">
    <div class="container px-3 px-lg-4">
        <div class="marketplace-profile-inline-ads__head">
            <span><i class="fa-solid fa-rectangle-ad"></i> Sponsored recommendations</span>
            <small id="{{ $placementId }}Title">Selected for this page</small>
        </div>
        <div class="row g-3">
            @foreach($supportingAds as $ad)
                <div class="{{ $supportingAds->count() === 1 ? 'col-12 col-xl-8' : 'col-12 col-lg-6' }}">
                    @include('frontend.partials.profile-ad-card', [
                        'ad' => $ad,
                        'variant' => 'strip',
                    ])
                </div>
            @endforeach
        </div>
    </div>
</aside>
@endif
