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

    #myOffersTable th,
    #myOffersTable td {
        white-space: normal;
        word-break: break-word;
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

<div class="modal fade" id="myOfferModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content ems-modal">
            <div class="modal-header">
                <h5 class="modal-title">Edit Offer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="myOfferForm" method="POST" novalidate>
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Offer Title</label>
                            <input type="text" name="title" id="myOfferTitle" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Discount Tag</label>
                            <input type="text" name="discount_tag" id="myOfferDiscountTag" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Coupon Code</label>
                            <input type="text" name="coupon_code" id="myOfferCouponCode" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Valid Until</label>
                            <input type="date" name="valid_until" id="myOfferValidUntil" class="form-control">
                        </div>
                        <div class="col-md-6 {{ $canApproveOffer ? '' : 'd-none' }}">
                            <label class="form-label">Status</label>
                            <select name="status" id="myOfferStatus" class="form-select" {{ $canApproveOffer ? '' : 'disabled' }}>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Banner Image (optional)</label>
                            <input type="file" name="banner_image" id="myOfferBannerImage" class="form-control" accept="image/png,image/jpeg,image/webp">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Short Description</label>
                            <textarea name="short_description" id="myOfferShortDescription" class="form-control" rows="3" maxlength="300"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="myOfferSubmitBtn" class="btn btn-primary ems-btn-primary">
                        <span class="btn-text">Update Offer</span>
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
<script src="{{ asset('assets/js/offer-and-discount.js') }}?v={{ now()->timestamp }}"></script>
@endpush
