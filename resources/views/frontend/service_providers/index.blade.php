@extends('frontend.layouts.app')

@section('content')
<div class="main-wrap">
  <div class="main-col">
    <section class="sec">
      <div class="sec-head">
        <div class="sec-title"><span class="icon"><i class="fa-solid fa-user-tie"></i></span> All Service Providers</div>
      </div>
      <p class="small text-muted mb-3">
        @if($hasLocation)
          Showing nearby premium service providers first, then nearby normal service_providers.
        @else
          Showing latest premium service providers first, followed by latest normal service_providers.
        @endif
      </p>

      <div class="vendor-grid row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-3">
        @forelse($service_providers as $service_provider)
          @php
            $primaryBranch = $service_provider->branches->first();
            $profilePlaceholder = asset('assets/images/profile-placeholder.svg');
            $service_providerCardImage = $primaryBranch?->logo ? asset($primaryBranch->logo) : $profilePlaceholder;
          @endphp
          <div class="col">
            <div class="vendor-card card h-100">
              <img src="{{ $service_providerCardImage }}" alt="{{ $service_provider->publicDisplayName() }}" onerror="this.onerror=null;this.src='{{ $profilePlaceholder }}';">
              <div class="vendor-card-body card-body d-flex flex-column">
                <p>{{ $service_provider->publicDisplayName() }} @if($service_provider->is_premium)⭐@endif</p>
                <div class="vendor-card-sub">
                  {{ $primaryBranch?->city ?: ($service_provider->city ?: 'Local Area') }} • {{ $service_provider->services_count }} Services
                  @if($hasLocation && $service_provider->nearest_distance_km !== null)
                    • {{ number_format($service_provider->nearest_distance_km, 1) }} km
                  @endif
                </div>
                <a href="{{ route('service_provider.show', $service_provider->slug) }}" class="vendor-card-btn text-center text-decoration-none">View Service Provider</a>
              </div>
            </div>
          </div>
        @empty
          <div class="col-12"><p class="text-muted">No service providers found.</p></div>
        @endforelse
      </div>

      <div class="mt-4">
        {{ $service_providers->links() }}
      </div>
    </section>
  </div>
</div>
@endsection
