<div class="modal fade offer-details-modal" id="adDetailsModal" tabindex="-1" aria-hidden="true">
    <div id="adDetailsModalDialog" class="modal-dialog modal-dialog-centered modal-dialog-scrollable offer-details-dialog">
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
                    <p class="text-muted mb-3" id="adDetailsModalDescription"></p>
                    <button type="button" class="btn btn-outline-primary btn-sm mb-3 d-none" id="adDetailsEnlargeBtn">
                        <i class="fa-solid fa-up-right-and-down-left-from-center me-1"></i> Enlarge image
                    </button>
                    <div class="offer-login-message d-none" id="adLoginMessageBox" role="status" aria-live="polite">
                        <div class="offer-login-message-icon"><i class="fa-solid fa-lock"></i></div>
                        <div>
                            <h4 class="offer-login-message-title mb-1">You are not logged in</h4>
                            <p class="offer-login-message-text mb-2">Please log in to view this ad details and share options.</p>
                            <a href="{{ route('login') }}" class="btn btn-sm btn-primary">Login to continue</a>
                        </div>
                    </div>

                    <div class="offer-share-panel mt-4" id="adSharePanel">
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
                                    <a id="adShareFacebook" href="#" target="_blank" rel="noopener" class="btn btn-sm offer-share-btn share-facebook">Facebook</a>
                                    <a id="adShareInstagram" href="#" target="_blank" class="btn btn-sm offer-share-btn share-instagram">Instagram</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 border-top pt-3" id="adReportActions">
                        <button type="button" class="btn btn-outline-danger btn-sm" id="openAdReportPopupBtn">
                            <i class="fa-regular fa-flag me-1"></i> Report this ad
                        </button>
                    </div>

                    <div class="mt-3 d-none" id="adReportPopupWrap">
                        <div class="ad-report-popup border rounded-3 p-3 bg-light">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h5 class="h6 mb-0"><i class="fa-regular fa-flag me-1 text-danger"></i>Report this ad</h5>
                                <button type="button" class="btn btn-sm btn-link text-muted p-0" id="closeAdReportPopupBtn">Close</button>
                            </div>
                            @auth
                                <form id="adReportForm" method="POST" action="#">
                                    @csrf
                                    <textarea name="reason" class="form-control form-control-sm mb-2 ad-report-textarea" rows="3" placeholder="Enter reason for reporting this ad" required></textarea>
                                    <button type="submit" class="btn btn-sm btn-danger">Submit Report</button>
                                </form>
                            @else
                                <p class="mb-0 small text-muted">Please <a href="{{ route('login') }}">login</a> to report this ad.</p>
                            @endauth
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="adReportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title h5 mb-0"><i class="fa-regular fa-flag me-1 text-danger"></i>Report this ad</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @auth
                    <form id="adReportForm" method="POST" action="#">
                        @csrf
                        <textarea name="reason" class="form-control form-control-sm mb-2 ad-report-textarea" rows="3" placeholder="Enter reason for reporting this ad" required></textarea>
                        <button type="submit" class="btn btn-sm btn-danger">Submit Report</button>
                    </form>
                @else
                    <p class="mb-0 small text-muted">Please <a href="{{ route('login') }}">login</a> to report this ad.</p>
                @endauth
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
