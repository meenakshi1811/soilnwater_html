@extends('frontend.layouts.app')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/premium-page.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div class="main-wrap">
  <div class="main-col">
    @include('frontend.premium.partials.listing-cta', ['type' => 'vendor'])
    <section class="sec">
      <div class="sec-head">
        <div class="sec-title"><span class="icon"><i class="fa-solid fa-store"></i></span> All Vendors</div>
      </div>
      <p class="small text-muted mb-3">
        @if($hasLocation)
          Showing nearby premium vendors first, then nearby normal vendors.
        @else
          Showing latest premium vendors first, followed by latest normal vendors.
        @endif
      </p>

      <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-1 offer-coupon-grid vendor-offer-grid">
        @forelse($vendors as $vendor)
          <div class="col">
            @include('frontend.partials.vendor-card', ['vendor' => $vendor, 'hasLocation' => $hasLocation])
          </div>
        @empty
          <div class="col-12"><p class="text-muted">No vendors found.</p></div>
        @endforelse
      </div>

      <div class="mt-4">
        {{ $vendors->links() }}
      </div>
    </section>
  </div>
</div>
@endsection
