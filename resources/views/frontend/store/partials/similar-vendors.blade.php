@if($similarVendors->isNotEmpty())
    @once
        @push('styles')
            {{-- The vendor listing page's cards --}}
            <link rel="stylesheet" href="{{ asset('assets/css/vendors-page.css') }}?v={{ now()->timestamp }}">
        @endpush
    @endonce

    <section class="vendor-store-section vendor-store-similar-vendors-section">
        <div class="container">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
                <div>
                    <p class="vendor-store-eyebrow mb-1">Similar stores</p>
                    <h2 class="vendor-store-section-title mb-0">Similar Vendors</h2>
                </div>
                <a href="{{ route('frontend.vendors.listings') }}" class="btn btn-store-primary">View all vendors</a>
            </div>

            {{-- One row of the listing page's vendor cards (two in view on phones) that slides by itself and by hand (business-profile.js) --}}
            <div class="biz-similar-row biz-similar-row--listing" data-biz-carousel="vendors" aria-label="Similar vendors">
                @foreach($similarVendors as $similarVendor)
                    <div class="biz-similar-row__item">
                        @include('frontend.vendors.partials.compact-card', ['vendor' => $similarVendor, 'hasLocation' => false])
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
