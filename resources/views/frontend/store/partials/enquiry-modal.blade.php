<div class="modal fade" id="enquiryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Send Product Enquiry</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        @auth
        <form id="enquiryForm">
          @csrf
          <div class="mb-2"><label>Email</label><input type="email" name="email" value="{{ auth()->user()->email }}" class="form-control" required></div>
          <div class="mb-2"><label>Number</label><input type="text" name="phone_number" value="{{ auth()->user()->phone_number }}" class="form-control" required></div>
          <div class="mb-2"><label>Way to connect</label><select name="preferred_contact" class="form-select" required><option value="text">Text</option><option value="whatsapp">WhatsApp</option><option value="call">Call</option><option value="email">Email</option></select></div>
          <div class="mb-2"><label>Reason</label><textarea name="reason" class="form-control" rows="4" required></textarea></div>
          <button class="btn btn-primary w-100" id="enquirySubmitBtn" type="submit">
            <span class="js-enquiry-btn-text">Send Enquiry</span>
            <span class="spinner-border spinner-border-sm ms-2 d-none js-enquiry-btn-loader" role="status" aria-hidden="true"></span>
            <span class="ms-1 d-none js-enquiry-btn-sending">Sending...</span>
          </button>
        </form>
        @else
        <div class="alert alert-warning mb-0">Please login to send enquiry.</div>
        @endauth
      </div>
    </div>
  </div>
</div>
