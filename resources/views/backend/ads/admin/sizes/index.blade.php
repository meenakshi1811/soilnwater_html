@extends('backend.layouts.app')

@section('title', 'Ad Sizes')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
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
                        <div class="col-md-4">
                            <label class="form-label">Width</label>
                            <input type="number" min="1" max="5000" name="width" id="adSizeWidth" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Height</label>
                            <input type="number" min="1" max="5000" name="height" id="adSizeHeight" class="form-control" required>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_paid" id="adSizeIsPaid" value="1">
                                <label class="form-check-label" for="adSizeIsPaid">Module Price</label>
                            </div>
                        </div>
                        <div class="col-12 d-none" id="modulePricingFieldsSection">
                            <hr class="my-1">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                <label class="form-label mb-0">Module Pricing</label>
                                <small class="text-secondary">Assign prices for one or multiple modules.</small>
                            </div>
                            <div class="row g-3" id="modulePricingFieldsWrap">
                                @foreach($modules as $moduleKey => $moduleLabel)
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
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="admin_only" id="adSizeAdminOnly" value="1">
                                <label class="form-check-label" for="adSizeAdminOnly">Admin only</label>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="is_paid" id="adSizeIsPaid" value="1">
                                <label class="form-check-label" for="adSizeIsPaid">Paid size</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label mb-1">Category pricing mode</label>
                            <div class="d-flex flex-wrap gap-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="categoryPriceModeAll">
                                    <label class="form-check-label" for="categoryPriceModeAll">Apply one price for all categories</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 d-none" id="applyAllCategoriesPriceWrap">
                            <label class="form-label">Price for all categories</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" min="0" step="0.01" id="applyAllCategoriesPriceInput" class="form-control" placeholder="0.00">
                            </div>
                            <small class="text-secondary">When enabled, this value is copied to every category price.</small>
                        </div>
                        <div class="col-12" id="categoryPricingFieldsSection">
                            <hr class="my-1">
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