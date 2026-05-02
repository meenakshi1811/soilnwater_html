<div class="modal fade offer-details-modal" id="adDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable offer-details-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5">Ad Details</h2>
                <button type="button" class="offer-modal-close-btn" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body p-0">
                <img id="adDetailsModalImage" src="" alt="Ad image" class="d-none offer-details-modal-image">
                <div class="offer-details-content">
                    <h3 class="h4 mb-2" id="adDetailsModalTitle"></h3>
                    <p class="text-muted mb-2" id="adDetailsModalMeta"></p>
                    <p class="text-muted mb-2" id="adDetailsModalSize"></p>
                    <p class="text-muted mb-3" id="adDetailsModalDescription"></p>
                    <button type="button" class="btn btn-outline-primary btn-sm mb-3 d-none" id="adDetailsEnlargeBtn">
                        <i class="fa-solid fa-up-right-and-down-left-from-center me-1"></i> Enlarge image
                    </button>

                    <div class="offer-share-panel mt-4">
                        <div class="offer-share-panel-head">
                            <h4 class="offer-share-title mb-1">Share this ad</h4>
                        </div>
                        <div class="offer-share-panel-body">
                            <div class="offer-share-qr-wrap">
                                <img id="adShareQr" src="" alt="Ad QR" class="offer-share-qr">
                            </div>
                            <div class="offer-share-links-wrap">
                                <input type="text" id="adShareLink" class="form-control form-control-sm offer-share-link-input" readonly>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    <a id="adShareWhatsapp" href="#" target="_blank" class="btn btn-sm offer-share-btn share-whatsapp">WhatsApp</a>
                                    <a id="adShareFacebook" href="#" target="_blank" class="btn btn-sm offer-share-btn share-facebook">Facebook</a>
                                    <a id="adShareInstagram" href="#" target="_blank" class="btn btn-sm offer-share-btn share-instagram">Instagram</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="adImageEnlargeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-dark">
            <div class="modal-header border-0">
                <h2 class="modal-title fs-6 text-white">Ad Image Preview</h2>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">
                <img id="adImageEnlargePreview" src="" alt="Enlarged ad image" class="img-fluid w-100 rounded">
            </div>
        </div>
    </div>
</div>
