<header class="vendor-store-header">
    <div class="container">
        <div class="vendor-store-header__inner">
            <a href="{{ route('consultant.show', $consultant->slug) }}" class="vendor-store-brand">
                @if($consultant->logo)
                    <img src="{{ asset($consultant->logo) }}" alt="{{ $consultant->publicDisplayName() }}" height="44">
                @else
                    <span class="vendor-store-brand__text">{{ $consultant->publicDisplayName() }}</span>
                @endif
            </a>

            @include('frontend.partials.marketplace-store-header-link', [
                'storeUrl' => route('consultant.show', $consultant->slug),
                'linkLabel' => 'Consultant',
            ])

            <button class="vendor-store-mobile-toggle d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#consultantStoreNav" aria-controls="consultantStoreNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fa-solid fa-bars"></i>
            </button>

            <nav class="vendor-store-nav collapse d-lg-flex" id="consultantStoreNav">
                <a href="{{ route('consultant.show', $consultant->slug) }}" class="vendor-store-nav-link {{ ($activeNav ?? '') === 'home' ? 'is-active' : '' }}">Home</a>


                <a href="{{ route('consultant.about', $consultant->slug) }}" class="vendor-store-nav-link {{ ($activeNav ?? '') === 'about' ? 'is-active' : '' }}">About Us</a>

                <a href="{{ route('consultant.public-services.index', $consultant->slug) }}" class="vendor-store-nav-link {{ in_array($activeNav ?? '', ['services', 'category', 'subcategory'], true) ? 'is-active' : '' }}">Services</a>

                <a href="{{ route('consultant.contact', $consultant->slug) }}" class="vendor-store-nav-link {{ ($activeNav ?? '') === 'contact' ? 'is-active' : '' }}">Contact</a>

                @auth
                    @if((int) auth()->id() !== (int) $consultant->user_id)
                        <button type="button" class="vendor-share-trigger vendor-store-nav-share" data-bs-toggle="modal" data-bs-target="#consultantReportModal">
                            <i class="fa-regular fa-flag"></i>
                            <span>Report</span>
                        </button>
                    @endif
                @endauth

                <button type="button" class="vendor-share-trigger vendor-store-nav-share" data-bs-toggle="modal" data-bs-target="#consultantShareModal">
                    <i class="fa-solid fa-qrcode"></i>
                    <span>Scan &amp; Share</span>
                </button>
            </nav>
        </div>
    </div>
</header>

<div class="modal fade" id="consultantShareModal" tabindex="-1" aria-labelledby="consultantShareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content vendor-share-modal">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="consultantShareModalLabel">Scan & Share this consultant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="vendor-share-qr-wrap mb-3 text-center">
                    <img
                        src="https://api.qrserver.com/v1/create-qr-code/?size=260x260&data={{ urlencode(route('consultant.show', $consultant->slug)) }}"
                        class="vendor-share-qr"
                        alt="QR code for {{ $consultant->publicDisplayName() }} consultant"
                        loading="lazy"
                    >
                </div>
                <label for="consultantStoreShareUrl" class="form-label small text-muted mb-1">Consultant link</label>
                <div class="input-group mb-3">
                    <input id="consultantStoreShareUrl" type="text" class="form-control" readonly value="{{ route('consultant.show', $consultant->slug) }}">
                    <button class="btn btn-outline-secondary js-copy-store-url" type="button" data-url="{{ route('consultant.show', $consultant->slug) }}">Copy</button>
                </div>
                <div class="vendor-share-actions">
                    <a href="https://wa.me/?text={{ urlencode('Check out this consultant: '.route('consultant.show', $consultant->slug)) }}" target="_blank" rel="noopener" class="vendor-share-btn share-whatsapp"><i class="fa-brands fa-whatsapp"></i><span>WhatsApp</span></a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('consultant.show', $consultant->slug)) }}" target="_blank" rel="noopener" class="vendor-share-btn share-facebook"><i class="fa-brands fa-facebook-f"></i><span>Facebook</span></a>
                    <a href="https://www.instagram.com/?url={{ urlencode(route('consultant.show', $consultant->slug)) }}" target="_blank" rel="noopener" class="vendor-share-btn share-instagram"><i class="fa-brands fa-instagram"></i><span>Instagram</span></a>
                </div>
            </div>
        </div>
    </div>
</div>
