@if($post->isAwarenessPost())
    @php
        $structuredLocation = $post->structuredLocationForDisplay();
        $locationLabels = \App\Models\CommunityPost::structuredLocationLabelsFor($post->content_type);
        $awarenessPostedBy = data_get($post->meta, 'awareness_posted_by');
        $awarenessOrganizationName = data_get($post->meta, 'awareness_organization_name');
    @endphp

    @include('community.partials.awareness-meta-details', ['post' => $post])

    @if(filled($awarenessPostedBy) || filled($awarenessOrganizationName))
        <div class="awareness-section-panel about-box mb-4">
            <div class="awareness-section-panel__header">
                <i class="fa-solid fa-building" aria-hidden="true"></i>
                <h4 class="mb-0">Organization details</h4>
            </div>
            <div class="row g-3">
                @if(filled($awarenessPostedBy))
                    <div class="col-md-6">
                        <div class="awareness-meta-item">
                            <span class="awareness-meta-item__label">Posted by</span>
                            <span>{{ $awarenessPostedBy }}</span>
                        </div>
                    </div>
                @endif
                @if(filled($awarenessOrganizationName))
                    <div class="col-md-6">
                        <div class="awareness-meta-item">
                            <span class="awareness-meta-item__label">Organization name</span>
                            <span>{{ $awarenessOrganizationName }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if($structuredLocation->isNotEmpty() || $post->hasMapCoordinates())
        <div class="awareness-section-panel about-box mb-4">
            <div class="awareness-section-panel__header">
                <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                <h4 class="mb-0">Location</h4>
            </div>
            <div class="row g-3 align-items-start awareness-location-layout">
                @if($structuredLocation->isNotEmpty())
                    <div class="{{ $post->hasMapCoordinates() ? 'col-lg-7' : 'col-12' }}">
                        <div class="row g-3">
                            @foreach($structuredLocation as $key => $value)
                                <div class="col-md-6 col-lg-4">
                                    <div class="awareness-meta-item">
                                        <span class="awareness-meta-item__label">{{ $locationLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</span>
                                        <span>{{ $value }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                @if($post->hasMapCoordinates())
                    <div class="{{ $structuredLocation->isNotEmpty() ? 'col-lg-5' : 'col-12' }}">
                        @include('community.partials.location-map-embed', [
                            'post' => $post,
                            'title' => 'Awareness location map',
                            'wrapperClass' => 'awareness-location-map h-100',
                        ])
                    </div>
                @endif
            </div>
        </div>
    @endif

    @include('community.partials.awareness-media-sections', ['post' => $post])
    @include('community.partials.awareness-engagement-sections', [
        'post' => $post,
        'awarenessEngagement' => $awarenessEngagement ?? null,
        'awarenessPledgeCounts' => $awarenessPledgeCounts ?? [],
    ])
@endif
