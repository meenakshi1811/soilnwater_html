@extends('backend.layouts.app')

@section('title', 'My Offers & Discounts')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<style>
    .offers-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    #myOffersTable {
        width: 100% !important;
    }

    #myOffersTable th {
        white-space: nowrap;
        vertical-align: middle;
    }

    #myOffersTable td {
        white-space: nowrap;
        vertical-align: middle;
    }

    #myOffersTable td.offer-col-wrap {
        white-space: normal;
        word-break: break-word;
        min-width: 220px;
    }

    #myOffersTable td:last-child {
        white-space: nowrap;
    }
</style>
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Offer Management</p>
            <h2 class="admin-title mb-1">{{ $isAdminView ? 'Offers & Discounts' : 'My Offers & Discounts' }}</h2>
            <p class="mb-0 text-secondary">
                {{ $isAdminView ? 'As admin you can view, edit, delete, and change status for all offers.' : 'View, edit, and delete all offers posted by your account.' }}
            </p>
        </div>
    </div>

    <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h5 class="mb-0">Offer listing</h5>
            @if($canCreateOffer)
                <a href="{{ route('post-offer') }}" class="btn btn-primary ems-btn-primary">
                    <i class="fa-solid fa-plus me-2"></i>Post New Offer
                </a>
            @endif
        </div>

        <div id="myOfferAlert" class="alert d-none" role="alert"></div>

        <div class="row g-2 mb-3">
            <div class="col-12 col-md-4">
                <label for="offersFilterCategory" class="form-label mb-1">Category</label>
                <select id="offersFilterCategory" class="form-select">
                    <option value="">All categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label for="offersFilterSubcategory" class="form-label mb-1">Subcategory</label>
                <select id="offersFilterSubcategory" class="form-select" disabled>
                    <option value="">All subcategories</option>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label for="offersFilterValidity" class="form-label mb-1">Validity</label>
                <select id="offersFilterValidity" class="form-select">
                    <option value="">All</option>
                    <option value="valid">Valid (Not expired)</option>
                    <option value="expired">Expired</option>
                    <option value="expires_today">Expires today</option>
                    <option value="no_expiry">No expiry</option>
                </select>
            </div>
        </div>

        <div class="table-responsive offers-table-wrap">
            <table
                id="myOffersTable"
                class="table table-bordered align-middle w-100"
                data-url="{{ route('offers.data') }}"
                data-show-url-base="{{ route('offers.index') }}"
                data-update-url-base="{{ route('offers.index') }}"
                data-update-status-url-template="{{ route('offers.update-offer-status', ['offer' => '__ID__']) }}"
                data-delete-url-base="{{ route('offers.index') }}"
                data-can-edit="{{ $canEditOffer ? '1' : '0' }}"
                data-can-delete="{{ $canDeleteOffer ? '1' : '0' }}"
                data-can-approve="{{ $canApproveOffer ? '1' : '0' }}"
                data-categories='@json($categoriesForFilter)'
            >
                <thead>
                <tr>
                    <th>Title</th>
                    <th>Created By</th>
                    <th>Banner</th>
                    <th>Discount</th>
                    <th>Coupon</th>
                    <th>Category</th>
                    <th>Sub Category</th>
                    <th>Valid Until</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
<script src="{{ asset('assets/js/offer-and-discount.js') }}?v={{ now()->timestamp }}"></script>
@endpush
