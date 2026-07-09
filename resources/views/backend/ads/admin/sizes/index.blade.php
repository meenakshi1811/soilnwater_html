@extends('backend.layouts.app')

@section('title', 'Ad Sizes')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<style>
    #adSizeModal .ad-size-option {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-height: 54px;
        margin: 0;
        padding: 0.75rem 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        background: #f8fafc;
    }

    #adSizeModal .ad-size-option .form-check-input {
        width: 2.5rem;
        height: 1.35rem;
        margin: 0;
        flex-shrink: 0;
        cursor: pointer;
    }

    #adSizeModal .ad-size-option .form-check-label {
        display: flex;
        flex-direction: column;
        gap: 0.1rem;
        margin: 0;
        cursor: pointer;
        line-height: 1.25;
    }

    #adSizeModal .ad-size-option .form-check-label strong {
        font-size: 0.9rem;
        font-weight: 600;
        color: #0f172a;
    }

    #adSizeModal .ad-size-option .form-check-label small {
        font-size: 0.75rem;
        color: #64748b;
    }

    #adSizeModal .ad-size-option.is-active {
        border-color: rgba(13, 110, 253, 0.35);
        background: rgba(13, 110, 253, 0.06);
    }

    #adSizeModal .ad-size-base-price-card {
        border: 1px solid #f5d08a;
        border-radius: 14px;
        background: linear-gradient(135deg, #fffbeb 0%, #fff7ed 100%);
        padding: 1rem 1.1rem;
    }

    #adSizeModal .ad-size-base-price-card .form-label {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #b45309;
    }
</style>
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Ads</p>
            <h2 class="admin-title mb-1">Ad Sizes</h2>
            <p class="mb-0 text-secondary">Manage custom ad sizes for user and admin placements.</p>
        </div>
        <button type="button" class="btn btn-primary ems-btn-primary" id="openAdSizeModalBtn">
            <i class="fa-solid fa-plus me-2"></i> Add Size
        </button>
    </div>

    <div class="chart-card">
        <div id="adSizeAlert" class="alert d-none" role="alert"></div>
        <div class="table-responsive">
            <table id="adSizesTable" class="table table-bordered align-middle w-100">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Key</th>
                        <th>Dimensions</th>
                        <th>Placement</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="adSizeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content ems-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="adSizeModalTitle">Add Ad Size</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="adSizeForm" method="POST" action="{{ route('admin.ads.sizes.store') }}" novalidate>
                @csrf
                <input type="hidden" id="adSizeId" value="">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" id="adSizeName" class="form-control" maxlength="120" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Key</label>
                            <input type="text" name="size_key" id="adSizeKey" class="form-control" maxlength="60" required>
                            <small class="text-secondary">Use lowercase letters, numbers, underscore only (example: home_banner).</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Width</label>
                            <input type="number" min="1" max="5000" name="width" id="adSizeWidth" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Height</label>
                            <input type="number" min="1" max="5000" name="height" id="adSizeHeight" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch ad-size-option" id="adSizeAdminOnlyOption">
                                <input class="form-check-input" type="checkbox" name="admin_only" id="adSizeAdminOnly" value="1">
                                <label class="form-check-label" for="adSizeAdminOnly">
                                    <strong>Admin only</strong>
                                    <small>Visible to admin placements only</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch ad-size-option" id="adSizeIsPaidOption">
                                <input class="form-check-input" type="checkbox" name="is_paid" id="adSizeIsPaid" value="1">
                                <label class="form-check-label" for="adSizeIsPaid">
                                    <strong>Paid size</strong>
                                    <small>Enable module and category pricing</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-12 d-none" id="paidSizeFieldsSection">
                            <div class="ad-size-base-price-card mb-3">
                                <label class="form-label mb-1" for="adSizeBasePrice">Base price</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" min="0" step="0.01" name="amount" id="adSizeBasePrice" class="form-control" placeholder="0.00">
                                </div>
                                <small class="text-secondary d-block mt-2">Per-day base placement price shown to users before category and module adjustments.</small>
                            </div>
                            <div class="row g-3">
                                <div class="col-12" id="modulePricingFieldsSection">
                                    <hr class="my-1">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                        <label class="form-label mb-0">Module Pricing</label>
                                        <small class="text-secondary">Assign prices for one or multiple modules.</small>
                                    </div>
                                    <div class="row g-3" id="modulePricingFieldsWrap">
                                        @php
                                            // Temporarily show only Vendors, Services, and Consultants in module pricing.
                                            // $allowedModulePricingKeys = array_keys($modules); // all modules
                                            $allowedModulePricingKeys = ['vendors', 'services', 'consultants'];
                                        @endphp
                                        @foreach($modules as $moduleKey => $moduleLabel)
                                            @if(! in_array($moduleKey, $allowedModulePricingKeys, true))
                                                {{-- Commented for now: {{ $moduleLabel }} ({{ $moduleKey }}) --}}
                                                @continue
                                            @endif
                                            <div class="col-md-6">
                                                <label class="form-label text-uppercase small mb-1">{{ $moduleLabel }}</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">₹</span>
                                                    <input type="number" min="0" step="0.01" class="form-control" name="module_prices[{{ $moduleKey }}]" placeholder="0.00">
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-12">
                                    <hr class="my-1">
                                    <div class="form-check form-switch ad-size-option" id="categoryPriceModeAllOption">
                                        <input class="form-check-input" type="checkbox" id="categoryPriceModeAll">
                                        <label class="form-check-label" for="categoryPriceModeAll">
                                            <strong>Apply one price for all categories</strong>
                                            <small>Use a single price instead of per-category amounts</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 d-none" id="applyAllCategoriesPriceWrap">
                                    <label class="form-label">Price for all categories</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" min="0" step="0.01" id="applyAllCategoriesPriceInput" class="form-control" placeholder="0.00">
                                    </div>
                                    <small class="text-secondary">When enabled, this value is copied to every category price.</small>
                                </div>
                                <div class="col-12" id="categoryPricingFieldsSection">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                        <label class="form-label mb-0">Category Pricing</label>
                                        <small class="text-secondary">Leave a category blank when this size is not priced for it.</small>
                                    </div>
                                    <div class="row g-3" id="categoryPricingFieldsWrap">
                                        @foreach($categories as $categoryId => $categoryName)
                                            <div class="col-md-6">
                                                <label class="form-label text-uppercase small mb-1">{{ $categoryName }}</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">₹</span>
                                                    <input type="number" min="0" step="0.01" class="form-control js-category-price-input" name="category_prices[{{ $categoryId }}]" placeholder="0.00">
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="adSizeSubmitBtn" class="btn btn-primary ems-btn-primary">
                        <span class="btn-text">Save Size</span>
                        <span class="btn-loader d-none" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
<script src="{{ asset('assets/js/admin-ad-sizes.js') }}?v={{ now()->timestamp }}"></script>
@endpush