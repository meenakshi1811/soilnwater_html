@extends('backend.layouts.app')

@section('title', 'All Vendor Products')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h2 class="admin-title mb-0">All Vendor Products</h2>
        <a href="{{ route('admin.vendor-products.create') }}" class="btn btn-primary">Create Product</a>
    </div>

    <div class="chart-card p-3 mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label for="statusFilter" class="form-label mb-1">Status Filter</label>
                <select id="statusFilter" class="form-select">
                    <option value="">All</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
        </div>
    </div>

    <div class="chart-card p-3 table-responsive">
        <table id="vendorProductsTable" class="table table-bordered align-middle w-100" data-source-url="{{ route('admin.vendor-products.all.data') }}">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Vendor Name</th>
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/js/admin-vendor-products.js') }}?v={{ now()->timestamp }}"></script>
@endpush
