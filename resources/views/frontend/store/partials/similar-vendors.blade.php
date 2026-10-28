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

            {{-- One row of cards (two in view on phones) that slides by itself and by hand (business-profile.js) --}}
            <div class="offer-coupon-grid vendor-offer-grid vendor-store-similar-vendors-slider biz-similar-row" data-biz-carousel="vendors" aria-label="Similar vendors">
                @foreach($similarVendors as $similarVendor)
                    <div class="biz-similar-row__item">
                        @include('frontend.partials.vendor-card', ['vendor' => $similarVendor])
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
