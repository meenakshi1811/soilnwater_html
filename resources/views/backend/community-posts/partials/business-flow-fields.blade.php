@php
    $selectedBusinessCategory = old('business_category', data_get($post->meta, 'business_category', $post->category));
    $selectedBusinessAudiences = old('business_target_audience', data_get($post->meta, 'business_target_audience', []));
    $selectedBusinessChallenges = old('business_challenges', data_get($post->meta, 'business_challenges', []));
    $selectedMarketSegments = old('business_market_segments', data_get($post->meta, 'business_market_segments', []));
    $selectedBusinessThemes = old('business_themes', data_get($post->meta, 'business_themes', []));
    $selectedContactOptions = old('business_contact_options', data_get($post->meta, 'business_contact_options', []));
    $businessPollOptions = old(
        'business_poll_options',
        data_get($post->meta, 'business_poll_options', \App\Support\CommunityContentTaxonomy::businessDefaultPollOptions())
    );
    if (is_string($businessPollOptions)) {
        $businessPollOptions = array_values(array_filter(array_map('trim', preg_split('/\R/', $businessPollOptions))));
    }
    $flowPlacement = $placement ?? 'all';
    $showBusinessSetup = in_array($flowPlacement, ['all', 'setup'], true);
    $showBusinessRest = in_array($flowPlacement, ['all', 'rest'], true);
@endphp

@if($showBusinessSetup)
<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Main category</h5>
            <p class="text-muted mb-0 small">Choose the primary business topic for this post.</p>
        </div>
        <span class="badge bg-primary text-white">Main category</span>
    </div>
    <label class="form-label" for="businessCategory">Main category <span class="text-danger">*</span></label>
    <select name="business_category" id="businessCategory" class="form-select business-required" required>
        <option value="">Select main category</option>
        @foreach(\App\Support\CommunityContentTaxonomy::businessMainCategories() as $category)
            <option value="{{ $category }}" @selected($selectedBusinessCategory === $category)>{{ $category }}</option>
        @endforeach
    </select>
    <small class="text-muted d-block mt-2">
        Examples: Entrepreneurship, Startup, Small Business, Retail Business, Manufacturing, Service Business, Home-Based Business, Women Entrepreneurship, Agriculture Business, Construction Business, Real Estate Business, E-Commerce, Marketing, Finance, Business Technology, Leadership.
    </small>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Business content type</h5>
            <p class="text-muted mb-0 small">How this business content should be classified for readers.</p>
        </div>
        <span class="badge bg-danger text-white">Very important</span>
    </div>
    <label class="form-label" for="businessContentType">Type <span class="text-danger">*</span></label>
    <select name="business_content_type" id="businessContentType" class="form-select business-required" required>
        <option value="">Select business content type</option>
        @foreach(\App\Support\CommunityContentTaxonomy::businessContentTypeGroups() as $groupLabel => $groupOptions)
            <optgroup label="{{ $groupLabel }}">
                @foreach($groupOptions as $contentType)
                    <option value="{{ $contentType }}" @selected(old('business_content_type', data_get($post->meta, 'business_content_type')) === $contentType)>{{ $contentType }}</option>
                @endforeach
            </optgroup>
        @endforeach
    </select>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Business stage</h5>
            <p class="text-muted mb-0 small">Useful for startup and growth-focused content.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Useful for startups</span>
    </div>
    <label class="form-label" for="businessStage">Business stage <span class="text-danger">*</span></label>
    <select name="business_stage" id="businessStage" class="form-select business-required" required>
        <option value="">Select business stage</option>
        @foreach(\App\Support\CommunityContentTaxonomy::businessStages() as $stage)
            <option value="{{ $stage }}" @selected(old('business_stage', data_get($post->meta, 'business_stage')) === $stage)>{{ $stage }}</option>
        @endforeach
    </select>
</div>

<div class="news-flow-card story-flow-card story-flow-card--audience border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Target audience</h5>
            <p class="text-muted mb-0 small">Select all groups this business content is meant for.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Multiple selection</span>
    </div>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::businessTargetAudiences() as $audience)
            <div class="col-md-4 col-sm-6">
                <label class="form-check border rounded py-2 px-3 bg-white h-100 mb-0">
                    <input
                        type="checkbox"
                        name="business_target_audience[]"
                        value="{{ $audience }}"
                        class="form-check-input business-audience-required"
                        @checked(in_array($audience, (array) $selectedBusinessAudiences, true))
                    >
                    <span class="form-check-label">{{ $audience }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>
@endif

@if($showBusinessRest)
<div class="news-flow-card story-flow-card story-flow-card--location border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Location</h5>
            <p class="text-muted mb-0 small">Important for SoilnWater — country, state, district, and city.</p>
        </div>
        <span class="badge bg-success-subtle text-success border">Required</span>
    </div>
    <div id="communityBusinessLocationSlot"></div>
</div>

<div class="news-flow-card story-flow-card story-flow-card--cover border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Featured image</h5>
            <p class="text-muted mb-0 small">Cover image for listings, social sharing, and homepage.</p>
        </div>
        <span class="badge bg-warning text-dark">Recommended</span>
    </div>
    <p class="mb-2"><strong>Cover image</strong> <span class="text-muted fw-normal">Recommended size: 1200 × 630 px</span></p>
    <div id="communityBusinessFeaturedImagesSlot"></div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Business profile</h5>
            <p class="text-muted mb-0 small">Optional context about the business and author behind this content.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional but valuable</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="businessName">Business name</label>
            <input
                type="text"
                name="business_name"
                id="businessName"
                class="form-control"
                value="{{ old('business_name', data_get($post->meta, 'business_name')) }}"
                maxlength="160"
                placeholder="Your business or brand name"
            >
        </div>
        <div class="col-md-6">
            <label class="form-label" for="businessAuthorDesignation">Designation</label>
            <input
                type="text"
                name="business_author_designation"
                id="businessAuthorDesignation"
                class="form-control"
                value="{{ old('business_author_designation', data_get($post->meta, 'business_author_designation')) }}"
                maxlength="120"
                placeholder="e.g. Founder, CEO, Consultant"
            >
        </div>
        <div class="col-md-6">
            <label class="form-label" for="businessProfileType">Business type</label>
            <select name="business_profile_type" id="businessProfileType" class="form-select">
                <option value="">Select business type (optional)</option>
                @foreach(\App\Support\CommunityContentTaxonomy::businessProfileTypes() as $profileType)
                    <option value="{{ $profileType }}" @selected(old('business_profile_type', data_get($post->meta, 'business_profile_type')) === $profileType)>{{ $profileType }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="businessIndustry">Industry</label>
            <select name="business_industry" id="businessIndustry" class="form-select">
                <option value="">Select industry (optional)</option>
                @foreach(\App\Support\CommunityContentTaxonomy::businessIndustries() as $industry)
                    <option value="{{ $industry }}" @selected(old('business_industry', data_get($post->meta, 'business_industry')) === $industry)>{{ $industry }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Business challenges</h5>
            <p class="text-muted mb-0 small">Select the challenges this content addresses.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Multiple selection</span>
    </div>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::businessChallenges() as $challenge)
            <div class="col-md-4 col-sm-6">
                <label class="form-check border rounded py-2 px-3 bg-white h-100 mb-0">
                    <input
                        type="checkbox"
                        name="business_challenges[]"
                        value="{{ $challenge }}"
                        class="form-check-input"
                        @checked(in_array($challenge, (array) $selectedBusinessChallenges, true))
                    >
                    <span class="form-check-label">{{ $challenge }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Business opportunity section</h5>
            <p class="text-muted mb-0 small">Optional opportunity or collaboration context.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <label class="form-label" for="businessOpportunityType">Opportunity type</label>
    <select name="business_opportunity_type" id="businessOpportunityType" class="form-select mb-3">
        <option value="">Select opportunity type (optional)</option>
        @foreach(\App\Support\CommunityContentTaxonomy::businessOpportunityTypes() as $opportunityType)
            <option value="{{ $opportunityType }}" @selected(old('business_opportunity_type', data_get($post->meta, 'business_opportunity_type')) === $opportunityType)>{{ $opportunityType }}</option>
        @endforeach
    </select>
    <label class="form-label d-block">Market segment</label>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::businessMarketSegments() as $segment)
            <div class="col-md-4 col-sm-6">
                <label class="form-check border rounded py-2 px-3 bg-light h-100 mb-0">
                    <input
                        type="checkbox"
                        name="business_market_segments[]"
                        value="{{ $segment }}"
                        class="form-check-input"
                        @checked(in_array($segment, (array) $selectedMarketSegments, true))
                    >
                    <span class="form-check-label">{{ $segment }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card story-flow-card--theme border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Business themes</h5>
            <p class="text-muted mb-0 small">Select all themes that apply to this content.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Multiple selection</span>
    </div>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::businessThemes() as $theme)
            <div class="col-md-4 col-sm-6">
                <label class="form-check border rounded py-2 px-3 bg-white h-100 mb-0">
                    <input
                        type="checkbox"
                        name="business_themes[]"
                        value="{{ $theme }}"
                        class="form-check-input"
                        @checked(in_array($theme, (array) $selectedBusinessThemes, true))
                    >
                    <span class="form-check-label">{{ $theme }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card story-flow-card--gallery border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Image gallery</h5>
            <p class="text-muted mb-0 small">Optional visual gallery alongside your business content.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
    </div>
    <p class="small text-muted mb-3">Examples: Office, Products, Projects, Events, Manufacturing Unit</p>
    <input type="file" name="business_gallery[]" id="businessGallery" class="form-control" accept="image/*" multiple>
    <small class="text-muted d-block mt-2">JPG, PNG, WebP, or GIF. Up to 10 images, max 4 MB each.</small>
    @if(!empty(data_get($post->meta, 'business_gallery')))
        <div class="mt-3 d-flex flex-column gap-2">
            @foreach(data_get($post->meta, 'business_gallery', []) as $galleryImage)
                <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                    <input type="checkbox" name="removed_business_gallery[]" value="{{ data_get($galleryImage, 'path') }}" class="form-check-input">
                    <span class="form-check-label">Remove {{ data_get($galleryImage, 'name', 'gallery image') }}</span>
                </label>
            @endforeach
        </div>
    @endif
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Documents</h5>
            <p class="text-muted mb-0 small">Upload supporting business documents. You can also link documents inside the rich text editor.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <label class="form-label" for="businessDocuments">Upload documents</label>
    <input
        type="file"
        name="business_documents[]"
        id="businessDocuments"
        class="form-control"
        accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
        multiple
    >
    <small class="text-muted d-block mt-2">PDF, DOC, PPT, or XLS. Examples: Business Plan, Market Research, Presentation, Case Study. Up to 6 files, max 20 MB each.</small>
    @if(!empty(data_get($post->meta, 'business_documents')))
        <div class="mt-3 d-flex flex-column gap-2">
            @foreach(data_get($post->meta, 'business_documents', []) as $document)
                <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                    <input type="checkbox" name="removed_business_documents[]" value="{{ data_get($document, 'path') }}" class="form-check-input">
                    <span class="form-check-label d-flex align-items-center gap-2">
                        <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
                        Remove {{ data_get($document, 'name', 'document') }}
                    </span>
                </label>
            @endforeach
        </div>
    @endif
</div>

<div class="news-flow-card story-flow-card story-flow-card--video border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Video section</h5>
            <p class="text-muted mb-0 small">Optional business video. You can also embed videos inside the rich text editor.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
    </div>
    <label class="form-label" for="businessVideoType">Video type</label>
    <select name="business_video_type" id="businessVideoType" class="form-select mb-3">
        <option value="">Select video type (optional)</option>
        @foreach(\App\Support\CommunityContentTaxonomy::businessVideoTypes() as $videoType)
            <option value="{{ $videoType }}" @selected(old('business_video_type', data_get($post->meta, 'business_video_type')) === $videoType)>{{ $videoType }}</option>
        @endforeach
    </select>
    <small class="text-muted d-block mb-3">Examples: Business Introduction, Founder Interview, Factory Tour, Customer Testimonial.</small>
    <div id="communityBusinessVideoSlot"></div>
</div>

@include('backend.community-posts.partials.business-engagement-fields', ['post' => $post])
@endif
