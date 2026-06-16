<div class="modal fade" id="communityShareModal" tabindex="-1" aria-labelledby="communityShareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content community-share-modal">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="communityShareModalLabel">Share this post</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <p class="text-muted small mb-3" id="communityShareModalSubtitle">Send this story using QR code or social channels.</p>
                <div class="community-share-qr-wrap mb-3">
                    <img
                        id="communityShareModalQr"
                        src=""
                        class="community-share-qr"
                        alt="QR code for community post"
                        loading="lazy"
                    >
                </div>
                <label for="communityShareModalUrl" class="form-label small text-muted mb-1">Post link</label>
                <div class="input-group mb-3">
                    <input id="communityShareModalUrl" type="text" class="form-control" readonly value="">
                    <button class="btn btn-outline-secondary" type="button" data-copy-community-share-link="communityShareModalUrl">Copy</button>
                </div>
                <div class="community-share-actions">
                    <a id="communityShareModalWhatsapp" href="#" target="_blank" rel="noopener" class="community-share-btn share-whatsapp"><i class="fa-brands fa-whatsapp"></i><span>WhatsApp</span></a>
                    <a id="communityShareModalFacebook" href="#" target="_blank" rel="noopener" class="community-share-btn share-facebook"><i class="fa-brands fa-facebook-f"></i><span>Facebook</span></a>
                    <a id="communityShareModalInstagram" href="#" target="_blank" rel="noopener" class="community-share-btn share-instagram"><i class="fa-brands fa-instagram"></i><span>Instagram</span></a>
                </div>
            </div>
        </div>
    </div>
</div>
