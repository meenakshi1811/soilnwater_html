{{-- Shared payment modal for paid ads & offers. Opened via window.ListingPayment.open({...}). --}}
@push('styles')
<style>
    #listingPaymentModal .listing-pay-layout { display:grid; grid-template-columns: 1fr; gap:1.25rem; }
    @media (min-width: 768px) { #listingPaymentModal .listing-pay-layout { grid-template-columns: 0.9fr 1.1fr; } }
    #listingPaymentModal .listing-pay-qr-card { background:#f8fbff; border:1px solid #dbe5ef; border-radius:14px; padding:1rem; text-align:center; }
    #listingPaymentModal .listing-pay-qr-card img { max-width:220px; width:100%; height:auto; border-radius:10px; border:1px solid #e5e7eb; background:#fff; }
    #listingPaymentModal .listing-pay-qr-head { font-weight:700; color:#0f172a; display:flex; align-items:center; justify-content:center; gap:.4rem; margin-bottom:.75rem; }
    #listingPaymentModal .listing-pay-payee { font-weight:700; color:#0f172a; margin:.75rem 0 .15rem; font-size:.9rem; }
    #listingPaymentModal .listing-pay-upi { font-size:.85rem; color:#334155; margin-bottom:.25rem; }
    #listingPaymentModal .listing-pay-hint { font-size:.78rem; color:#64748b; }
    #listingPaymentModal .listing-pay-amount { background:#fff7ed; border:1px solid #f7c793; color:#b45309; border-radius:10px; padding:.65rem .9rem; font-weight:700; display:flex; align-items:center; justify-content:space-between; }
    #listingPaymentModal .listing-pay-steps { display:flex; gap:.5rem; flex-wrap:wrap; margin-bottom:1rem; }
    #listingPaymentModal .listing-pay-step { display:flex; align-items:center; gap:.4rem; font-size:.8rem; color:#475569; }
    #listingPaymentModal .listing-pay-step-no { width:1.4rem; height:1.4rem; border-radius:50%; background:#e2e8f0; color:#0f172a; display:inline-flex; align-items:center; justify-content:center; font-weight:700; font-size:.72rem; }
    #listingPaymentModal .listing-pay-drop { display:flex; flex-direction:column; align-items:center; justify-content:center; gap:.25rem; border:2px dashed #cbd5e1; border-radius:12px; padding:1.25rem; cursor:pointer; text-align:center; color:#475569; }
    #listingPaymentModal .listing-pay-drop:hover { border-color:#94a3b8; background:#f8fafc; }
    #listingPaymentModal .listing-pay-drop i { font-size:1.5rem; color:#64748b; }
    #listingPaymentModal .listing-pay-preview img { max-height:180px; border-radius:10px; border:1px solid #e5e7eb; }
</style>
@endpush

<div class="modal fade" id="listingPaymentModal" tabindex="-1" aria-labelledby="listingPaymentModalLabel" aria-hidden="true"
     data-submit-url="{{ route('listing.payment.submit') }}"
     data-qr-src="{{ asset('assets/images/premium-payment-qr.png') }}"
     data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title" id="listingPaymentModalLabel">
            <i class="fa-solid fa-credit-card me-2 text-primary"></i>
            Complete Payment
          </h5>
          <p class="text-secondary small mb-0" id="listingPaymentSubtitle">Scan &amp; pay, then upload the screenshot for admin verification.</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="listing-pay-layout">
          <aside>
            <div class="listing-pay-qr-card">
              <div class="listing-pay-qr-head"><i class="fa-solid fa-qrcode"></i><span>Scan &amp; Pay via UPI</span></div>
              <img src="{{ asset('assets/images/premium-payment-qr.png') }}" alt="UPI payment QR code">
              <p class="listing-pay-payee">ANNUVEDANT ELECTRONICS OPC PRIVATE LIMITED</p>
              <p class="listing-pay-upi"><strong>UPI ID:</strong> yespay.bizsbiz240983@yesbankltd</p>
              <p class="listing-pay-hint">Scan with PhonePe, Google Pay, Paytm, or any UPI app</p>
            </div>
          </aside>

          <div>
            <div class="listing-pay-steps">
              <div class="listing-pay-step"><span class="listing-pay-step-no">1</span><span>Scan QR &amp; pay</span></div>
              <div class="listing-pay-step"><span class="listing-pay-step-no">2</span><span>Upload screenshot</span></div>
              <div class="listing-pay-step"><span class="listing-pay-step-no">3</span><span>Admin verifies</span></div>
            </div>

            <div class="listing-pay-amount mb-3 d-none" id="listingPaymentAmountWrap">
              <span>Amount to pay</span>
              <span id="listingPaymentAmount">₹0.00</span>
            </div>

            <form id="listingPaymentForm" enctype="multipart/form-data" novalidate>
              <input type="hidden" name="listing_type" id="listingPaymentType" value="">
              <input type="hidden" name="listing_id" id="listingPaymentId" value="">

              <div class="mb-3">
                <label class="form-label fw-semibold">Payment screenshot <span class="text-danger">*</span></label>
                <label class="listing-pay-drop" for="listingPaymentScreenshot">
                  <i class="fa-solid fa-cloud-arrow-up"></i>
                  <span class="fw-semibold">Click to upload payment screenshot</span>
                  <span class="small text-secondary">JPG, PNG, WEBP — max 5 MB</span>
                  <span class="small fw-semibold d-none" id="listingPaymentFileName"></span>
                </label>
                <input type="file" class="visually-hidden" id="listingPaymentScreenshot" name="screenshot" accept="image/*" required>
              </div>

              <div id="listingPaymentPreview" class="listing-pay-preview text-center d-none mb-3">
                <img src="" alt="Payment screenshot preview" id="listingPaymentPreviewImage">
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Transaction reference <span class="text-muted fw-normal">(optional)</span></label>
                <input type="text" class="form-control" name="transaction_reference" id="listingPaymentReference" maxlength="120" placeholder="UPI ref / transaction ID">
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Note <span class="text-muted fw-normal">(optional)</span></label>
                <textarea class="form-control" name="user_note" id="listingPaymentUserNote" rows="2" maxlength="1000" placeholder="Any extra payment details for admin"></textarea>
              </div>

              <div id="listingPaymentAlert" class="alert d-none" role="alert"></div>

              <button type="submit" class="btn btn-primary w-100" id="listingPaymentSubmitBtn">
                <i class="fa-solid fa-paper-plane me-1"></i> Submit Payment Proof
              </button>
              <p class="small text-secondary mt-2 mb-0">Your ad/offer stays pending until admin verifies this payment.</p>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/listing-payment.js') }}?v={{ now()->timestamp }}"></script>
@endpush
