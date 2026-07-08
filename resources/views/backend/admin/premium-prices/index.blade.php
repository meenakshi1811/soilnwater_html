@extends('backend.layouts.app')

@section('title', 'Premium Prices')

@push('styles')
<style>
    .premium-price-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1.25rem;
    }

    @media (max-width: 991.98px) {
        .premium-price-grid {
            grid-template-columns: 1fr;
        }
    }

    .premium-price-card {
        position: relative;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        height: 100%;
    }

    .premium-price-card::before {
        content: "";
        position: absolute;
        inset: 0 0 auto;
        height: 4px;
        background: var(--premium-accent, #0d6efd);
    }

    .premium-price-card[data-type="vendor"] {
        --premium-accent: #15803d;
    }

    .premium-price-card[data-type="service"] {
        --premium-accent: #ea580c;
    }

    .premium-price-card[data-type="consultant"] {
        --premium-accent: #2563eb;
    }

    .premium-price-card-body {
        padding: 1.35rem 1.35rem 1.2rem;
    }

    .premium-price-card-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.1rem;
    }

    .premium-price-icon {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 0.85rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        background: var(--premium-accent);
        flex-shrink: 0;
        font-size: 1.05rem;
    }

    .premium-price-title {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 700;
        color: #0f172a;
    }

    .premium-price-desc {
        margin: 0.2rem 0 0;
        color: #64748b;
        font-size: 0.82rem;
        line-height: 1.4;
    }

    .premium-price-current {
        display: flex;
        align-items: baseline;
        gap: 0.35rem;
        margin-bottom: 1.15rem;
        padding: 0.9rem 1rem;
        border-radius: 0.85rem;
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid #e2e8f0;
    }

    .premium-price-current-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        font-weight: 600;
        width: 100%;
        margin-bottom: 0.15rem;
    }

    .premium-price-current-amount {
        font-size: 1.75rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1;
    }

    .premium-price-current-period {
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 500;
    }

    .premium-price-card .form-label {
        font-size: 0.82rem;
        font-weight: 600;
        color: #334155;
    }

    .premium-price-card .input-group-text {
        background: #f8fafc;
        border-color: #dbe3ee;
        font-weight: 700;
        color: #0f172a;
    }

    .premium-price-card .form-control {
        border-color: #dbe3ee;
    }

    .premium-price-card .form-check {
        margin: 0;
        min-height: auto;
        padding-left: 2.4rem;
        display: flex;
        align-items: center;
        min-height: 38px;
    }

    .premium-price-card .form-switch .form-check-input {
        width: 2.4rem;
        height: 1.25rem;
        margin-left: -2.4rem;
        cursor: pointer;
    }

    .premium-price-card .form-check-label {
        font-size: 0.85rem;
        color: #475569;
        cursor: pointer;
    }

    .premium-price-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 1rem;
    }

    .premium-price-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 0.35rem 0.6rem;
        border-radius: 999px;
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }

    .premium-price-badge.is-inactive {
        background: #fef2f2;
        color: #b91c1c;
        border-color: #fecaca;
    }
</style>
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Admin Portal</p>
            <h2 class="admin-title mb-1">Premium Prices</h2>
            <p class="mb-0 text-secondary">Set and update premium membership prices for vendors, service providers, and consultants. These amounts show on the Get Premium payment screens.</p>
        </div>
        <a href="{{ route('admin.premium-payments.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-credit-card me-1"></i> Premium Payments
        </a>
    </div>

    <div id="premiumPriceAlert" class="alert d-none" role="alert"></div>

    <div class="premium-price-grid">
        @foreach($prices as $price)
            @php $meta = $price->meta; @endphp
            <div class="premium-price-card" data-type="{{ $price->profile_type }}" data-price-id="{{ $price->id }}">
                <div class="premium-price-card-body">
                    <div class="premium-price-card-head">
                        <div class="d-flex align-items-start gap-3">
                            <span class="premium-price-icon">
                                <i class="fa-solid {{ $meta['icon'] }}"></i>
                            </span>
                            <div>
                                <h3 class="premium-price-title">{{ $meta['singular'] }}</h3>
                                <p class="premium-price-desc">{{ $meta['description'] }}</p>
                            </div>
                        </div>
                        <span class="premium-price-badge {{ $price->is_active ? '' : 'is-inactive' }}" data-role="status-badge">
                            <i class="fa-solid {{ $price->is_active ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>
                            <span data-role="status-text">{{ $price->is_active ? 'Active' : 'Inactive' }}</span>
                        </span>
                    </div>

                    <div class="premium-price-current">
                        <div class="w-100">
                            <div class="premium-price-current-label">Current price</div>
                            <div class="d-flex align-items-baseline gap-2">
                                <span class="premium-price-current-amount" data-role="display-amount">{{ $price->formatted_amount }}</span>
                                <span class="premium-price-current-period">/ membership</span>
                            </div>
                        </div>
                    </div>

                    <form class="js-premium-price-form" method="POST" action="{{ route('admin.premium-prices.update', $price) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="is_active" value="0">
                        <div class="row g-3 align-items-end">
                            <div class="col-7">
                                <label class="form-label" for="premiumAmount{{ $price->id }}">Membership amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="form-control"
                                        id="premiumAmount{{ $price->id }}"
                                        name="amount"
                                        value="{{ number_format((float) $price->amount, 2, '.', '') }}"
                                        required
                                    >
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="form-check form-switch">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        role="switch"
                                        id="premiumActive{{ $price->id }}"
                                        name="is_active"
                                        value="1"
                                        {{ $price->is_active ? 'checked' : '' }}
                                    >
                                    <label class="form-check-label" for="premiumActive{{ $price->id }}">Show on frontend</label>
                                </div>
                            </div>
                        </div>
                        <div class="premium-price-actions">
                            <button type="submit" class="btn btn-primary ems-btn-primary js-premium-price-save">
                                <span class="btn-text"><i class="fa-solid fa-floppy-disk me-1"></i> Save Price</span>
                                <span class="btn-loader d-none" aria-hidden="true"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
<script>
(function ($) {
    if (!$ || !window.FormHelper) {
        return;
    }

    function showAlert(type, message) {
        var $alert = $('#premiumPriceAlert');
        $alert.removeClass('d-none alert-success alert-danger alert-warning')
            .addClass('alert-' + type)
            .text(message);
    }

    $(document).on('submit', '.js-premium-price-form', function (e) {
        e.preventDefault();

        var $form = $(this);
        var $card = $form.closest('.premium-price-card');
        var $btn = $form.find('.js-premium-price-save');
        var amount = $form.find('input[name="amount"]').val();
        var isActive = $form.find('input[name="is_active"]').is(':checked') ? 1 : 0;

        FormHelper.setButtonLoading($btn, true, 'Saving...', 'Save Price');
        FormHelper.clearFormErrors($form);

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                _method: 'PUT',
                amount: amount,
                is_active: isActive
            },
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).done(function (response) {
            var price = response.price || {};
            $card.find('[data-role="display-amount"]').text(price.formatted_amount || ('₹' + amount));

            var $badge = $card.find('[data-role="status-badge"]');
            var active = !!price.is_active;
            $badge.toggleClass('is-inactive', !active);
            $badge.find('i').attr('class', active ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-xmark');
            $badge.find('[data-role="status-text"]').text(active ? 'Active' : 'Inactive');

            showAlert('success', response.message || 'Premium price updated successfully.');
            FormHelper.showToast('success', response.message || 'Premium price updated successfully.');
        }).fail(function (xhr) {
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                FormHelper.renderFieldErrors($form, xhr.responseJSON.errors);
            }

            var message = (xhr.responseJSON && xhr.responseJSON.message)
                ? xhr.responseJSON.message
                : 'Unable to update premium price.';
            showAlert('danger', message);
            FormHelper.showToast('danger', message);
        }).always(function () {
            FormHelper.setButtonLoading($btn, false, 'Saving...', 'Save Price');
        });
    });
})(window.jQuery);
</script>
@endpush
