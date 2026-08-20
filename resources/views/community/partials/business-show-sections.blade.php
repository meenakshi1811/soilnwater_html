@if($post->isBusinessPost())
    @php
        $audiences = array_values(array_filter((array) data_get($post->meta, 'business_target_audience', [])));
        $challenges = array_values(array_filter((array) data_get($post->meta, 'business_challenges', [])));
        $themes = array_values(array_filter((array) data_get($post->meta, 'business_themes', [])));
        $marketSegments = array_values(array_filter((array) data_get($post->meta, 'business_market_segments', [])));
        $businessCategory = $post->businessCategoryLabel();
        $contentType = data_get($post->meta, 'business_content_type');
        $businessStage = data_get($post->meta, 'business_stage');
        $businessIndustry = data_get($post->meta, 'business_industry');
        $opportunityType = data_get($post->meta, 'business_opportunity_type');
        $businessName = data_get($post->meta, 'business_name');
        $designation = data_get($post->meta, 'business_author_designation');
        $profileType = data_get($post->meta, 'business_profile_type');
    @endphp

    @if(filled($businessCategory) || filled($contentType) || filled($businessStage) || filled($businessIndustry))
        <div class="business-hero-strip mb-4">
            @if(filled($businessCategory))
                <div class="business-hero-strip__item">
                    <span class="business-hero-strip__label">Main category</span>
                    <span class="business-hero-strip__value">{{ $businessCategory }}</span>
                </div>
            @endif
            @if(filled($contentType))
                <div class="business-hero-strip__item">
                    <span class="business-hero-strip__label">Content type</span>
                    <span class="business-hero-strip__value">{{ $contentType }}</span>
                </div>
            @endif
            @if(filled($businessStage))
                <div class="business-hero-strip__item">
                    <span class="business-hero-strip__label">Business stage</span>
                    <span class="business-hero-strip__value">{{ $businessStage }}</span>
                </div>
            @endif
            @if(filled($businessIndustry))
                <div class="business-hero-strip__item">
                    <span class="business-hero-strip__label">Industry</span>
                    <span class="business-hero-strip__value">{{ $businessIndustry }}</span>
                </div>
            @endif
        </div>
    @endif

    @if(filled($businessName) || filled($designation) || filled($profileType))
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-briefcase" aria-hidden="true"></i>
                <h4 class="mb-0">Business profile</h4>
            </div>
            <div class="row g-3">
                @if(filled($businessName))
                    <div class="col-md-4">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Business name</span>
                            <span>{{ $businessName }}</span>
                        </div>
                    </div>
                @endif
                @if(filled($designation))
                    <div class="col-md-4">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Author designation</span>
                            <span>{{ $designation }}</span>
                        </div>
                    </div>
                @endif
                @if(filled($profileType))
                    <div class="col-md-4">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Business type</span>
                            <span>{{ $profileType }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if($audiences !== [])
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-users" aria-hidden="true"></i>
                <h4 class="mb-0">Target audience</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($audiences as $audience)
                    <span class="business-pill">{{ $audience }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($challenges !== [] || filled($opportunityType) || $marketSegments !== [])
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-chart-line" aria-hidden="true"></i>
                <h4 class="mb-0">Opportunity & challenges</h4>
            </div>
            @if(filled($opportunityType))
                <div class="business-meta-item mb-3">
                    <span class="business-meta-item__label">Opportunity type</span>
                    <span>{{ $opportunityType }}</span>
                </div>
            @endif
            @if($marketSegments !== [])
                <div class="mb-3">
                    <span class="business-meta-item__label d-block mb-2">Market segment</span>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($marketSegments as $segment)
                            <span class="business-pill business-pill--segment">{{ $segment }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
            @if($challenges !== [])
                <div>
                    <span class="business-meta-item__label d-block mb-2">Business challenges</span>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($challenges as $challenge)
                            <span class="business-pill business-pill--challenge">{{ $challenge }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if($themes !== [])
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-tags" aria-hidden="true"></i>
                <h4 class="mb-0">Business themes</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($themes as $theme)
                    <span class="badge bg-warning-subtle text-dark border">{{ $theme }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @php
        $structuredLocation = $post->structuredLocationForDisplay();
        $locationLabels = \App\Models\CommunityPost::structuredLocationLabelsFor($post->content_type);
    @endphp
    @if($structuredLocation->isNotEmpty() && ! ($moveCareerBusinessExtrasToRail ?? false))
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                <h4 class="mb-0">Location</h4>
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
        </div>
    @endif

    @include('community.partials.business-media-sections', ['post' => $post])
    @include('community.partials.business-engagement-sections', [
        'post' => $post,
        'businessEngagement' => $businessEngagement ?? null,
    ])
@endif
