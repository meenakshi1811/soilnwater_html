@extends('frontend.layouts.app')

@section('meta_title', $config['meta_title'])
@section('meta_description', $config['meta_description'])

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/premium-page.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
@php
  $colorClass = 'type-' . $config['color'];
@endphp

<div class="premium-page">
  <section class="premium-hero">
    <div class="premium-hero-inner">
      <div class="premium-brand-row">
        <img src="{{ asset('assets/images/logo_soilnwater.webp') }}" alt="SoilnWater">
        <div class="premium-type-switch">
          @foreach($allTypes as $typeKey => $typeConfig)
            <a
              href="{{ route('frontend.premium.show', $typeKey) }}"
              class="{{ $type === $typeKey ? 'is-active ' . 'type-' . $typeConfig['color'] : '' }}"
            >
              <i class="fa-solid {{ $typeConfig['icon'] }}"></i>
              {{ $typeConfig['label'] }}
            </a>
          @endforeach
        </div>
      </div>

      <div class="premium-hero-grid">
        <div class="premium-hero-copy">
          <h1>
            <span class="accent-navy">PREMIUM PROFILES.</span>
            <span class="accent-green"> MORE VISIBILITY.</span>
            <span class="accent-navy"> MORE BUSINESS.</span>
          </h1>
          <p class="premium-hero-subtitle">For Vendors, Consultants &amp; Service Providers</p>

          <div class="premium-audience-pills">
            @foreach($allTypes as $typeKey => $typeConfig)
              <span class="premium-audience-pill type-{{ $typeConfig['color'] }} {{ $type === $typeKey ? 'is-active' : '' }}">
                <i class="fa-solid {{ $typeConfig['icon'] }}"></i>
                {{ $typeConfig['tagline'] }}
              </span>
            @endforeach
          </div>

          <ul class="premium-hero-points list-unstyled mb-0">
            <li>
              <i class="fa-solid fa-circle-check"></i>
              <span><strong>Choose the right listing that grows your business.</strong> Start with FREE. Grow with PREMIUM.</span>
            </li>
            <li>
              <i class="fa-solid fa-circle-check"></i>
              <span>Build a professional {{ $config['profile_label'] }} and reach thousands of customers across India.</span>
            </li>
            <li>
              <i class="fa-solid fa-circle-check"></i>
              <span>Upgrade to premium for more visibility, more enquiries, and a trusted premium badge.</span>
            </li>
          </ul>
        </div>

        <div class="premium-device-card">
          <div class="premium-device-mock">
            <div class="premium-device-banner"></div>
            <div class="premium-device-body">
              <div class="premium-device-logo">
                <i class="fa-solid {{ $config['icon'] }}"></i>
              </div>
              <h3>Your {{ $config['singular'] }} Profile</h3>
              <p>Grow Your Presence. Grow Your Business.</p>
              <div class="premium-device-tags">
                <span>Home</span>
                <span>Products / Services</span>
                <span>About</span>
                <span>Contact</span>
              </div>
              <div class="premium-device-actions">
                <span class="primary">View Profile</span>
                <span class="secondary">Contact Us</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="premium-section">
    <div class="premium-compare-head">
      <h2>Free vs Premium Membership</h2>
      <p>Compare what you get today and what premium unlocks for your {{ strtolower($config['singular']) }} business.</p>
    </div>

    <div class="premium-compare-grid">
      <article class="premium-tier-card free">
        <div class="tier-head">
          <h3>FREE</h3>
          <p>Free listing features to get started</p>
        </div>
        <ul>
          @foreach($config['free_features'] as $feature)
            <li>
              <i class="fa-solid fa-check"></i>
              <span>{{ $feature }}</span>
            </li>
          @endforeach
        </ul>
      </article>

      <div class="premium-compare-vs">VS</div>

      <article class="premium-tier-card premium">
        <div class="tier-head">
          <h3>PREMIUM</h3>
          <p>Membership benefits that drive growth</p>
        </div>
        <ul>
          @foreach($config['premium_features'] as $feature)
            <li>
              <i class="fa-solid fa-crown"></i>
              <span>{{ $feature }}</span>
            </li>
          @endforeach
        </ul>
      </article>
    </div>
  </section>

  <section class="premium-section pt-0">
    <div class="premium-upgrade-band">
      <h2>Upgrade to Premium &amp; Take Your Business to the Next Level!</h2>

      <div class="premium-benefit-icons">
        <div class="premium-benefit-icon">
          <i class="fa-solid fa-chart-line"></i>
          <span>More Visibility</span>
        </div>
        <div class="premium-benefit-icon">
          <i class="fa-solid fa-envelope-open-text"></i>
          <span>More Enquiries</span>
        </div>
        <div class="premium-benefit-icon">
          <i class="fa-solid fa-shield-heart"></i>
          <span>More Trust</span>
        </div>
        <div class="premium-benefit-icon">
          <i class="fa-solid fa-briefcase"></i>
          <span>More Business</span>
        </div>
      </div>

      <div class="premium-cta-row">
        <a href="{{ route('login') }}" class="premium-btn premium-btn-free">FREE TO START</a>
        <button type="button" class="premium-btn premium-btn-premium" data-bs-toggle="modal" data-bs-target="#premiumQrModal">
          <i class="fa-solid fa-crown"></i>
          CHOOSE PREMIUM. CHOOSE GROWTH.
        </button>
      </div>
    </div>

    <div class="premium-footer-band">
      <div>
        <h3>Join thousands of businesses already growing on SoilnWater</h3>
        <p>Create your professional profile today and start reaching more customers.</p>
      </div>
      <ul class="premium-footer-contact">
        <li><i class="fa-solid fa-globe"></i> www.soilnwater.in</li>
        <li><i class="fa-solid fa-envelope"></i> support@soilnwater.in</li>
        <li><i class="fa-solid fa-phone"></i> +91 7055533011</li>
      </ul>
      <div class="premium-footer-qr">
        <img src="{{ asset('assets/images/premium-payment-qr.png') }}" alt="Yes Bank UPI payment QR code for ANNUVEDANT ELECTRONICS OPC PRIVATE LIMITED">
        <small>SCAN TO PAY VIA UPI</small>
      </div>
    </div>
  </section>
</div>

<div class="modal fade premium-qr-modal" id="premiumQrModal" tabindex="-1" aria-labelledby="premiumQrModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable premium-qr-modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title" id="premiumQrModalLabel">
            <i class="fa-solid fa-crown me-2 text-warning"></i>
            Get Premium – {{ $config['singular'] }}
          </h5>
          <p class="premium-modal-subtitle mb-0">Complete payment and upload proof for admin verification</p>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <div class="premium-modal-layout">
          <aside class="premium-modal-pay-panel">
            <div class="premium-qr-card">
              <div class="premium-qr-card-head">
                <i class="fa-solid fa-qrcode"></i>
                <span>Scan &amp; Pay via UPI</span>
              </div>
              <div class="premium-qr-image-wrap">
                <img
                  src="{{ asset('assets/images/premium-payment-qr.png') }}"
                  alt="Yes Bank UPI payment QR code for ANNUVEDANT ELECTRONICS OPC PRIVATE LIMITED"
                  class="premium-qr-image"
                >
              </div>
              <div class="premium-qr-details">
                <p class="premium-qr-payee">ANNUVEDANT ELECTRONICS OPC PRIVATE LIMITED</p>
                <p class="premium-qr-upi">
                  <span>UPI ID</span>
                  <strong>yespay.bizsbiz240983@yesbankltd</strong>
                </p>
                <p class="premium-qr-hint">Scan with PhonePe, Google Pay, Paytm, or any UPI app</p>
              </div>
            </div>
          </aside>

          <div class="premium-modal-form-panel">
            <div class="premium-payment-steps">
              <div class="premium-payment-step">
                <span class="premium-payment-step-no">1</span>
                <span>Scan QR &amp; pay</span>
              </div>
              <div class="premium-payment-step">
                <span class="premium-payment-step-no">2</span>
                <span>Upload screenshot</span>
              </div>
              <div class="premium-payment-step">
                <span class="premium-payment-step-no">3</span>
                <span>Admin verifies</span>
              </div>
            </div>

            @if(($paymentState['mode'] ?? '') === 'login_required')
              <div class="alert alert-info mb-0">
                Please <a href="{{ route('login') }}">login</a> with your {{ strtolower($config['singular']) }} account to confirm payment.
              </div>
            @elseif(($paymentState['mode'] ?? '') === 'wrong_account')
              <div class="alert alert-warning mb-0">
                This premium page is for {{ strtolower($config['label']) }}. Please login with the matching business account to submit payment proof.
              </div>
            @elseif(($paymentState['mode'] ?? '') === 'already_premium')
              <div class="alert alert-success mb-0">
                <i class="fa-solid fa-crown me-1"></i> Your profile is already premium. Thank you!
              </div>
            @elseif(($paymentState['mode'] ?? '') === 'pending')
              <div class="alert alert-warning mb-0" id="premiumPaymentStatus">
                <i class="fa-solid fa-clock me-1"></i>
                Your payment proof is under review.
                @if(!empty($paymentState['submitted_at']))
                  Submitted {{ $paymentState['submitted_at']->diffForHumans() }}.
                @endif
              </div>
            @else
              @if(!empty($paymentState['last_rejected_note']))
                <div class="alert alert-danger">
                  Previous submission was declined: {{ $paymentState['last_rejected_note'] }}
                </div>
              @endif

              <form id="premiumPaymentForm" enctype="multipart/form-data" novalidate>
                <div class="mb-3">
                  <label for="premiumPaymentScreenshot" class="form-label fw-semibold">Payment screenshot <span class="text-danger">*</span></label>
                  <label class="premium-file-drop" for="premiumPaymentScreenshot">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <span class="premium-file-drop-title">Click to upload payment screenshot</span>
                    <span class="premium-file-drop-hint">JPG, PNG, WEBP — max 5 MB</span>
                    <span class="premium-file-drop-name d-none" id="premiumFileName"></span>
                  </label>
                  <input type="file" class="visually-hidden" id="premiumPaymentScreenshot" name="screenshot" accept="image/*" required>
                </div>

                <div id="premiumPaymentPreview" class="premium-payment-upload-preview d-none mb-3">
                  <img src="" alt="Payment screenshot preview" id="premiumPaymentPreviewImage">
                </div>

                <div class="mb-3">
                  <label for="premiumTransactionReference" class="form-label fw-semibold">Transaction reference <span class="text-muted fw-normal">(optional)</span></label>
                  <input type="text" class="form-control" id="premiumTransactionReference" name="transaction_reference" maxlength="120" placeholder="UPI ref / transaction ID">
                </div>

                <div class="mb-3">
                  <label for="premiumUserNote" class="form-label fw-semibold">Note <span class="text-muted fw-normal">(optional)</span></label>
                  <textarea class="form-control" id="premiumUserNote" name="user_note" rows="2" maxlength="1000" placeholder="Any extra payment details for admin"></textarea>
                </div>

                <div id="premiumPaymentAlert" class="alert d-none" role="alert"></div>

                <button type="submit" class="btn btn-primary w-100 premium-payment-submit-btn" id="premiumPaymentSubmitBtn">
                  <i class="fa-solid fa-paper-plane me-1"></i> Submit Payment Proof
                </button>
              </form>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
  const form = document.getElementById('premiumPaymentForm');
  if (!form) {
    return;
  }

  const screenshotInput = document.getElementById('premiumPaymentScreenshot');
  const previewWrap = document.getElementById('premiumPaymentPreview');
  const previewImage = document.getElementById('premiumPaymentPreviewImage');
  const fileNameLabel = document.getElementById('premiumFileName');
  const alertBox = document.getElementById('premiumPaymentAlert');
  const submitBtn = document.getElementById('premiumPaymentSubmitBtn');
  const submitUrl = @json(route('frontend.premium.payment.submit', $type));
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  screenshotInput?.addEventListener('change', function () {
    const file = screenshotInput.files?.[0];
    if (!file) {
      previewWrap?.classList.add('d-none');
      fileNameLabel?.classList.add('d-none');
      return;
    }

    previewImage.src = URL.createObjectURL(file);
    previewWrap?.classList.remove('d-none');
    if (fileNameLabel) {
      fileNameLabel.textContent = file.name;
      fileNameLabel.classList.remove('d-none');
    }
  });

  form.addEventListener('submit', async function (event) {
    event.preventDefault();

    if (!screenshotInput?.files?.length) {
      showAlert('Please upload a payment screenshot.', 'danger');
      return;
    }

    const formData = new FormData(form);
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Submitting...';
    hideAlert();

    try {
      const response = await fetch(submitUrl, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json',
        },
        body: formData,
      });

      const payload = await response.json();

      if (!response.ok) {
        throw new Error(payload.message || 'Unable to submit payment proof.');
      }

      showAlert(payload.message, 'success');
      form.replaceWith(createPendingMessage(payload.message));
    } catch (error) {
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> Submit Payment Proof';
      showAlert(error.message || 'Something went wrong. Please try again.', 'danger');
    }
  });

  function showAlert(message, type) {
    if (!alertBox) {
      return;
    }
    alertBox.textContent = message;
    alertBox.className = 'alert alert-' + type;
    alertBox.classList.remove('d-none');
  }

  function hideAlert() {
    alertBox?.classList.add('d-none');
  }

  function createPendingMessage(message) {
    const wrapper = document.createElement('div');
    wrapper.className = 'alert alert-warning mb-0';
    wrapper.id = 'premiumPaymentStatus';
    wrapper.innerHTML = '<i class="fa-solid fa-clock me-1"></i> ' + message;
    return wrapper;
  }
})();
</script>
@endpush
