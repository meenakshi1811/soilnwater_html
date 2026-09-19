<div class="modal fade" id="schoolShareModal" tabindex="-1" aria-labelledby="schoolShareModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title" id="schoolShareModalLabel">Share this school</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-3">
        <div class="text-center mb-3">
          <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode($shareUrl) }}" alt="QR code" class="img-fluid">
        </div>
        <div class="input-group">
          <input type="text" class="form-control" readonly value="{{ $shareUrl }}" id="schoolShareUrl">
          <button type="button" class="btn btn-outline-secondary js-sch-copy-url">Copy</button>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="schNoticeModal" tabindex="-1" aria-labelledby="schNoticeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title" id="schNoticeModalLabel">Notice</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-2">
        <p class="text-muted small mb-2" id="schNoticeModalExpiry"></p>
        <div id="schNoticeModalBody" class="sch-notice-modal__body"></div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="schoolGalleryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content bg-dark">
      <div class="modal-header border-0">
        <h5 class="modal-title text-white">Gallery preview</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-0 text-center">
        <img id="schoolGalleryModalImg" src="" alt="Gallery image" class="img-fluid rounded" style="max-height:80vh;object-fit:contain;">
      </div>
    </div>
  </div>
</div>
