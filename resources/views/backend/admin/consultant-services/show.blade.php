@extends('backend.layouts.app')
@section('title','Consultation Service Details')
@section('content')
<div class="admin-panel ems-page">
  <div class="d-flex justify-content-between align-items-center mb-4"><div><p class="ems-kicker mb-1">Admin Portal</p><h2 class="admin-title mb-0">{{ $service->name }}</h2></div><a href="{{ route('admin.consultant-services.index') }}" class="btn btn-outline-secondary">Back</a></div>
  <div class="chart-card p-4">
    <div class="d-flex gap-2 mb-3"><span class="badge bg-{{ $service->status === 'approved' ? 'success' : ($service->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($service->status ?? 'pending') }}</span></div>
    <dl class="row"><dt class="col-sm-3">Consultant</dt><dd class="col-sm-9">{{ $service->consultant?->display_name ?: $service->consultant?->company_name ?: '-' }}</dd><dt class="col-sm-3">Category</dt><dd class="col-sm-9">{{ $service->categoryModel?->name ?? $service->category ?? '-' }} / {{ $service->subcategoryModel?->name ?? '-' }}</dd><dt class="col-sm-3">Price</dt><dd class="col-sm-9">₹{{ number_format((float) $service->price, 2) }}</dd><dt class="col-sm-3">Duration</dt><dd class="col-sm-9">{{ $service->duration ?: '-' }}</dd><dt class="col-sm-3">Location</dt><dd class="col-sm-9">{{ $service->location ?: '-' }} {{ $service->is_online ? '(Online available)' : '' }}</dd><dt class="col-sm-3">Description</dt><dd class="col-sm-9" style="white-space:pre-line;">{{ $service->description ?: $service->short_description ?: '-' }}</dd></dl>
    @if($service->images)<div class="row g-3 mt-2">@foreach($service->images as $image)<div class="col-6 col-md-3"><img src="{{ asset($image) }}" alt="{{ $service->name }}" class="img-fluid rounded"></div>@endforeach</div>@endif
  </div>
</div>
@endsection
