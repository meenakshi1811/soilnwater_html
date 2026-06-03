@php($sponsoredFillers = $sponsoredFillers ?? [])
@if(!empty($placement['ads']) && $placement['ads']->isNotEmpty())
<section class="vendor-store-ads-zone vendor-store-ads-zone--slider vendor-store-section py-0">
    <div class="container px-3 px-lg-4">
        @include('frontend.service_provider.partials.ads-rail', [
            'ads' => $placement['ads'],
            'railId' => ($placement['grid_id'] ?? 'service_providerPlacementAds').'_slider',
            'sliderOnly' => true,
        ])
    </div>
</section>
@endif
