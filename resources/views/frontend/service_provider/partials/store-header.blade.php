<header class="vendor-store-header">
    <div class="container">
        <div class="vendor-store-header__inner">
            <a href="{{ route('service_provider.show', $service_provider->slug) }}" class="vendor-store-brand">
                @if($service_provider->logo)
                    <img src="{{ asset($service_provider->logo) }}" alt="{{ $service_provider->publicDisplayName() }}" height="44">
                @else
                    <span class="vendor-store-brand__text">{{ $service_provider->publicDisplayName() }}</span>
                @endif
            </a>

            @include('frontend.partials.marketplace-store-header-link', [
                'storeUrl' => route('service_provider.show', $service_provider->slug),
                'linkLabel' => 'Service',
            ])

            <button class="vendor-store-mobile-toggle d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#service_providerStoreNav" aria-controls="service_providerStoreNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fa-solid fa-bars"></i>
            </button>

            <nav class="vendor-store-nav collapse d-lg-flex" id="service_providerStoreNav">
                <a href="{{ route('service_provider.show', $service_provider->slug) }}" class="vendor-store-nav-link {{ ($activeNav ?? '') === 'home' ? 'is-active' : '' }}">Home</a>


                <a href="{{ route('service_provider.about', $service_provider->slug) }}" class="vendor-store-nav-link {{ ($activeNav ?? '') === 'about' ? 'is-active' : '' }}">About Us</a>

                <a href="{{ route('service_provider.public-services.index', $service_provider->slug) }}" class="vendor-store-nav-link {{ in_array($activeNav ?? '', ['services', 'category', 'subcategory'], true) ? 'is-active' : '' }}">Services</a>

                <a href="{{ route('service_provider.contact', $service_provider->slug) }}" class="vendor-store-nav-link {{ ($activeNav ?? '') === 'contact' ? 'is-active' : '' }}">Contact</a>

                @auth
                    @if((int) auth()->id() !== (int) $service_provider->user_id)
                        <button type="button" class="vendor-share-trigger vendor-store-nav-share" data-bs-toggle="modal" data-bs-target="#serviceProviderReportModal">
                            <i class="fa-regular fa-flag"></i>
                            <span>Report</span>
                        </button>
                    @endif
                @endauth

                <button type="button" class="vendor-share-trigger vendor-store-nav-share" data-bs-toggle="modal" data-bs-target="#service_providerShareModal">
                    <i class="fa-solid fa-qrcode"></i>
                    <span>Scan &amp; Share</span>
                </button>
            </nav>
        </div>
    </div>
</header>

<div class="modal fade" id="service_providerShareModal" tabindex="-1" aria-labelledby="service_providerShareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content vendor-share-modal">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="service_providerShareModalLabel">Scan & Share this service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="vendor-share-qr-wrap mb-3 text-center">
                    <img
                        src="https://api.qrserver.com/v1/create-qr-code/?size=260x260&data={{ urlencode(route('service_provider.show', $service_provider->slug)) }}"
                        class="vendor-share-qr"
                        alt="QR code for {{ $service_provider->publicDisplayName() }} service"
                        loading="lazy"
                    >
                </div>
                <label for="service_providerStoreShareUrl" class="form-label small text-muted mb-1">Service link</label>
                <div class="input-group mb-3">
                    <input id="service_providerStoreShareUrl" type="text" class="form-control" readonly value="{{ route('service_provider.show', $service_provider->slug) }}">
                    <button class="btn btn-outline-secondary js-copy-store-url" type="button" data-url="{{ route('service_provider.show', $service_provider->slug) }}">Copy</button>
                </div>
                <div class="vendor-share-actions">
                    <a href="https://wa.me/?text={{ urlencode('Check out this service: '.route('service_provider.show', $service_provider->slug)) }}" target="_blank" rel="noopener" class="vendor-share-btn share-whatsapp"><i class="fa-brands fa-whatsapp"></i><span>WhatsApp</span></a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('service_provider.show', $service_provider->slug)) }}" target="_blank" rel="noopener" class="vendor-share-btn share-facebook"><i class="fa-brands fa-facebook-f"></i><span>Facebook</span></a>
                    <a href="https://www.instagram.com/?url={{ urlencode(route('service_provider.show', $service_provider->slug)) }}" target="_blank" rel="noopener" class="vendor-share-btn share-instagram"><i class="fa-brands fa-instagram"></i><span>Instagram</span></a>
                </div>
            </div>
        </div>
    </div>
</div>
