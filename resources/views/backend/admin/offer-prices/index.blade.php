@extends('backend.layouts.app')

@section('title', 'Offer Prices')

@push('styles')
<style>
    .offer-price-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
    }

    .offer-price-apply-card {
        border: 1px solid #dbeafe;
        border-radius: 1rem;
        background: linear-gradient(135deg, #eff6ff 0%, #ffffff 55%, #f8fafc 100%);
        box-shadow: 0 12px 30px rgba(37, 99, 235, 0.08);
        padding: 1.35rem 1.4rem;
        margin-bottom: 1.35rem;
    }

    .offer-price-apply-card__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .offer-price-apply-icon {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 0.85rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #2563eb;
        color: #fff;
        flex-shrink: 0;
    }

    .offer-price-apply-title {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 700;
        color: #0f172a;
    }

    .offer-price-apply-desc {
        margin: 0.2rem 0 0;
        color: #64748b;
        font-size: 0.84rem;
        line-height: 1.45;
    }

    .offer-price-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
        margin-bottom: 1.25rem;
    }

    .offer-price-stat {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.45rem 0.75rem;
        border-radius: 999px;
        background: #fff;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .offer-price-grid {
        display: grid;
        gap: 1rem;
    }

    .offer-price-group {
        border: 1px solid #e5e7eb;
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .offer-price-group::before {
        content: "";
        display: block;
        height: 4px;
        background: linear-gradient(90deg, #2563eb, #7c3aed);
    }

    .offer-price-group-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.2rem;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }

    .offer-price-group-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
    }

    .offer-price-group-meta {
        margin: 0.15rem 0 0;
        font-size: 0.78rem;
        color: #64748b;
    }

    .offer-price-row {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(0, 0.8fr) minmax(0, 1fr) auto;
        gap: 0.85rem;
        align-items: end;
        padding: 1rem 1.2rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .offer-price-row:last-child {
        border-bottom: 0;
    }

    .offer-price-row.is-subcategory {
        background: #fcfdff;
        padding-left: 2.2rem;
    }

    .offer-price-row-label {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        min-height: 38px;
    }

    .offer-price-row-name {
        font-size: 0.92rem;
        font-weight: 600;
        color: #0f172a;
    }

    .offer-price-row-type {
        display: inline-flex;
        align-items: center;
        padding: 0.2rem 0.5rem;
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }

    .offer-price-row-type.is-sub {
        background: #f5f3ff;
        color: #6d28d9;
        border-color: #ddd6fe;
    }

    .offer-price-current {
        display: flex;
        flex-direction: column;
        gap: 0.1rem;
        min-height: 38px;
        justify-content: center;
    }

    .offer-price-current-label {
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #94a3b8;
        font-weight: 700;
    }

    .offer-price-current-value {
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
    }

    .offer-price-current-value.is-free {
        color: #047857;
    }

    .offer-price-current-period {
        font-size: 0.75rem;
        color: #64748b;
    }

    .offer-price-form .form-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 0.25rem;
    }

    .offer-price-form .input-group-text {
        background: #f8fafc;
        border-color: #dbe3ee;
        font-weight: 700;
        color: #0f172a;
    }

    .offer-price-empty {
        border: 1px dashed #cbd5e1;
        border-radius: 1rem;
        background: #f8fafc;
        padding: 2.5rem 1.5rem;
        text-align: center;
        color: #64748b;
    }

    @media (max-width: 991.98px) {
        .offer-price-row {
            grid-template-columns: 1fr;
        }

        .offer-price-row.is-subcategory {
            padding-left: 1.2rem;
        }
    }
</style>
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Admin Portal</p>
            <h2 class="admin-title mb-1">Offer Prices</h2>
            <p class="mb-0 text-secondary">Set per-day posting prices for offer categories and subcategories. Subcategory prices override the parent category when greater than zero.</p>
        </div>
        <div class="offer-price-hero-actions">
            <a href="{{ route('offers.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-tags me-1"></i> All Offers
            </a>
            <a href="{{ route('admin.listing-payments.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-credit-card me-1"></i> Offer Payments
            </a>
        </div>
    </div>

    <div id="offerPriceAlert" class="alert d-none" role="alert"></div>

    <div class="offer-price-stats">
        <span class="offer-price-stat">
            <i class="fa-solid fa-layer-group text-primary"></i>
            {{ $categories->count() }} offer {{ Str::plural('category', $categories->count()) }}
        </span>
        <span class="offer-price-stat">
            <i class="fa-solid fa-sitemap text-primary"></i>
            {{ $pricedCount }} total pricing rows
        </span>
    </div>

    <div class="offer-price-apply-card">
        <div class="offer-price-apply-card__head">
            <div class="d-flex align-items-start gap-3">
                <span class="offer-price-apply-icon">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                </span>
                <div>
                    <h3 class="offer-price-apply-title">Apply price to all offer categories</h3>
                    <p class="offer-price-apply-desc">Set the same per-day price for every offer-enabled category and subcategory at once. Use 0.00 to make all offers free.</p>
                </div>
            </div>
        </div>
        <form id="offerPriceApplyAllForm" class="offer-price-form" method="POST" action="{{ route('admin.offer-prices.apply-all') }}">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-md-4 col-lg-3">
                    <label class="form-label" for="offerApplyAllAmount">Per-day amount</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" min="0" step="0.01" class="form-control" id="offerApplyAllAmount" name="offer_price" value="0.00" required>
                    </div>
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary ems-btn-primary js-offer-price-apply-all">
                        <span class="btn-text"><i class="fa-solid fa-bolt me-1"></i> Apply to All</span>
                        <span class="btn-loader d-none" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    @if($categories->isEmpty())
        <div class="offer-price-empty">
            <i class="fa-solid fa-folder-open fa-2x mb-3 d-block text-secondary"></i>
            <h5 class="mb-2">No offer categories yet</h5>
            <p class="mb-3">Enable the <strong>Offers</strong> module on categories first, then return here to set pricing.</p>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-primary">Manage Categories</a>
        </div>
    @else
        <div class="offer-price-grid">
            @foreach($categories as $category)
                <section class="offer-price-group" data-category-group="{{ $category->id }}">
                    <div class="offer-price-group-head">
                        <div>
                            <h3 class="offer-price-group-title">{{ $category->name }}</h3>
                            <p class="offer-price-group-meta">
                                {{ \App\Services\OfferPriceService::countDescendants($category) }} nested {{ Str::plural('category', \App\Services\OfferPriceService::countDescendants($category)) }}
                                · Includes subcategories
                            </p>
                        </div>
                    </div>

                    @include('backend.admin.offer-prices.partials.category-rows', [
                        'categories' => collect([$category]),
                        'depth' => 0,
                    ])
                </section>
            @endforeach
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
<script src="{{ asset('assets/js/admin-offer-prices.js') }}?v={{ now()->timestamp }}"></script>
@endpush
