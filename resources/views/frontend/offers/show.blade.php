@extends('frontend.layouts.app')

@section('meta_title', $offer->seoTitle())
@section('meta_description', $offer->seoDescription())
@section('meta_url', $offer->shareUrl())
@section('meta_canonical', $offer->shareUrl())
@section('meta_image', $offer->seoImageUrl())
@section('meta_type', 'article')

@php($ogImage = $offer->openGraphImage())

@push('head')
<meta property="og:site_name" content="SoilnWater">
@if(!empty($ogImage['width']) && !empty($ogImage['height']))
<meta property="og:image:width" content="{{ $ogImage['width'] }}">
<meta property="og:image:height" content="{{ $ogImage['height'] }}">
@endif
<meta property="og:image:alt" content="{{ $offer->title }}">
@if(str_starts_with($offer->seoImageUrl(), 'https://'))
<meta property="og:image:secure_url" content="{{ $offer->seoImageUrl() }}">
@endif
@php($ogImageMime = \App\Support\SocialShare::mimeTypeForPublicPath($ogImage['path'] ?? null))
@if($ogImageMime)
<meta property="og:image:type" content="{{ $ogImageMime }}">
@endif
@endpush

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
            <p class="text-muted mb-3">{!! nl2br(e($offer->short_description ?: 'Special limited-time offer available now.')) !!}</p>

            @if ($offer->coupon_code)
                <div class="coupon-code mb-3">{{ strtoupper($offer->coupon_code) }}</div>
            @endif

            <p class="mb-3"><strong>Valid until:</strong> {{ $offer->valid_until?->format('d M Y') ?? 'No expiry' }}</p>
            <button
                type="button"
                class="btn btn-outline-danger btn-sm"
                data-bs-toggle="modal"
                data-bs-target="#offerDetailReportModal"
            >
                <i class="fa-regular fa-flag me-1"></i> Report this offer
            </button>
        </div>
    </article>
</div>

<div class="modal fade" id="offerDetailReportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5 mb-0"><i class="fa-regular fa-flag me-1 text-danger"></i>Report this offer</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @auth
                    <form method="POST" action="{{ route('frontend.offers.report', $offer) }}">
                        @csrf
                        <textarea name="reason" class="form-control mb-2" rows="4" placeholder="Enter reason for reporting this offer" required></textarea>
                        <button type="submit" class="btn btn-danger btn-sm">Submit Report</button>
                    </form>
                @else
                    <p class="mb-0 small text-muted">Please <a href="{{ route('login') }}">login</a> to report this offer.</p>
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection
