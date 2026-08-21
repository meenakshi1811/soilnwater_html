@php
    $post = $post ?? null;
    if (! $post) {
        return;
    }

    $structuredLocation = $post->structuredLocationForDisplay();
    $locationLabels = \App\Models\CommunityPost::structuredLocationLabelsFor($post->content_type);
    $landmark = $landmark ?? data_get($post->meta, 'location_landmark');
    $title = $title ?? 'Location';
    $hasLocation = $structuredLocation->isNotEmpty() || filled($landmark) || $post->hasMapCoordinates();
@endphp

@if($hasLocation)
    <div class="community-news-rail__card community-news-rail__card--detail community-detail-card community-detail-card--location community-detail-card--rail">
        <div class="community-detail-card__head">
            <span class="community-detail-card__icon" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></span>
            <div>
                <h4 class="community-detail-card__title">{{ $title }}</h4>
            </div>
        </div>
        @if($post->hasMapCoordinates())
            @include('community.partials.location-map-embed', [
                'post' => $post,
                'title' => $mapTitle ?? ($title.' map'),
            ])
        @endif
        @if($structuredLocation->isNotEmpty() || filled($landmark))
            <div class="community-detail-grid community-detail-grid--rail {{ $post->hasMapCoordinates() ? 'mt-3' : '' }}">
                @foreach($structuredLocation as $key => $value)
                    <div class="community-detail-item">
                        <span class="community-detail-item__label">{{ $locationLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</span>
                        <span class="community-detail-item__value">{{ $value }}</span>
                    </div>
                @endforeach
                @if(filled($landmark))
                    <div class="community-detail-item">
                        <span class="community-detail-item__label">Landmark</span>
                        <span class="community-detail-item__value">{{ $landmark }}</span>
                    </div>
                @endif
            </div>
        @endif
    </div>
@endif
