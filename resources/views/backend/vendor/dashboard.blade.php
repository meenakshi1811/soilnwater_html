@extends('backend.layouts.app')

@section('title', 'Vendor Dashboard')

@section('content')
<div class="admin-panel ems-page vendor-dashboard-redesign">
    <div class="vendor-hero mb-4">
        <div>
            <p class="vendor-kicker mb-2">Vendor Panel</p>
            <h2 class="admin-title mb-2 text-white">Overview</h2>
            <p class="mb-0 text-white-50">Welcome, {{ $vendor->publicDisplayName() }}. Manage your branches and public storefront.</p>
        </div>
        <a href="{{ route('store.show', $vendor->slug) }}" target="_blank" class="btn btn-light vendor-hero-btn">
            <i class="fa-solid fa-external-link-alt me-1"></i> View live store
        </a>
    </div>

    <div class="row g-3 mb-4">
        @foreach($stats as $stat)
            <div class="col-md-3 col-6">
                <a href="{{ $stat['url'] }}" class="stat-card {{ $stat['class'] }} text-center h-100 d-block text-decoration-none">
                    <p class="small mb-1 text-white-50">{{ $stat['label'] }}</p>
                    <h3 class="mb-1 text-white">{{ number_format($stat['value']) }}</h3>
                    <span class="stat-detail text-white-50">{{ $stat['detail'] }}</span>
                </a>
            </div>
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <a href="{{ route('vendor.public-page.edit') }}" class="action-card d-block text-decoration-none h-100">
                <span class="icon-wrap bg-purple"><i class="fa-solid fa-globe"></i></span>
                <h5 class="mt-3">Public Page</h5>
                <p class="small mb-0">Edit hero banner, headings, and custom sections for your India storefront.</p>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('vendor.branches.index') }}" class="action-card d-block text-decoration-none h-100">
                <span class="icon-wrap bg-green"><i class="fa-solid fa-code-branch"></i></span>
                <h5 class="mt-3">My Branches</h5>
                <p class="small mb-0">Manage branch profiles with PAN, GST, contact and gallery.</p>
            </a>
        </div>
        <div class="col-md-4">
            <div class="action-card h-100">
                <span class="icon-wrap bg-amber"><i class="fa-solid fa-link"></i></span>
                <h5 class="mt-3">Store link</h5>
                <p class="small">Share your public page:</p>
                <code class="d-block p-2 rounded vendor-store-code">{{ route('store.show', $vendor->slug) }}</code>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.vendor-dashboard-redesign .vendor-hero {
    background: linear-gradient(135deg, #7c3aed 0%, #2563eb 45%, #06b6d4 100%);
    border-radius: 18px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    box-shadow: 0 12px 24px rgba(37, 99, 235, .25);
}
.vendor-dashboard-redesign .vendor-kicker {
    color: #e9d5ff;
    font-weight: 600;
    letter-spacing: .08em;
    text-transform: uppercase;
    font-size: .75rem;
}
.vendor-dashboard-redesign .vendor-hero-btn { border-radius: 999px; font-weight: 600; }
.vendor-dashboard-redesign .stat-card,
.vendor-dashboard-redesign .action-card {
    border-radius: 16px;
    padding: 1.1rem;
    border: 0;
    box-shadow: 0 8px 18px rgba(15, 23, 42, .08);
}
.vendor-dashboard-redesign .stat-card {
    transition: transform .2s ease, box-shadow .2s ease;
}
.vendor-dashboard-redesign .stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(15, 23, 42, .18);
}
.vendor-dashboard-redesign .stat-detail {
    display: block;
    font-size: .72rem;
    line-height: 1.35;
}
.vendor-dashboard-redesign .stat-purple { background: linear-gradient(135deg, #9333ea, #7e22ce); }
.vendor-dashboard-redesign .stat-blue { background: linear-gradient(135deg, #2563eb, #1d4ed8); }
.vendor-dashboard-redesign .stat-cyan { background: linear-gradient(135deg, #06b6d4, #0e7490); }
.vendor-dashboard-redesign .stat-orange { background: linear-gradient(135deg, #f97316, #ea580c); }
.vendor-dashboard-redesign .action-card {
    background: #fff;
    color: #1f2937;
    transition: transform .2s ease, box-shadow .2s ease;
}
.vendor-dashboard-redesign .action-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(15, 23, 42, .15);
}
.vendor-dashboard-redesign .icon-wrap {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1.15rem;
}
.vendor-dashboard-redesign .bg-purple { background: linear-gradient(135deg, #a855f7, #7e22ce); }
.vendor-dashboard-redesign .bg-green { background: linear-gradient(135deg, #22c55e, #15803d); }
.vendor-dashboard-redesign .bg-amber { background: linear-gradient(135deg, #f59e0b, #d97706); }
.vendor-dashboard-redesign .vendor-store-code {
    background: #eff6ff;
    color: #1e3a8a;
    border: 1px solid #bfdbfe;
    word-break: break-all;
}
@media (max-width: 767px) {
    .vendor-dashboard-redesign .vendor-hero { flex-direction: column; align-items: flex-start; }
}
</style>
@endpush
