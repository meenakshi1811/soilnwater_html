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
                                    <span class="badge text-bg-danger">{{ $hasCategoryPricing ? 'Category Pricing' : 'Paid ₹'.number_format((float) ($size['amount'] ?? 0), 2) }}</span>
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

        <div class="d-flex justify-content-end mt-4 pt-3 border-top">
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const paidCards = document.querySelectorAll('.js-paid-size-card');
    if (!paidCards.length || typeof bootstrap === 'undefined') return;

    const paymentModalElement = document.getElementById('sizePaymentModal');
    const paymentModal = new bootstrap.Modal(paymentModalElement);
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
            paymentModal.show();
        });
    });


});
</script>
@endpush
