@extends('backend.layouts.app')

@section('title', 'Consultant Details')

@section('content')
<div class="admin-panel ems-page">
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <p class="ems-kicker mb-1">Consultant</p>
            <h2 class="admin-title mb-1">{{ $consultant->company_name }}</h2>
            <span class="badge text-bg-{{ $consultant->status === 'approved' ? 'success' : ($consultant->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($consultant->status) }}</span>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.consultants.index') }}" class="btn btn-light">Back to list</a>
            <a href="{{ route('admin.consultants.public-page.edit', $consultant) }}" class="btn btn-primary">Edit store page</a>
            @if($consultant->isApproved())
                <a href="{{ route('consultant.show', $consultant->slug) }}" target="_blank" class="btn btn-outline-primary">View consultant</a>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="chart-card mb-4">
                <h5 class="mb-3">Company details</h5>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Contact person</dt><dd class="col-sm-8">{{ $consultant->contact_person ?? '—' }}</dd>
                    <dt class="col-sm-4">Phone / WhatsApp</dt><dd class="col-sm-8">{{ $consultant->phone ?? '—' }} / {{ $consultant->whatsapp ?? '—' }}</dd>
                    <dt class="col-sm-4">Email</dt><dd class="col-sm-8">{{ $consultant->email ?? '—' }}</dd>
                    <dt class="col-sm-4">Address</dt><dd class="col-sm-8">{{ $consultant->address }}, {{ $consultant->city }}, {{ $consultant->state }} {{ $consultant->pincode }}</dd>
                    <dt class="col-sm-4">PAN / GST</dt><dd class="col-sm-8">{{ $consultant->pan_number ?? '—' }} / {{ $consultant->gst_number ?? '—' }}</dd>
                    <dt class="col-sm-4">Other Government Certificate</dt><dd class="col-sm-8">{{ $consultant->government_certificate_number ?? '—' }}</dd>
                    <dt class="col-sm-4">Consultant URL</dt><dd class="col-sm-8"><a href="{{ route('consultant.show', $consultant->slug) }}" target="_blank" rel="noopener noreferrer">{{ route('consultant.show', $consultant->slug) }}</a></dd>
                </dl>
            </div>
            <div class="chart-card">
                <h5 class="mb-3">Branches ({{ $consultant->branches->count() }})</h5>
                @forelse($consultant->branches as $branch)
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
                            <dt class="col-sm-4">Professional experience</dt><dd class="col-sm-8" style="white-space:pre-line">{{ $branch->professional_experience ?? '—' }}</dd>
                            <dt class="col-sm-4">Services offered</dt><dd class="col-sm-8" style="white-space:pre-line">{{ $branch->services_offered ?? '—' }}</dd>
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
                <p class="mb-1"><strong>{{ $consultant->user?->name }}</strong></p>
                <p class="mb-1 small">{{ $consultant->user?->email }}</p>
                <p class="mb-0 small">{{ $consultant->user?->phone_number }}</p>
            </div>
            @if($consultant->logo)
                <div class="chart-card text-center">
                    <img src="{{ asset($consultant->logo) }}" alt="Logo" class="img-fluid rounded" style="max-height:120px">
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
