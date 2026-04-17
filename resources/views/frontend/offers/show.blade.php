@extends('frontend.layouts.app')

@section('content')
<div class="container py-4 py-lg-5">
    <a href="{{ route('frontend.offers.index') }}" class="view-all d-inline-block mb-3">← Back to offers</a>

    <article class="card border-0 shadow-sm">
        @if ($offer->banner_image)
            <img
                src="{{ asset($offer->banner_image) }}"
                alt="{{ $offer->title }}"
                class="card-img-top"
                style="max-height: 360px; object-fit: cover;"
            >
        @endif
        <div class="card-body">
            <span class="badge text-bg-primary">{{ $offer->discount_tag }}</span>
            <h1 class="h3 mt-2">{{ $offer->title }}</h1>
            <p class="text-muted mb-3">{{ $offer->short_description ?: 'Special limited-time offer available now.' }}</p>

            @if ($offer->coupon_code)
                <div class="coupon-code mb-3">{{ strtoupper($offer->coupon_code) }}</div>
            @endif

            <p class="mb-0"><strong>Valid until:</strong> {{ $offer->valid_until?->format('d M Y') ?? 'No expiry' }}</p>
        </div>
    </article>
</div>
@endsection
