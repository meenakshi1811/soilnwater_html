@if($post->isAgriculturePost())
    @php
        $shareType = $post->agricultureShareTypeLabel();
        $category = $post->agricultureCategoryLabel();
        $cropName = data_get($post->meta, 'agriculture_crop_name');
        $cropVariety = data_get($post->meta, 'agriculture_crop_variety');
        $sowingDate = data_get($post->meta, 'agriculture_sowing_date');
        $harvestDate = data_get($post->meta, 'agriculture_harvest_date');
        $growingSeason = data_get($post->meta, 'agriculture_growing_season');
        $climateZone = data_get($post->meta, 'agriculture_climate_zone');
        $soilType = data_get($post->meta, 'agriculture_soil_type');
        $farmSize = data_get($post->meta, 'agriculture_farm_size');
        $farmingType = data_get($post->meta, 'agriculture_farming_type');
        $irrigationMethod = data_get($post->meta, 'agriculture_irrigation_method');
        $waterSource = data_get($post->meta, 'agriculture_water_source');
        $waterPractices = $post->agricultureWaterConservationPractices();
        $soilTestConducted = data_get($post->meta, 'agriculture_soil_test_conducted');
        $soilRecommendations = data_get($post->meta, 'agriculture_soil_recommendations');
        $problemType = data_get($post->meta, 'agriculture_problem_type');
        $equipmentName = data_get($post->meta, 'agriculture_equipment_name');
        $schemeName = data_get($post->meta, 'agriculture_scheme_name');
        $marketCommodity = data_get($post->meta, 'agriculture_market_commodity');
        $innovationName = data_get($post->meta, 'agriculture_innovation_name');
        $agriBusinessType = data_get($post->meta, 'agriculture_agri_business_type');
        $weatherImpact = data_get($post->meta, 'agriculture_weather_impact');
        $videoType = data_get($post->meta, 'agriculture_video_type');
        $askCommunity = data_get($post->meta, 'agriculture_ask_community');
        $targetAudiences = $post->agricultureTargetAudiences();
        $livestockTypes = $post->agricultureLivestockTypes();
        $structuredLocation = $post->structuredLocationForDisplay();
        $locationLabels = \App\Models\CommunityPost::structuredLocationLabelsFor($post->content_type);
        $galleryCategories = \App\Support\CommunityContentTaxonomy::agricultureGalleryCategories();
        $capabilities = [
            ['label' => 'Knowledge exchange', 'enabled' => $post->enablesAgricultureKnowledgeExchange(), 'icon' => 'fa-people-arrows'],
            ['label' => 'Crop Doctor', 'enabled' => $post->enablesAgricultureCropDoctor(), 'icon' => 'fa-stethoscope'],
            ['label' => 'Comments', 'enabled' => (bool) $post->allow_comments, 'icon' => 'fa-comments'],
            ['label' => 'Questions', 'enabled' => (bool) $post->allow_questions, 'icon' => 'fa-circle-question'],
            ['label' => 'Suggestions', 'enabled' => (bool) $post->allow_suggestions, 'icon' => 'fa-lightbulb'],
            ['label' => 'Feedback', 'enabled' => (bool) $post->allow_feedback, 'icon' => 'fa-message'],
            ['label' => 'Share', 'enabled' => (bool) $post->allow_sharing, 'icon' => 'fa-share-nodes'],
            ['label' => 'Poll', 'enabled' => (bool) $post->allow_poll, 'icon' => 'fa-square-poll-vertical'],
        ];
    @endphp

    <div class="ag-show-overview">
        <div class="ag-show-overview__kicker">Agriculture · SoilnWater farmer network</div>
        <div class="ag-show-overview__title">Practical field knowledge, water-smart farming, and community crop support</div>
        <div class="ag-show-overview__chips">
            @if(filled($shareType))
                <span class="ag-show-chip">{{ $shareType }}</span>
            @endif
            @if(filled($category))
                <span class="ag-show-chip">{{ $category }}</span>
            @endif
            @if(filled($cropName))
                <span class="ag-show-chip">{{ $cropName }}@if(filled($cropVariety)) · {{ $cropVariety }}@endif</span>
            @endif
            @if(filled($irrigationMethod))
                <span class="ag-show-chip ag-show-chip--flagship"><i class="fa-solid fa-droplet me-1"></i>{{ $irrigationMethod }}</span>
            @endif
            @if($post->enablesAgricultureCropDoctor())
                <span class="ag-show-chip ag-show-chip--flagship"><i class="fa-solid fa-stethoscope me-1"></i>Crop Doctor</span>
            @endif
            @if($post->agricultureNeedsExpertAssistance())
                <span class="ag-show-chip ag-show-chip--flagship"><i class="fa-solid fa-user-doctor me-1"></i>Expert help requested</span>
            @endif
        </div>
    </div>

    @if($post->agricultureHasWaterManagementDetails())
        <div class="ag-flagship-banner ag-flagship-banner--water d-flex align-items-start gap-3 mb-4" role="status">
            <i class="fa-solid fa-droplet text-info fs-4 mt-1" aria-hidden="true"></i>
            <div class="flex-grow-1">
                <div class="text-info fw-bold mb-1">Water management · flagship focus</div>
                <div class="row g-3">
                    @if(filled($irrigationMethod))
                        <div class="col-md-4">
                            <div class="business-meta-item">
                                <span class="business-meta-item__label">Irrigation method</span>
                                <span>{{ $irrigationMethod }}</span>
                            </div>
                        </div>
                    @endif
                    @if(filled($waterSource))
                        <div class="col-md-4">
                            <div class="business-meta-item">
                                <span class="business-meta-item__label">Water source</span>
                                <span>{{ $waterSource }}</span>
                            </div>
                        </div>
                    @endif
                    @if($waterPractices !== [])
                        <div class="col-md-12">
                            <div class="business-meta-item">
                                <span class="business-meta-item__label">Conservation practices</span>
                                <div class="d-flex flex-wrap gap-2 mt-1">
                                    @foreach($waterPractices as $practice)
                                        <span class="badge bg-info-subtle text-info border border-info-subtle">{{ $practice }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if($post->enablesAgricultureCropDoctor() || $post->enablesAgricultureKnowledgeExchange())
        <div class="row g-3 mb-4">
            @if($post->enablesAgricultureKnowledgeExchange())
                <div class="col-lg-6">
                    <div class="ag-flagship-banner h-100">
                        <div class="text-success fw-bold mb-1"><i class="fa-solid fa-people-arrows me-1"></i>Farmer knowledge exchange</div>
                        <p class="small text-muted mb-0">This post invites farmers to share practical tips, compare methods, and learn from each other's field experience.</p>
                    </div>
                </div>
            @endif
            @if($post->enablesAgricultureCropDoctor())
                <div class="col-lg-6">
                    <div class="ag-flagship-banner ag-flagship-banner--crop-doctor h-100">
                        <div class="text-warning fw-bold mb-1"><i class="fa-solid fa-stethoscope me-1"></i>Crop Doctor</div>
                        <p class="small text-muted mb-0">
                            @if($post->agricultureNeedsExpertAssistance())
                                The author has requested expert assistance. Agronomists and experienced farmers can respond in the comments.
                            @else
                                Community experts and fellow farmers can help diagnose crop issues through comments and suggestions.
                            @endif
                        </p>
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if(filled($cropName) || filled($farmSize) || filled($farmingType) || filled($growingSeason) || filled($climateZone) || filled($soilType))
        <div class="business-section-panel about-box mb-4 border-success">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-seedling text-success" aria-hidden="true"></i>
                <div>
                    <h4 class="mb-0">Crop &amp; farm profile</h4>
                    <p class="text-muted small mb-0">Season, soil, and farm context for this agriculture post.</p>
                </div>
            </div>
            <div class="row g-3">
                @foreach([
                    'Crop' => $cropName,
                    'Variety' => $cropVariety,
                    'Growing season' => $growingSeason,
                    'Climate zone' => $climateZone,
                    'Soil type' => $soilType,
                    'Farm size' => $farmSize,
                    'Farming type' => $farmingType,
                ] as $label => $value)
                    @if(filled($value))
                        <div class="col-md-6 col-lg-4">
                            <div class="business-meta-item">
                                <span class="business-meta-item__label">{{ $label }}</span>
                                <span>{{ $value }}</span>
                            </div>
                        </div>
                    @endif
                @endforeach
                @if(filled($sowingDate))
                    <div class="col-md-6 col-lg-4">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Sowing date</span>
                            <span>{{ \Illuminate\Support\Carbon::parse($sowingDate)->format('d M Y') }}</span>
                        </div>
                    </div>
                @endif
                @if(filled($harvestDate))
                    <div class="col-md-6 col-lg-4">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Harvest date</span>
                            <span>{{ \Illuminate\Support\Carbon::parse($harvestDate)->format('d M Y') }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if($soilTestConducted === 'yes' || filled($soilRecommendations) || filled(data_get($post->meta, 'agriculture_soil_ph')))
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-mountain text-secondary" aria-hidden="true"></i>
                <h4 class="mb-0">Soil health</h4>
            </div>
            <div class="row g-3">
                @foreach([
                    'agriculture_soil_ph' => 'pH',
                    'agriculture_soil_organic_carbon' => 'Organic carbon',
                    'agriculture_soil_nitrogen' => 'Nitrogen',
                    'agriculture_soil_phosphorus' => 'Phosphorus',
                    'agriculture_soil_potassium' => 'Potassium',
                ] as $key => $label)
                    @if(filled(data_get($post->meta, $key)))
                        <div class="col-md-6 col-lg-4">
                            <div class="business-meta-item">
                                <span class="business-meta-item__label">{{ $label }}</span>
                                <span>{{ data_get($post->meta, $key) }}</span>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
            @if(filled($soilRecommendations))
                <div class="mt-3">
                    <div class="business-meta-item__label mb-1">Recommendations</div>
                    <p class="mb-0">{!! nl2br(e($soilRecommendations)) !!}</p>
                </div>
            @endif
        </div>
    @endif

    @if(filled($problemType) || $post->agricultureProblemPhotos() !== [])
        <div class="business-section-panel about-box mb-4 border-warning">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-bug text-warning" aria-hidden="true"></i>
                <h4 class="mb-0">Problem reporting</h4>
            </div>
            @if(filled($problemType))
                <p class="mb-3"><strong>Problem type:</strong> {{ $problemType }}</p>
            @endif
            @if($post->agricultureProblemPhotos() !== [])
                <div class="business-gallery-grid">
                    @foreach($post->agricultureProblemPhotos() as $photo)
                        <a href="{{ data_get($photo, 'url') }}" target="_blank" rel="noopener" class="business-gallery-card">
                            <img src="{{ data_get($photo, 'url') }}" alt="Problem evidence" loading="lazy">
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <div class="row g-3 mb-4">
        @if(filled($equipmentName))
            <div class="col-lg-6">
                <div class="business-section-panel about-box h-100">
                    <div class="business-section-panel__header">
                        <i class="fa-solid fa-tractor" aria-hidden="true"></i>
                        <h4 class="mb-0">Farm machinery</h4>
                    </div>
                    <p class="fw-semibold mb-2">{{ $equipmentName }}</p>
                    @foreach([
                        'Manufacturer' => data_get($post->meta, 'agriculture_equipment_manufacturer'),
                        'Cost' => data_get($post->meta, 'agriculture_equipment_cost'),
                    ] as $label => $value)
                        @if(filled($value))
                            <div class="business-meta-item mb-2">
                                <span class="business-meta-item__label">{{ $label }}</span>
                                <span>{{ $value }}</span>
                            </div>
                        @endif
                    @endforeach
                    @if(filled(data_get($post->meta, 'agriculture_equipment_experience')))
                        <p class="small mb-0">{!! nl2br(e(data_get($post->meta, 'agriculture_equipment_experience'))) !!}</p>
                    @endif
                </div>
            </div>
        @endif

        @if(filled($schemeName))
            <div class="col-lg-6">
                <div class="business-section-panel about-box h-100 border-primary">
                    <div class="business-section-panel__header">
                        <i class="fa-solid fa-landmark text-primary" aria-hidden="true"></i>
                        <h4 class="mb-0">Government scheme</h4>
                    </div>
                    <p class="fw-semibold mb-2">{{ $schemeName }}</p>
                    @foreach([
                        'Department' => data_get($post->meta, 'agriculture_scheme_department'),
                        'Subsidy' => data_get($post->meta, 'agriculture_scheme_subsidy'),
                        'Last date' => filled(data_get($post->meta, 'agriculture_scheme_last_date'))
                            ? \Illuminate\Support\Carbon::parse(data_get($post->meta, 'agriculture_scheme_last_date'))->format('d M Y')
                            : null,
                    ] as $label => $value)
                        @if(filled($value))
                            <div class="business-meta-item mb-2">
                                <span class="business-meta-item__label">{{ $label }}</span>
                                <span>{{ $value }}</span>
                            </div>
                        @endif
                    @endforeach
                    @if(filled(data_get($post->meta, 'agriculture_scheme_application_link')))
                        <a href="{{ data_get($post->meta, 'agriculture_scheme_application_link') }}" class="btn btn-sm btn-outline-primary mt-2" target="_blank" rel="noopener">Apply / learn more</a>
                    @endif
                </div>
            </div>
        @endif

        @if(filled($marketCommodity))
            <div class="col-lg-6">
                <div class="business-section-panel about-box h-100">
                    <div class="business-section-panel__header">
                        <i class="fa-solid fa-chart-line text-success" aria-hidden="true"></i>
                        <h4 class="mb-0">Market information</h4>
                    </div>
                    <div class="row g-3">
                        @foreach([
                            'Commodity' => $marketCommodity,
                            'Market' => data_get($post->meta, 'agriculture_market_name'),
                            'Price' => data_get($post->meta, 'agriculture_market_price'),
                            'Trend' => data_get($post->meta, 'agriculture_market_price_trend'),
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
                        @if(filled(data_get($post->meta, 'agriculture_market_date')))
                            <div class="col-md-6">
                                <div class="business-meta-item">
                                    <span class="business-meta-item__label">Date</span>
                                    <span>{{ \Illuminate\Support\Carbon::parse(data_get($post->meta, 'agriculture_market_date'))->format('d M Y') }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if($livestockTypes !== [])
            <div class="col-lg-6">
                <div class="business-section-panel about-box h-100">
                    <div class="business-section-panel__header">
                        <i class="fa-solid fa-cow" aria-hidden="true"></i>
                        <h4 class="mb-0">Livestock</h4>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($livestockTypes as $type)
                            <span class="badge bg-light text-dark border">{{ $type }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if(filled($innovationName))
            <div class="col-lg-6">
                <div class="business-section-panel about-box h-100 border-success">
                    <div class="business-section-panel__header">
                        <i class="fa-solid fa-lightbulb text-success" aria-hidden="true"></i>
                        <h4 class="mb-0">Agricultural innovation</h4>
                    </div>
                    <p class="fw-semibold mb-2">{{ $innovationName }}</p>
                    @if(filled(data_get($post->meta, 'agriculture_innovation_description')))
                        <p class="small mb-0">{!! nl2br(e(data_get($post->meta, 'agriculture_innovation_description'))) !!}</p>
                    @endif
                </div>
            </div>
        @endif

        @if(filled($agriBusinessType))
            <div class="col-lg-6">
                <div class="business-section-panel about-box h-100">
                    <div class="business-section-panel__header">
                        <i class="fa-solid fa-store" aria-hidden="true"></i>
                        <h4 class="mb-0">Agri-business</h4>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">{{ $agriBusinessType }}</span>
                </div>
            </div>
        @endif
    </div>

    @if(filled($weatherImpact))
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-cloud-sun" aria-hidden="true"></i>
                <h4 class="mb-0">Weather impact</h4>
            </div>
            <span class="badge bg-light text-dark border px-3 py-2">{{ $weatherImpact }}</span>
        </div>
    @endif

    @if($structuredLocation->isNotEmpty())
        <div class="business-section-panel about-box mb-4 border-success">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-location-dot text-success" aria-hidden="true"></i>
                <div>
                    <h4 class="mb-0">Farm location</h4>
                    <p class="text-muted small mb-0">Structured location for this agriculture post.</p>
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
                <div class="ratio ratio-16x9 border rounded overflow-hidden mt-3">
                    <iframe
                        title="Agriculture farm map"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        src="https://www.openstreetmap.org/export/embed.html?bbox={{ $post->location_lng - 0.02 }},{{ $post->location_lat - 0.02 }},{{ $post->location_lng + 0.02 }},{{ $post->location_lat + 0.02 }}&layer=mapnik&marker={{ $post->location_lat }},{{ $post->location_lng }}"
                    ></iframe>
                </div>
            @endif
        </div>
    @endif

    @if($post->featuredImageUrl() || $post->agricultureGallery() !== [] || $post->hasVideo() || $post->agricultureDocuments() !== [])
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-images text-primary" aria-hidden="true"></i>
                <h4 class="mb-0">Media &amp; documents</h4>
            </div>

            @if($post->featuredImageUrl())
                <div class="mb-3">
                    <div class="business-meta-item__label mb-2">Featured image</div>
                    <img src="{{ $post->featuredImageUrl() }}" alt="Featured agriculture image" class="img-fluid rounded border">
                </div>
            @endif

            @foreach($galleryCategories as $categoryKey => $categoryLabel)
                @php $photos = array_values((array) data_get($post->agricultureGallery(), $categoryKey, [])); @endphp
                @if($photos !== [])
                    <div class="ag-gallery-category">
                        <div class="ag-gallery-category__label">{{ $categoryLabel }}</div>
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
                        Video@if(filled($videoType)) · {{ $videoType }}@endif
                    </div>
                    @if($post->youtubeEmbedUrl())
                        <div class="ratio ratio-16x9 rounded overflow-hidden"><iframe src="{{ $post->youtubeEmbedUrl() }}" title="Agriculture video" allowfullscreen></iframe></div>
                    @elseif($post->videoFileUrl())
                        <video controls class="w-100 rounded" preload="metadata"><source src="{{ $post->videoFileUrl() }}"></video>
                    @endif
                </div>
            @endif

            @if($post->agricultureDocuments() !== [])
                <div class="d-flex flex-wrap gap-2">
                    @foreach($post->agricultureDocuments() as $document)
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
            <p class="mb-0 fs-5">“{{ $askCommunity }}”</p>
        </div>
    @endif

    @if($targetAudiences !== [] || !empty($post->tags))
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-tags text-secondary" aria-hidden="true"></i>
                <h4 class="mb-0">Audience &amp; tags</h4>
            </div>
            @if($targetAudiences !== [])
                <div class="mb-3">
                    <div class="business-meta-item__label mb-2">Target audiences</div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($targetAudiences as $audience)
                            <span class="badge bg-light text-dark border">{{ $audience }}</span>
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
            <i class="fa-solid fa-sliders text-success" aria-hidden="true"></i>
            <h4 class="mb-0">Community engagement settings</h4>
        </div>
        <div class="ag-capability-grid">
            @foreach($capabilities as $capability)
                <span class="ag-capability-pill {{ $capability['enabled'] ? '' : 'is-disabled' }}">
                    <i class="fa-solid {{ $capability['icon'] }}" aria-hidden="true"></i>
                    {{ $capability['label'] }}
                </span>
            @endforeach
        </div>
    </div>
@endif
