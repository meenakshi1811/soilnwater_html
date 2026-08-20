@php
    $railLayout = $railLayout ?? false;
    $businessName = data_get($post->meta, 'womens_world_business_name');
    $businessCategory = data_get($post->meta, 'womens_world_business_category');
    $websiteUrl = data_get($post->meta, 'womens_world_website_url');
    $vendorProfileUrl = data_get($post->meta, 'womens_world_vendor_profile_url');
    $askCommunity = data_get($post->meta, 'womens_world_ask_community');
    $supportRequests = array_values(array_filter((array) data_get($post->meta, 'womens_world_support_requests', [])));
    $communityGroups = array_values(array_filter((array) data_get($post->meta, 'womens_world_community_groups', [])));
    $hasBusinessInfo = filled($businessName) || filled($businessCategory) || filled($websiteUrl) || filled($vendorProfileUrl);
@endphp

@if($hasBusinessInfo)
    <div class="business-section-panel about-box mb-4">
        <div class="business-section-panel__header">
            <i class="fa-solid fa-briefcase" aria-hidden="true"></i>
            <div>
                <h4 class="mb-0">Business information</h4>
                <p class="text-muted small mb-0">For entrepreneurs sharing their journey or advice.</p>
            </div>
        </div>
        <div class="row g-3">
            @if(filled($businessName))
                <div class="col-md-6">
                    <div class="business-meta-item">
                        <span class="business-meta-item__label">Business name</span>
                        <span>{{ $businessName }}</span>
                    </div>
                </div>
            @endif
            @if(filled($businessCategory))
                <div class="col-md-6">
                    <div class="business-meta-item">
                        <span class="business-meta-item__label">Business category</span>
                        <span>{{ $businessCategory }}</span>
                    </div>
                </div>
            @endif
            @if(filled($websiteUrl))
                <div class="col-md-6">
                    <div class="business-meta-item">
                        <span class="business-meta-item__label">Website / profile</span>
                        <a href="{{ $websiteUrl }}" target="_blank" rel="noopener noreferrer">{{ $websiteUrl }}</a>
                    </div>
                </div>
            @endif
            @if(filled($vendorProfileUrl))
                <div class="col-md-6">
                    <div class="business-meta-item">
                        <span class="business-meta-item__label">SoilnWater vendor profile</span>
                        <a href="{{ $vendorProfileUrl }}" target="_blank" rel="noopener noreferrer">View vendor profile</a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endif

@if($supportRequests !== [])
    <div class="business-section-panel about-box mb-4">
        <div class="business-section-panel__header">
            <i class="fa-solid fa-hand-holding-heart" aria-hidden="true"></i>
            <div>
                <h4 class="mb-0">Support request</h4>
                <p class="text-muted small mb-0">The author is seeking guidance from the community.</p>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @foreach($supportRequests as $supportRequest)
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $supportRequest }}</span>
            @endforeach
        </div>
    </div>
@endif

@if($communityGroups !== [])
    <div class="business-section-panel about-box mb-4">
        <div class="business-section-panel__header">
            <i class="fa-solid fa-people-group" aria-hidden="true"></i>
            <div>
                <h4 class="mb-0">Community groups</h4>
                <p class="text-muted small mb-0">Tagged communities for this post.</p>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @foreach($communityGroups as $communityGroup)
                <span class="badge bg-light text-dark border">{{ $communityGroup }}</span>
            @endforeach
        </div>
    </div>
@endif

@if(filled($askCommunity))
    <div class="business-section-panel business-ask-panel about-box mb-4">
        <div class="business-section-panel__header">
            <i class="fa-solid fa-comments" aria-hidden="true"></i>
            <div>
                <h4 class="mb-0">Ask the community</h4>
                <p class="text-muted small mb-0">Share your experience or answer the author's question below.</p>
            </div>
        </div>
        <p class="business-ask-panel__lead mb-3">{{ $askCommunity }}</p>
        @if($post->allow_questions || $post->allow_comments || $post->allow_suggestions)
            <div class="d-flex flex-wrap gap-2">
                @if($post->allow_questions)
                    <a href="#communityAuthorQuestions" class="btn btn-sm btn-outline-primary">Ask a question</a>
                @endif
                @if($post->allow_comments)
                    <a href="#participation-comments" class="btn btn-sm btn-outline-secondary">Join the discussion</a>
                @endif
                @if($post->allow_suggestions)
                    <a href="#public-participation" class="btn btn-sm btn-outline-success">Share a suggestion</a>
                @endif
            </div>
        @endif
    </div>
@endif

@php
    $usefulWebsites = trim((string) data_get($post->meta, 'womens_world_useful_websites', ''));
    $governmentSchemes = trim((string) data_get($post->meta, 'womens_world_government_schemes', ''));
    $trainingPrograms = trim((string) data_get($post->meta, 'womens_world_training_programs', ''));
    $scholarships = trim((string) data_get($post->meta, 'womens_world_scholarships', ''));
    $supportOrganizations = trim((string) data_get($post->meta, 'womens_world_support_organizations', ''));
    $resourceItems = array_values(array_filter([
        [
            'title' => 'Useful websites',
            'icon' => 'fa-globe',
            'content' => $usefulWebsites,
        ],
        [
            'title' => 'Government schemes',
            'icon' => 'fa-landmark',
            'content' => $governmentSchemes,
        ],
        [
            'title' => 'Training programs',
            'icon' => 'fa-chalkboard-user',
            'content' => $trainingPrograms,
        ],
        [
            'title' => 'Scholarships',
            'icon' => 'fa-graduation-cap',
            'content' => $scholarships,
        ],
        [
            'title' => 'Support organizations',
            'icon' => 'fa-hands-holding-heart',
            'content' => $supportOrganizations,
        ],
    ], fn (array $item): bool => filled($item['content'])));
    $hasResources = $resourceItems !== [];
@endphp

@if($hasResources)
    @if($railLayout)
        <div class="community-news-rail__card community-news-rail__card--detail community-news-rail__card--womens-resources mb-3">
            <div class="ww-rail-resources__head">
                <span class="ww-rail-resources__icon" aria-hidden="true">
                    <i class="fa-solid fa-book-open"></i>
                </span>
                <div>
                    <h3 class="community-news-rail__title mb-0">Resources</h3>
                    <p class="ww-rail-resources__lead mb-0">{{ count($resourceItems) }} {{ count($resourceItems) === 1 ? 'category' : 'categories' }}</p>
                </div>
            </div>
            <ul class="ww-rail-resources__list">
                @foreach($resourceItems as $resource)
                    <li class="ww-rail-resource">
                        <div class="ww-rail-resource__badge" aria-hidden="true">
                            <i class="fa-solid {{ $resource['icon'] }}"></i>
                        </div>
                        <div class="ww-rail-resource__body">
                            <strong class="ww-rail-resource__title">{{ $resource['title'] }}</strong>
                            <div class="ww-rail-resource__text">
                                @foreach(preg_split('/\r\n|\r|\n/', $resource['content']) as $line)
                                    @php
                                        $line = trim($line);
                                    @endphp
                                    @continue($line === '')
                                    @if(filter_var($line, FILTER_VALIDATE_URL))
                                        <a href="{{ $line }}" target="_blank" rel="noopener noreferrer" class="ww-rail-resource__link">
                                            <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                                            <span>{{ $line }}</span>
                                        </a>
                                    @else
                                        <p class="ww-rail-resource__line mb-0">{{ $line }}</p>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @else
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-book-open" aria-hidden="true"></i>
                <h4 class="mb-0">Resources</h4>
            </div>
            <div class="row g-3">
                @foreach($resourceItems as $resource)
                    <div @class(['col-md-6' => count($resourceItems) > 1, 'col-12' => count($resourceItems) === 1])>
                        <div class="business-resource-card">
                            <h5 class="business-resource-card__title">{{ $resource['title'] }}</h5>
                            <div class="business-resource-text">{!! nl2br(e($resource['content'])) !!}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endif
