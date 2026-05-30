<footer id="contact" class="vendor-store-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6">
                <h5 class="text-white">{{ $vendor->publicDisplayName() }}</h5>
                <p class="small mb-0 mt-2">
                    @if($vendor->address){{ $vendor->address }}, @endif
                    {{ $vendor->city }}@if($vendor->state), {{ $vendor->state }}@endif @if($vendor->pincode){{ $vendor->pincode }}@endif
                </p>
            </div>
            <div class="col-md-3">
                <h6 class="text-white">Contact</h6>
                @if($vendor->phone)<p class="small mb-1"><i class="fa-solid fa-phone me-1"></i> {{ $vendor->phone }}</p>@endif
                @if($vendor->email)<p class="small mb-1"><i class="fa-solid fa-envelope me-1"></i> {{ $vendor->email }}</p>@endif
            </div>
            <div class="col-md-3">
                <h6 class="text-white">Quick links</h6>
                <a href="{{ route('store.show', $vendor->slug) }}" class="small d-block mb-1">Store home</a>
                <a href="{{ route('store.products.index', $vendor->slug) }}" class="small d-block mb-1">All products</a>
            </div>
        </div>
        <hr class="border-secondary my-4">
        <p class="small text-center mb-0">&copy; {{ date('Y') }} {{ $vendor->publicDisplayName() }} · Powered by SoilNWater</p>
    </div>
</footer>

@if($vendor->whatsapp)
    <a href="https://wa.me/91{{ preg_replace('/\D/', '', $vendor->whatsapp) }}" class="vendor-whatsapp-float" target="_blank" rel="noopener" aria-label="WhatsApp">
        <i class="fa-brands fa-whatsapp"></i>
    </a>
@endif
