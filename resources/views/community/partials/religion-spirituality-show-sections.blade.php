@if($post->isReligionSpiritualityPost())
    @php
        $postType = $post->religionSpiritualityPostTypeLabel();
        $category = $post->religionSpiritualityCategoryLabel();
        $tradition = data_get($post->meta, 'religion_spirituality_tradition');
        $audiences = $post->religionSpiritualityTargetAudiences();
        $moralMessages = (array) data_get($post->meta, 'religion_spirituality_moral_messages', []);
        $meditationTopics = $post->religionSpiritualityMeditationTopics();
        $serviceActivities = $post->religionSpiritualityCommunityServiceActivities();
        $askCommunity = data_get($post->meta, 'religion_spirituality_ask_community');
        $serviceActions = (array) data_get($post->meta, 'religion_spirituality_related_service_actions', []);
        $documents = (array) data_get($post->meta, 'religion_spirituality_documents', []);
        $gallery = (array) data_get($post->meta, 'religion_spirituality_gallery', []);
        $audio = data_get($post->meta, 'religion_spirituality_audio');
        $videoType = data_get($post->meta, 'religion_spirituality_video_type');
        $uniqueFeatures = $post->religionSpiritualityUniqueFeatureLabels();
        $capabilities = [
            ['label' => 'Digital Pilgrimage Guide', 'enabled' => (bool) data_get($post->meta, 'religion_spirituality_enable_digital_pilgrimage_guide'), 'icon' => 'fa-map-location-dot'],
            ['label' => 'Festival Calendar', 'enabled' => (bool) data_get($post->meta, 'religion_spirituality_enable_festival_calendar'), 'icon' => 'fa-calendar-days'],
            ['label' => 'Service Directory', 'enabled' => (bool) data_get($post->meta, 'religion_spirituality_enable_community_service_directory'), 'icon' => 'fa-hands-holding-heart'],
            ['label' => 'Wisdom Library', 'enabled' => (bool) data_get($post->meta, 'religion_spirituality_enable_wisdom_library'), 'icon' => 'fa-book'],
            ['label' => 'Comments', 'enabled' => (bool) $post->allow_comments, 'icon' => 'fa-comments'],
            ['label' => 'Questions', 'enabled' => (bool) $post->allow_questions, 'icon' => 'fa-circle-question'],
            ['label' => 'Share', 'enabled' => (bool) $post->allow_sharing, 'icon' => 'fa-share-nodes'],
            ['label' => 'Poll', 'enabled' => (bool) $post->allowsPoll(), 'icon' => 'fa-square-poll-vertical'],
        ];
    @endphp

    <div class="rs-show-overview">
        <div class="rs-show-overview__kicker">Religion &amp; Spirituality · SoilnWater community</div>
        <p class="rs-show-overview__objective mb-0">{{ \App\Support\CommunityContentTaxonomy::religionSpiritualityObjective() }}</p>
        <div class="rs-show-overview__chips mt-3">
            @if(filled($postType))
                <span class="rs-show-chip">{{ $postType }}</span>
            @endif
            @if(filled($category))
                <span class="rs-show-chip">{{ $category }}</span>
            @endif
            @if(filled($tradition))
                <span class="rs-show-chip">{{ $tradition }}</span>
            @endif
            @foreach($uniqueFeatures as $feature)
                <span class="rs-show-chip rs-show-chip--flagship"><i class="fa-solid fa-star me-1" aria-hidden="true"></i>{{ $feature }}</span>
            @endforeach
        </div>
    </div>

    @if($audiences !== [])
        <div class="rs-section-panel">
            <div class="rs-section-panel__label">Target audience</div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($audiences as $audience)
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">{{ $audience }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($moralMessages !== [])
        <div class="rs-section-panel">
            <div class="rs-section-panel__label">Moral message</div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($moralMessages as $message)
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">{{ $message }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if(filled(data_get($post->meta, 'religion_spirituality_scripture_name')))
        <div class="rs-flagship-banner rs-flagship-banner--scripture">
            <div class="rs-flagship-banner__heading">
                <i class="fa-solid fa-book-bible" aria-hidden="true"></i>Scripture reference
            </div>
            <p class="mb-1">
                <strong>{{ data_get($post->meta, 'religion_spirituality_scripture_name') }}</strong>
                @if(filled(data_get($post->meta, 'religion_spirituality_scripture_chapter')))
                    · Chapter {{ data_get($post->meta, 'religion_spirituality_scripture_chapter') }}
                @endif
                @if(filled(data_get($post->meta, 'religion_spirituality_scripture_verse')))
                    · Verse {{ data_get($post->meta, 'religion_spirituality_scripture_verse') }}
                @endif
            </p>
            @if(filled(data_get($post->meta, 'religion_spirituality_scripture_reference')))
                <p class="small text-muted mb-0">{{ data_get($post->meta, 'religion_spirituality_scripture_reference') }}</p>
            @endif
        </div>
    @endif

    @if(filled(data_get($post->meta, 'religion_spirituality_festival_name')))
        <div class="rs-flagship-banner rs-flagship-banner--festival">
            <div class="rs-flagship-banner__heading">
                <i class="fa-solid fa-sun" aria-hidden="true"></i>Festival information
            </div>
            <p class="mb-2 fw-semibold">
                {{ data_get($post->meta, 'religion_spirituality_festival_name') }}
                @if(filled(data_get($post->meta, 'religion_spirituality_festival_date')))
                    <span class="text-muted fw-normal">— {{ data_get($post->meta, 'religion_spirituality_festival_date') }}</span>
                @endif
            </p>
            @foreach([
                'Historical significance' => data_get($post->meta, 'religion_spirituality_festival_historical_significance'),
                'Traditional practices' => data_get($post->meta, 'religion_spirituality_festival_traditional_practices'),
                'Celebration methods' => data_get($post->meta, 'religion_spirituality_festival_celebration_methods'),
                'Regional variations' => data_get($post->meta, 'religion_spirituality_festival_regional_variations'),
            ] as $label => $value)
                @if(filled($value))
                    <p class="small mb-2"><span class="text-muted">{{ $label }}:</span> {{ $value }}</p>
                @endif
            @endforeach
        </div>
    @endif

    @if(filled(data_get($post->meta, 'religion_spirituality_pilgrimage_name')))
        <div class="rs-flagship-banner rs-flagship-banner--pilgrimage">
            <div class="rs-flagship-banner__heading">
                <i class="fa-solid fa-route" aria-hidden="true"></i>Pilgrimage guide
            </div>
            <p class="mb-2 fw-semibold">
                {{ data_get($post->meta, 'religion_spirituality_pilgrimage_name') }}
                @if(filled(data_get($post->meta, 'religion_spirituality_pilgrimage_location')))
                    <span class="text-muted fw-normal">— {{ data_get($post->meta, 'religion_spirituality_pilgrimage_location') }}</span>
                @endif
            </p>
            @foreach([
                'Best time to visit' => data_get($post->meta, 'religion_spirituality_pilgrimage_best_time'),
                'History' => data_get($post->meta, 'religion_spirituality_pilgrimage_history'),
                'Facilities' => data_get($post->meta, 'religion_spirituality_pilgrimage_facilities'),
                'Travel tips' => data_get($post->meta, 'religion_spirituality_pilgrimage_travel_tips'),
                'Accommodation' => data_get($post->meta, 'religion_spirituality_pilgrimage_accommodation'),
            ] as $label => $value)
                @if(filled($value))
                    <p class="small mb-2"><span class="text-muted">{{ $label }}:</span> {{ $value }}</p>
                @endif
            @endforeach
        </div>
    @endif

    @if(filled(data_get($post->meta, 'religion_spirituality_place_of_worship_type')))
        <div class="rs-section-panel">
            <div class="rs-section-panel__label">Place of worship</div>
            <p class="mb-0 fw-semibold">{{ data_get($post->meta, 'religion_spirituality_place_of_worship_type') }}</p>
        </div>
    @endif

    @if(collect([
        data_get($post->meta, 'religion_spirituality_location_country'),
        data_get($post->meta, 'religion_spirituality_location_state'),
        data_get($post->meta, 'religion_spirituality_location_city'),
        data_get($post->meta, 'religion_spirituality_location_gps'),
    ])->contains(fn ($value) => filled($value)))
        <div class="rs-section-panel">
            <div class="rs-section-panel__label">Location</div>
            <p class="mb-1">
                {{ collect([
                    data_get($post->meta, 'religion_spirituality_location_city'),
                    data_get($post->meta, 'religion_spirituality_location_district'),
                    data_get($post->meta, 'religion_spirituality_location_state'),
                    data_get($post->meta, 'religion_spirituality_location_country'),
                ])->filter()->implode(', ') }}
            </p>
            @if(filled(data_get($post->meta, 'religion_spirituality_location_gps')))
                <a href="{{ str_starts_with((string) data_get($post->meta, 'religion_spirituality_location_gps'), 'http') ? data_get($post->meta, 'religion_spirituality_location_gps') : 'https://www.google.com/maps/search/?api=1&query='.urlencode((string) data_get($post->meta, 'religion_spirituality_location_gps')) }}" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">
                    <i class="fa-solid fa-location-dot me-1" aria-hidden="true"></i>Open map
                </a>
            @endif
        </div>
    @endif

    @if($meditationTopics !== [])
        <div class="rs-section-panel">
            <div class="rs-section-panel__label">Meditation &amp; wellness</div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($meditationTopics as $topic)
                    <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2">{{ $topic }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($serviceActivities !== [])
        <div class="rs-section-panel">
            <div class="rs-section-panel__label">Community service</div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($serviceActivities as $activity)
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">{{ $activity }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @include('community.partials.religion-spirituality-unique-features-show', ['post' => $post])

    <div class="rs-section-panel">
        <div class="rs-section-panel__label">Post capabilities</div>
        <div class="rs-capability-grid">
            @foreach($capabilities as $capability)
                <span class="rs-capability-pill {{ $capability['enabled'] ? '' : 'is-disabled' }}">
                    <i class="fa-solid {{ $capability['icon'] }}" aria-hidden="true"></i>
                    {{ $capability['label'] }}
                </span>
            @endforeach
        </div>
    </div>

    @if($post->featuredImageUrl() || $post->hasVideo() || $gallery !== [] || filled($audio) || $documents !== [])
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-images text-primary" aria-hidden="true"></i>
                <h4 class="mb-0">Media &amp; documents</h4>
            </div>

            @if($post->featuredImageUrl())
                <div class="mb-3">
                    <div class="business-meta-item__label mb-2">Featured image</div>
                    <img src="{{ $post->featuredImageUrl() }}" alt="Featured Religion & Spirituality image" class="img-fluid rounded border">
                </div>
            @endif

            @if($gallery !== [])
                <div class="mb-3 rs-gallery-grid">
                    <div class="business-meta-item__label mb-2">Image gallery</div>
                    <div class="row g-2">
                        @foreach($gallery as $image)
                            <div class="col-6 col-md-4 col-lg-3">
                                <img src="{{ data_get($image, 'url') }}" alt="" class="img-fluid rounded border">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($post->hasVideo())
                <div class="mb-3">
                    <div class="business-meta-item__label mb-2">
                        Video{{ filled($videoType) ? ' · '.$videoType : '' }}
                    </div>
                    @if($post->youtubeEmbedUrl())
                        <div class="ratio ratio-16x9 rounded overflow-hidden"><iframe src="{{ $post->youtubeEmbedUrl() }}" title="Religion & Spirituality video" allowfullscreen></iframe></div>
                    @elseif($post->videoFileUrl())
                        <video controls class="w-100 rounded" preload="metadata"><source src="{{ $post->videoFileUrl() }}"></video>
                    @endif
                </div>
            @endif

            @if(filled($audio))
                <div class="mb-3">
                    <div class="business-meta-item__label mb-2">
                        Audio{{ filled(data_get($post->meta, 'religion_spirituality_audio_type')) ? ' · '.data_get($post->meta, 'religion_spirituality_audio_type') : '' }}
                    </div>
                    <audio controls class="w-100 rounded" preload="metadata" src="{{ data_get($audio, 'url') }}"></audio>
                </div>
            @endif

            @if($documents !== [])
                <div class="d-flex flex-wrap gap-2">
                    @foreach($documents as $document)
                        <a href="{{ data_get($document, 'url') }}" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener">
                            <i class="fa-solid fa-file-lines me-1"></i>{{ data_get($document, 'name', 'Document') }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    @if($serviceActions !== [])
        <div class="rs-flagship-banner rs-flagship-banner--service">
            <div class="rs-flagship-banner__heading">
                <i class="fa-solid fa-hands-holding-heart" aria-hidden="true"></i>Related community service
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($serviceActions as $action)
                    <span class="badge bg-white text-success border border-success-subtle px-3 py-2">{{ $action }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if(filled($askCommunity))
        <div class="rs-ask-community">
            <div class="rs-section-panel__label mb-2">Ask the community</div>
            <p class="rs-ask-community__quote">“{{ $askCommunity }}”</p>
        </div>
    @endif

    <div class="rs-guidelines">
        <i class="fa-solid fa-shield-heart me-1 text-success" aria-hidden="true"></i>
        {{ \App\Support\CommunityContentTaxonomy::religionSpiritualityGuidelinesText() }}
    </div>
@endif
