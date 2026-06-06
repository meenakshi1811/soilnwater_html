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
            $profilePlaceholder = asset('assets/images/profile-placeholder.svg');
            $consultantCardImage = $primaryBranch?->logo ? asset($primaryBranch->logo) : $profilePlaceholder;
          @endphp
          <div class="col">
            <div class="vendor-card card h-100">
              <img src="{{ $consultantCardImage }}" alt="{{ $consultant->publicDisplayName() }}" onerror="this.onerror=null;this.src='{{ $profilePlaceholder }}';">
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
