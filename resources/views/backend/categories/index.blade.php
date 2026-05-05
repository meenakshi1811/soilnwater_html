@extends('backend.layouts.app')

@section('title', 'Categories')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Category Management</p>
            <h2 class="admin-title mb-1">Categories</h2>
            <p class="mb-0 text-secondary">Create categories, create sub categories, and assign modules. Sub categories automatically inherit modules from the parent category.</p>
        </div>
        <button type="button" class="btn btn-primary ems-btn-primary" id="openCategoryModalBtn">
            <i class="fa-solid fa-folder-plus me-2"></i> Add Category
        </button>
    </div>

    <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h5 class="mb-0">Category listing</h5>
        </div>
        <div id="categoryAlert" class="alert d-none" role="alert"></div>
        <div class="table-responsive">
            <table id="categoriesTable" class="table table-bordered align-middle w-100">
                <thead>
                <tr>
                    <th>Category</th>
                    <th>Sub Category</th>
                    <th>Modules</th>
                    <th>Ads Price</th>
                    <th>Sub Categories</th>
                    <th>Created</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content ems-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalTitle">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="categoryForm" method="POST" action="{{ route('admin.categories.store') }}" novalidate>
                @csrf
                <input type="hidden" id="categoryId" name="category_id" value="">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Category name</label>
                            <input type="text" name="name" id="categoryName" class="form-control" placeholder="Enter category name">
                            <small class="text-secondary">Use this field when creating/updating a top-level category.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sub category name</label>
                            <input type="text" id="subcategoryName" class="form-control" placeholder="Enter sub category name">
                            <small class="text-secondary">Use this field when creating/updating a sub category.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Parent category (optional)</label>
                            <select name="parent_id" id="categoryParentId" class="form-select">
                                <option value="">None (Top-level category)</option>
                            </select>
                            <small class="text-secondary">If selected, this will be created as a sub category.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Assign modules</label>
                            <div class="row g-2" id="categoryModulesWrap">
                                @foreach($modules as $slug => $label)
                                    <div class="col-md-4">
                                        <div class="form-check border rounded p-2">
                                            <input class="form-check-input js-module-check" type="checkbox" value="{{ $slug }}" id="category_module_{{ $slug }}">
                                            <label class="form-check-label" for="category_module_{{ $slug }}">{{ $label }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <small class="text-secondary d-block mt-1" id="modulesHelpText">Select one or more modules for this category.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ads price</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" name="ads_price" id="categoryAdsPrice" class="form-control" value="0" min="0" step="0.01">
                            </div>
                            <small class="text-secondary d-block mt-1" id="adsPriceHelpText">Pricing is only used for Ads module categories. Set 0.00 to keep it Free.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="categorySubmitBtn" class="btn btn-primary ems-btn-primary">
                        <span class="btn-text">Save Category</span>
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
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
<script src="{{ asset('assets/js/admin-categories.js') }}?v={{ now()->timestamp }}"></script>
@endpush
