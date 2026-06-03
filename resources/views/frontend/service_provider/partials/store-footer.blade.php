<footer id="contact" class="vendor-store-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6">
                <h5 class="text-white">{{ $service_provider->publicDisplayName() }}</h5>
                <p class="small mb-0 mt-2">
                    {{ $service_provider->formattedAddress() ?: 'Address details are not available yet.' }}
                </p>
            </div>
            <div class="col-md-3">
                <h6 class="text-white">Contact</h6>
                @if($service_provider->phone)<p class="small mb-1"><i class="fa-solid fa-phone me-1"></i> {{ $service_provider->phone }}</p>@endif
                @if($service_provider->email)<p class="small mb-1"><i class="fa-solid fa-envelope me-1"></i> {{ $service_provider->email }}</p>@endif
            </div>
            <div class="col-md-3">
                <h6 class="text-white">Quick links</h6>
                <a href="{{ route('service_provider.show', $service_provider->slug) }}" class="small d-block mb-1">Service Provider home</a>
                <a href="{{ route('service_provider.about', $service_provider->slug) }}" class="small d-block mb-1">About Us</a>
            </div>
        </div>
        <hr class="border-secondary my-4">
        <p class="small text-center mb-0">&copy; {{ date('Y') }} {{ $service_provider->publicDisplayName() }} · Powered by SoilNWater</p>
    </div>
</footer>

@if(($activeNav ?? '') !== 'contact' && $service_provider->whatsapp)
    <a href="https://wa.me/91{{ preg_replace('/\D/', '', $service_provider->whatsapp) }}" class="vendor-whatsapp-float" target="_blank" rel="noopener" aria-label="WhatsApp">
        <i class="fa-brands fa-whatsapp"></i>
    </a>
@endif
