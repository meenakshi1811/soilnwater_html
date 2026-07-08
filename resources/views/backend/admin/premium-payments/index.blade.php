@extends('backend.layouts.app')

@section('title', 'Payments')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Premium Management</p>
            <h2 class="admin-title mb-1">Payments</h2>
            <p class="mb-0 text-secondary">Review premium payment proofs submitted by vendors, consultants, and service providers.</p>
        </div>
        <a href="{{ route('admin.premium-prices.index') }}" class="btn btn-outline-primary">
            <i class="fa-solid fa-tags me-1"></i> Manage Premium Prices
        </a>
    </div>

    <div class="chart-card">
        <div class="table-responsive">
            <table id="premiumPaymentsTable" class="table table-bordered align-middle w-100">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Profile Type</th>
                    <th>Amount</th>
                    <th>Profile Name</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Transaction Ref.</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Reviewed</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="{{ asset('assets/js/admin-premium-payments.js') }}?v={{ now()->timestamp }}"></script>
@endpush
