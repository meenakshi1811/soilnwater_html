@if($post->isAstroConsultancyPost())
    @php
        $postType = $post->astroConsultancyPostTypeLabel();
        $category = $post->astroConsultancyCategoryLabel();
        $language = data_get($post->meta, 'astro_consultancy_content_language');
        $topics = $post->astroConsultancyConsultationTopics();
        $audiences = $post->astroConsultancyTargetAudiences();
        $knowledgeTopics = $post->astroConsultancyKnowledgeLibraryTopics();
        $askCommunity = data_get($post->meta, 'astro_consultancy_ask_community');
        $consultantUrl = data_get($post->meta, 'astro_consultancy_consultant_profile_url');
        $serviceActions = (array) data_get($post->meta, 'astro_consultancy_related_service_actions', []);
        $documents = $post->astroConsultancyDocuments();
        $videoType = data_get($post->meta, 'astro_consultancy_video_type');
        $capabilities = [
            ['label' => 'Consultant directory', 'enabled' => $post->astroEnablesConsultantLinking(), 'icon' => 'fa-user-check'],
            ['label' => 'Live Q&A', 'enabled' => $post->astroEnablesLiveQa(), 'icon' => 'fa-comments'],
            ['label' => 'Knowledge library', 'enabled' => $knowledgeTopics !== [], 'icon' => 'fa-book-open'],
            ['label' => 'Comments', 'enabled' => (bool) $post->allow_comments, 'icon' => 'fa-comments'],
            ['label' => 'Questions', 'enabled' => (bool) $post->allow_questions, 'icon' => 'fa-circle-question'],
            ['label' => 'Share', 'enabled' => (bool) $post->allow_sharing, 'icon' => 'fa-share-nodes'],
            ['label' => 'Poll', 'enabled' => (bool) $post->allowsPoll(), 'icon' => 'fa-square-poll-vertical'],
        ];
    @endphp

    <div class="astro-show-overview">
        <div class="astro-show-overview__kicker">Astro Consultancy · SoilnWater guidance network</div>
        <div class="astro-show-overview__title">Educational astrology, spiritual guidance, and traditional knowledge</div>
        <div class="astro-show-overview__chips">
            @if(filled($postType))
                <span class="astro-show-chip">{{ $postType }}</span>
            @endif
            @if(filled($category))
                <span class="astro-show-chip">{{ $category }}</span>
            @endif
            @if(filled($language))
                <span class="astro-show-chip">{{ $language }}</span>
            @endif
            @if($post->astroEnablesLiveQa())
                <span class="astro-show-chip astro-show-chip--flagship"><i class="fa-solid fa-microphone-lines me-1"></i>Live Q&amp;A</span>
            @endif
            @if($post->astroEnablesConsultantLinking())
                <span class="astro-show-chip astro-show-chip--flagship"><i class="fa-solid fa-user-check me-1"></i>Verified consultant</span>
            @endif
        </div>
    </div>

    @if($audiences !== [])
        <div class="mb-4">
            <h5 class="h6 text-muted mb-2">Target audience</h5>
            <div class="d-flex flex-wrap gap-2">
                @foreach($audiences as $audience)
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $audience }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($topics !== [])
        <div class="mb-4">
            <h5 class="h6 text-muted mb-2">Consultation topics</h5>
            <div class="d-flex flex-wrap gap-2">
                @foreach($topics as $topic)
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">{{ $topic }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($post->astroHasHoroscopeDetails())
        <div class="astro-flagship-banner astro-flagship-banner--horoscope d-flex align-items-start gap-3 mb-4" role="status">
            <i class="fa-solid fa-star text-warning fs-4 mt-1" aria-hidden="true"></i>
            <div class="flex-grow-1">
                <div class="text-warning-emphasis fw-bold mb-2"><i class="fa-solid fa-moon me-1"></i>Horoscope focus</div>
                <div class="d-flex flex-wrap gap-2">
                    @if(filled(data_get($post->meta, 'astro_consultancy_zodiac_sign')))
                        <span class="badge bg-warning text-dark px-3 py-2">{{ data_get($post->meta, 'astro_consultancy_zodiac_sign') }}</span>
                    @endif
                    @if(filled(data_get($post->meta, 'astro_consultancy_horoscope_period')))
                        <span class="badge bg-light text-dark border px-3 py-2">{{ data_get($post->meta, 'astro_consultancy_horoscope_period') }}</span>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if($post->astroHasVastuDetails())
        <div class="astro-flagship-banner astro-flagship-banner--vastu mb-4">
            <div class="text-info fw-bold mb-2"><i class="fa-solid fa-compass me-1"></i>Vastu guidance</div>
            @if($post->astroConsultancyVastuPropertyTypes() !== [])
                <div class="mb-2">
                    <div class="small text-muted mb-1">Property types</div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($post->astroConsultancyVastuPropertyTypes() as $propertyType)
                            <span class="badge bg-info-subtle text-info border border-info-subtle">{{ $propertyType }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
            @if($post->astroConsultancyVastuAreas() !== [])
                <div>
                    <div class="small text-muted mb-1">Areas covered</div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($post->astroConsultancyVastuAreas() as $area)
                            <span class="badge bg-light text-dark border">{{ $area }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if($post->astroHasNumerologyDetails())
        <div class="astro-flagship-banner astro-flagship-banner--numerology mb-4">
            <div class="text-primary fw-bold mb-2"><i class="fa-solid fa-hashtag me-1"></i>Numerology details</div>
            <div class="row g-3">
                @foreach([
                    'Life path number' => data_get($post->meta, 'astro_consultancy_life_path_number'),
                    'Destiny number' => data_get($post->meta, 'astro_consultancy_destiny_number'),
                    'Name number' => data_get($post->meta, 'astro_consultancy_name_number'),
                    'Lucky number' => data_get($post->meta, 'astro_consultancy_lucky_number'),
                    'Compatibility' => data_get($post->meta, 'astro_consultancy_compatibility'),
                ] as $label => $value)
                    @if(filled($value))
                        <div class="col-md-4 col-lg-3">
                            <div class="business-meta-item">
                                <span class="business-meta-item__label">{{ $label }}</span>
                                <span>{{ $value }}</span>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    @if($post->astroHasGemstoneDetails())
        <div class="astro-flagship-banner astro-flagship-banner--gemstone mb-4">
            <div class="text-success fw-bold mb-2"><i class="fa-solid fa-gem me-1"></i>Gemstone guidance</div>
            <div class="row g-3">
                @foreach([
                    'Gemstone' => data_get($post->meta, 'astro_consultancy_gemstone'),
                    'Planet' => data_get($post->meta, 'astro_consultancy_gemstone_planet'),
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
                @if(filled(data_get($post->meta, 'astro_consultancy_gemstone_benefits')))
                    <div class="col-md-6">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Traditional benefits</span>
                            <span>{!! nl2br(e(data_get($post->meta, 'astro_consultancy_gemstone_benefits'))) !!}</span>
                        </div>
                    </div>
                @endif
                @if(filled(data_get($post->meta, 'astro_consultancy_gemstone_precautions')))
                    <div class="col-md-6">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Precautions</span>
                            <span>{!! nl2br(e(data_get($post->meta, 'astro_consultancy_gemstone_precautions'))) !!}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if($post->astroHasFestivalDetails())
        <div class="astro-flagship-banner astro-flagship-banner--festival mb-4">
            <div class="text-success fw-bold mb-2"><i class="fa-solid fa-calendar-days me-1"></i>Festival &amp; muhurat</div>
            <div class="row g-3">
                @foreach([
                    'Festival' => data_get($post->meta, 'astro_consultancy_festival_name'),
                    'Muhurat type' => data_get($post->meta, 'astro_consultancy_muhurat_type'),
                    'Time' => data_get($post->meta, 'astro_consultancy_muhurat_time'),
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
                @if(filled(data_get($post->meta, 'astro_consultancy_muhurat_date')))
                    <div class="col-md-4">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Date</span>
                            <span>{{ \Illuminate\Support\Carbon::parse(data_get($post->meta, 'astro_consultancy_muhurat_date'))->format('d M Y') }}</span>
                        </div>
                    </div>
                @endif
            </div>
            @if(filled(data_get($post->meta, 'astro_consultancy_festival_significance')))
                <div class="mt-3">
                    <div class="business-meta-item__label mb-1">Traditional significance</div>
                    <p class="small mb-0">{!! nl2br(e(data_get($post->meta, 'astro_consultancy_festival_significance'))) !!}</p>
                </div>
            @endif
        </div>
    @endif

    @if($knowledgeTopics !== [])
        <div class="business-section-panel about-box mb-4 border-info">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-book-open text-info" aria-hidden="true"></i>
                <div>
                    <h4 class="mb-0">Astrology knowledge library</h4>
                    <p class="text-muted small mb-0">Educational topics covered in this post.</p>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($knowledgeTopics as $topic)
                    <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2">{{ $topic }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($post->astroEnablesLiveQa())
        <div class="business-section-panel about-box mb-4 border-warning">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-microphone-lines text-warning" aria-hidden="true"></i>
                <div>
                    <h4 class="mb-0">Live Q&amp;A sessions</h4>
                    <p class="text-muted small mb-0">This post is marked for live Q&amp;A or archived session discovery. Use private consultation below instead of sharing personal details publicly.</p>
                </div>
            </div>
        </div>
    @endif

    <div class="business-section-panel about-box mb-4">
        <div class="business-section-panel__header">
            <i class="fa-solid fa-sliders text-secondary" aria-hidden="true"></i>
            <h4 class="mb-0">Post capabilities</h4>
        </div>
        <div class="astro-capability-grid">
            @foreach($capabilities as $capability)
                <span class="astro-capability-pill {{ $capability['enabled'] ? '' : 'is-disabled' }}">
                    <i class="fa-solid {{ $capability['icon'] }}" aria-hidden="true"></i>
                    {{ $capability['label'] }}
                </span>
            @endforeach
        </div>
    </div>

    @if($post->featuredImageUrl() || $post->hasVideo() || $documents !== [])
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-images text-primary" aria-hidden="true"></i>
                <h4 class="mb-0">Media &amp; documents</h4>
            </div>

            @if($post->featuredImageUrl())
                <div class="mb-3">
                    <div class="business-meta-item__label mb-2">Featured image</div>
                    <img src="{{ $post->featuredImageUrl() }}" alt="Featured astro consultancy image" class="img-fluid rounded border">
                </div>
            @endif

            @if($post->hasVideo())
                <div class="mb-3">
                    <div class="business-meta-item__label mb-2">
                        Video{{ filled($videoType) ? ' · '.$videoType : '' }}
                    </div>
                    @if($post->youtubeEmbedUrl())
                        <div class="ratio ratio-16x9 rounded overflow-hidden"><iframe src="{{ $post->youtubeEmbedUrl() }}" title="Astro consultancy video" allowfullscreen></iframe></div>
                    @elseif($post->videoFileUrl())
                        <video controls class="w-100 rounded" preload="metadata"><source src="{{ $post->videoFileUrl() }}"></video>
                    @endif
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

    @if(filled($consultantUrl) || $serviceActions !== [])
        <div class="business-section-panel about-box mb-4 border-primary">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-user-check text-primary" aria-hidden="true"></i>
                <h4 class="mb-0">Related services</h4>
            </div>
            @if(filled($consultantUrl))
                <p class="mb-2">
                    <a href="{{ $consultantUrl }}" class="btn btn-sm btn-primary" target="_blank" rel="noopener">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>View consultant profile
                    </a>
                </p>
            @endif
            @if($serviceActions !== [])
                <div class="d-flex flex-wrap gap-2">
                    @foreach($serviceActions as $action)
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">{{ $action }}</span>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    @include('community.partials.astro-consultancy-meta-details', ['post' => $post])

    <div class="astro-disclaimer-panel p-3 p-lg-4 mt-4 mb-0 small" role="note">
        <div class="fw-semibold mb-1 text-warning-emphasis"><i class="fa-solid fa-triangle-exclamation me-1"></i>Disclaimer</div>
        <p class="mb-0">{{ \App\Support\CommunityContentTaxonomy::astroConsultancyDisclaimerText() }}</p>
    </div>
@endif
