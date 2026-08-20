@if($post->isYouthCornerPost())
    @php
        $mainCategory = data_get($post->meta, 'youth_corner_category') ?: $post->category;
        $contentType = data_get($post->meta, 'youth_corner_content_type');
        $ageGroup = data_get($post->meta, 'youth_corner_age_group');
        $occupation = data_get($post->meta, 'youth_corner_occupation');
        $educationLevel = data_get($post->meta, 'youth_corner_education_level');
        $audiences = array_values(array_filter((array) data_get($post->meta, 'youth_corner_target_audience', [])));
        $opportunityTypes = array_values(array_filter((array) data_get($post->meta, 'youth_corner_opportunity_types', [])));
        $skills = array_values(array_filter((array) data_get($post->meta, 'youth_corner_skills', [])));
        $themes = array_values(array_filter((array) data_get($post->meta, 'youth_corner_themes', [])));
        $communityService = array_values(array_filter((array) data_get($post->meta, 'youth_corner_community_service', [])));
        $networkingOptions = array_values(array_filter((array) data_get($post->meta, 'youth_corner_networking_options', [])));
        $mentorshipRequests = array_values(array_filter((array) data_get($post->meta, 'youth_corner_mentorship_requests', [])));
        $careerArea = data_get($post->meta, 'youth_corner_career_area');
        $startupName = data_get($post->meta, 'youth_corner_startup_name');
        $startupIndustry = data_get($post->meta, 'youth_corner_startup_industry');
        $fundingStage = data_get($post->meta, 'youth_corner_funding_stage');
        $businessIdea = data_get($post->meta, 'youth_corner_business_idea');
        $startupChallenges = data_get($post->meta, 'youth_corner_startup_challenges');
        $startupLessons = data_get($post->meta, 'youth_corner_startup_lessons');
        $projectTitle = data_get($post->meta, 'youth_corner_project_title');
        $projectCategory = data_get($post->meta, 'youth_corner_project_category');
        $projectDescription = data_get($post->meta, 'youth_corner_project_description');
        $projectOutcome = data_get($post->meta, 'youth_corner_project_outcome');
        $askCommunity = data_get($post->meta, 'youth_corner_ask_community');
        $structuredLocation = $post->structuredLocationForDisplay();
        $locationLabels = \App\Models\CommunityPost::structuredLocationLabelsFor($post->content_type);
        $visibilityLabel = $post->youthCornerVisibilityLabel();
        $publishAsLabel = \App\Support\CommunityContentTaxonomy::youthCornerPublishAsOptions()[$post->resolvedPublishAs()]
            ?? $post->publishAsLabel();
        $isStartupStory = $contentType === 'Startup Story';
        $isProjectShowcase = $contentType === \App\Support\CommunityContentTaxonomy::youthCornerProjectContentType();
    @endphp

    @if(filled($mainCategory) || filled($contentType) || filled($ageGroup) || filled($occupation))
        <div class="business-hero-strip mb-4">
            @if(filled($mainCategory))
                <div class="business-hero-strip__item">
                    <span class="business-hero-strip__label">Main category</span>
                    <span class="business-hero-strip__value">{{ $mainCategory }}</span>
                </div>
            @endif
            @if(filled($contentType))
                <div class="business-hero-strip__item">
                    <span class="business-hero-strip__label">Content type</span>
                    <span class="business-hero-strip__value">{{ $contentType }}</span>
                </div>
            @endif
            @if(filled($ageGroup))
                <div class="business-hero-strip__item">
                    <span class="business-hero-strip__label">Age group</span>
                    <span class="business-hero-strip__value">{{ $ageGroup }}</span>
                </div>
            @endif
            @if(filled($occupation))
                <div class="business-hero-strip__item">
                    <span class="business-hero-strip__label">Occupation</span>
                    <span class="business-hero-strip__value">{{ $occupation }}</span>
                </div>
            @endif
            @if($post->youthCornerVisibilitySetting() !== 'public')
                <div class="business-hero-strip__item">
                    <span class="business-hero-strip__label">Visibility</span>
                    <span class="business-hero-strip__value">{{ $visibilityLabel }}</span>
                </div>
            @endif
        </div>
    @endif

    @if(filled($educationLevel) || filled($careerArea))
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-user" aria-hidden="true"></i>
                <h4 class="mb-0">Youth profile</h4>
            </div>
            <div class="row g-3">
                @if(filled($educationLevel))
                    <div class="col-md-6">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Education level</span>
                            <span>{{ $educationLevel }}</span>
                        </div>
                    </div>
                @endif
                @if(filled($careerArea))
                    <div class="col-md-6">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Career area</span>
                            <span>{{ $careerArea }}</span>
                        </div>
                    </div>
                @endif
                <div class="col-md-6">
                    <div class="business-meta-item">
                        <span class="business-meta-item__label">Published as</span>
                        <span>{{ $publishAsLabel }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($isStartupStory && (filled($startupName) || filled($startupIndustry) || filled($fundingStage)))
        <div class="business-section-panel about-box mb-4 border-warning">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-rocket text-warning" aria-hidden="true"></i>
                <h4 class="mb-0">Startup &amp; entrepreneurship</h4>
            </div>
            <div class="row g-3">
                @if(filled($startupName))
                    <div class="col-md-6">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Startup name</span>
                            <span>{{ $startupName }}</span>
                        </div>
                    </div>
                @endif
                @if(filled($startupIndustry))
                    <div class="col-md-6">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Industry</span>
                            <span>{{ $startupIndustry }}</span>
                        </div>
                    </div>
                @endif
                @if(filled($fundingStage))
                    <div class="col-md-6">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Funding stage</span>
                            <span>{{ $fundingStage }}</span>
                        </div>
                    </div>
                @endif
                @if(filled($businessIdea))
                    <div class="col-12">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Business idea</span>
                            <span>{!! nl2br(e($businessIdea)) !!}</span>
                        </div>
                    </div>
                @endif
                @if(filled($startupChallenges))
                    <div class="col-md-6">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Challenges faced</span>
                            <span>{!! nl2br(e($startupChallenges)) !!}</span>
                        </div>
                    </div>
                @endif
                @if(filled($startupLessons))
                    <div class="col-md-6">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Lessons learned</span>
                            <span>{!! nl2br(e($startupLessons)) !!}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if($isProjectShowcase && (filled($projectTitle) || filled($projectCategory)))
        <div class="business-section-panel about-box mb-4 border-primary">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-diagram-project text-primary" aria-hidden="true"></i>
                <h4 class="mb-0">Project showcase</h4>
            </div>
            <div class="row g-3">
                @if(filled($projectTitle))
                    <div class="col-md-6">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Project title</span>
                            <span>{{ $projectTitle }}</span>
                        </div>
                    </div>
                @endif
                @if(filled($projectCategory))
                    <div class="col-md-6">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Category</span>
                            <span>{{ $projectCategory }}</span>
                        </div>
                    </div>
                @endif
                @if(filled($projectDescription))
                    <div class="col-12">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Description</span>
                            <span>{!! nl2br(e($projectDescription)) !!}</span>
                        </div>
                    </div>
                @endif
                @if(filled($projectOutcome))
                    <div class="col-12">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Outcome</span>
                            <span>{!! nl2br(e($projectOutcome)) !!}</span>
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

    @if($opportunityTypes !== [])
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-briefcase" aria-hidden="true"></i>
                <h4 class="mb-0">Opportunity types</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($opportunityTypes as $type)
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $type }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($skills !== [])
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-bolt" aria-hidden="true"></i>
                <h4 class="mb-0">Skills</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($skills as $skill)
                    <span class="badge bg-light text-dark border">{{ $skill }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($themes !== [])
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-tags" aria-hidden="true"></i>
                <h4 class="mb-0">Themes</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($themes as $theme)
                    <span class="badge bg-warning-subtle text-dark border">{{ $theme }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($communityService !== [])
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-hand-holding-heart" aria-hidden="true"></i>
                <h4 class="mb-0">Community service</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($communityService as $activity)
                    <span class="badge bg-success-subtle text-success border border-success-subtle">{{ $activity }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($networkingOptions !== [])
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-network-wired" aria-hidden="true"></i>
                <h4 class="mb-0">Networking</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($networkingOptions as $option)
                    <span class="badge bg-info-subtle text-info-emphasis border">{{ $option }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($mentorshipRequests !== [])
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-hands-helping" aria-hidden="true"></i>
                <h4 class="mb-0">Mentorship requests</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($mentorshipRequests as $request)
                    <span class="badge bg-info-subtle text-info-emphasis border">{{ $request }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($structuredLocation->isNotEmpty())
        @unless($portalSidebarLayout ?? false)
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                <div>
                    <h4 class="mb-0">Location</h4>
                    <p class="text-muted small mb-0">Useful for local opportunities, events, and networking.</p>
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
        </div>
        @endunless
    @endif

    @include('community.partials.youth-corner-media-sections', ['post' => $post])

    @if(filled($askCommunity))
        <div class="business-section-panel about-box mb-4 border-primary">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-comments text-primary" aria-hidden="true"></i>
                <div>
                    <h4 class="mb-0">Ask the community</h4>
                    <p class="text-muted small mb-0">Share your experience or advice in the comments below.</p>
                </div>
            </div>
            <blockquote class="mb-0 ps-3 border-start border-primary border-3 fst-italic">"{{ $askCommunity }}"</blockquote>
        </div>
    @endif

    @if(!empty($post->tags))
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-hashtag" aria-hidden="true"></i>
                <h4 class="mb-0">Tags</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($post->tags as $tag)
                    <span class="badge bg-light text-dark border">{{ $tag }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($post->allowsPoll() || $post->allow_comments || $post->allow_questions || $post->allow_feedback || $post->allow_sharing)
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-sliders" aria-hidden="true"></i>
                <h4 class="mb-0">Reader engagement</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if($post->allowsPoll())
                    <span class="badge bg-primary text-white">Poll open</span>
                @endif
                @if($post->allow_comments)
                    <span class="badge bg-success">Comments</span>
                @endif
                @if($post->allow_questions)
                    <span class="badge bg-success">Questions</span>
                @endif
                @if($post->allow_feedback)
                    <span class="badge bg-success">Peer discussion</span>
                @endif
                @if($post->allow_sharing)
                    <span class="badge bg-success">Sharing</span>
                @endif
            </div>
            @if($post->allowsPoll() && filled(data_get($post->meta, 'youth_corner_poll_question')))
                <p class="small text-muted mb-0 mt-3">
                    <strong>Poll:</strong> {{ data_get($post->meta, 'youth_corner_poll_question') }}
                </p>
            @endif
        </div>
    @endif

    <div class="business-section-panel about-box mb-4">
        <div class="business-section-panel__header">
            <i class="fa-solid fa-heart" aria-hidden="true"></i>
            <h4 class="mb-0">Community reactions</h4>
        </div>
        <p class="text-muted small mb-2">Positive reactions only.</p>
        <div class="d-flex flex-wrap gap-2">
            @foreach(\App\Support\CommunityContentTaxonomy::youthCornerReactionOptions() as $reaction => $icon)
                <span class="badge bg-light text-dark border">
                    <i class="{{ $icon }} me-1" aria-hidden="true"></i>{{ $reaction }}
                </span>
            @endforeach
        </div>
    </div>
@endif
