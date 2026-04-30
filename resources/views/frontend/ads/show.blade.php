@extends('frontend.layouts.app')

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
            <p class="text-muted mb-3">{{ $ad->location ?: 'Premium approved advertisement in our marketplace.' }}</p>
            <p class="mb-2"><strong>Category:</strong> {{ $ad->category?->name ?? 'Uncategorized' }}</p>

            @if ($ad->subcategory)
                <p class="mb-2"><strong>Subcategory:</strong> {{ $ad->subcategory->name }}</p>
            @endif

            <p class="mb-0"><strong>Approved on:</strong> {{ $ad->reviewed_at?->format('d M Y') ?? 'N/A' }}</p>
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
@endsection
