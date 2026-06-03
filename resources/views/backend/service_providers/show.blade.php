@extends('backend.layouts.app')

@section('title', 'Service Provider Details')

@section('content')
<div class="admin-panel ems-page">
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <p class="ems-kicker mb-1">Service Provider</p>
            <h2 class="admin-title mb-1">{{ $service_provider->company_name }}</h2>
            <span class="badge text-bg-{{ $service_provider->status === 'approved' ? 'success' : ($service_provider->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($service_provider->status) }}</span>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.service_providers.index') }}" class="btn btn-light">Back to list</a>
            @if($service_provider->isApproved())
                <a href="{{ route('service_provider.show', $service_provider->slug) }}" target="_blank" class="btn btn-outline-primary">View service_provider</a>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="chart-card mb-4">
                <h5 class="mb-3">Company details</h5>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Contact person</dt><dd class="col-sm-8">{{ $service_provider->contact_person ?? '—' }}</dd>
                    <dt class="col-sm-4">Phone / WhatsApp</dt><dd class="col-sm-8">{{ $service_provider->phone ?? '—' }} / {{ $service_provider->whatsapp ?? '—' }}</dd>
                    <dt class="col-sm-4">Email</dt><dd class="col-sm-8">{{ $service_provider->email ?? '—' }}</dd>
                    <dt class="col-sm-4">Address</dt><dd class="col-sm-8">{{ $service_provider->address }}, {{ $service_provider->city }}, {{ $service_provider->state }} {{ $service_provider->pincode }}</dd>
                    <dt class="col-sm-4">PAN / GST</dt><dd class="col-sm-8">{{ $service_provider->pan_number ?? '—' }} / {{ $service_provider->gst_number ?? '—' }}</dd>
                    <dt class="col-sm-4">Other Government Certificate</dt><dd class="col-sm-8">{{ $service_provider->government_certificate_number ?? '—' }}</dd>
                    <dt class="col-sm-4">Service Provider URL</dt><dd class="col-sm-8"><a href="{{ route('service_provider.show', $service_provider->slug) }}" target="_blank" rel="noopener noreferrer">{{ route('service_provider.show', $service_provider->slug) }}</a></dd>
                </dl>
            </div>
            <div class="chart-card">
                <h5 class="mb-3">Branches ({{ $service_provider->branches->count() }})</h5>
                @forelse($service_provider->branches as $branch)
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
                <p class="mb-1"><strong>{{ $service_provider->user?->name }}</strong></p>
                <p class="mb-1 small">{{ $service_provider->user?->email }}</p>
                <p class="mb-0 small">{{ $service_provider->user?->phone_number }}</p>
            </div>
            @if($service_provider->logo)
                <div class="chart-card text-center">
                    <img src="{{ asset($service_provider->logo) }}" alt="Logo" class="img-fluid rounded" style="max-height:120px">
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
