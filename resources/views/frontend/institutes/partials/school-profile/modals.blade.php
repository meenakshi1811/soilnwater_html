<div class="modal fade" id="schoolEnquiryModal" tabindex="-1" aria-labelledby="schoolEnquiryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title" id="schoolEnquiryModalLabel">Send enquiry</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-2">
        @guest
          <p class="mb-0">Please <a href="{{ route('login') }}">log in</a> to send an enquiry.</p>
        @else
          <form id="schoolEnquiryModalForm" method="post" action="{{ route('schools.enquiry', $institute->slug) }}" novalidate>
            @csrf
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label" for="school_modal_enquiry_name">Your name</label>
                <input type="text" class="form-control" id="school_modal_enquiry_name" name="name" value="{{ auth()->user()->name }}" required>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="school_modal_enquiry_email">Email</label>
                <input type="email" class="form-control" id="school_modal_enquiry_email" name="email" value="{{ auth()->user()->email }}">
              </div>
              <div class="col-md-6">
                <label class="form-label" for="school_modal_enquiry_phone">Phone</label>
                <input type="text" class="form-control" id="school_modal_enquiry_phone" name="phone" value="{{ auth()->user()->phone_number }}">
              </div>
              <div class="col-md-6">
                <label class="form-label" for="school_modal_enquiry_subject">Subject</label>
                <input type="text" class="form-control" id="school_modal_enquiry_subject" name="subject" placeholder="Admission enquiry">
              </div>
              <div class="col-12">
                <label class="form-label" for="school_modal_enquiry_message">Message</label>
                <textarea class="form-control" id="school_modal_enquiry_message" name="message" rows="4" required placeholder="Tell us about your enquiry..."></textarea>
              </div>
              <div class="col-12">
                <div id="schoolEnquiryModalFeedback" class="alert d-none" role="alert"></div>
                <button type="submit" class="sch-btn sch-btn-primary js-school-enquiry-submit">
                  <span class="js-enquiry-btn-text">Send enquiry</span>
                  <span class="js-enquiry-btn-sending d-none">Sending...</span>
                </button>
              </div>
            </div>
          </form>
        @endguest
      </div>
    </div>
  </div>
</div>

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
