@extends('frontend.layouts.app')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/premium-page.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div class="main-wrap">
  <div class="main-col">
    @include('frontend.premium.partials.listing-cta', ['type' => 'service'])
    <section class="sec">
      <div class="sec-head">
        <div class="sec-title"><span class="icon"><i class="fa-solid fa-user-tie"></i></span> All Services</div>
      </div>
      <p class="small text-muted mb-3">
        @if($hasLocation)
          Showing nearby premium services first, then nearby normal services.
        @else
          Showing latest premium services first, followed by latest normal services.
        @endif
      </p>

      <div class="vendor-grid row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-3">
        @forelse($service_providers as $service_provider)
          @php
            $primaryBranch = $service_provider->branches->first();
            $profilePlaceholder = asset('assets/images/profile-placeholder.svg');
            $serviceProviderProfileImage = $primaryBranch?->logo ? asset($primaryBranch->logo) : null;
            $service_providerCardImage = $service_provider->logo ? asset($service_provider->logo) : ($serviceProviderProfileImage ?? $profilePlaceholder);
          @endphp
          <div class="col">
            <div class="vendor-card card h-100{{ $service_provider->is_premium ? ' is-premium-card' : '' }}">
              <img src="{{ $service_providerCardImage }}" alt="{{ $service_provider->publicDisplayName() }}" onerror="this.onerror=null;this.src='{{ $profilePlaceholder }}';">
              <div class="vendor-card-body card-body d-flex flex-column">
                <p class="vendor-card-name">
                  {{ $service_provider->publicDisplayName() }}
                  @if($service_provider->is_premium)
                    @include('frontend.premium.partials.badge', ['size' => 'xs'])
                  @endif
                </p>
                <div class="vendor-card-sub">
                  {{ $primaryBranch?->city ?: ($service_provider->city ?: 'Local Area') }} • {{ $service_provider->services_count }} Services
                  @if($hasLocation && $service_provider->nearest_distance_km !== null)
                    • {{ number_format($service_provider->nearest_distance_km, 1) }} km
                  @endif
                </div>
                <a href="{{ route('service_provider.show', $service_provider->slug) }}" class="vendor-card-btn text-center text-decoration-none">View Service</a>
              </div>
            </div>
          </div>
        @empty
          <div class="col-12"><p class="text-muted">No services found.</p></div>
        @endforelse
      </div>

      <div class="mt-4 frontend-pagination-wrap">
        @include('frontend.partials.pagination-summary', ['paginator' => $service_providers])
        {{ $service_providers->links() }}
      </div>
    </section>
  </div>
</div>
@endsection
