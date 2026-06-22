@php
    $womensWorldPollOptions = old(
        'womens_world_poll_options',
        data_get($post->meta, 'womens_world_poll_options', \App\Support\CommunityContentTaxonomy::womensWorldDefaultPollOptions())
    );
    if (is_string($womensWorldPollOptions)) {
        $womensWorldPollOptions = array_values(array_filter(array_map('trim', preg_split('/\R/', $womensWorldPollOptions))));
    }
    $selectedSupportRequests = old('womens_world_support_requests', data_get($post->meta, 'womens_world_support_requests', []));
    $selectedCommunityGroups = old('womens_world_community_groups', data_get($post->meta, 'womens_world_community_groups', []));
@endphp

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Business information</h5>
            <p class="text-muted mb-0 small">Optional context for entrepreneurs sharing their journey or advice.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <p class="small text-muted mb-3">For entrepreneurs.</p>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="womensWorldBusinessName">Business name</label>
            <input
                type="text"
                name="womens_world_business_name"
                id="womensWorldBusinessName"
                class="form-control"
                value="{{ old('womens_world_business_name', data_get($post->meta, 'womens_world_business_name')) }}"
                maxlength="160"
                placeholder="Your business or brand name"
            >
        </div>
        <div class="col-md-6">
            <label class="form-label" for="womensWorldBusinessCategory">Business category</label>
            <select name="womens_world_business_category" id="womensWorldBusinessCategory" class="form-select">
                <option value="">Select business category (optional)</option>
                @foreach(\App\Support\CommunityContentTaxonomy::womensWorldBusinessCategories() as $category)
                    <option value="{{ $category }}" @selected(old('womens_world_business_category', data_get($post->meta, 'womens_world_business_category')) === $category)>{{ $category }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="womensWorldWebsiteUrl">Website / profile link</label>
            <input
                type="url"
                name="womens_world_website_url"
                id="womensWorldWebsiteUrl"
                class="form-control"
                value="{{ old('womens_world_website_url', data_get($post->meta, 'womens_world_website_url')) }}"
                maxlength="255"
                placeholder="https://example.com"
            >
        </div>
        <div class="col-md-6">
            <label class="form-label" for="womensWorldVendorProfileUrl">SoilnWater vendor profile link</label>
            <input
                type="url"
                name="womens_world_vendor_profile_url"
                id="womensWorldVendorProfileUrl"
                class="form-control"
                value="{{ old('womens_world_vendor_profile_url', data_get($post->meta, 'womens_world_vendor_profile_url')) }}"
                maxlength="255"
                placeholder="https://soilnwater.com/vendor/your-profile"
            >
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3" id="womensWorldPollWrap">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Poll section</h5>
            <p class="text-muted mb-0 small">Highly engaging — ask readers a question with multiple choice answers.</p>
        </div>
        <span class="badge bg-warning text-dark">Highly engaging</span>
    </div>
    <div class="community-flow-stack d-flex flex-column gap-2 mb-3">
        <label class="form-check border rounded p-3 bg-white mb-0" for="womensWorldAllowPoll">
            <input type="checkbox" name="allow_poll" value="1" class="form-check-input" id="womensWorldAllowPoll" @checked(old('allow_poll', $post->allow_poll ?? false))>
            <span class="form-check-label">Enable poll on this Women's World post</span>
            <small class="text-muted d-block mt-1">Example: What is the biggest challenge for working women?</small>
        </label>
    </div>
    <div id="womensWorldPollFields">
        <label class="form-label" for="womensWorldPollQuestion">Poll question</label>
        <input
            type="text"
            name="womens_world_poll_question"
            id="womensWorldPollQuestion"
            class="form-control mb-3"
            maxlength="255"
            value="{{ old('womens_world_poll_question', data_get($post->meta, 'womens_world_poll_question')) }}"
            placeholder="What is the biggest challenge for working women?"
        >
        <label class="form-label" for="womensWorldPollOptions">Poll options</label>
        <textarea
            name="womens_world_poll_options"
            id="womensWorldPollOptions"
            class="form-control"
            rows="4"
            placeholder="One option per line"
        >{{ old('womens_world_poll_options', is_array($womensWorldPollOptions) ? implode("\n", $womensWorldPollOptions) : $womensWorldPollOptions) }}</textarea>
        <small class="text-muted d-block mt-2">Example options: Work-Life Balance, Child Care, Career Growth, Financial Independence.</small>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-primary-subtle mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Ask the community</h5>
            <p class="text-muted mb-0 small">Very powerful feature — invite readers to share experiences in comments.</p>
        </div>
        <span class="badge bg-primary text-white">Very powerful</span>
    </div>
    <label class="form-label" for="womensWorldAskCommunity">Community question</label>
    <textarea
        name="womens_world_ask_community"
        id="womensWorldAskCommunity"
        class="form-control"
        rows="3"
        maxlength="500"
        placeholder="How do you manage work and family responsibilities?"
    >{{ old('womens_world_ask_community', data_get($post->meta, 'womens_world_ask_community')) }}</textarea>
    <small class="text-muted d-block mt-2">Encourages discussion when comments are enabled.</small>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Support request</h5>
            <p class="text-muted mb-0 small">Allow users to seek guidance from the community.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    @foreach(\App\Support\CommunityContentTaxonomy::womensWorldSupportRequestGroups() as $groupLabel => $supportRequests)
        <p class="small text-muted fw-semibold mb-2">{{ $groupLabel }}</p>
        <div class="row g-2 community-flow-checklist mb-3">
            @foreach($supportRequests as $supportRequest)
                <div class="col-md-6">
                    <label class="form-check border rounded py-2 px-3 bg-light h-100 mb-0">
                        <input
                            type="checkbox"
                            name="womens_world_support_requests[]"
                            value="{{ $supportRequest }}"
                            class="form-check-input"
                            @checked(in_array($supportRequest, (array) $selectedSupportRequests, true))
                        >
                        <span class="form-check-label">{{ $supportRequest }}</span>
                    </label>
                </div>
            @endforeach
        </div>
    @endforeach
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Community groups</h5>
            <p class="text-muted mb-0 small">Tag the communities this post is most relevant to.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Multiple selection</span>
    </div>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::womensWorldCommunityGroups() as $communityGroup)
            <div class="col-md-4 col-sm-6">
                <label class="form-check border rounded py-2 px-3 bg-white h-100 mb-0">
                    <input
                        type="checkbox"
                        name="womens_world_community_groups[]"
                        value="{{ $communityGroup }}"
                        class="form-check-input"
                        @checked(in_array($communityGroup, (array) $selectedCommunityGroups, true))
                    >
                    <span class="form-check-label">{{ $communityGroup }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3" id="communityWomensWorldTagsSlotWrap">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Tags</h5>
            <p class="text-muted mb-0 small">Add up to 10 tags to help readers discover this content.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Maximum 10 tags</span>
    </div>
    <div id="communityWomensWorldTagsSlot"></div>
    <small class="text-muted d-block mt-2">
        Examples: {{ implode(', ', \App\Support\CommunityContentTaxonomy::womensWorldTagExamples()) }}.
    </small>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3" id="womensWorldParticipationWrap">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Comments settings</h5>
            <p class="text-muted mb-0 small">Choose how readers can engage with this post.</p>
        </div>
        <span class="badge bg-primary text-white">Engagement</span>
    </div>
    <p class="small text-muted mb-2">Allow:</p>
    <div class="community-flow-stack d-flex flex-column gap-2">
        <label class="form-check border rounded p-3 bg-light mb-0" for="womensWorldAllowComments">
            <input type="checkbox" name="allow_comments" value="1" class="form-check-input" id="womensWorldAllowComments" @checked(old('allow_comments', $post->allow_comments ?? true))>
            <span class="form-check-label">Comments</span>
        </label>
        <label class="form-check border rounded p-3 bg-light mb-0" for="womensWorldAllowQuestions">
            <input type="checkbox" name="allow_questions" value="1" class="form-check-input" id="womensWorldAllowQuestions" @checked(old('allow_questions', $post->allow_questions ?? true))>
            <span class="form-check-label">Questions</span>
        </label>
        <label class="form-check border rounded p-3 bg-light mb-0" for="womensWorldAllowSuggestions">
            <input type="checkbox" name="allow_suggestions" value="1" class="form-check-input" id="womensWorldAllowSuggestions" @checked(old('allow_suggestions', $post->allow_suggestions ?? false))>
            <span class="form-check-label">Suggestions</span>
        </label>
        <label class="form-check border rounded p-3 bg-light mb-0" for="womensWorldAllowSharing">
            <input type="checkbox" name="allow_sharing" value="1" class="form-check-input" id="womensWorldAllowSharing" @checked(old('allow_sharing', $post->allow_sharing ?? true))>
            <span class="form-check-label">Sharing</span>
        </label>
    </div>
</div>
