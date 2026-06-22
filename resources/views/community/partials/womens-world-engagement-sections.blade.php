@php
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
    $hasResources = filled($usefulWebsites) || filled($governmentSchemes) || filled($trainingPrograms) || filled($scholarships) || filled($supportOrganizations);
@endphp

@if($hasResources)
    <div class="business-section-panel about-box mb-4">
        <div class="business-section-panel__header">
            <i class="fa-solid fa-book-open" aria-hidden="true"></i>
            <h4 class="mb-0">Resources</h4>
        </div>
        <div class="row g-3">
            @if(filled($usefulWebsites))
                <div class="col-md-6">
                    <div class="business-resource-card">
                        <h5 class="business-resource-card__title">Useful websites</h5>
                        <div class="business-resource-text">{!! nl2br(e($usefulWebsites)) !!}</div>
                    </div>
                </div>
            @endif
            @if(filled($governmentSchemes))
                <div class="col-md-6">
                    <div class="business-resource-card">
                        <h5 class="business-resource-card__title">Government schemes</h5>
                        <div class="business-resource-text">{!! nl2br(e($governmentSchemes)) !!}</div>
                    </div>
                </div>
            @endif
            @if(filled($trainingPrograms))
                <div class="col-md-6">
                    <div class="business-resource-card">
                        <h5 class="business-resource-card__title">Training programs</h5>
                        <div class="business-resource-text">{!! nl2br(e($trainingPrograms)) !!}</div>
                    </div>
                </div>
            @endif
            @if(filled($scholarships))
                <div class="col-md-6">
                    <div class="business-resource-card">
                        <h5 class="business-resource-card__title">Scholarships</h5>
                        <div class="business-resource-text">{!! nl2br(e($scholarships)) !!}</div>
                    </div>
                </div>
            @endif
            @if(filled($supportOrganizations))
                <div class="col-12">
                    <div class="business-resource-card">
                        <h5 class="business-resource-card__title">Support organizations</h5>
                        <div class="business-resource-text">{!! nl2br(e($supportOrganizations)) !!}</div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endif
