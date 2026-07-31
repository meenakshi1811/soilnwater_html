@extends('frontend.layouts.app')

@section('meta_title', $ad->seoTitle())
@section('meta_description', $ad->seoDescription())
@section('meta_url', $ad->shareUrl())
@section('meta_canonical', $ad->shareUrl())
@section('meta_image', $ad->seoImageUrl())
@section('meta_type', 'article')

@php($ogImage = $ad->openGraphImage())

@push('head')
@if(!empty($ogImage['width']) && !empty($ogImage['height']))
<meta property="og:image:width" content="{{ $ogImage['width'] }}">
<meta property="og:image:height" content="{{ $ogImage['height'] }}">
@endif
<meta property="og:image:alt" content="{{ $ad->title }}">
@if(str_starts_with($ad->seoImageUrl(), 'https://'))
<meta property="og:image:secure_url" content="{{ $ad->seoImageUrl() }}">
@endif
@endpush

@section('content')
<div class="container py-4 py-lg-5">
    <a href="{{ route('frontend.ads.index') }}" class="view-all d-inline-block mb-3">← Back to ads market</a>

    <article class="card border-0 shadow-sm overflow-hidden">
        @if ($ad->final_image)
            <div class="ad-detail-image-wrap">
                <img
                    src="{{ asset($ad->final_image) }}"
                    alt="{{ $ad->title }}"
                    class="ad-detail-image"
                >
            </div>
        @endif

        <div class="card-body p-4 p-lg-5">
            <h1 class="h3 mt-1">{{ $ad->title }}</h1>
            @if ($ad->short_description)
                <p class="text-muted mb-2">{{ $ad->short_description }}</p>
            @endif
            <p class="text-muted mb-3">{{ $ad->location ?: 'Premium approved advertisement in our marketplace.' }}</p>
            <p class="mb-2">
                <strong>Categories:</strong>
                {{ ($selectedCategoryLabels ?? []) !== [] ? implode(', ', $selectedCategoryLabels) : 'Uncategorized' }}
            </p>

            @if (($selectedSubcategoryLabels ?? []) !== [])
                <p class="mb-2"><strong>Subcategories:</strong> {{ implode(', ', $selectedSubcategoryLabels) }}</p>
            @endif

            <p class="mb-2"><strong>Valid Upto:</strong> {{ $ad->valid_until?->format('d M Y') ?? 'N/A' }}</p>
            <p class="mb-0"><strong>Approved on:</strong> {{ $ad->reviewed_at?->format('d M Y') ?? 'N/A' }}</p>

            <div class="mt-4 pt-3 border-top">
                <button
                    type="button"
                    class="btn btn-outline-danger btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#adDetailReportModal"
                >
                    <i class="fa-regular fa-flag me-1"></i> Report this ad
                </button>
            </div>

        </div>
    </article>

    @if ($ad->location_lat && $ad->location_lng)
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body">
                <h2 class="h5 mb-3"><i class="fa-solid fa-location-dot me-2 text-danger"></i>Ad Location</h2>
                <div class="ad-detail-map-wrap">
                    <iframe
                        title="Ad location map"
                        width="100%"
                        height="340"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        src="https://www.openstreetmap.org/export/embed.html?bbox={{ $ad->location_lng - 0.02 }},{{ $ad->location_lat - 0.02 }},{{ $ad->location_lng + 0.02 }},{{ $ad->location_lat + 0.02 }}&layer=mapnik&marker={{ $ad->location_lat }},{{ $ad->location_lng }}"
                    ></iframe>
                </div>
            </div>
        </div>
    @endif
</div>

<div class="modal fade" id="adDetailReportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5 mb-0"><i class="fa-regular fa-flag me-1 text-danger"></i>Report this ad</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @auth
                    <form method="POST" action="{{ route('frontend.ads.report', $ad) }}">
                        @csrf
                        <textarea name="reason" class="form-control mb-2" rows="3" placeholder="Enter reason" required></textarea>
                        <button type="submit" class="btn btn-danger btn-sm">Submit Report</button>
                    </form>
                @else
                    <p class="small text-muted mb-0">Please <a href="{{ route('login') }}">login</a> to report this ad.</p>
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection
