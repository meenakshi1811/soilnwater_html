@php
    $variant = $variant ?? 'strip';
    $categoryName = $ad->category?->name ?: 'Recommended';
    $description = $ad->short_description ?: 'Explore this featured marketplace listing selected for marketplace visitors.';
@endphp

<a href="{{ route('frontend.ads.show', $ad) }}" class="marketplace-profile-ad marketplace-profile-ad--{{ $variant }} h-100">
    <div class="marketplace-profile-ad__media">
        <img src="{{ asset($ad->final_image) }}" alt="{{ $ad->title }}" loading="lazy" decoding="async">
        <span class="marketplace-profile-ad__badge">Sponsored</span>
    </div>
    <div class="marketplace-profile-ad__body">
        <p class="marketplace-profile-ad__category">{{ $categoryName }}</p>
        <h3>{{ $ad->title }}</h3>
        <p class="marketplace-profile-ad__description">{{ \Illuminate\Support\Str::limit($description, in_array($variant, ['service', 'product'], true) ? 76 : 110) }}</p>
        <span class="marketplace-profile-ad__link">View ad <i class="fa-solid fa-arrow-right"></i></span>
    </div>
</a>
