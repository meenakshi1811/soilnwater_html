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
                <button type="button" class="vendor-share-trigger mt-2" data-bs-toggle="modal" data-bs-target="#vendorShareModal">
                    <i class="fa-solid fa-qrcode me-2"></i> Scan & Share
                </button>
            </div>
        </div>
        <hr class="border-secondary my-4">
        <p class="small text-center mb-0">&copy; {{ date('Y') }} {{ $vendor->publicDisplayName() }} · Powered by SoilNWater</p>
    </div>
</footer>

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
                    <button class="btn btn-outline-secondary" type="button" id="copyVendorStoreLink">Copy</button>
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

@if($vendor->whatsapp)
    <a href="https://wa.me/91{{ preg_replace('/\D/', '', $vendor->whatsapp) }}" class="vendor-whatsapp-float" target="_blank" rel="noopener" aria-label="WhatsApp">
        <i class="fa-brands fa-whatsapp"></i>
    </a>
@endif
