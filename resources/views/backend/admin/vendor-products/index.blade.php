@extends('backend.layouts.app')

@section('title', 'Vendor Product Approvals')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@section('content')
<div class="admin-panel ems-page">
    <h2 class="admin-title mb-3">Vendor Product Approvals</h2>

    <div class="chart-card p-3 table-responsive">
        <div class="d-flex justify-content-end mb-3">
            <select id="statusFilter" class="form-select" style="max-width:220px">
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>

        <table id="vendorProductsTable" class="table table-bordered align-middle w-100">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="{{ asset('assets/js/admin-vendor-products.js') }}?v={{ now()->timestamp }}"></script>
@endpush
