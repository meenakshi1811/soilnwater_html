@extends('frontend.layouts.app')

@section('content')
<div class="container py-4 py-lg-5">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h1 class="h3 mb-0">All Offers</h1>
        <a href="{{ route('frontend.index') }}" class="view-all">Back to home ▶</a>
    </div>

    <div class="row g-3">
        @forelse ($offers as $offer)
            <div class="col-12 col-md-6 col-xl-4">
                <article class="card h-100 shadow-sm border-0 offer-coupon-card">
                    @if ($offer->banner_image)
                        <img
                            src="{{ asset($offer->banner_image) }}"
                            alt="{{ $offer->title }}"
                            class="card-img-top"
                            style="height: 180px; object-fit: cover;"
                        >
                    @endif
                    <div class="card-body d-flex flex-column gap-2">
                        <span class="badge text-bg-primary w-fit">{{ $offer->discount_tag }}</span>
                        <h2 class="h5 mb-1">{{ $offer->title }}</h2>
                        <p class="small text-muted mb-2">{{ $offer->short_description ?: 'Special offer available now.' }}</p>
                        @if ($offer->coupon_code)
                            <div class="coupon-code">{{ strtoupper($offer->coupon_code) }}</div>
                        @endif
                        <a href="{{ route('frontend.offers.show', $offer) }}" class="btn btn-sm btn-outline-primary mt-auto">View Details</a>
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info mb-0">No active offers available right now.</div>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $offers->links() }}
    </div>
</div>
@endsection
