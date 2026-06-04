@extends('backend.layouts.app')
@section('title','Service Details')
@section('content')
<div class="admin-panel ems-page">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <p class="ems-kicker mb-1">Service Provider Portal</p>
      <h2 class="admin-title mb-0">{{ $service->name }}</h2>
    </div>
    <a href="{{ route('service_provider.services.index') }}" class="btn btn-outline-secondary">Back</a>
  </div>
  <div class="chart-card p-4">
    <span class="badge bg-{{ $service->status === 'approved' ? 'success' : ($service->status === 'rejected' ? 'danger' : 'warning') }} mb-3">{{ ucfirst($service->status ?? 'pending') }}</span>

    @if($service->image_path)
      <div class="mb-4">
        <img src="{{ asset($service->image_path) }}" alt="{{ $service->name }}" class="img-fluid rounded shadow-sm" style="max-height: 360px; object-fit: cover; width: 100%;">
      </div>
    @endif

    <dl class="row">
      <dt class="col-sm-3">Category</dt>
      <dd class="col-sm-9">{{ $service->categoryModel?->name ?? $service->category ?? '-' }} / {{ $service->subcategoryModel?->name ?? '-' }}</dd>

      <dt class="col-sm-3">Service Type</dt>
      <dd class="col-sm-9">{{ ucfirst($service->consultation_type ?: ($service->is_online ? 'online' : 'offline')) }}</dd>

      <dt class="col-sm-3">Business Type</dt>
      <dd class="col-sm-9">{{ $service->business_type ?: '-' }}</dd>

      <dt class="col-sm-3">Charges</dt>
      <dd class="col-sm-9">
        <div class="table-responsive">
          <table class="table table-bordered table-sm align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Duration</th>
                <th>Price</th>
                <th>Notes</th>
              </tr>
            </thead>
            <tbody>
              @foreach($service->consultationChargeRows() as $charge)
                <tr>
                  <td>{{ $charge['duration'] }}</td>
                  <td>{{ $charge['price'] }}</td>
                  <td>{{ $charge['note'] !== '' ? $charge['note'] : '-' }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </dd>

      <dt class="col-sm-3">Address</dt>
      <dd class="col-sm-9">{{ $service->location ?: '-' }}</dd>


      <dt class="col-sm-3">Postal Code</dt>
      <dd class="col-sm-9">{{ $service->postal_code ?: '-' }}</dd>

      <dt class="col-sm-3">City</dt>
      <dd class="col-sm-9">{{ $service->city ?: '-' }}</dd>

      <dt class="col-sm-3">Service Radius</dt>
      <dd class="col-sm-9">{{ filled($service->service_radius) ? $service->service_radius.' km' : '-' }}</dd>

      <dt class="col-sm-3">Working Hours</dt>
      <dd class="col-sm-9" style="white-space:pre-line;">{{ $service->working_hours ?: '-' }}</dd>

      <dt class="col-sm-3">Geographical Service Area</dt>
      <dd class="col-sm-9" style="white-space:pre-line;">{{ $service->service_area ?: '-' }}</dd>

      <dt class="col-sm-3">Short Description</dt>
      <dd class="col-sm-9">{{ $service->short_description ?: '-' }}</dd>

      <dt class="col-sm-3">Description</dt>
      <dd class="col-sm-9" style="white-space:pre-line;">{{ $service->description ?: '-' }}</dd>
    </dl>
  </div>
</div>
@endsection
