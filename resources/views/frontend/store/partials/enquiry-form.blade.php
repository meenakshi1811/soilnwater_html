@auth
<form id="enquiryForm" class="vendor-enquiry-form">
  @csrf
  <div class="mb-3">
    <label class="form-label" for="enquiryEmail">Email</label>
    <input type="email" id="enquiryEmail" name="email" value="{{ auth()->user()->email }}" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label" for="enquiryPhone">Number</label>
    <input type="text" id="enquiryPhone" name="phone_number" value="{{ auth()->user()->phone_number }}" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label" for="enquiryPreferredContact">Way to connect</label>
    <select id="enquiryPreferredContact" name="preferred_contact" class="form-select" required>
      <option value="text">Text</option>
      <option value="whatsapp">WhatsApp</option>
      <option value="call">Call</option>
      <option value="email">Email</option>
    </select>
  </div>
  <div class="mb-3">
    <label class="form-label" for="enquiryReason">Reason</label>
    <textarea id="enquiryReason" name="reason" class="form-control" rows="4" required></textarea>
  </div>
  <button class="btn enquiry-btn w-100" id="enquirySubmitBtn" type="submit">
    <span class="js-enquiry-btn-text"><i class="fa-regular fa-paper-plane me-2"></i>Send Enquiry</span>
    <span class="spinner-border spinner-border-sm ms-2 d-none js-enquiry-btn-loader" role="status" aria-hidden="true"></span>
    <span class="ms-1 d-none js-enquiry-btn-sending">Sending...</span>
  </button>
</form>
@else
<div class="alert alert-warning mb-0">Please <a href="{{ route('login') }}" class="alert-link">login</a> to send enquiry.</div>
@endauth
