@extends('frontend.layouts.app')

@section('meta_title', $offer->title.' | SoilnWater Offers Market')
@section('meta_description', $offer->short_description ?: 'Special limited-time offer available now.')
@section('meta_url', route('frontend.offers.show', $offer))
@section('meta_image', $offer->banner_image ? asset($offer->banner_image) : asset('assets/images/logo_soilnwater.webp'))

@section('content')
<div class="container py-4 py-lg-5">
    <a href="{{ route('frontend.offers.index') }}" class="view-all d-inline-block mb-3">← Back to offers</a>

    <article class="card border-0 shadow-sm">
        @if ($offer->banner_image)
            <img
                src="{{ asset($offer->banner_image) }}"
                alt="{{ $offer->title }}"
                class="card-img-top"
                style="width:100%; aspect-ratio:768/1080; object-fit:cover;"
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
