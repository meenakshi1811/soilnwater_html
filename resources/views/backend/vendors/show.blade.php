@extends('backend.layouts.app')

@section('title', 'Vendor Details')

@section('content')
<div class="admin-panel ems-page">
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <p class="ems-kicker mb-1">Vendor</p>
            <h2 class="admin-title mb-1">{{ $vendor->company_name }}</h2>
            <span class="badge text-bg-{{ $vendor->status === 'approved' ? 'success' : ($vendor->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($vendor->status) }}</span>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.vendors.index') }}" class="btn btn-light">Back to list</a>
            <a href="{{ route('admin.vendors.store-preview', $vendor) }}" target="_blank" class="btn btn-outline-primary">View store</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="chart-card mb-4">
                <h5 class="mb-3">Company details</h5>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Contact person</dt><dd class="col-sm-8">{{ $vendor->contact_person ?? '—' }}</dd>
                    <dt class="col-sm-4">Phone / WhatsApp</dt><dd class="col-sm-8">{{ $vendor->phone ?? '—' }} / {{ $vendor->whatsapp ?? '—' }}</dd>
                    <dt class="col-sm-4">Email</dt><dd class="col-sm-8">{{ $vendor->email ?? '—' }}</dd>
                    <dt class="col-sm-4">Address</dt><dd class="col-sm-8">{{ $vendor->address }}, {{ $vendor->city }}, {{ $vendor->state }} {{ $vendor->pincode }}</dd>
                    <dt class="col-sm-4">PAN / GST</dt><dd class="col-sm-8">{{ $vendor->pan_number ?? '—' }} / {{ $vendor->gst_number ?? '—' }}</dd>
                    <dt class="col-sm-4">Other Government Certificate</dt><dd class="col-sm-8">{{ $vendor->government_certificate_number ?? '—' }}</dd>
                    <dt class="col-sm-4">Store URL</dt><dd class="col-sm-8"><a href="{{ route('admin.vendors.store-preview', $vendor) }}" target="_blank" rel="noopener noreferrer">{{ route('store.show', $vendor->slug) }}</a>@unless($vendor->isPublicProfileLive()) <span class="badge text-bg-warning">Preview only</span>@endunless</dd>
                </dl>
            </div>
            <div class="chart-card">
                <h5 class="mb-3">Branches ({{ $vendor->branches->count() }})</h5>
                @forelse($vendor->branches as $branch)
                    <details class="border rounded p-3 mb-2">
                        <summary class="d-flex justify-content-between align-items-start gap-3" style="cursor:pointer">
                            <span>
                                <strong>{{ $branch->branch_name }}</strong>
                                @if($branch->is_primary)<span class="badge text-bg-primary ms-2">Primary</span>@endif
                                <span class="d-block small text-secondary">{{ $branch->city }}, {{ $branch->state }}</span>
                            </span>
                            <span class="small text-primary">View details</span>
                        </summary>
                        <dl class="row small mb-0 mt-3">
                            <dt class="col-sm-4">Contact person</dt><dd class="col-sm-8">{{ $branch->contact_person ?? '—' }}</dd>
                            <dt class="col-sm-4">Phone</dt><dd class="col-sm-8">{{ $branch->phone ?? '—' }}</dd>
                            <dt class="col-sm-4">Alternative mobile</dt><dd class="col-sm-8">{{ $branch->alt_mobile_number ?? '—' }}</dd>
                            <dt class="col-sm-4">WhatsApp</dt><dd class="col-sm-8">{{ $branch->whatsapp ?? '—' }}</dd>
                            <dt class="col-sm-4">Email</dt><dd class="col-sm-8">{{ $branch->email ?? '—' }}</dd>
                            <dt class="col-sm-4">Address</dt><dd class="col-sm-8">{{ $branch->address }}, {{ $branch->city }}, {{ $branch->state }} {{ $branch->pincode }}</dd>
                            <dt class="col-sm-4">PAN / GST</dt><dd class="col-sm-8">{{ $branch->pan_number ?? '—' }} / {{ $branch->gst_number ?? '—' }}</dd>
                        </dl>
                    </details>
                @empty
                    <p class="text-secondary mb-0">No branches.</p>
                @endforelse
            </div>
        </div>
        <div class="col-lg-4">
            <div class="chart-card mb-4">
                <h5 class="mb-3">Account owner</h5>
                <p class="mb-1"><strong>{{ $vendor->user?->name }}</strong></p>
                <p class="mb-1 small">{{ $vendor->user?->email }}</p>
                <p class="mb-0 small">{{ $vendor->user?->phone_number }}</p>
            </div>
            @if($vendor->logo)
                <div class="chart-card text-center">
                    <img src="{{ asset($vendor->logo) }}" alt="Logo" class="img-fluid rounded" style="max-height:120px">
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
