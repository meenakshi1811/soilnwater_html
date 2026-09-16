@extends('backend.layouts.app')

@section('title', 'Schools & Institutes')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Institute Management</p>
            <h2 class="admin-title mb-1">Schools &amp; Institutes</h2>
            <p class="mb-0 text-secondary">Review institute registrations, approve accounts, and manage profiles.</p>
        </div>
        @include('backend.partials.create-account-button', [
            'role' => 'institute',
            'label' => 'Add School / Institute',
            'modalTitle' => 'Add School / Institute',
        ])
    </div>

    <div class="chart-card">
        <div class="table-responsive">
            <table id="institutesTable" class="table table-bordered align-middle w-100">
                <thead>
                <tr>
                    <th>Institution</th>
                    <th>Type</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>City</th>
                    <th>Status</th>
                    <th>Public page</th>
                    <th>Registered</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@include('backend.partials.create-account-modal')
@endsection

@push('styles')
@include('backend.partials.create-account-styles')
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@include('backend.partials.create-account-scripts')
<script src="{{ asset('assets/js/admin-institutes.js') }}?v={{ now()->timestamp }}"></script>
@endpush
