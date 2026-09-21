@push('styles')
<style>
    #studyMaterialPaymentModal .sm-pay-layout { display:grid; grid-template-columns: 1fr; gap:1.25rem; }
    @media (min-width: 768px) { #studyMaterialPaymentModal .sm-pay-layout { grid-template-columns: 0.9fr 1.1fr; } }
    #studyMaterialPaymentModal .sm-pay-qr-card { background:#f8fbff; border:1px solid #dbe5ef; border-radius:14px; padding:1rem; text-align:center; }
    #studyMaterialPaymentModal .sm-pay-qr-card img { max-width:220px; width:100%; height:auto; border-radius:10px; border:1px solid #e5e7eb; background:#fff; }
    #studyMaterialPaymentModal .sm-pay-qr-head { font-weight:700; color:#0f172a; display:flex; align-items:center; justify-content:center; gap:.4rem; margin-bottom:.75rem; }
    #studyMaterialPaymentModal .sm-pay-payee { font-weight:700; color:#0f172a; margin:.75rem 0 .15rem; font-size:.9rem; }
    #studyMaterialPaymentModal .sm-pay-upi { font-size:.85rem; color:#334155; margin-bottom:.25rem; }
    #studyMaterialPaymentModal .sm-pay-hint { font-size:.78rem; color:#64748b; }
    #studyMaterialPaymentModal .sm-pay-amount { background:#fff7ed; border:1px solid #f7c793; color:#b45309; border-radius:10px; padding:.65rem .9rem; font-weight:700; display:flex; align-items:center; justify-content:space-between; }
    #studyMaterialPaymentModal .sm-pay-steps { display:flex; gap:.5rem; flex-wrap:wrap; margin-bottom:1rem; }
    #studyMaterialPaymentModal .sm-pay-step { display:flex; align-items:center; gap:.4rem; font-size:.8rem; color:#475569; }
    #studyMaterialPaymentModal .sm-pay-step-no { width:1.4rem; height:1.4rem; border-radius:50%; background:#e2e8f0; color:#0f172a; display:inline-flex; align-items:center; justify-content:center; font-weight:700; font-size:.72rem; }
    #studyMaterialPaymentModal .sm-pay-drop { display:flex; flex-direction:column; align-items:center; justify-content:center; gap:.25rem; border:2px dashed #cbd5e1; border-radius:12px; padding:1.25rem; cursor:pointer; text-align:center; color:#475569; }
    #studyMaterialPaymentModal .sm-pay-drop:hover { border-color:#94a3b8; background:#f8fafc; }
    #studyMaterialPaymentModal .sm-pay-drop i { font-size:1.5rem; color:#64748b; }
    #studyMaterialPaymentModal .sm-pay-preview img { max-height:180px; border-radius:10px; border:1px solid #e5e7eb; }
</style>
@endpush

<div class="modal fade premium-qr-modal" id="studyMaterialPaymentModal" tabindex="-1" aria-labelledby="studyMaterialPaymentModalLabel" aria-hidden="true"
     data-submit-url="{{ route('study-materials.payment.submit') }}"
     data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title" id="studyMaterialPaymentModalLabel">
            <i class="fa-solid fa-indian-rupee-sign me-2 text-primary"></i>
            <span id="studyMaterialPaymentTitle">Purchase Study Material</span>
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
              <strong id="studyMaterialPaymentAmount">₹0.00</strong>
            </div>

            <form id="studyMaterialPaymentForm" enctype="multipart/form-data" novalidate>
              <input type="hidden" name="study_material_id" id="studyMaterialPaymentId" value="">

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
                <i class="fa-solid fa-paper-plane me-1"></i> Proceed to Payment
              </button>
              <p class="small text-secondary mt-2 mb-0">You will get access after admin verifies your payment.</p>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
