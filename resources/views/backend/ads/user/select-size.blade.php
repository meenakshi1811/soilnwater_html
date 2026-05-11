@extends('backend.layouts.app')

@section('title', 'Select Ad Size')

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Ads</p>
            <h2 class="admin-title mb-1">Select Ad Size</h2>
            <p class="mb-0 text-secondary">Choose the size that best fits where you want to run your ad.</p>
        </div>
    </div>

    @php
        $maxWidth = max(array_column($sizes, 'w'));
    @endphp

    <div class="chart-card">
        <div class="d-flex justify-content-end mb-3">
            <button type="button" class="btn btn-outline-primary px-4" id="openCustomizationRequestBtn">
                <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Request Customization
            </button>
        </div>
        <div class="row g-3">
            @foreach($sizes as $sizeType => $size)
                @php
                    $isPaid = (bool) ($size['is_paid'] ?? false);
                    $hasPaidAccess = (bool) ($paidSizeAccess[$sizeType] ?? false);
                    $hasCategoryPricing = ! empty($size['category_prices'] ?? []);
                @endphp
                <div class="col-12 col-md-6 col-xl-4">
                    <a href="{{ route('ads.create.customize.default', ['sizeType' => $sizeType]) }}" class="ads-size-card d-block text-decoration-none {{ $isPaid ? 'js-paid-size-card' : '' }}" data-size-type="{{ $sizeType }}" data-size-name="{{ $size['name'] }}" data-size-amount="{{ (float) ($size['amount'] ?? 0) }}" data-size-paid="{{ $isPaid ? '1' : '0' }}" data-category-pricing="{{ $hasCategoryPricing ? '1' : '0' }}" data-size-unlocked="{{ $hasPaidAccess ? '1' : '0' }}">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <div class="fw-semibold text-dark">{{ $size['name'] }}</div>
                            <div class="d-flex gap-2">
                                @if($isPaid)
                                    @if(! $hasCategoryPricing)
                                        <span class="badge text-bg-danger">{{ 'Paid ₹'.number_format((float) ($size['amount'] ?? 0), 2) }}</span>
                                @endif
                                @endif
                                @if(($size['admin_only'] ?? false) === true)
                                    <span class="badge text-bg-warning">Admin Placement</span>
                                @endif
                            </div>
                        </div>
                        @php $previewScale = 0.7 + (0.3 * ($size['w'] / $maxWidth)); @endphp
                        <div class="ads-size-shape" style="aspect-ratio: {{ $size['ratio'] }}; width: min(100%, {{ round($previewScale * 100, 2) }}%); margin-inline: auto;">
                            <div class="ads-size-shape-inner">
                                <span class="ads-size-dim">{{ $size['w'] }}×{{ $size['h'] }}</span>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="text-secondary small">Aspect ratio {{ $size['ratio'] }}</div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-end align-items-center gap-2 mt-4 pt-3 border-top">
            <a href="{{ route('ads.index') }}" class="btn btn-light px-4">Back</a>
        </div>
    </div>
</div>

<div class="modal fade" id="sizePaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title d-flex align-items-center gap-2">
                    <span class="badge rounded-pill text-bg-warning px-3 py-2"><i class="fa-solid fa-crown me-1"></i>Premium Size</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2">
                <h4 class="fw-semibold mb-3">Unlock paid ad size</h4>
                <div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid #e2e8f0;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary">Selected Size</span>
                        <strong id="paymentSizeName">-</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-secondary">Unlock Fee</span>
                        <strong class="text-dark" id="paymentSizeAmount">₹0.00</strong>
                    </div>
                </div>
                <p class="small text-secondary mt-3 mb-0">Payment is temporarily disabled. You can review the amount now and continue once payment is enabled.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary px-4" id="confirmSizePaymentBtn" disabled aria-disabled="true" title="Payment is temporarily disabled">Pay now</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="customizationRequestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content customization-request-modal">
            <div class="modal-header border-0">
                <h5 class="modal-title">Request Ad Size Customization</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="customizationRequestForm" action="{{ route('ads.request-customization') }}" method="POST">
                @csrf
                <div class="modal-body pt-0">
                    <p class="text-secondary mb-3">Select an inactive size and share your requirement details. Our admin team will contact you.</p>
                    <div class="mb-3">
                        <label for="inactiveSizeType" class="form-label fw-semibold">Select Size</label>
                        <select class="form-select" id="inactiveSizeType" name="size_type" required>
                            <option value="">— Select inactive size —</option>
                            @foreach($inactiveSizes as $sizeType => $size)
                                <option value="{{ $sizeType }}">{{ $size['name'] }} ({{ $size['w'] }}×{{ $size['h'] }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="customizationDetails" class="form-label fw-semibold">Details</label>
                        <textarea class="form-control" id="customizationDetails" name="details" rows="4" maxlength="2000" placeholder="Write your size customization requirement..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitCustomizationRequestBtn">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.customization-request-modal{border:0;border-radius:14px;box-shadow:0 20px 44px rgba(15,23,42,.18)}
.customization-request-modal .modal-header{padding:1rem 1.25rem .5rem}
.customization-request-modal .modal-body,.customization-request-modal .modal-footer{padding:1rem 1.25rem}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const paidCards = document.querySelectorAll('.js-paid-size-card');
    if (typeof bootstrap === 'undefined') return;

    const paymentModalElement = document.getElementById('sizePaymentModal');
    const paymentModal = paymentModalElement ? new bootstrap.Modal(paymentModalElement) : null;
    const sizeNameEl = document.getElementById('paymentSizeName');
    const sizeAmountEl = document.getElementById('paymentSizeAmount');
    const confirmBtn = document.getElementById('confirmSizePaymentBtn');
    let selectedSizeType = '';

    paidCards.forEach((card) => {
        card.addEventListener('click', function (event) {
            if (card.dataset.categoryPricing === '1') {
                return;
            }
            event.preventDefault();
            selectedSizeType = card.dataset.sizeType || '';
            sizeNameEl.textContent = card.dataset.sizeName || '-';
            const amount = Number(card.dataset.sizeAmount || 0);
            sizeAmountEl.textContent = `₹${amount.toFixed(2)}`;
            paymentModal?.show();
        });
    });

    const openCustomizationModalBtn = document.getElementById('openCustomizationRequestBtn');
    const customizationModalEl = document.getElementById('customizationRequestModal');
    const customizationForm = document.getElementById('customizationRequestForm');
    const customizationSubmitBtn = document.getElementById('submitCustomizationRequestBtn');
    const inactiveSizeType = document.getElementById('inactiveSizeType');
    const customizationModal = (window.bootstrap && customizationModalEl) ? new bootstrap.Modal(customizationModalEl) : null;

    function notify(type, message) {
        const normalizedType = type === 'danger' ? 'error' : type;
        if (window.FormHelper && typeof window.FormHelper.showToast === 'function') {
            window.FormHelper.showToast(normalizedType, message);
            return;
        }
        if (window.toastr && typeof window.toastr[normalizedType] === 'function') {
            window.toastr[normalizedType](message);
            return;
        }
        const fallbackToast = document.createElement('div');
        fallbackToast.className = `alert alert-${normalizedType === 'error' ? 'danger' : normalizedType} position-fixed shadow`;
        fallbackToast.style.cssText = 'top:20px;right:20px;z-index:2000;min-width:280px;';
        fallbackToast.textContent = message || 'Done';
        document.body.appendChild(fallbackToast);
        setTimeout(() => fallbackToast.remove(), 2800);
    }

    openCustomizationModalBtn?.addEventListener('click', function () {
        if (!inactiveSizeType || inactiveSizeType.options.length <= 1) {
            notify('warning', 'No inactive sizes are available for customization request.');
            return;
        }
        customizationModal?.show();
    });

    customizationForm?.addEventListener('submit', function (event) {
        event.preventDefault();
        const formData = new FormData(customizationForm);
        customizationSubmitBtn.disabled = true;
        customizationSubmitBtn.textContent = 'Submitting...';

        fetch(customizationForm.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(async (response) => {
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw data;
            }
            notify('success', data.message || 'Request submitted successfully.');
            customizationForm.reset();
            customizationModal?.hide();
        })
        .catch((error) => {
            const firstError = error?.errors ? Object.values(error.errors)[0]?.[0] : null;
            notify('error', firstError || error?.message || 'Failed to submit customization request.');
        })
        .finally(() => {
            customizationSubmitBtn.disabled = false;
            customizationSubmitBtn.textContent = 'Submit Request';
        });
    });

});
</script>
@endpush
