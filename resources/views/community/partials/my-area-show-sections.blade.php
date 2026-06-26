@if($post->isMyAreaPost())
    @php
        $activity = $post->myAreaActivityType();
        $topic = $post->myAreaTopicCategory();
        $impact = data_get($post->meta, 'my_area_impact_level');
        $status = data_get($post->meta, 'my_area_status_tracker');
        $authorities = array_values(array_filter((array) data_get($post->meta, 'my_area_authorities', [])));
        $affected = array_values(array_filter((array) data_get($post->meta, 'my_area_affected_communities', [])));
        $solution = data_get($post->meta, 'my_area_suggested_solution');
        $structuredLocation = $post->structuredLocationForDisplay();
        $locationLabels = \App\Models\CommunityPost::structuredLocationLabelsFor($post->content_type);
        $statusSteps = \App\Support\CommunityContentTaxonomy::myAreaStatusTrackerSteps();
    @endphp

    @if(filled($activity) || filled($topic) || filled($impact))
        <div class="business-hero-strip mb-4">
            @if(filled($activity))
                <div class="business-hero-strip__item">
                    <span class="business-hero-strip__label">Activity</span>
                    <span class="business-hero-strip__value">{{ $activity }}</span>
                </div>
            @endif
            @if(filled($topic))
                <div class="business-hero-strip__item">
                    <span class="business-hero-strip__label">Topic</span>
                    <span class="business-hero-strip__value">{{ $topic }}</span>
                </div>
            @endif
            @if(filled($impact))
                <div class="business-hero-strip__item">
                    <span class="business-hero-strip__label">Impact</span>
                    <span class="business-hero-strip__value">{{ $impact }}</span>
                </div>
            @endif
        </div>
    @endif

    @if(filled($status))
        <div class="business-section-panel about-box mb-4 border-success">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-route text-success" aria-hidden="true"></i>
                <h4 class="mb-0">Resolution monitoring</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($statusSteps as $step)
                    <span class="badge {{ $step === $status ? 'bg-success' : 'bg-light text-dark border' }}">{{ $step }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($structuredLocation->isNotEmpty())
        <div class="business-section-panel about-box mb-4 border-danger">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-location-dot text-danger" aria-hidden="true"></i>
                <h4 class="mb-0">Area location</h4>
            </div>
            <div class="row g-3">
                @foreach($structuredLocation as $key => $value)
                    <div class="col-md-6 col-lg-4">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">{{ $locationLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</span>
                            <span>{{ $value }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
            @if($post->hasMapCoordinates())
                <div class="ratio ratio-16x9 border rounded overflow-hidden mt-3">
                    <iframe title="My Area map" loading="lazy" src="https://www.openstreetmap.org/export/embed.html?bbox={{ $post->location_lng - 0.02 }},{{ $post->location_lat - 0.02 }},{{ $post->location_lng + 0.02 }},{{ $post->location_lat + 0.02 }}&layer=mapnik&marker={{ $post->location_lat }},{{ $post->location_lng }}"></iframe>
                </div>
            @endif
        </div>
    @endif

    @if($authorities !== [])
        <div class="business-section-panel about-box mb-4">
            <h4 class="mb-2">Authority tagging</h4>
            <div class="d-flex flex-wrap gap-2">
                @foreach($authorities as $authority)
                    <span class="badge bg-light text-dark border">{{ $authority }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($affected !== [])
        <div class="business-section-panel about-box mb-4">
            <h4 class="mb-2">Affected community</h4>
            <div class="d-flex flex-wrap gap-2">
                @foreach($affected as $community)
                    <span class="badge bg-light text-dark border">{{ $community }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if(filled($solution))
        <div class="business-section-panel about-box mb-4 border-primary">
            <h4 class="mb-2">Suggested solution</h4>
            <p class="mb-0">{!! nl2br(e($solution)) !!}</p>
        </div>
    @endif

    @if($activity === 'Recognize Heroes')
        @php
            $heroName = data_get($post->meta, 'my_area_hero_name');
            $heroContribution = data_get($post->meta, 'my_area_hero_contribution');
            $heroImages = $post->myAreaHeroImages();
        @endphp
        @if(filled($heroName) || filled($heroContribution))
            <div class="business-section-panel about-box mb-4 border-warning">
                <h4 class="mb-3">Local hero</h4>
                @if(filled($heroName))<p><strong>{{ $heroName }}</strong></p>@endif
                @if(filled(data_get($post->meta, 'my_area_hero_location')))
                    <p class="text-muted small mb-2"><i class="fa-solid fa-location-dot me-1"></i>{{ data_get($post->meta, 'my_area_hero_location') }}</p>
                @endif
                @if(filled($heroContribution))<p class="mb-0">{!! nl2br(e($heroContribution)) !!}</p>@endif
                @if($heroImages !== [])
                    <div class="business-gallery-grid mt-3">
                        @foreach($heroImages as $image)
                            <a href="{{ data_get($image, 'url') }}" target="_blank" rel="noopener" class="business-gallery-card">
                                <img src="{{ data_get($image, 'url') }}" alt="Hero image" loading="lazy">
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    @endif

    @if($activity === 'Share Local Achievements')
        @php
            $achievementTitle = data_get($post->meta, 'my_area_achievement_title');
            $achievementDescription = data_get($post->meta, 'my_area_achievement_description');
        @endphp
        @if(filled($achievementTitle) || filled($achievementDescription))
            <div class="business-section-panel about-box mb-4">
                <h4 class="mb-2">{{ $achievementTitle ?: 'Local achievement' }}</h4>
                @if(filled($achievementDescription))<p class="mb-0">{!! nl2br(e($achievementDescription)) !!}</p>@endif
            </div>
        @endif
    @endif

    @if($post->myAreaPhotoEvidence() !== [])
        <div class="business-section-panel about-box mb-4">
            <h4 class="mb-3">Photo evidence</h4>
            <div class="business-gallery-grid">
                @foreach($post->myAreaPhotoEvidence() as $photo)
                    <a href="{{ data_get($photo, 'url') }}" target="_blank" rel="noopener" class="business-gallery-card">
                        <img src="{{ data_get($photo, 'url') }}" alt="Evidence" loading="lazy">
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if($post->hasVideo())
        <div class="business-section-panel about-box mb-4">
            <h4 class="mb-3">Video</h4>
            @if($post->youtubeEmbedUrl())
                <div class="ratio ratio-16x9 rounded overflow-hidden"><iframe src="{{ $post->youtubeEmbedUrl() }}" title="Video" allowfullscreen></iframe></div>
            @elseif($post->videoFileUrl())
                <video controls class="w-100 rounded" preload="metadata"><source src="{{ $post->videoFileUrl() }}"></video>
            @endif
        </div>
    @endif

    @if($post->myAreaDocuments() !== [])
        <div class="business-section-panel about-box mb-4">
            <h4 class="mb-2">Documents</h4>
            <div class="d-flex flex-wrap gap-2">
                @foreach($post->myAreaDocuments() as $document)
                    <a href="{{ data_get($document, 'url') }}" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener">{{ data_get($document, 'name', 'Document') }}</a>
                @endforeach
            </div>
        </div>
    @endif
@endif
