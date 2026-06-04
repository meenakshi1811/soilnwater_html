<div class="vendor-store-catalog">
    <div class="row g-4 align-items-start">
        <div class="col-12">
            @include('frontend.service_provider.partials.category-sidebar', [
                'service_provider' => $service_provider,
                'service_providerCategories' => $service_providerCategories,
                'activeCategory' => $activeCategory ?? null,
                'activeSubcategory' => $activeSubcategory ?? null,
            ])
        </div>

        <div class="col-12">
            <div class="vendor-store-results-bar">
                <p class="mb-0">
                    @if(method_exists($approvedServices, 'total'))
                        Showing {{ $approvedServices->firstItem() ?? 0 }}–{{ $approvedServices->lastItem() ?? 0 }} of {{ $approvedServices->total() }} services
                    @else
                        {{ $approvedServices->count() }} {{ Str::plural('service', $approvedServices->count()) }}
                    @endif
                </p>
            </div>

            @include('frontend.service_provider.partials.services-section', [
                'service_provider' => $service_provider,
                'approvedServices' => $approvedServices,
                'showViewAllServicesButton' => false,
                'showServicesHeading' => false,
                'wrapSection' => false,
                'wrapContainer' => false,
                'serviceColumnClass' => 'col-6 col-md-4 col-xl-3',
            ])

            @if($approvedServices->count() === 0)
                <div class="vendor-store-empty-products">
                    <p class="mb-0">No services found in this category yet.</p>
                </div>
            @endif

            @if(method_exists($approvedServices, 'links'))
                <div class="mt-4 d-flex justify-content-center vendor-store-pagination">
                    {{ $approvedServices->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
