@if($post->isLocalVoicesPost())
    @php
        $portalSidebarLayout = $portalSidebarLayout ?? false;
        $mainCategory = data_get($post->meta, 'local_voice_category') ?: $post->category;
        $voiceType = data_get($post->meta, 'local_voice_type');
        $issueType = data_get($post->meta, 'local_voice_issue_type');
        $affectedCommunities = array_values(array_filter((array) data_get($post->meta, 'local_voice_affected_communities', [])));
        $impactLevel = data_get($post->meta, 'local_voice_impact_level');
        $authorities = array_values(array_filter((array) data_get($post->meta, 'local_voice_authorities', [])));
        $callForActions = array_values(array_filter((array) data_get($post->meta, 'local_voice_call_for_action', [])));
        $statusTracker = data_get($post->meta, 'local_voice_status_tracker');
        $suggestedSolution = data_get($post->meta, 'local_voice_suggested_solution');
        $estimatedBenefit = data_get($post->meta, 'local_voice_estimated_benefit');
        $videoType = data_get($post->meta, 'local_voice_video_type');
        $initiatives = array_values(array_filter((array) data_get($post->meta, 'local_voice_initiatives', [])));
        $heroName = data_get($post->meta, 'local_voice_hero_name');
        $heroLocation = data_get($post->meta, 'local_voice_hero_location');
        $heroContribution = data_get($post->meta, 'local_voice_hero_contribution');
        $heroAchievements = data_get($post->meta, 'local_voice_hero_achievements');
        $eventDate = data_get($post->meta, 'local_voice_event_date');
        $eventTime = data_get($post->meta, 'local_voice_event_time');
        $eventVenue = data_get($post->meta, 'local_voice_event_venue');
        $eventOrganizer = data_get($post->meta, 'local_voice_event_organizer');
        $structuredLocation = $post->structuredLocationForDisplay();
        $locationLabels = \App\Models\CommunityPost::structuredLocationLabelsFor($post->content_type);
        $photoEvidence = $post->localVoicePhotoEvidence();
        $documents = $post->localVoiceDocuments();
        $heroImages = $post->localVoiceHeroImages();
        $statusSteps = \App\Support\CommunityContentTaxonomy::localVoiceStatusTrackerSteps();
        $publishAsLabel = \App\Support\CommunityContentTaxonomy::localVoicePublishAsOptions()[$post->resolvedPublishAs()]
            ?? $post->publishAsLabel();
    @endphp

    @unless($portalSidebarLayout)
    @if(filled($voiceType) || filled($mainCategory) || filled($issueType) || filled($impactLevel))
        <div class="business-hero-strip mb-4">
            @if(filled($voiceType))
                <div class="business-hero-strip__item">
                    <span class="business-hero-strip__label">Voice type</span>
                    <span class="business-hero-strip__value">{{ $voiceType }}</span>
                </div>
            @endif
            @if(filled($mainCategory))
                <div class="business-hero-strip__item">
                    <span class="business-hero-strip__label">Main category</span>
                    <span class="business-hero-strip__value">{{ $mainCategory }}</span>
                </div>
            @endif
            @if(filled($issueType))
                <div class="business-hero-strip__item">
                    <span class="business-hero-strip__label">Issue type</span>
                    <span class="business-hero-strip__value">{{ $issueType }}</span>
                </div>
            @endif
            @if(filled($impactLevel))
                <div class="business-hero-strip__item">
                    <span class="business-hero-strip__label">Impact level</span>
                    <span class="business-hero-strip__value">{{ $impactLevel }}</span>
                </div>
            @endif
            @if($post->localVoiceVisibilitySetting() !== 'public')
                <div class="business-hero-strip__item">
                    <span class="business-hero-strip__label">Visibility</span>
                    <span class="business-hero-strip__value">{{ $post->localVoiceVisibilityLabel() }}</span>
                </div>
            @endif
        </div>
    @endif

    @if(filled($statusTracker))
        <div class="business-section-panel about-box mb-4 border-success">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-route text-success" aria-hidden="true"></i>
                <div>
                    <h4 class="mb-0">Status tracker</h4>
                    <p class="text-muted small mb-0">Where this issue stands in the civic process.</p>
                </div>
            </div>
            <div class="local-voice-status-tracker d-flex flex-wrap gap-2">
                @foreach($statusSteps as $step)
                    <span class="badge {{ $step === $statusTracker ? 'bg-success' : 'bg-light text-dark border' }}">{{ $step }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($affectedCommunities !== [])
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-users" aria-hidden="true"></i>
                <h4 class="mb-0">Affected community</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($affectedCommunities as $community)
                    <span class="badge bg-light text-dark border">{{ $community }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($structuredLocation->isNotEmpty())
        <div class="business-section-panel about-box mb-4 border-danger">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-location-dot text-danger" aria-hidden="true"></i>
                <div>
                    <h4 class="mb-0">Location details</h4>
                    <p class="text-muted small mb-0">Local Voices are location-centric — this post is tied to the place below.</p>
                </div>
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
                <p class="mb-2 mt-3"><strong>GPS location:</strong> {{ $post->location_lat }}, {{ $post->location_lng }}</p>
                <div class="ratio ratio-16x9 border rounded overflow-hidden">
                    <iframe
                        title="Local voice GPS map"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        src="https://www.openstreetmap.org/export/embed.html?bbox={{ $post->location_lng - 0.02 }},{{ $post->location_lat - 0.02 }},{{ $post->location_lng + 0.02 }},{{ $post->location_lat + 0.02 }}&layer=mapnik&marker={{ $post->location_lat }},{{ $post->location_lng }}"
                    ></iframe>
                </div>
            @endif
        </div>
    @endif
    @endunless

    @if(filled($suggestedSolution) || filled($estimatedBenefit))
        <div class="business-section-panel about-box mb-4 border-primary">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-lightbulb text-primary" aria-hidden="true"></i>
                <h4 class="mb-0">Community solution</h4>
            </div>
            @if(filled($suggestedSolution))
                <div class="business-meta-item mb-3">
                    <span class="business-meta-item__label">Suggested solution</span>
                    <span>{!! nl2br(e($suggestedSolution)) !!}</span>
                </div>
            @endif
            @if(filled($estimatedBenefit))
                <div class="business-meta-item">
                    <span class="business-meta-item__label">Estimated benefit</span>
                    <span>{{ $estimatedBenefit }}</span>
                </div>
            @endif
        </div>
    @endif

    @if($authorities !== [] || $callForActions !== [])
        <div class="row g-3 mb-4">
            @if($authorities !== [])
                <div class="col-md-6">
                    <div class="business-section-panel about-box h-100">
                        <div class="business-section-panel__header">
                            <i class="fa-solid fa-building-columns" aria-hidden="true"></i>
                            <h4 class="mb-0">Authority concerned</h4>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($authorities as $authority)
                                <span class="badge bg-light text-dark border">{{ $authority }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
            @if($callForActions !== [])
                <div class="col-md-6">
                    <div class="business-section-panel about-box h-100">
                        <div class="business-section-panel__header">
                            <i class="fa-solid fa-bullhorn" aria-hidden="true"></i>
                            <h4 class="mb-0">Call for action</h4>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($callForActions as $action)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $action }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if($voiceType === 'Local Hero' && (filled($heroName) || filled($heroContribution) || $heroImages !== []))
        <div class="business-section-panel about-box mb-4 border-warning">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-medal text-warning" aria-hidden="true"></i>
                <h4 class="mb-0">Local hero</h4>
            </div>
            <div class="row g-3">
                @if(filled($heroName))
                    <div class="col-md-6">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Person name</span>
                            <span>{{ $heroName }}</span>
                        </div>
                    </div>
                @endif
                @if(filled($heroLocation))
                    <div class="col-md-6">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Location</span>
                            <span>{{ $heroLocation }}</span>
                        </div>
                    </div>
                @endif
                @if(filled($heroContribution))
                    <div class="col-12">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Contribution</span>
                            <span>{!! nl2br(e($heroContribution)) !!}</span>
                        </div>
                    </div>
                @endif
                @if(filled($heroAchievements))
                    <div class="col-12">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Achievements</span>
                            <span>{!! nl2br(e($heroAchievements)) !!}</span>
                        </div>
                    </div>
                @endif
            </div>
            @if($heroImages !== [])
                <div class="business-gallery-grid mt-3">
                    @foreach($heroImages as $image)
                        <a href="{{ data_get($image, 'url') }}" target="_blank" rel="noopener" class="business-gallery-card">
                            <img src="{{ data_get($image, 'url') }}" alt="{{ data_get($image, 'name', 'Hero image') }}" loading="lazy">
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    @if($voiceType === 'Community Initiative' && $initiatives !== [])
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-people-group" aria-hidden="true"></i>
                <h4 class="mb-0">Community initiative</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($initiatives as $initiative)
                    <span class="badge bg-light text-dark border">{{ $initiative }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if(filled($eventDate) || filled($eventVenue) || filled($eventOrganizer))
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                <h4 class="mb-0">Event details</h4>
            </div>
            <div class="row g-3">
                @if(filled($eventDate))
                    <div class="col-md-3">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Date</span>
                            <span>{{ \Illuminate\Support\Carbon::parse($eventDate)->format('M j, Y') }}</span>
                        </div>
                    </div>
                @endif
                @if(filled($eventTime))
                    <div class="col-md-3">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Time</span>
                            <span>{{ $eventTime }}</span>
                        </div>
                    </div>
                @endif
                @if(filled($eventOrganizer))
                    <div class="col-md-6">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Organizer</span>
                            <span>{{ $eventOrganizer }}</span>
                        </div>
                    </div>
                @endif
                @if(filled($eventVenue))
                    <div class="col-12">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Venue</span>
                            <span>{{ $eventVenue }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if($photoEvidence !== [])
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-camera" aria-hidden="true"></i>
                <h4 class="mb-0">Photo evidence</h4>
            </div>
            <div class="business-gallery-grid">
                @foreach($photoEvidence as $photo)
                    <a href="{{ data_get($photo, 'url') }}" target="_blank" rel="noopener" class="business-gallery-card">
                        <img src="{{ data_get($photo, 'url') }}" alt="{{ data_get($photo, 'name', 'Photo evidence') }}" loading="lazy">
                        <span class="business-gallery-card__label">{{ data_get($photo, 'name', 'Photo') }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if($post->hasVideo())
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-video" aria-hidden="true"></i>
                <h4 class="mb-0">
                    Video evidence
                    @if(filled($videoType))
                        <span class="text-muted fw-normal fs-6">— {{ $videoType }}</span>
                    @endif
                </h4>
            </div>
            @if($post->youtubeEmbedUrl())
                <div class="ratio ratio-16x9 rounded overflow-hidden shadow-sm">
                    <iframe
                        src="{{ $post->youtubeEmbedUrl() }}"
                        title="Video for {{ $post->title }}"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen
                    ></iframe>
                </div>
            @elseif($post->videoFileUrl())
                <video controls class="w-100 rounded shadow-sm" preload="metadata">
                    <source src="{{ $post->videoFileUrl() }}">
                    Your browser does not support embedded video playback.
                </video>
            @endif
        </div>
    @endif

    @if($documents !== [])
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
                <h4 class="mb-0">Documents</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($documents as $document)
                    <a href="{{ data_get($document, 'url') }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                        <i class="fa-solid fa-download me-1" aria-hidden="true"></i>{{ data_get($document, 'name', 'Document') }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <div class="business-meta-item mb-4">
        <span class="business-meta-item__label">Published as</span>
        <span>{{ $publishAsLabel }}</span>
    </div>
@endif
