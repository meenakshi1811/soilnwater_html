@extends('backend.layouts.app')

@section('title', 'Institute Details')

@section('content')
<div class="admin-panel ems-page">
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <p class="ems-kicker mb-1">{{ $institute->roleLabel() }}</p>
            <h2 class="admin-title mb-1">{{ $institute->displayName() }}</h2>
            <span class="badge text-bg-{{ $institute->status === 'approved' ? 'success' : ($institute->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($institute->status) }}</span>
            @if($institute->is_verified)
                <span class="badge text-bg-primary">Verified</span>
            @endif
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.institutes.index') }}" class="btn btn-light">Back to list</a>
            @if($institute->isApproved())
                <a href="{{ $institute->publicUrl() }}" target="_blank" class="btn btn-outline-primary">View public profile</a>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="chart-card mb-4">
                <h5 class="mb-3">Profile details</h5>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Type</dt><dd class="col-sm-8">{{ $institute->institutionTypeLabel() }}</dd>
                    <dt class="col-sm-4">Board</dt><dd class="col-sm-8">{{ $institute->board_affiliation ?: '—' }}</dd>
                    <dt class="col-sm-4">Contact person</dt><dd class="col-sm-8">{{ $institute->contact_person ?: '—' }}</dd>
                    <dt class="col-sm-4">Phone / WhatsApp</dt><dd class="col-sm-8">{{ $institute->phone ?: '—' }} / {{ $institute->whatsapp ?: '—' }}</dd>
                    <dt class="col-sm-4">Email</dt><dd class="col-sm-8">{{ $institute->email ?: '—' }}</dd>
                    <dt class="col-sm-4">Location</dt><dd class="col-sm-8">{{ $institute->address ?: '—' }}, {{ $institute->city }}, {{ $institute->state }} {{ $institute->pincode }}</dd>
                    <dt class="col-sm-4">PAN / GST</dt><dd class="col-sm-8">{{ $institute->pan_number ?: '—' }} / {{ $institute->gst_number ?: '—' }}</dd>
                    <dt class="col-sm-4">Public URL</dt>
                    <dd class="col-sm-8">
                        @if($institute->isApproved())
                            <a href="{{ $institute->publicUrl() }}" target="_blank" rel="noopener">{{ $institute->publicUrl() }}</a>
                        @else
                            —
                        @endif
                    </dd>
                </dl>
            </div>

            <div class="chart-card mb-4">
                <h5 class="mb-3">About</h5>
                <p class="mb-0" style="white-space:pre-line">{{ $institute->about ?: $institute->description ?: 'No about text provided.' }}</p>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="chart-card mb-4">
                <h5 class="mb-3">Account owner</h5>
                <p class="mb-1"><strong>{{ $institute->user?->name }}</strong></p>
                <p class="mb-1 small">{{ $institute->user?->email }}</p>
                <p class="mb-0 small">{{ $institute->user?->phone_number }}</p>
            </div>
            @if($institute->logoUrl())
                <div class="chart-card text-center mb-4">
                    <img src="{{ $institute->logoUrl() }}" alt="{{ $institute->displayName() }}" class="img-fluid rounded" style="max-height:160px">
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
