@php
    $variant = $variant ?? 'strip';
    $categoryName = $ad->category?->name ?: 'Recommended';
    $description = $ad->short_description ?: 'Explore this featured marketplace listing selected for service customers.';
@endphp

<a href="{{ route('frontend.ads.show', $ad) }}" class="service-provider-compact-ad service-provider-compact-ad--{{ $variant }} h-100">
    <div class="service-provider-compact-ad__media">
        <img src="{{ asset($ad->final_image) }}" alt="{{ $ad->title }}" loading="lazy" decoding="async">
        <span class="service-provider-compact-ad__badge">Sponsored</span>
    </div>
    <div class="service-provider-compact-ad__body">
        <p class="service-provider-compact-ad__category">{{ $categoryName }}</p>
        <h3>{{ $ad->title }}</h3>
        <p class="service-provider-compact-ad__description">{{ \Illuminate\Support\Str::limit($description, $variant === 'service' ? 76 : 110) }}</p>
        <span class="service-provider-compact-ad__link">View ad <i class="fa-solid fa-arrow-right"></i></span>
    </div>
</a>
