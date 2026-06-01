@extends('frontend.layouts.app')

@section('content')
<div class="main-wrap">
  <div class="main-col">
    <section class="sec">
      <div class="sec-head">
        <div class="sec-title"><span class="icon"><i class="fa-solid fa-user-tie"></i></span> All Consultants</div>
      </div>
      <p class="small text-muted mb-3">
        @if($hasLocation)
          Showing nearby premium consultants first, then nearby normal consultants.
        @else
          Showing latest premium consultants first, followed by latest normal consultants.
        @endif
      </p>

      <div class="vendor-grid row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-3">
        @forelse($consultants as $consultant)
          @php
            $primaryBranch = $consultant->branches->first();
            $firstService = $consultant->services->firstWhere('status', 'approved') ?: $consultant->services->first();
            $serviceImage = $firstService?->image_path ? asset($firstService->image_path) : null;
            $bannerImage = $consultant->bannerSlides->first()?->image_path ? asset($consultant->bannerSlides->first()->image_path) : null;
            $logoImage = $consultant->logo ? asset($consultant->logo) : null;
            $consultantCardImage = $serviceImage ?? $bannerImage ?? $logoImage ?? 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=300&q=70';
          @endphp
          <div class="col">
            <div class="vendor-card card h-100">
              <img src="{{ $consultantCardImage }}" alt="{{ $consultant->publicDisplayName() }}">
              <div class="vendor-card-body card-body d-flex flex-column">
                <p>{{ $consultant->publicDisplayName() }} @if($consultant->is_premium)⭐@endif</p>
                <div class="vendor-card-sub">
                  {{ $primaryBranch?->city ?: ($consultant->city ?: 'Local Area') }} • {{ $consultant->services_count }} Services
                  @if($hasLocation && $consultant->nearest_distance_km !== null)
                    • {{ number_format($consultant->nearest_distance_km, 1) }} km
                  @endif
                </div>
                <a href="{{ route('consultant.show', $consultant->slug) }}" class="vendor-card-btn text-center text-decoration-none">View Consultant</a>
              </div>
            </div>
          </div>
        @empty
          <div class="col-12"><p class="text-muted">No consultants found.</p></div>
        @endforelse
      </div>

      <div class="mt-4">
        {{ $consultants->links() }}
      </div>
    </section>
  </div>
</div>
@endsection
