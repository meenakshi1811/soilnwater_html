@php
    $card = $card ?? [];
    $href = $card['url'] ?? '#';
    $image = $card['image'] ?? asset('assets/images/vendor-card-placeholder.svg');
    $title = $card['title'] ?? 'Browse listings';
    $category = $card['category_label'] ?? '';
    $location = $card['location'] ?? 'Near you';
@endphp

<a href="{{ $href }}" class="homepage-discover-card homepage-discover-card--category-listing text-decoration-none">
    <div class="homepage-discover-card__media">
        <img src="{{ $image }}" alt="" loading="lazy" decoding="async" width="640" height="360">
        @if(filled($category))
            <span class="homepage-discover-card__badge">{{ $category }}</span>
        @endif
    </div>
    <div class="homepage-discover-card__body">
        <h3 class="homepage-discover-card__title">{{ $title }}</h3>
        <p class="homepage-discover-card__meta">
            <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
            {{ $location }}
        </p>
    </div>
</a>
