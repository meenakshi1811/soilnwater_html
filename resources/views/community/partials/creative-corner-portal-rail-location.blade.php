@php
    $post = $post ?? null;
    if (! $post?->isCreativeCornerPost()) {
        return;
    }

    $locationFields = collect([
        'City' => data_get($post->meta, 'creative_corner_location_city'),
        'District' => data_get($post->meta, 'creative_corner_location_district'),
        'State' => data_get($post->meta, 'creative_corner_location_state'),
        'Country' => data_get($post->meta, 'creative_corner_location_country'),
    ])->filter(fn (mixed $value): bool => filled($value));
    $hasLocation = $locationFields->isNotEmpty() || $post->hasMapCoordinates();
@endphp

@if($hasLocation)
    <div class="community-news-rail__card community-news-rail__card--detail community-detail-card community-detail-card--location community-detail-card--rail">
        <div class="community-detail-card__head">
            <span class="community-detail-card__icon" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></span>
            <div>
                <h4 class="community-detail-card__title">Location</h4>
            </div>
        </div>
        @if($post->hasMapCoordinates())
            @include('community.partials.location-map-embed', [
                'post' => $post,
                'title' => 'Creative work location map',
            ])
        @endif
        @if($locationFields->isNotEmpty())
            <div class="community-detail-grid community-detail-grid--rail {{ $post->hasMapCoordinates() ? 'mt-3' : '' }}">
                @foreach($locationFields as $label => $value)
                    <div class="community-detail-item">
                        <span class="community-detail-item__label">{{ $label }}</span>
                        <span class="community-detail-item__value">{{ $value }}</span>
                    </div>
                @endforeach
            </div>
        @elseif(filled($post->location) && ! $post->hasMapCoordinates())
            <p class="community-detail-location-inline mb-0">{{ $post->location }}</p>
        @endif
    </div>
@endif
