<div class="modal fade" id="studyMaterialPaymentModal" tabindex="-1" aria-labelledby="studyMaterialPaymentModalLabel" aria-hidden="true"
     data-submit-url="{{ route('study-materials.payment.submit') }}"
     data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title" id="studyMaterialPaymentModalLabel">
            <i class="fa-solid fa-indian-rupee-sign me-2 text-primary"></i>
            Purchase Note
          </h5>
          <p class="text-secondary small mb-0">Scan &amp; pay, then upload the screenshot for admin verification.</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="sm-pay-layout">
          <aside>
            <div class="sm-pay-qr-card">
              <div class="sm-pay-qr-head"><i class="fa-solid fa-qrcode"></i><span>Scan &amp; Pay via UPI</span></div>
              <img src="{{ asset('assets/images/premium-payment-qr.png') }}" alt="UPI payment QR code">
              <p class="sm-pay-payee">ANNUVEDANT ELECTRONICS OPC PRIVATE LIMITED</p>
              <p class="sm-pay-upi"><strong>UPI ID:</strong> yespay.bizsbiz240983@yesbankltd</p>
              <p class="sm-pay-hint">Scan with PhonePe, Google Pay, Paytm, or any UPI app</p>
            </div>
          </aside>

          <div>
            <div class="sm-pay-steps">
              <div class="sm-pay-step"><span class="sm-pay-step-no">1</span><span>Scan QR &amp; pay</span></div>
              <div class="sm-pay-step"><span class="sm-pay-step-no">2</span><span>Upload screenshot</span></div>
              <div class="sm-pay-step"><span class="sm-pay-step-no">3</span><span>Admin verifies</span></div>
            </div>

            <div class="sm-pay-amount mb-3">
              <span>Amount to pay</span>
              <strong id="studyMaterialPaymentAmount">{{ $material->formattedPrice() }}</strong>
            </div>

            <form id="studyMaterialPaymentForm" enctype="multipart/form-data" novalidate>
              <input type="hidden" name="study_material_id" id="studyMaterialPaymentId" value="{{ $material->id }}">

              <div class="mb-3">
                <label class="form-label fw-semibold">Payment screenshot <span class="text-danger">*</span></label>
                <label class="sm-pay-drop" for="studyMaterialPaymentScreenshot">
                  <i class="fa-solid fa-cloud-arrow-up"></i>
                  <span class="fw-semibold">Click to upload payment screenshot</span>
                  <span class="small text-secondary">JPG, PNG, WEBP — max 5 MB</span>
                  <span class="small fw-semibold d-none" id="studyMaterialPaymentFileName"></span>
                </label>
                <input type="file" class="visually-hidden" id="studyMaterialPaymentScreenshot" name="screenshot" accept="image/*" required>
              </div>

              <div id="studyMaterialPaymentPreview" class="sm-pay-preview text-center d-none mb-3">
                <img src="" alt="Payment screenshot preview" id="studyMaterialPaymentPreviewImage">
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Transaction reference <span class="text-muted fw-normal">(optional)</span></label>
                <input type="text" class="form-control" name="transaction_reference" maxlength="120" placeholder="UPI ref / transaction ID">
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Note <span class="text-muted fw-normal">(optional)</span></label>
                <textarea class="form-control" name="user_note" rows="2" maxlength="1000" placeholder="Any extra payment details for admin"></textarea>
              </div>

              <div id="studyMaterialPaymentAlert" class="alert d-none" role="alert"></div>

              <button type="submit" class="btn btn-primary w-100" id="studyMaterialPaymentSubmitBtn">
                <i class="fa-solid fa-paper-plane me-1"></i> Submit Payment Proof
              </button>
              <p class="small text-secondary mt-2 mb-0">You will get access to the full note after admin verifies your payment.</p>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
