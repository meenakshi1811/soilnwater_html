@if($post->isCreativeCornerPost())
    @php
        $portalSidebarLayout = $portalSidebarLayout ?? false;
        $postType = $post->creativeCornerPostTypeLabel();
        $category = $post->creativeCornerCategoryLabel();
        $audiences = $post->creativeCornerTargetAudiences();
        $mediums = (array) data_get($post->meta, 'creative_corner_mediums', []);
        $softwareTools = (array) data_get($post->meta, 'creative_corner_software_tools', []);
        $materials = (array) data_get($post->meta, 'creative_corner_materials', []);
        $themes = (array) data_get($post->meta, 'creative_corner_themes', []);
        $licenses = (array) data_get($post->meta, 'creative_corner_creative_licenses', []);
        $askCommunity = data_get($post->meta, 'creative_corner_ask_community');
        $gallery = (array) data_get($post->meta, 'creative_corner_gallery', []);
        $documents = (array) data_get($post->meta, 'creative_corner_documents', []);
        $audio = data_get($post->meta, 'creative_corner_audio');
        $videoType = data_get($post->meta, 'creative_corner_video_type');
        $availableForSale = (bool) data_get($post->meta, 'creative_corner_available_for_sale');
        $commissionOptions = (array) data_get($post->meta, 'creative_corner_commission_options', []);
        $collaborationRoles = (array) data_get($post->meta, 'creative_corner_collaboration_roles', []);
        $competitionCategories = (array) data_get($post->meta, 'creative_corner_competition_categories', []);
        $socialLinks = array_filter([
            'Portfolio' => data_get($post->meta, 'creative_corner_social_portfolio'),
            'Instagram' => data_get($post->meta, 'creative_corner_social_instagram'),
            'YouTube' => data_get($post->meta, 'creative_corner_social_youtube'),
            'Website' => data_get($post->meta, 'creative_corner_social_website'),
            'SoilnWater vendor' => data_get($post->meta, 'creative_corner_social_vendor_profile'),
        ]);
        $capabilities = [
            ['label' => 'For sale', 'enabled' => $availableForSale, 'icon' => 'fa-store'],
            ['label' => 'Competition', 'enabled' => (bool) data_get($post->meta, 'creative_corner_submit_to_competition'), 'icon' => 'fa-trophy'],
            ['label' => 'Commissions', 'enabled' => $commissionOptions !== [], 'icon' => 'fa-handshake'],
            ['label' => 'Collaboration', 'enabled' => $collaborationRoles !== [], 'icon' => 'fa-people-group'],
            ['label' => 'Comments', 'enabled' => (bool) $post->allow_comments, 'icon' => 'fa-comments'],
            ['label' => 'Poll', 'enabled' => (bool) $post->allowsPoll(), 'icon' => 'fa-square-poll-vertical'],
            ['label' => 'Share', 'enabled' => (bool) $post->allow_sharing, 'icon' => 'fa-share-nodes'],
        ];
    @endphp

    <div class="cc-show-overview">
        <div class="cc-show-overview__kicker">Creative Corner · SoilnWater community</div>
        <p class="cc-show-overview__tagline mb-0">Original creative work shared with the SoilnWater community — art, design, craft, music, and innovation.</p>
        @unless($portalSidebarLayout)
        <div class="cc-show-overview__chips mt-3">
            @if(filled($postType))
                <span class="cc-show-chip cc-show-chip--highlight">{{ $postType }}</span>
            @endif
            @if(filled($category))
                <span class="cc-show-chip">{{ $category }}</span>
            @endif
            @if(filled(data_get($post->meta, 'creative_corner_creation_type')))
                <span class="cc-show-chip">{{ data_get($post->meta, 'creative_corner_creation_type') }}</span>
            @endif
            @if(filled(data_get($post->meta, 'creative_corner_difficulty_level')))
                <span class="cc-show-chip">{{ data_get($post->meta, 'creative_corner_difficulty_level') }}</span>
            @endif
        </div>
        @endunless
    </div>

    @if($availableForSale)
        <div class="cc-commerce-strip">
            <div class="cc-commerce-strip__title"><i class="fa-solid fa-palette me-1" aria-hidden="true"></i>SoilnWater Creative Marketplace</div>
            <p class="mb-0 small text-secondary">This creation is listed for sale on SoilnWater. Connect with the artist for purchase, custom orders, or shipping details.</p>
        </div>
    @endif

    @if($audiences !== [])
        <div class="cc-section-panel">
            <div class="cc-section-panel__label">Target audience</div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($audiences as $audience)
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">{{ $audience }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($mediums !== [] || $softwareTools !== [] || $materials !== [])
        <div class="cc-section-panel">
            <div class="cc-section-panel__label">Materials &amp; tools</div>
            @if($mediums !== [])
                <p class="small text-muted mb-1">Medium</p>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @foreach($mediums as $medium)
                        <span class="badge bg-light text-dark border px-3 py-2">{{ $medium }}</span>
                    @endforeach
                </div>
            @endif
            @if($softwareTools !== [])
                <p class="small text-muted mb-1">Software / tools</p>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @foreach($softwareTools as $tool)
                        <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2">{{ $tool }}</span>
                    @endforeach
                </div>
            @endif
            @if($materials !== [])
                <p class="small text-muted mb-1">Materials</p>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($materials as $material)
                        <span class="badge bg-secondary-subtle text-secondary border px-3 py-2">{{ $material }}</span>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    @if(collect([
        data_get($post->meta, 'creative_corner_time_taken'),
        data_get($post->meta, 'creative_corner_creation_date'),
        data_get($post->meta, 'creative_corner_copyright'),
    ])->contains(fn ($value) => filled($value)))
        <div class="cc-section-panel">
            <div class="cc-section-panel__label">Creative details</div>
            <div class="cc-meta-grid">
                @foreach([
                    'Time taken' => data_get($post->meta, 'creative_corner_time_taken'),
                    'Created on' => data_get($post->meta, 'creative_corner_creation_date'),
                    'Copyright' => data_get($post->meta, 'creative_corner_copyright'),
                ] as $label => $value)
                    @if(filled($value))
                        <div class="cc-meta-item">
                            <div class="cc-meta-item__label">{{ $label }}</div>
                            <p class="cc-meta-item__value">{{ $value }}</p>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    @if($themes !== [])
        <div class="cc-section-panel">
            <div class="cc-section-panel__label">Themes</div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($themes as $theme)
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">{{ $theme }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @unless($portalSidebarLayout)
    @if(collect([
        data_get($post->meta, 'creative_corner_location_city'),
        data_get($post->meta, 'creative_corner_location_state'),
        data_get($post->meta, 'creative_corner_location_country'),
    ])->filter()->isNotEmpty())
        <div class="cc-section-panel">
            <div class="cc-section-panel__label">Location</div>
            <p class="mb-0">
                <i class="fa-solid fa-location-dot text-danger me-1" aria-hidden="true"></i>
                {{ collect([
                    data_get($post->meta, 'creative_corner_location_city'),
                    data_get($post->meta, 'creative_corner_location_district'),
                    data_get($post->meta, 'creative_corner_location_state'),
                    data_get($post->meta, 'creative_corner_location_country'),
                ])->filter()->implode(', ') }}
            </p>
        </div>
    @endif
    @endunless

    @if(collect([
        data_get($post->meta, 'creative_corner_material_cost'),
        data_get($post->meta, 'creative_corner_equipment_cost'),
        data_get($post->meta, 'creative_corner_total_cost'),
    ])->contains(fn ($value) => filled($value)))
        <div class="cc-section-panel">
            <div class="cc-section-panel__label">Project cost</div>
            <div class="cc-meta-grid">
                @foreach([
                    'Materials' => data_get($post->meta, 'creative_corner_material_cost'),
                    'Equipment' => data_get($post->meta, 'creative_corner_equipment_cost'),
                    'Total' => data_get($post->meta, 'creative_corner_total_cost'),
                ] as $label => $value)
                    @if(filled($value))
                        <div class="cc-meta-item">
                            <div class="cc-meta-item__label">{{ $label }}</div>
                            <p class="cc-meta-item__value">{{ $value }}</p>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    @if($availableForSale || $commissionOptions !== [])
        <div class="cc-flagship-banner cc-flagship-banner--commerce">
            <div class="cc-flagship-banner__heading">
                <i class="fa-solid fa-store" aria-hidden="true"></i>Commerce &amp; commissions
            </div>
            @if($availableForSale)
                <p class="mb-2">
                    <strong>Available for sale</strong>
                    @if(filled(data_get($post->meta, 'creative_corner_sale_price')))
                        <span class="text-muted">— {{ data_get($post->meta, 'creative_corner_sale_price') }}</span>
                    @endif
                </p>
                <div class="d-flex flex-wrap gap-2 mb-2">
                    @if(data_get($post->meta, 'creative_corner_custom_orders_accepted'))
                        <span class="badge bg-warning-subtle text-dark border">Custom orders accepted</span>
                    @endif
                    @if(data_get($post->meta, 'creative_corner_limited_edition'))
                        <span class="badge bg-warning-subtle text-dark border">Limited edition</span>
                    @endif
                    @if(data_get($post->meta, 'creative_corner_shipping_available'))
                        <span class="badge bg-warning-subtle text-dark border">Shipping available</span>
                    @endif
                </div>
            @endif
            @if($commissionOptions !== [])
                <div class="d-flex flex-wrap gap-2">
                    @foreach($commissionOptions as $option)
                        <span class="badge bg-primary-subtle text-primary border px-3 py-2">{{ $option }}</span>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    @if(data_get($post->meta, 'creative_corner_submit_to_competition') && $competitionCategories !== [])
        <div class="cc-flagship-banner cc-flagship-banner--competition">
            <div class="cc-flagship-banner__heading">
                <i class="fa-solid fa-trophy" aria-hidden="true"></i>Creative competition entry
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($competitionCategories as $compCategory)
                    <span class="badge bg-white text-dark border px-3 py-2">{{ $compCategory }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($collaborationRoles !== [])
        <div class="cc-flagship-banner cc-flagship-banner--collab">
            <div class="cc-flagship-banner__heading">
                <i class="fa-solid fa-people-group" aria-hidden="true"></i>Open to collaboration
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($collaborationRoles as $role)
                    <span class="badge bg-white text-dark border px-3 py-2">{{ $role }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($licenses !== [])
        <div class="cc-section-panel">
            <div class="cc-section-panel__label">Creative license</div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($licenses as $license)
                    <span class="badge bg-light text-dark border px-3 py-2">{{ $license }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($socialLinks !== [])
        <div class="cc-section-panel">
            <div class="cc-section-panel__label">Artist links</div>
            <div class="cc-social-grid">
                @foreach($socialLinks as $label => $url)
                    <a href="{{ str_starts_with((string) $url, 'http') ? $url : 'https://'.$url }}" class="cc-social-link" target="_blank" rel="noopener">
                        <i class="fa-solid fa-link" aria-hidden="true"></i>{{ $label }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <div class="cc-section-panel">
        <div class="cc-section-panel__label">Post capabilities</div>
        <div class="cc-capability-grid">
            @foreach($capabilities as $capability)
                <span class="cc-capability-pill {{ $capability['enabled'] ? '' : 'is-disabled' }}">
                    <i class="fa-solid {{ $capability['icon'] }}" aria-hidden="true"></i>
                    {{ $capability['label'] }}
                </span>
            @endforeach
        </div>
    </div>

    @if(filled(data_get($post->meta, 'creative_corner_ai_used')) && data_get($post->meta, 'creative_corner_ai_used') !== 'No')
        <div class="cc-ai-disclosure">
            <div class="cc-section-panel__label">AI disclosure</div>
            <p class="mb-1 fw-semibold">{{ data_get($post->meta, 'creative_corner_ai_used') }}</p>
            @if(filled(data_get($post->meta, 'creative_corner_ai_tool')))
                <p class="small mb-1"><span class="text-muted">Tool:</span> {{ data_get($post->meta, 'creative_corner_ai_tool') }}</p>
            @endif
            @if(filled(data_get($post->meta, 'creative_corner_ai_description')))
                <p class="small mb-0">{{ data_get($post->meta, 'creative_corner_ai_description') }}</p>
            @endif
        </div>
    @endif

    @unless($portalSidebarLayout)
        @include('community.partials.creative-corner-media-sections', ['post' => $post])
    @endunless

    @if(filled($askCommunity))
        <div class="cc-ask-community">
            <div class="cc-section-panel__label mb-2">Ask the community</div>
            <p class="cc-ask-community__quote">“{{ $askCommunity }}”</p>
        </div>
    @endif
@endif
