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
                @endphp
                <div class="col-12 col-md-6 col-xl-4">
                    <a href="{{ route('ads.create.customize.default', ['sizeType' => $sizeType]) }}" class="ads-size-card d-block text-decoration-none {{ $isPaid && ! $hasPaidAccess ? 'js-paid-size-card' : '' }}" data-size-type="{{ $sizeType }}" data-size-name="{{ $size['name'] }}" data-size-amount="{{ (float) ($size['amount'] ?? 0) }}" data-size-paid="{{ $isPaid ? '1' : '0' }}" data-size-unlocked="{{ $hasPaidAccess ? '1' : '0' }}">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <div class="fw-semibold text-dark">{{ $size['name'] }}</div>
                            <div class="d-flex gap-2">
                                @if($isPaid)
                                    <span class="badge text-bg-danger">Paid ₹{{ number_format((float) ($size['amount'] ?? 0), 2) }}</span>
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
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Unlock paid ad size</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Size: <strong id="paymentSizeName">-</strong></p>
                <p class="mb-0">Amount: <strong id="paymentSizeAmount">₹0.00</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmSizePaymentBtn">Pay now</button>
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
            if (card.dataset.sizeUnlocked === '1') {
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

    confirmBtn.addEventListener('click', async function () {
        if (!selectedSizeType) return;

        confirmBtn.disabled = true;
        try {
            const response = await fetch(`{{ url('dashboard/ads/create') }}/${encodeURIComponent(selectedSizeType)}/pay`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) throw new Error('Payment failed');
            const data = await response.json();
            if (!data.redirect_url) throw new Error('Invalid payment response');

            window.location.href = data.redirect_url;
        } catch (error) {
            alert('Payment could not be completed. Please try again.');
        } finally {
            confirmBtn.disabled = false;
        }
    });
});
</script>
@endpush
