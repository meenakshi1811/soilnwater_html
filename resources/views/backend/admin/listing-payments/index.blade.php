@extends('backend.layouts.app')

@section('title', 'Ad & Offer Payments')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Payments</p>
            <h2 class="admin-title mb-1">Ad &amp; Offer Payments</h2>
            <p class="mb-0 text-secondary">Review payment proofs submitted for paid ads and offers, then verify to activate them.</p>
        </div>
    </div>

    <div class="chart-card">
        <div class="table-responsive">
            <table id="listingPaymentsTable" class="table table-bordered align-middle w-100">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>Title</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Amount</th>
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
<script src="{{ asset('assets/js/admin-listing-payments.js') }}?v={{ now()->timestamp }}"></script>
@endpush
