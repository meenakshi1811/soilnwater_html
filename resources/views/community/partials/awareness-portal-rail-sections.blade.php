@php
    $post = $post ?? null;
    if (! $post?->isAwarenessPost()) {
        return;
    }

    $structuredLocation = $post->structuredLocationForDisplay();
    $locationLabels = \App\Models\CommunityPost::structuredLocationLabelsFor($post->content_type);
    $awarenessPostedBy = data_get($post->meta, 'awareness_posted_by');
    $awarenessOrganizationName = data_get($post->meta, 'awareness_organization_name');
    $hasOrganization = filled($awarenessPostedBy) || filled($awarenessOrganizationName);
    $hasLocation = $structuredLocation->isNotEmpty() || $post->hasMapCoordinates();
@endphp

<div class="community-news-rail__awareness-extras" aria-label="Awareness post details">
    @include('community.partials.awareness-meta-details', [
        'post' => $post,
        'railLayout' => true,
    ])

    @if($hasOrganization)
        <div class="community-news-rail__card community-news-rail__card--detail community-detail-card community-detail-card--rail">
            <div class="community-detail-card__head">
                <span class="community-detail-card__icon" aria-hidden="true"><i class="fa-solid fa-building"></i></span>
                <div>
                    <h4 class="community-detail-card__title">Organization details</h4>
                </div>
            </div>
            <div class="community-detail-grid community-detail-grid--rail">
                @if(filled($awarenessPostedBy))
                    <div class="community-detail-item">
                        <span class="community-detail-item__label">Posted by</span>
                        <span class="community-detail-item__value">{{ $awarenessPostedBy }}</span>
                    </div>
                @endif
                @if(filled($awarenessOrganizationName))
                    <div class="community-detail-item">
                        <span class="community-detail-item__label">Organization name</span>
                        <span class="community-detail-item__value">{{ $awarenessOrganizationName }}</span>
                    </div>
                @endif
            </div>
        </div>
    @endif

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
                    'title' => 'Awareness location map',
                ])
            @endif
            @if($structuredLocation->isNotEmpty())
                <div class="community-detail-grid community-detail-grid--rail {{ $post->hasMapCoordinates() ? 'mt-3' : '' }}">
                    @foreach($structuredLocation as $key => $value)
                        <div class="community-detail-item">
                            <span class="community-detail-item__label">{{ $locationLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</span>
                            <span class="community-detail-item__value">{{ $value }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    @include('community.partials.awareness-additional-details', [
        'post' => $post,
        'railLayout' => true,
    ])
</div>
