@extends('backend.layouts.app')
@section('title', 'View Product')

@section('content')
<div class="admin-panel ems-page">
    @php
        $categoryName = $product->category?->name ?? (is_string($product->category) ? $product->category : 'N/A');
        $subcategoryName = $product->subcategory?->name ?? 'N/A';
        $status = $product->status ?? 'pending';
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="admin-title mb-0">Product Details</h2>
        <a class="btn btn-outline-secondary" href="{{ route('admin.vendor-products.index') }}">Back</a>
    </div>

    <div class="chart-card p-4">
        <h4>{{ $product->name }}</h4>
        <p class="mb-1"><strong>Category:</strong> {{ $categoryName }}</p>
        <p class="mb-1"><strong>Subcategory:</strong> {{ $subcategoryName }}</p>
        <p class="mb-3"><strong>Status:</strong> <span class="badge bg-{{ $status === 'approved' ? 'success' : ($status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($status) }}</span></p>

        <div class="d-flex gap-2">
            @if ($status !== 'approved')
                <button type="button" class="btn btn-success js-approve" data-id="{{ $product->id }}">Approve</button>
            @endif
            @if ($status !== 'rejected')
                <button type="button" class="btn btn-outline-warning js-reject" data-id="{{ $product->id }}">Reject</button>
            @endif
            <button type="button" class="btn btn-outline-danger js-delete" data-id="{{ $product->id }}">Delete</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="{{ asset('assets/js/admin-vendor-products.js') }}?v={{ now()->timestamp }}"></script>
@endpush
