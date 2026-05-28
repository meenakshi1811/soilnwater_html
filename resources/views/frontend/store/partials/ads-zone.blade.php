@php($sponsoredFillers = $sponsoredFillers ?? [])
@if(!empty($placement['ads']) && $placement['ads']->isNotEmpty())
<section class="vendor-store-ads-zone vendor-store-section py-0">
    <div class="container-fluid px-3 px-lg-4">
        <p class="vendor-store-ads-zone__label">Sponsored listings</p>
        @include('frontend.ads.partials.cards', [
            'ads' => $placement['ads'],
            'sponsoredFillers' => $sponsoredFillers,
            'gridId' => $placement['grid_id'],
            'autoRender' => false,
        ])
    </div>
</section>
@endif
