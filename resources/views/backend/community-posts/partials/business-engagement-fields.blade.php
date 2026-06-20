@php
    $selectedContactOptions = old('business_contact_options', data_get($post->meta, 'business_contact_options', []));
    $businessPollOptions = old(
        'business_poll_options',
        data_get($post->meta, 'business_poll_options', \App\Support\CommunityContentTaxonomy::businessDefaultPollOptions())
    );
    if (is_string($businessPollOptions)) {
        $businessPollOptions = array_values(array_filter(array_map('trim', preg_split('/\R/', $businessPollOptions))));
    }
@endphp

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3" id="communityBusinessTagsSlotWrap">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Tags</h5>
            <p class="text-muted mb-0 small">Add up to 10 tags to help readers discover this business content.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Maximum 10 tags</span>
    </div>
    <div id="communityBusinessTagsSlot"></div>
    <small class="text-muted d-block mt-2">
        Examples: {{ implode(', ', \App\Support\CommunityContentTaxonomy::businessTagExamples()) }}.
    </small>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3" id="businessPollWrap">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Poll section</h5>
            <p class="text-muted mb-0 small">Highly recommended — ask readers a business question with multiple choice answers.</p>
        </div>
        <span class="badge bg-warning text-dark">Highly recommended</span>
    </div>
    <div class="community-flow-stack d-flex flex-column gap-2 mb-3">
        <label class="form-check border rounded p-3 bg-light mb-0" for="businessAllowPoll">
            <input type="checkbox" name="allow_poll" value="1" class="form-check-input" id="businessAllowPoll" @checked(old('allow_poll', $post->allow_poll ?? false))>
            <span class="form-check-label">Enable poll on this business post</span>
            <small class="text-muted d-block mt-1">Example: What is the biggest challenge for small businesses?</small>
        </label>
    </div>
    <div id="businessPollFields">
        <label class="form-label" for="businessPollQuestion">Poll question</label>
        <input
            type="text"
            name="business_poll_question"
            id="businessPollQuestion"
            class="form-control mb-3"
            maxlength="255"
            value="{{ old('business_poll_question', data_get($post->meta, 'business_poll_question')) }}"
            placeholder="What is the biggest challenge for small businesses?"
        >
        <label class="form-label" for="businessPollOptions">Poll options</label>
        <textarea
            name="business_poll_options"
            id="businessPollOptions"
            class="form-control"
            rows="4"
            placeholder="One option per line"
        >{{ old('business_poll_options', is_array($businessPollOptions) ? implode("\n", $businessPollOptions) : $businessPollOptions) }}</textarea>
        <small class="text-muted d-block mt-2">Example options: Marketing, Finance, Staff, Technology.</small>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-primary-subtle mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Ask the community</h5>
            <p class="text-muted mb-0 small">Excellent engagement feature — invite readers to share experiences in comments.</p>
        </div>
        <span class="badge bg-primary text-white">Engagement</span>
    </div>
    <label class="form-label" for="businessAskCommunity">Community question</label>
    <textarea
        name="business_ask_community"
        id="businessAskCommunity"
        class="form-control"
        rows="3"
        maxlength="500"
        placeholder="What strategies helped you grow your business?"
    >{{ old('business_ask_community', data_get($post->meta, 'business_ask_community')) }}</textarea>
    <small class="text-muted d-block mt-2">Users can comment and share experiences when comments are enabled.</small>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Business resources</h5>
            <p class="text-muted mb-0 small">Optional links and references for readers.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="businessUsefulLinks">Useful links</label>
            <textarea name="business_useful_links" id="businessUsefulLinks" class="form-control" rows="3" placeholder="One URL per line">{{ old('business_useful_links', data_get($post->meta, 'business_useful_links')) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="businessGovernmentSchemes">Government schemes</label>
            <textarea name="business_government_schemes" id="businessGovernmentSchemes" class="form-control" rows="3" placeholder="Scheme names or links">{{ old('business_government_schemes', data_get($post->meta, 'business_government_schemes')) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="businessTrainingPrograms">Training programs</label>
            <textarea name="business_training_programs" id="businessTrainingPrograms" class="form-control" rows="3" placeholder="Program names or links">{{ old('business_training_programs', data_get($post->meta, 'business_training_programs')) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="businessIndustryResources">Industry resources</label>
            <textarea name="business_industry_resources" id="businessIndustryResources" class="form-control" rows="3" placeholder="Reports, portals, or reference links">{{ old('business_industry_resources', data_get($post->meta, 'business_industry_resources')) }}</textarea>
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Contact / networking option</h5>
            <p class="text-muted mb-0 small">Allow readers to connect with you for networking opportunities.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::businessContactOptions() as $contactOption)
            <div class="col-md-4 col-sm-6">
                <label class="form-check border rounded py-2 px-3 bg-white h-100 mb-0">
                    <input
                        type="checkbox"
                        name="business_contact_options[]"
                        value="{{ $contactOption }}"
                        class="form-check-input"
                        @checked(in_array($contactOption, (array) $selectedContactOptions, true))
                    >
                    <span class="form-check-label">{{ $contactOption }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Author profile</h5>
            <p class="text-muted mb-0 small">Displayed on the public post from your account and business profile fields.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Auto display</span>
    </div>
    <ul class="list-unstyled small text-muted mb-0">
        <li><i class="fa-solid fa-user me-1 text-success" aria-hidden="true"></i>Author name from your account profile</li>
        <li><i class="fa-solid fa-building me-1 text-success" aria-hidden="true"></i>Business name from the business profile section above</li>
        <li><i class="fa-solid fa-id-badge me-1 text-success" aria-hidden="true"></i>Designation from the business profile section above</li>
        <li><i class="fa-solid fa-location-dot me-1 text-success" aria-hidden="true"></i>Location from the location fields above</li>
    </ul>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3" id="businessParticipationWrap">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Comments &amp; discussion</h5>
            <p class="text-muted mb-0 small">Choose how readers can engage with this business post.</p>
        </div>
        <span class="badge bg-primary text-white">Engagement</span>
    </div>
    <div class="community-flow-stack d-flex flex-column gap-2">
        <label class="form-check border rounded p-3 bg-white mb-0" for="businessAllowComments">
            <input type="checkbox" name="allow_comments" value="1" class="form-check-input" id="businessAllowComments" @checked(old('allow_comments', $post->allow_comments ?? true))>
            <span class="form-check-label">Comments</span>
        </label>
        <label class="form-check border rounded p-3 bg-white mb-0" for="businessAllowQuestions">
            <input type="checkbox" name="allow_questions" value="1" class="form-check-input" id="businessAllowQuestions" @checked(old('allow_questions', $post->allow_questions ?? true))>
            <span class="form-check-label">Questions</span>
        </label>
        <label class="form-check border rounded p-3 bg-white mb-0" for="businessAllowSuggestions">
            <input type="checkbox" name="allow_suggestions" value="1" class="form-check-input" id="businessAllowSuggestions" @checked(old('allow_suggestions', $post->allow_suggestions ?? false))>
            <span class="form-check-label">Suggestions</span>
        </label>
        <label class="form-check border rounded p-3 bg-white mb-0" for="businessAllowSharing">
            <input type="checkbox" name="allow_sharing" value="1" class="form-check-input" id="businessAllowSharing" @checked(old('allow_sharing', $post->allow_sharing ?? true))>
            <span class="form-check-label">Sharing</span>
        </label>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Community reactions</h5>
            <p class="text-muted mb-0 small">Readers can react to this business post on the public page.</p>
        </div>
        <span class="badge bg-success text-white">Enabled</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::businessReactionLabels() as $reaction)
            <span class="badge bg-light text-dark border">{{ $reaction }}</span>
        @endforeach
    </div>
</div>
