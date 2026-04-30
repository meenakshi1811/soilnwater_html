@extends('frontend.layouts.app')

@section('content')
<div class="container py-4 py-lg-5">
    <a href="{{ route('frontend.ads.index') }}" class="view-all d-inline-block mb-3">← Back to ads market</a>

    <article class="card border-0 shadow-sm overflow-hidden">
        @if ($ad->final_image)
            <img
                src="{{ asset($ad->final_image) }}"
                alt="{{ $ad->title }}"
                class="card-img-top"
                style="width:100%; max-height:560px; object-fit:cover;"
            >
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
</div>
@endsection
