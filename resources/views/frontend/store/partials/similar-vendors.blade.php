@php
    $similarVendors = $similarVendors ?? collect();
@endphp

@if($similarVendors->isNotEmpty())
    <section class="vendor-store-section vendor-store-similar-vendors-section">
        <div class="container">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
                <div>
                    <p class="vendor-store-eyebrow mb-1">Similar stores</p>
                    <h2 class="vendor-store-section-title mb-0">Similar Vendors</h2>
                </div>
                <a href="{{ route('frontend.vendors.index') }}" class="btn btn-store-primary">View all vendors</a>
            </div>
            <div class="vendor-grid row row-cols-1 row-cols-sm-2 row-cols-xl-5 g-3 ad-slider auto-ad-slider vendor-store-similar-vendors-slider" data-show-arrows="true" data-pause-on-hover="false" data-show-dots="false" aria-label="Similar vendors slider">
                @foreach($similarVendors as $similarVendor)
                    <div class="col ad-slide">
                        @include('frontend.partials.vendor-card', ['vendor' => $similarVendor])
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
