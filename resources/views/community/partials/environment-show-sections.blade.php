@if($post->isEnvironmentPost())
    @php
        $postType = $post->environmentPostTypeLabel();
        $category = $post->environmentCategoryLabel();
        $issueType = data_get($post->meta, 'environment_issue_type');
        $initiativeType = data_get($post->meta, 'environment_initiative_type');
        $naturalFeature = data_get($post->meta, 'environment_natural_feature_name');
        $mapPinType = data_get($post->meta, 'environment_map_pin_type');
        $structuredLocation = $post->structuredLocationForDisplay();
        $locationLabels = \App\Models\CommunityPost::structuredLocationLabelsFor($post->content_type);
        $galleryCategories = \App\Support\CommunityContentTaxonomy::environmentGalleryCategories();
        $videoType = data_get($post->meta, 'environment_video_type');
        $askCommunity = data_get($post->meta, 'environment_ask_community');
        $capabilities = [
            ['label' => 'Impact calculator', 'enabled' => $post->enablesEnvironmentImpactCalculator(), 'icon' => 'fa-calculator'],
            ['label' => 'Green Map', 'enabled' => $post->showsOnGreenMap(), 'icon' => 'fa-map-location-dot'],
            ['label' => 'Green Leader', 'enabled' => $post->enablesEnvironmentGreenLeader(), 'icon' => 'fa-award'],
            ['label' => 'Comments', 'enabled' => (bool) $post->allow_comments, 'icon' => 'fa-comments'],
            ['label' => 'Questions', 'enabled' => (bool) $post->allow_questions, 'icon' => 'fa-circle-question'],
            ['label' => 'Suggestions', 'enabled' => (bool) $post->allow_suggestions, 'icon' => 'fa-lightbulb'],
            ['label' => 'Feedback', 'enabled' => (bool) $post->allow_feedback, 'icon' => 'fa-message'],
            ['label' => 'Share', 'enabled' => (bool) $post->allow_sharing, 'icon' => 'fa-share-nodes'],
            ['label' => 'Poll', 'enabled' => (bool) $post->allow_poll, 'icon' => 'fa-square-poll-vertical'],
        ];
    @endphp

    <div class="env-show-overview">
        <div class="env-show-overview__kicker">Environment · SoilnWater conservation network</div>
        <div class="env-show-overview__title">Geo-tagged environmental stories, conservation impact, and community action</div>
        <div class="env-show-overview__chips">
            @if(filled($postType))
                <span class="env-show-chip">{{ $postType }}</span>
            @endif
            @if(filled($category))
                <span class="env-show-chip">{{ $category }}</span>
            @endif
            @if(filled($naturalFeature))
                <span class="env-show-chip env-show-chip--flagship"><i class="fa-solid fa-mountain-sun me-1"></i>{{ $naturalFeature }}</span>
            @endif
            @if(filled($mapPinType))
                <span class="env-show-chip"><i class="fa-solid fa-location-dot me-1"></i>{{ $mapPinType }}</span>
            @endif
            @if($post->showsOnGreenMap())
                <span class="env-show-chip env-show-chip--flagship"><i class="fa-solid fa-map-location-dot me-1"></i>Green Map</span>
            @endif
            @if($post->enablesEnvironmentGreenLeader())
                <span class="env-show-chip env-show-chip--flagship"><i class="fa-solid fa-award me-1"></i>Green Leader</span>
            @endif
        </div>
    </div>

    @if(filled($issueType))
        <div class="env-flagship-banner env-flagship-banner--issue d-flex align-items-start gap-3 mb-4" role="status">
            <i class="fa-solid fa-triangle-exclamation text-danger fs-4 mt-1" aria-hidden="true"></i>
            <div class="flex-grow-1">
                <div class="text-danger fw-bold mb-1">Environmental issue reported</div>
                <p class="mb-2"><strong>Issue type:</strong> {{ $issueType }}</p>
                @if($post->environmentWasteTypes() !== [])
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($post->environmentWasteTypes() as $wasteType)
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">{{ $wasteType }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if(filled($initiativeType))
        <div class="env-flagship-banner mb-4">
            <div class="text-success fw-bold mb-1"><i class="fa-solid fa-people-group me-1"></i>Community initiative</div>
            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">{{ $initiativeType }}</span>
        </div>
    @endif

    @if($post->environmentHasWaterDetails())
        <div class="env-flagship-banner env-flagship-banner--water d-flex align-items-start gap-3 mb-4" role="status">
            <i class="fa-solid fa-droplet text-info fs-4 mt-1" aria-hidden="true"></i>
            <div class="flex-grow-1">
                <div class="text-info fw-bold mb-1">Water conservation · flagship focus</div>
                <div class="row g-3">
                    @foreach([
                        'Water source' => data_get($post->meta, 'environment_water_source'),
                        'Conservation method' => data_get($post->meta, 'environment_conservation_method'),
                        'Estimated water saved' => data_get($post->meta, 'environment_water_saved'),
                    ] as $label => $value)
                        @if(filled($value))
                            <div class="col-md-4">
                                <div class="business-meta-item">
                                    <span class="business-meta-item__label">{{ $label }}</span>
                                    <span>{{ $value }}</span>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if($post->environmentHasImpactData())
        @php
            $impactMetrics = collect([
                'Trees planted' => data_get($post->meta, 'environment_data_trees_planted'),
                'Water saved' => data_get($post->meta, 'environment_data_water_saved'),
                'Waste collected' => data_get($post->meta, 'environment_data_waste_collected'),
                'People participated' => data_get($post->meta, 'environment_data_people_participated'),
                'Area covered' => data_get($post->meta, 'environment_data_area_covered'),
                'Carbon reduction' => data_get($post->meta, 'environment_data_carbon_reduction'),
                'Species recorded' => data_get($post->meta, 'environment_data_species_recorded'),
            ])->filter(fn ($value) => filled($value));
        @endphp
        @if($impactMetrics->isNotEmpty())
            <div class="business-section-panel about-box mb-4 border-info">
                <div class="business-section-panel__header">
                    <i class="fa-solid fa-calculator text-info" aria-hidden="true"></i>
                    <div>
                        <h4 class="mb-0">Environmental impact</h4>
                        <p class="text-muted small mb-0">Measured outcomes from this conservation activity.</p>
                    </div>
                </div>
                <div class="env-impact-strip">
                    @foreach($impactMetrics as $label => $value)
                        <div class="env-impact-strip__item">
                            <span class="env-impact-strip__value">{{ $value }}</span>
                            <span class="env-impact-strip__label">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif

    <div class="row g-3 mb-4">
        @if($post->environmentSoilConservationMethods() !== [])
            <div class="col-lg-6">
                <div class="business-section-panel about-box h-100">
                    <div class="business-section-panel__header">
                        <i class="fa-solid fa-mountain text-warning" aria-hidden="true"></i>
                        <h4 class="mb-0">Soil conservation</h4>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($post->environmentSoilConservationMethods() as $method)
                            <span class="badge bg-warning-subtle text-warning-emphasis border">{{ $method }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if(filled(data_get($post->meta, 'environment_tree_count')) || filled(data_get($post->meta, 'environment_tree_species')))
            <div class="col-lg-6">
                <div class="business-section-panel about-box h-100 border-success">
                    <div class="business-section-panel__header">
                        <i class="fa-solid fa-tree text-success" aria-hidden="true"></i>
                        <h4 class="mb-0">Tree plantation</h4>
                    </div>
                    <div class="row g-3">
                        @foreach([
                            'Trees planted' => data_get($post->meta, 'environment_tree_count'),
                            'Species' => data_get($post->meta, 'environment_tree_species'),
                            'Organization' => data_get($post->meta, 'environment_tree_organization'),
                            'Survival status' => data_get($post->meta, 'environment_tree_survival_status'),
                        ] as $label => $value)
                            @if(filled($value))
                                <div class="col-md-6">
                                    <div class="business-meta-item">
                                        <span class="business-meta-item__label">{{ $label }}</span>
                                        <span>{{ $value }}</span>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                        @if(filled(data_get($post->meta, 'environment_tree_plantation_date')))
                            <div class="col-md-6">
                                <div class="business-meta-item">
                                    <span class="business-meta-item__label">Plantation date</span>
                                    <span>{{ \Illuminate\Support\Carbon::parse(data_get($post->meta, 'environment_tree_plantation_date'))->format('d M Y') }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                    @if(filled(data_get($post->meta, 'environment_tree_maintenance_plan')))
                        <div class="mt-3">
                            <div class="business-meta-item__label mb-1">Maintenance plan</div>
                            <p class="small mb-0">{!! nl2br(e(data_get($post->meta, 'environment_tree_maintenance_plan'))) !!}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if($post->environmentWasteTypes() !== [] && blank($issueType))
            <div class="col-lg-6">
                <div class="business-section-panel about-box h-100">
                    <div class="business-section-panel__header">
                        <i class="fa-solid fa-recycle text-secondary" aria-hidden="true"></i>
                        <h4 class="mb-0">Waste management</h4>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($post->environmentWasteTypes() as $wasteType)
                            <span class="badge bg-light text-dark border">{{ $wasteType }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if($post->environmentBiodiversityTypes() !== [])
            <div class="col-lg-6">
                <div class="business-section-panel about-box h-100 border-success">
                    <div class="business-section-panel__header">
                        <i class="fa-solid fa-paw text-success" aria-hidden="true"></i>
                        <h4 class="mb-0">Biodiversity</h4>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($post->environmentBiodiversityTypes() as $type)
                            <span class="badge bg-success-subtle text-success border border-success-subtle">{{ $type }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if($post->environmentClimateImpacts() !== [])
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-cloud-bolt text-warning" aria-hidden="true"></i>
                <h4 class="mb-0">Climate impact</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($post->environmentClimateImpacts() as $impact)
                    <span class="badge bg-warning-subtle text-warning-emphasis border">{{ $impact }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($post->environmentHasSchemeDetails())
        <div class="business-section-panel about-box mb-4 border-primary">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-landmark text-primary" aria-hidden="true"></i>
                <h4 class="mb-0">Government scheme</h4>
            </div>
            <p class="fw-semibold mb-2">{{ data_get($post->meta, 'environment_scheme_name') }}</p>
            @foreach([
                'Department' => data_get($post->meta, 'environment_scheme_department'),
                'Eligibility' => data_get($post->meta, 'environment_scheme_eligibility'),
                'Benefits' => data_get($post->meta, 'environment_scheme_benefits'),
            ] as $label => $value)
                @if(filled($value))
                    <div class="business-meta-item mb-2">
                        <span class="business-meta-item__label">{{ $label }}</span>
                        <span>{!! nl2br(e((string) $value)) !!}</span>
                    </div>
                @endif
            @endforeach
            @if(filled(data_get($post->meta, 'environment_scheme_official_link')))
                <a href="{{ data_get($post->meta, 'environment_scheme_official_link') }}" class="btn btn-sm btn-outline-primary mt-2" target="_blank" rel="noopener">Official scheme link</a>
            @endif
        </div>
    @endif

    @if($post->environmentHasEventDetails())
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-calendar-days text-info" aria-hidden="true"></i>
                <h4 class="mb-0">Event &amp; campaign details</h4>
            </div>
            <div class="row g-3">
                @foreach([
                    'Campaign' => data_get($post->meta, 'environment_event_campaign_name'),
                    'Organizer' => data_get($post->meta, 'environment_event_organizer'),
                    'Venue' => data_get($post->meta, 'environment_event_venue'),
                    'Time' => data_get($post->meta, 'environment_event_time'),
                ] as $label => $value)
                    @if(filled($value))
                        <div class="col-md-6">
                            <div class="business-meta-item">
                                <span class="business-meta-item__label">{{ $label }}</span>
                                <span>{{ $value }}</span>
                            </div>
                        </div>
                    @endif
                @endforeach
                @if(filled(data_get($post->meta, 'environment_event_date')))
                    <div class="col-md-6">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Date</span>
                            <span>{{ \Illuminate\Support\Carbon::parse(data_get($post->meta, 'environment_event_date'))->format('d M Y') }}</span>
                        </div>
                    </div>
                @endif
            </div>
            @if(filled(data_get($post->meta, 'environment_event_registration_link')))
                <a href="{{ data_get($post->meta, 'environment_event_registration_link') }}" class="btn btn-sm btn-outline-info mt-3" target="_blank" rel="noopener">Register for event</a>
            @endif
        </div>
    @endif

    @if($structuredLocation->isNotEmpty() || filled($naturalFeature))
        <div class="business-section-panel about-box mb-4 border-info">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-location-dot text-info" aria-hidden="true"></i>
                <div>
                    <h4 class="mb-0">Geo-tagged location</h4>
                    <p class="text-muted small mb-0">Structured location for this environment post.</p>
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
                @if(filled($naturalFeature))
                    <div class="col-md-6 col-lg-4">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Natural feature</span>
                            <span>{{ $naturalFeature }}</span>
                        </div>
                    </div>
                @endif
                @if(filled($mapPinType))
                    <div class="col-md-6 col-lg-4">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Map pin type</span>
                            <span>{{ $mapPinType }}</span>
                        </div>
                    </div>
                @endif
            </div>
            @if($post->hasMapCoordinates())
                <div class="ratio ratio-16x9 border rounded overflow-hidden mt-3">
                    <iframe
                        title="Environment location map"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        src="https://www.openstreetmap.org/export/embed.html?bbox={{ $post->location_lng - 0.02 }},{{ $post->location_lat - 0.02 }},{{ $post->location_lng + 0.02 }},{{ $post->location_lat + 0.02 }}&layer=mapnik&marker={{ $post->location_lat }},{{ $post->location_lng }}"
                    ></iframe>
                </div>
            @endif
        </div>
    @endif

    @if($post->featuredImageUrl() || $post->environmentGallery() !== [] || $post->hasVideo() || $post->environmentDocuments() !== [])
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-images text-primary" aria-hidden="true"></i>
                <h4 class="mb-0">Media &amp; documents</h4>
            </div>

            @if($post->featuredImageUrl())
                <div class="mb-3">
                    <div class="business-meta-item__label mb-2">Featured image</div>
                    <img src="{{ $post->featuredImageUrl() }}" alt="Featured environment image" class="img-fluid rounded border">
                </div>
            @endif

            @foreach($galleryCategories as $categoryKey => $categoryLabel)
                @php $photos = array_values((array) data_get($post->environmentGallery(), $categoryKey, [])); @endphp
                @if($photos !== [])
                    <div class="env-gallery-category">
                        <div class="env-gallery-category__label">{{ $categoryLabel }}</div>
                        <div class="business-gallery-grid">
                            @foreach($photos as $photo)
                                @if(filled(data_get($photo, 'url')))
                                    <a href="{{ data_get($photo, 'url') }}" target="_blank" rel="noopener" class="business-gallery-card">
                                        <img src="{{ data_get($photo, 'url') }}" alt="{{ $categoryLabel }}" loading="lazy">
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach

            @if($post->hasVideo())
                <div class="mb-3">
                    <div class="business-meta-item__label mb-2">
                        Video{{ filled($videoType) ? ' · '.$videoType : '' }}
                    </div>
                    @if($post->youtubeEmbedUrl())
                        <div class="ratio ratio-16x9 rounded overflow-hidden"><iframe src="{{ $post->youtubeEmbedUrl() }}" title="Environment video" allowfullscreen></iframe></div>
                    @elseif($post->videoFileUrl())
                        <video controls class="w-100 rounded" preload="metadata"><source src="{{ $post->videoFileUrl() }}"></video>
                    @endif
                </div>
            @endif

            @if($post->environmentDocuments() !== [])
                <div class="d-flex flex-wrap gap-2">
                    @foreach($post->environmentDocuments() as $document)
                        <a href="{{ data_get($document, 'url') }}" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener">
                            <i class="fa-solid fa-file-lines me-1"></i>{{ data_get($document, 'name', 'Document') }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    @if(filled($askCommunity))
        <div class="business-section-panel about-box mb-4 border-primary">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-circle-question text-primary" aria-hidden="true"></i>
                <h4 class="mb-0">Ask the community</h4>
            </div>
            <p class="mb-0 fs-5">"{{ $askCommunity }}"</p>
        </div>
    @endif

    @if($post->environmentParticipationRequests() !== [] || !empty($post->tags))
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-tags text-secondary" aria-hidden="true"></i>
                <h4 class="mb-0">Participation &amp; tags</h4>
            </div>
            @if($post->environmentParticipationRequests() !== [])
                <div class="mb-3">
                    <div class="business-meta-item__label mb-2">Participation requests</div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($post->environmentParticipationRequests() as $request)
                            <span class="badge bg-info-subtle text-info border border-info-subtle">{{ $request }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
            @if(!empty($post->tags))
                <div class="d-flex flex-wrap gap-2">
                    @foreach($post->tags as $tag)
                        <span class="badge bg-success-subtle text-success border border-success-subtle">{{ $tag }}</span>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <div class="business-section-panel about-box mb-4">
        <div class="business-section-panel__header">
            <i class="fa-solid fa-sliders text-info" aria-hidden="true"></i>
            <h4 class="mb-0">Community engagement settings</h4>
        </div>
        <div class="env-capability-grid">
            @foreach($capabilities as $capability)
                <span class="env-capability-pill {{ $capability['enabled'] ? '' : 'is-disabled' }}">
                    <i class="fa-solid {{ $capability['icon'] }}" aria-hidden="true"></i>
                    {{ $capability['label'] }}
                </span>
            @endforeach
        </div>
    </div>
@endif
