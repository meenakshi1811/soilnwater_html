@extends('backend.layouts.app')

@section('title', 'Vendor Dashboard')

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Vendor Panel</p>
            <h2 class="admin-title mb-1">Overview</h2>
            <p class="mb-0 text-secondary">Welcome, {{ $vendor->publicDisplayName() }}. Manage your branches and public storefront.</p>
        </div>
        <a href="{{ route('store.show', $vendor->slug) }}" target="_blank" class="btn btn-outline-primary">
            <i class="fa-solid fa-external-link-alt me-1"></i> View live store
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="chart-card text-center h-100">
                <p class="text-secondary small mb-1">Products</p>
                <h3 class="mb-0">{{ $stats['products'] }}</h3>
                <span class="badge text-bg-secondary mt-2">Static preview</span>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="chart-card text-center h-100">
                <p class="text-secondary small mb-1">Branches</p>
                <h3 class="mb-0">{{ $stats['branches'] }}</h3>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="chart-card text-center h-100">
                <p class="text-secondary small mb-1">Banner slides</p>
                <h3 class="mb-0">{{ $stats['banner_slides'] }}</h3>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="chart-card text-center h-100">
                <p class="text-secondary small mb-1">Page sections</p>
                <h3 class="mb-0">{{ $stats['page_sections'] }}</h3>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <a href="{{ route('vendor.public-page.edit') }}" class="chart-card d-block text-decoration-none text-dark h-100">
                <i class="fa-solid fa-globe fa-2x text-primary mb-3"></i>
                <h5>Public Page</h5>
                <p class="text-secondary small mb-0">Edit hero banner, headings, and custom sections for your India storefront.</p>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('vendor.branches.index') }}" class="chart-card d-block text-decoration-none text-dark h-100">
                <i class="fa-solid fa-code-branch fa-2x text-success mb-3"></i>
                <h5>My Branches</h5>
                <p class="text-secondary small mb-0">Manage branch profiles with PAN, GST, contact and gallery.</p>
            </a>
        </div>
        <div class="col-md-4">
            <div class="chart-card h-100">
                <h5>Store link</h5>
                <p class="small text-secondary">Share your public page:</p>
                <code class="d-block p-2 bg-light rounded">{{ route('store.show', $vendor->slug) }}</code>
            </div>
        </div>
    </div>
</div>
@endsection


