@if(!empty($placement['ads']) && $placement['ads']->isNotEmpty())
<section class="vendor-store-ads-zone vendor-store-section py-0">
    <div class="container px-3 px-lg-4">
        @include('frontend.store.partials.ads-rail', [
            'ads' => $placement['ads'],
            'railId' => ($placement['grid_id'] ?? 'storePlacementAds').'_slider',
        ])
    </div>
</section>
@endif
