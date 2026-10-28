{{-- "Scan & share": a QR code, the link to copy, and WhatsApp / Facebook / Instagram. $shareUrl, $shareName, $shareTitle --}}
<div class="modal fade" id="bizShareModal" tabindex="-1" aria-labelledby="bizShareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content vendor-share-modal">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="bizShareModalLabel">{{ $shareTitle ?? 'Scan & share this profile' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="vendor-share-qr-wrap mb-3 text-center">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=260x260&data={{ urlencode($shareUrl) }}" class="vendor-share-qr" alt="QR code for {{ $shareName }}" loading="lazy">
                </div>
                <label for="bizShareUrl" class="form-label small text-muted mb-1">Link</label>
                <div class="input-group mb-3">
                    <input id="bizShareUrl" type="text" class="form-control" readonly value="{{ $shareUrl }}">
                    <button class="btn btn-outline-secondary" type="button" data-biz-copy="{{ $shareUrl }}">Copy</button>
                </div>
                <div class="vendor-share-actions">
                    <a href="https://wa.me/?text={{ urlencode('Check out '.$shareName.' on SoilnWater: '.$shareUrl) }}" target="_blank" rel="noopener" class="vendor-share-btn share-whatsapp"><i class="fa-brands fa-whatsapp"></i><span>WhatsApp</span></a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener" class="vendor-share-btn share-facebook"><i class="fa-brands fa-facebook-f"></i><span>Facebook</span></a>
                    <a href="https://www.instagram.com/?url={{ urlencode($shareUrl) }}" target="_blank" rel="noopener" class="vendor-share-btn share-instagram"><i class="fa-brands fa-instagram"></i><span>Instagram</span></a>
                </div>
            </div>
        </div>
    </div>
</div>
