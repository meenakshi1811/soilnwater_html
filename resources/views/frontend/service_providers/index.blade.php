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
            $firstService = $service_provider->services->firstWhere('status', 'approved') ?: $service_provider->services->first();
            $serviceImage = $firstService?->image_path ? asset($firstService->image_path) : null;
            $bannerImage = $service_provider->bannerSlides->first()?->image_path ? asset($service_provider->bannerSlides->first()->image_path) : null;
            $logoImage = $service_provider->logo ? asset($service_provider->logo) : null;
            $service_providerCardImage = $serviceImage ?? $bannerImage ?? $logoImage ?? 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=300&q=70';
          @endphp
          <div class="col">
            <div class="vendor-card card h-100">
              <img src="{{ $service_providerCardImage }}" alt="{{ $service_provider->publicDisplayName() }}">
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
