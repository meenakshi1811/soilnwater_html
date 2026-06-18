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
                <div class="col-12 col-md-6 col-xl-4">
                    <a href="{{ route('ads.create.customize.default', ['sizeType' => $sizeType]) }}" class="ads-size-card d-block text-decoration-none">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <div class="fw-semibold text-dark">{{ $size['name'] }}</div>
                            <div class="d-flex gap-2">
                                @if(($size['admin_only'] ?? false) === true)
                                    <span class="badge text-bg-warning">Admin Placement</span>
                                @else
                                    <span class="badge text-bg-info">User Placement</span>
                                @endif
                                @if(($size['is_active'] ?? true) === false)
                                    <span class="badge text-bg-secondary">Inactive</span>
                                @else
                                    <span class="badge text-bg-success">Active</span>
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
                            @php $sizeMaxPrice = \App\Support\AdSizes::maxPricePerDay($size); @endphp
                            @if($sizeMaxPrice !== null)
                                <div class="ads-size-card-price">
                                    <i class="fa-solid fa-tag" aria-hidden="true"></i>
                                    Up to ₹{{ number_format($sizeMaxPrice, 2) }}/day
                                </div>
                                <div class="text-secondary small mt-1">Price may vary by module selection</div>
                            @endif
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
.ads-size-card-price {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    margin-top: .5rem;
    padding: .35rem .75rem;
    border-radius: 999px;
    font-size: .82rem;
    font-weight: 700;
    color: #b45309;
    background: #fff7ed;
    border: 1px solid #f7c793;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof bootstrap === 'undefined') return;

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
