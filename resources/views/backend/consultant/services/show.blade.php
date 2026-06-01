@extends('backend.layouts.app')
@section('title','Consultation Service Details')
@section('content')
<div class="admin-panel ems-page">
  <div class="d-flex justify-content-between align-items-center mb-4"><div><p class="ems-kicker mb-1">Consultant Portal</p><h2 class="admin-title mb-0">{{ $service->name }}</h2></div><a href="{{ route('consultant.services.index') }}" class="btn btn-outline-secondary">Back</a></div>
  <div class="chart-card p-4">
    <span class="badge bg-{{ $service->status === 'approved' ? 'success' : ($service->status === 'rejected' ? 'danger' : 'warning') }} mb-3">{{ ucfirst($service->status ?? 'pending') }}</span>
    <dl class="row"><dt class="col-sm-3">Category</dt><dd class="col-sm-9">{{ $service->categoryModel?->name ?? $service->category ?? '-' }} / {{ $service->subcategoryModel?->name ?? '-' }}</dd><dt class="col-sm-3">Price</dt><dd class="col-sm-9">₹{{ number_format((float) $service->price, 2) }}</dd><dt class="col-sm-3">Duration</dt><dd class="col-sm-9">{{ $service->duration ?: '-' }}</dd><dt class="col-sm-3">Location</dt><dd class="col-sm-9">{{ $service->location ?: '-' }} {{ $service->is_online ? '(Online available)' : '' }}<div class="small text-muted">Lat: {{ $service->latitude ?: '-' }}, Long: {{ $service->longitude ?: '-' }}</div></dd><dt class="col-sm-3">Description</dt><dd class="col-sm-9" style="white-space:pre-line;">{{ $service->description ?: $service->short_description ?: '-' }}</dd></dl>
    @if($service->image_path)<div class="row g-3 mt-2"><div class="col-6 col-md-3"><img src="{{ asset($service->image_path) }}" alt="{{ $service->name }}" class="img-fluid rounded"></div></div>@endif
  </div>
</div>
@endsection
