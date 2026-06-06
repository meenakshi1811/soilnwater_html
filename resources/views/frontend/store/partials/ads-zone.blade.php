@php($fullPageAds = collect($ads ?? [])->filter()->values())
@if($fullPageAds->isNotEmpty())
<section class="vendor-store-ads-zone vendor-store-ads-zone--slider vendor-store-ads-zone--full-page vendor-store-section py-0">
    <div class="container px-3 px-lg-4">
        @include('frontend.store.partials.ads-rail', [
            'ads' => $fullPageAds,
            'railId' => 'vendorFullPageAdsSlider',
            'sliderOnly' => true,
        ])
    </div>
</section>
@endif
