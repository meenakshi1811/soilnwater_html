@extends('backend.layouts.app')

@section('title', 'Educator Details')

@section('content')
<div class="admin-panel ems-page">
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <p class="ems-kicker mb-1">{{ $educator->roleLabel() }}</p>
            <h2 class="admin-title mb-1">{{ $educator->display_name }}</h2>
            <span class="badge text-bg-{{ $educator->status === 'approved' ? 'success' : ($educator->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($educator->status) }}</span>
            @if($educator->is_verified)
                <span class="badge text-bg-primary">Verified</span>
            @endif
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.educators.index') }}" class="btn btn-light">Back to list</a>
            @if($educator->isApproved())
                <a href="{{ $educator->publicUrl() }}" target="_blank" class="btn btn-outline-primary">View public profile</a>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="chart-card mb-4">
                <h5 class="mb-3">Profile details</h5>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Type</dt><dd class="col-sm-8">{{ $educator->roleLabel() }}</dd>
                    <dt class="col-sm-4">Headline</dt><dd class="col-sm-8">{{ $educator->professional_headline ?: '—' }}</dd>
                    <dt class="col-sm-4">Tagline</dt><dd class="col-sm-8">{{ $educator->tagline ?: '—' }}</dd>
                    <dt class="col-sm-4">Institute</dt><dd class="col-sm-8">{{ $educator->associated_institute ?: '—' }}</dd>
                    <dt class="col-sm-4">Phone / WhatsApp</dt><dd class="col-sm-8">{{ $educator->phone ?: '—' }} / {{ $educator->whatsapp ?: '—' }}</dd>
                    <dt class="col-sm-4">Email</dt><dd class="col-sm-8">{{ $educator->email ?: '—' }}</dd>
                    <dt class="col-sm-4">Location</dt><dd class="col-sm-8">{{ $educator->residential_address ?: '—' }}, {{ $educator->city }}, {{ $educator->state }} {{ $educator->pincode }}</dd>
                    <dt class="col-sm-4">Experience</dt><dd class="col-sm-8">{{ $educator->years_experience }} years · {{ number_format($educator->students_taught) }} students</dd>
                    <dt class="col-sm-4">Public URL</dt>
                    <dd class="col-sm-8">
                        @if($educator->isApproved())
                            <a href="{{ $educator->publicUrl() }}" target="_blank" rel="noopener">{{ $educator->publicUrl() }}</a>
                        @else
                            —
                        @endif
                    </dd>
                </dl>
            </div>

            <div class="chart-card mb-4">
                <h5 class="mb-3">About</h5>
                <p class="mb-0" style="white-space:pre-line">{{ $educator->about ?: 'No about text provided.' }}</p>
            </div>

            <div class="chart-card">
                <h5 class="mb-3">Recent study materials ({{ $educator->studyMaterials->count() }})</h5>
                @forelse($educator->studyMaterials as $material)
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <div>
                            <strong>{{ $material->title }}</strong>
                            <div class="small text-muted">{{ $material->materialTypeLabel() }} · {{ $material->subject ?: '—' }}</div>
                        </div>
                        <span class="badge bg-{{ $material->status === 'approved' ? 'success' : ($material->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($material->status) }}</span>
                    </div>
                @empty
                    <p class="text-secondary mb-0">No study materials yet.</p>
                @endforelse
            </div>
        </div>
        <div class="col-lg-4">
            <div class="chart-card mb-4">
                <h5 class="mb-3">Account owner</h5>
                <p class="mb-1"><strong>{{ $educator->user?->name }}</strong></p>
                <p class="mb-1 small">{{ $educator->user?->email }}</p>
                <p class="mb-0 small">{{ $educator->user?->phone_number }}</p>
            </div>
            @if($educator->photoUrl())
                <div class="chart-card text-center mb-4">
                    <img src="{{ $educator->photoUrl() }}" alt="{{ $educator->display_name }}" class="img-fluid rounded" style="max-height:160px">
                </div>
            @endif
            <div class="chart-card">
                <h5 class="mb-3">Approval</h5>
                <p class="mb-1 small">Approved at: {{ $educator->approved_at?->format('d M Y H:i') ?: '—' }}</p>
                <p class="mb-0 small">Approved by: {{ $educator->approver?->name ?: '—' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
