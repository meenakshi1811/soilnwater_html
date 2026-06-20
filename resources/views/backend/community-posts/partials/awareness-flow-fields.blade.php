@php
    $selectedAwarenessCategory = old('awareness_category', data_get($post->meta, 'awareness_category', $post->category));
    $selectedAwarenessAudiences = old('awareness_target_audience', data_get($post->meta, 'awareness_target_audience', []));
    $flowPlacement = $placement ?? 'all';
    $showAwarenessSetup = in_array($flowPlacement, ['all', 'setup'], true);
    $showAwarenessRest = in_array($flowPlacement, ['all', 'rest'], true);
@endphp

@if($showAwarenessSetup)
<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Awareness category</h5>
            <p class="text-muted mb-0 small">Choose the main topic for this awareness post.</p>
        </div>
        <span class="badge bg-primary text-white">Main category</span>
    </div>
    <label class="form-label" for="awarenessCategory">Main category <span class="text-danger">*</span></label>
    <select name="awareness_category" id="awarenessCategory" class="form-select awareness-required" required>
        <option value="">Select main category</option>
        @foreach(\App\Support\CommunityContentTaxonomy::awarenessCategoryGroups() as $groupLabel => $categories)
            <optgroup label="{{ $groupLabel }}">
                @foreach($categories as $category)
                    <option value="{{ $category }}" @selected($selectedAwarenessCategory === $category)>{{ $category }}</option>
                @endforeach
            </optgroup>
        @endforeach
    </select>
    <small class="text-muted d-block mt-2">
        Examples: Water Conservation, Environment, Health Awareness, Women's Safety, Cyber Security, Financial Literacy, and more.
    </small>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Awareness type</h5>
            <p class="text-muted mb-0 small">How this content is intended to inform or mobilize readers.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Required</span>
    </div>
    <label class="form-label" for="awarenessType">Awareness type <span class="text-danger">*</span></label>
    <select name="awareness_type" id="awarenessType" class="form-select awareness-required" required>
        <option value="">Select awareness type</option>
        @foreach(\App\Support\CommunityContentTaxonomy::awarenessTypes() as $awarenessType)
            <option value="{{ $awarenessType }}" @selected(old('awareness_type', data_get($post->meta, 'awareness_type')) === $awarenessType)>{{ $awarenessType }}</option>
        @endforeach
    </select>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Target audience</h5>
            <p class="text-muted mb-0 small">Select all groups this awareness content is meant for.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Multiple selection</span>
    </div>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::awarenessTargetAudiences() as $audience)
            <div class="col-md-4 col-sm-6">
                <label class="form-check border rounded py-2 px-3 bg-light h-100 mb-0">
                    <input
                        type="checkbox"
                        name="awareness_target_audience[]"
                        value="{{ $audience }}"
                        class="form-check-input awareness-audience-required"
                        @checked(in_array($audience, (array) $selectedAwarenessAudiences, true))
                    >
                    <span class="form-check-label">{{ $audience }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Awareness level</h5>
            <p class="text-muted mb-0 small">Geographic or community scope of this awareness message.</p>
        </div>
        <span class="badge bg-primary text-white">Required</span>
    </div>
    <label class="form-label" for="awarenessLevel">Awareness level <span class="text-danger">*</span></label>
    <select name="awareness_level" id="awarenessLevel" class="form-select awareness-required" required>
        <option value="">Select awareness level</option>
        @foreach(\App\Support\CommunityContentTaxonomy::awarenessLevels() as $level)
            <option value="{{ $level }}" @selected(old('awareness_level', data_get($post->meta, 'awareness_level')) === $level)>{{ $level }}</option>
        @endforeach
    </select>
</div>
@endif

@if($showAwarenessRest)
<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Location</h5>
            <p class="text-muted mb-0 small">Important for SoilnWater — country, state, district, city, and area.</p>
        </div>
        <span class="badge bg-success-subtle text-success border">Required</span>
    </div>
    <div id="communityAwarenessLocationSlot"></div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    @php
        $selectedAwarenessPostedBy = old('awareness_posted_by', data_get($post->meta, 'awareness_posted_by'));
    @endphp
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Organization details</h5>
            <p class="text-muted mb-0 small">Who is posting this awareness content on behalf of SoilnWater.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Posted by required</span>
    </div>
    <label class="form-label d-block">Posted by <span class="text-danger">*</span></label>
    <div class="row g-2 mb-3 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::awarenessPostedByOptions() as $postedByOption)
            <div class="col-md-4 col-sm-6">
                <label class="form-check border rounded py-2 px-3 bg-white h-100 mb-0">
                    <input
                        type="radio"
                        name="awareness_posted_by"
                        value="{{ $postedByOption }}"
                        class="form-check-input awareness-posted-by-required"
                        @checked($selectedAwarenessPostedBy === $postedByOption)
                    >
                    <span class="form-check-label">{{ $postedByOption }}</span>
                </label>
            </div>
        @endforeach
    </div>
    <label class="form-label" for="awarenessOrganizationName">Organization name</label>
    <input
        type="text"
        name="awareness_organization_name"
        id="awarenessOrganizationName"
        class="form-control"
        value="{{ old('awareness_organization_name', data_get($post->meta, 'awareness_organization_name')) }}"
        maxlength="160"
        placeholder="Optional — school, NGO, business, or department name"
    >
    <small class="text-muted d-block mt-2">Optional. Add the organization name when posting on behalf of a group or institution.</small>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Campaign period</h5>
            <p class="text-muted mb-0 small">Useful for awareness drives with a defined start and end.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label" for="awarenessCampaignStartDate">Start date</label>
            <input
                type="date"
                name="awareness_campaign_start_date"
                id="awarenessCampaignStartDate"
                class="form-control"
                value="{{ old('awareness_campaign_start_date', data_get($post->meta, 'awareness_campaign_start_date')) }}"
            >
        </div>
        <div class="col-md-4">
            <label class="form-label" for="awarenessCampaignEndDate">End date</label>
            <input
                type="date"
                name="awareness_campaign_end_date"
                id="awarenessCampaignEndDate"
                class="form-control"
                value="{{ old('awareness_campaign_end_date', data_get($post->meta, 'awareness_campaign_end_date')) }}"
            >
        </div>
    </div>
    <small class="text-muted d-block mt-2">
        Example: World Environment Day Campaign — 1 June to 30 June.
    </small>
</div>

<div class="news-flow-card story-flow-card story-flow-card--cover border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Featured image</h5>
            <p class="text-muted mb-0 small">Campaign banner for listings, homepage, and social sharing.</p>
        </div>
        <span class="badge bg-warning text-dark">Recommended</span>
    </div>
    <p class="mb-2"><strong>Campaign banner <span class="text-danger">*</span></strong> <span class="text-muted fw-normal">(recommended)</span></p>
    <ul class="story-cover-uses list-unstyled small text-muted mb-3">
        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Homepage</li>
        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Social media</li>
        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Awareness listings</li>
    </ul>
    <div id="communityAwarenessFeaturedImagesSlot"></div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Infographics</h5>
            <p class="text-muted mb-0 small">Many users prefer visual content over long text.</p>
        </div>
        <span class="badge bg-info text-dark">Highly recommended</span>
    </div>
    <label class="form-label" for="awarenessInfographics">Upload infographics</label>
    <input
        type="file"
        name="awareness_infographics[]"
        id="awarenessInfographics"
        class="form-control"
        accept=".png,.jpg,.jpeg,.pdf,image/png,image/jpeg,application/pdf"
        multiple
    >
    <small class="text-muted d-block mt-2">PNG, JPG, or PDF. Up to 10 files, max 20 MB each.</small>
    @if(!empty(data_get($post->meta, 'awareness_infographics')))
        <div class="mt-3 d-flex flex-column gap-2">
            @foreach(data_get($post->meta, 'awareness_infographics', []) as $infographic)
                <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                    <input type="checkbox" name="removed_awareness_infographics[]" value="{{ data_get($infographic, 'path') }}" class="form-check-input">
                    <span class="form-check-label">Remove {{ data_get($infographic, 'name', 'infographic') }}</span>
                </label>
            @endforeach
        </div>
    @endif
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Video content</h5>
            <p class="text-muted mb-0 small">Optional awareness video, public service message, training clip, or expert talk.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
    </div>
    <label class="form-label" for="awarenessVideoType">Video type</label>
    <select name="awareness_video_type" id="awarenessVideoType" class="form-select mb-3">
        <option value="">Select video type (optional)</option>
        @foreach(\App\Support\CommunityContentTaxonomy::awarenessVideoTypes() as $videoType)
            <option value="{{ $videoType }}" @selected(old('awareness_video_type', data_get($post->meta, 'awareness_video_type')) === $videoType)>{{ $videoType }}</option>
        @endforeach
    </select>
    <small class="text-muted d-block mb-3">Examples: Awareness Video, Public Service Message, Training Video, Expert Talk.</small>
    <div id="communityAwarenessVideoSlot"></div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Documents</h5>
            <p class="text-muted mb-0 small">Supporting guidelines, brochures, training material, or research.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <label class="form-label" for="awarenessDocuments">Upload documents</label>
    <input
        type="file"
        name="awareness_documents[]"
        id="awarenessDocuments"
        class="form-control"
        accept=".pdf,.doc,.docx,.ppt,.pptx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation"
        multiple
    >
    <small class="text-muted d-block mt-2">
        PDF, DOC, or PPT. Examples: Government Guidelines, Awareness Brochures, Training Material, Research Documents. Up to 6 files, max 20 MB each.
    </small>
    @if(!empty(data_get($post->meta, 'awareness_documents')))
        <div class="mt-3 d-flex flex-column gap-2">
            @foreach(data_get($post->meta, 'awareness_documents', []) as $document)
                <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                    <input type="checkbox" name="removed_awareness_documents[]" value="{{ data_get($document, 'path') }}" class="form-check-input">
                    <span class="form-check-label d-flex align-items-center gap-2">
                        <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
                        Remove {{ data_get($document, 'name', 'document') }}
                    </span>
                </label>
            @endforeach
        </div>
    @endif
</div>

@include('backend.community-posts.partials.awareness-engagement-fields', ['post' => $post])
@endif
