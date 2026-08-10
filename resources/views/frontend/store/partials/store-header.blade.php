<header class="vendor-store-header">
    <div class="container">
        <div class="vendor-store-header__inner">
            <a href="{{ route('store.show', $vendor->slug) }}" class="vendor-store-brand">
                @if($vendor->logo)
                    <img src="{{ asset($vendor->logo) }}" alt="{{ $vendor->publicDisplayName() }}" height="44">
                @else
                    <span class="vendor-store-brand__text">{{ $vendor->publicDisplayName() }}</span>
                @endif
            </a>

            @include('frontend.partials.marketplace-store-header-link', [
                'storeUrl' => route('store.show', $vendor->slug),
                'linkLabel' => 'Store',
            ])

            <button class="vendor-store-mobile-toggle d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#vendorStoreNav" aria-controls="vendorStoreNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fa-solid fa-bars"></i>
            </button>

            <nav class="vendor-store-nav collapse d-lg-flex" id="vendorStoreNav">
                <a href="{{ route('store.show', $vendor->slug) }}" class="vendor-store-nav-link {{ ($activeNav ?? '') === 'home' ? 'is-active' : '' }}">Home</a>

                @include('frontend.store.partials.products-mega-menu', [
                    'vendor' => $vendor,
                    'vendorCategories' => $vendorCategories ?? collect(),
                    'activeNav' => $activeNav ?? '',
                ])

                <a href="{{ route('store.about', $vendor->slug) }}" class="vendor-store-nav-link {{ ($activeNav ?? '') === 'about' ? 'is-active' : '' }}">About Us</a>

                <a href="{{ route('store.contact', $vendor->slug) }}" class="vendor-store-nav-link {{ ($activeNav ?? '') === 'contact' ? 'is-active' : '' }}">Contact</a>

                <button type="button" class="vendor-share-trigger vendor-store-nav-share" data-bs-toggle="modal" data-bs-target="#vendorShareModal">
                    <i class="fa-solid fa-qrcode"></i>
                    <span>Scan &amp; Share</span>
                </button>
            </nav>
        </div>
    </div>
</header>

<div class="modal fade" id="vendorShareModal" tabindex="-1" aria-labelledby="vendorShareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content vendor-share-modal">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="vendorShareModalLabel">Scan & Share this store</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="vendor-share-qr-wrap mb-3 text-center">
                    <img
                        src="https://api.qrserver.com/v1/create-qr-code/?size=260x260&data={{ urlencode(route('store.show', $vendor->slug)) }}"
                        class="vendor-share-qr"
                        alt="QR code for {{ $vendor->publicDisplayName() }} store"
                        loading="lazy"
                    >
                </div>
                <label for="vendorStoreShareUrl" class="form-label small text-muted mb-1">Store link</label>
                <div class="input-group mb-3">
                    <input id="vendorStoreShareUrl" type="text" class="form-control" readonly value="{{ route('store.show', $vendor->slug) }}">
                    <button class="btn btn-outline-secondary js-copy-store-url" type="button" data-url="{{ route('store.show', $vendor->slug) }}">Copy</button>
                </div>
                <div class="vendor-share-actions">
                    <a href="https://wa.me/?text={{ urlencode('Check out this store: '.route('store.show', $vendor->slug)) }}" target="_blank" rel="noopener" class="vendor-share-btn share-whatsapp"><i class="fa-brands fa-whatsapp"></i><span>WhatsApp</span></a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('store.show', $vendor->slug)) }}" target="_blank" rel="noopener" class="vendor-share-btn share-facebook"><i class="fa-brands fa-facebook-f"></i><span>Facebook</span></a>
                    <a href="https://www.instagram.com/?url={{ urlencode(route('store.show', $vendor->slug)) }}" target="_blank" rel="noopener" class="vendor-share-btn share-instagram"><i class="fa-brands fa-instagram"></i><span>Instagram</span></a>
                </div>
            </div>
        </div>
    </div>
</div>
