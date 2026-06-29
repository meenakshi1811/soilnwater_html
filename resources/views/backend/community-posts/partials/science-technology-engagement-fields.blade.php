@php
    $stPollOptions = old(
        'science_technology_poll_options',
        data_get($post->meta, 'science_technology_poll_options', \App\Support\CommunityContentTaxonomy::scienceTechnologyDefaultPollOptions())
    );
    if (is_string($stPollOptions)) {
        $stPollOptions = array_values(array_filter(array_map('trim', preg_split('/\R/', $stPollOptions))));
    }
@endphp

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-primary-subtle mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Innovation showcase</h5>
            <p class="text-muted mb-0 small">A curated SoilnWater gallery — mark your post for the innovation showcase.</p>
        </div>
        <span class="badge bg-primary text-white">SoilnWater unique</span>
    </div>
    <label class="form-check border rounded p-3 bg-white mb-3" for="stEnableInnovationShowcase">
        <input type="checkbox" name="science_technology_enable_innovation_showcase" value="1" class="form-check-input science-technology-flow-field" id="stEnableInnovationShowcase" @checked(old('science_technology_enable_innovation_showcase', data_get($post->meta, 'science_technology_enable_innovation_showcase', false)))>
        <span class="form-check-label">Feature in Innovation Showcase (prototypes, patents, research, startups, engineering designs, working models)</span>
    </label>
    <label class="form-check border rounded p-3 bg-white mb-0" for="stEnableExpertReview">
        <input type="checkbox" name="science_technology_enable_expert_review" value="1" class="form-check-input science-technology-flow-field" id="stEnableExpertReview" @checked(old('science_technology_enable_expert_review', data_get($post->meta, 'science_technology_enable_expert_review', false)))>
        <span class="form-check-label">Allow verified experts to provide technical feedback and validation</span>
    </label>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Open innovation marketplace</h5>
            <p class="text-muted mb-0 small">Mark your work for collaboration, funding, or licensing opportunities.</p>
        </div>
        <span class="badge bg-success text-white">Commercialization</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::scienceTechnologyOpenInnovationOptions() as $option)
            <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                <input type="checkbox" name="science_technology_open_innovation[]" value="{{ $option }}" class="form-check-input science-technology-flow-field" @checked(in_array($option, (array) old('science_technology_open_innovation', data_get($post->meta, 'science_technology_open_innovation', [])), true))>
                <span class="form-check-label">{{ $option }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Innovation challenges</h5>
            <p class="text-muted mb-0 small">Tag your post for periodic innovation competitions.</p>
        </div>
        <span class="badge bg-warning text-dark">Competitions</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::scienceTechnologyInnovationChallengeThemes() as $theme)
            <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                <input type="checkbox" name="science_technology_challenge_themes[]" value="{{ $theme }}" class="form-check-input science-technology-flow-field" @checked(in_array($theme, (array) old('science_technology_challenge_themes', data_get($post->meta, 'science_technology_challenge_themes', [])), true))>
                <span class="form-check-label">{{ $theme }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Collaboration request</h5>
            <p class="text-muted mb-0 small">One unique SoilnWater feature — tell the community what kind of partner you need.</p>
        </div>
        <span class="badge bg-info text-dark">Unique feature</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::scienceTechnologyCollaborationRequests() as $request)
            <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                <input type="checkbox" name="science_technology_collaboration_requests[]" value="{{ $request }}" class="form-check-input science-technology-flow-field" @checked(in_array($request, (array) old('science_technology_collaboration_requests', data_get($post->meta, 'science_technology_collaboration_requests', [])), true))>
                <span class="form-check-label">{{ $request }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <label class="form-label" for="stAskCommunity">Ask the community</label>
    <textarea name="science_technology_ask_community" id="stAskCommunity" class="form-control science-technology-flow-field" rows="2" maxlength="500" placeholder="e.g. How can this design be improved? Has anyone implemented similar technology?">{{ old('science_technology_ask_community', data_get($post->meta, 'science_technology_ask_community')) }}</textarea>
    <small class="text-muted">Examples: design improvements, similar implementations, cost reduction suggestions.</small>
</div>

<div id="stPollWrap" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Poll section</h5>
            <p class="text-muted mb-0 small">Optional community poll — e.g. &ldquo;Should AI be widely adopted in agriculture?&rdquo;</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <label class="form-check border rounded p-3 bg-light mb-3" for="stAllowPoll">
        <input type="checkbox" name="science_technology_allow_poll" value="1" class="form-check-input science-technology-flow-field" id="stAllowPoll" @checked(old('science_technology_allow_poll', data_get($post->meta, 'science_technology_allow_poll', false)))>
        <span class="form-check-label">Include a poll on this post</span>
    </label>
    <div id="stPollFields" style="display:none;">
        <label class="form-label" for="stPollQuestion">Poll question</label>
        <input type="text" name="science_technology_poll_question" id="stPollQuestion" class="form-control science-technology-flow-field mb-3" maxlength="255" value="{{ old('science_technology_poll_question', data_get($post->meta, 'science_technology_poll_question')) }}" placeholder="e.g. Should AI be widely adopted in agriculture?">
        <label class="form-label" for="stPollOptions">Poll options (one per line)</label>
        <textarea name="science_technology_poll_options" id="stPollOptions" class="form-control science-technology-flow-field" rows="3" maxlength="2000">{{ is_array($stPollOptions) ? implode("\n", $stPollOptions) : $stPollOptions }}</textarea>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Comments settings</h5>
            <p class="text-muted mb-0 small">Choose what types of comments are welcome.</p>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::scienceTechnologyCommentSettings() as $setting)
            <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                <input type="checkbox" name="science_technology_comment_settings[]" value="{{ $setting }}" class="form-check-input science-technology-flow-field" @checked(in_array($setting, (array) old('science_technology_comment_settings', data_get($post->meta, 'science_technology_comment_settings', [])), true))>
                <span class="form-check-label">{{ $setting }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Community reactions</h5>
            <p class="text-muted mb-0 small">Technology-specific reactions readers can use.</p>
        </div>
        <span class="badge bg-primary text-white">Reactions</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::scienceTechnologyReactionOptions() as $reaction => $icon)
            <span class="badge bg-light text-dark border py-2 px-3"><i class="{{ $icon }} me-1" aria-hidden="true"></i>{{ $reaction }}</span>
        @endforeach
    </div>
    <small class="text-muted d-block mt-2">These reactions are enabled automatically for Science &amp; Technology posts.</small>
</div>

<div id="stParticipationWrap" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-success-subtle mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Engagement options</h5>
            <p class="text-muted mb-0 small">Allow readers to support, follow, or collaborate on your work.</p>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-3">
        <label class="form-check border rounded py-2 px-3 bg-white mb-0" for="stAllowSupport">
            <input type="checkbox" name="science_technology_allow_support" value="1" class="form-check-input science-technology-flow-field" id="stAllowSupport" @checked(old('science_technology_allow_support', data_get($post->meta, 'science_technology_allow_support', false)))>
            <span class="form-check-label">Allow support</span>
        </label>
        <label class="form-check border rounded py-2 px-3 bg-white mb-0" for="stAllowFollow">
            <input type="checkbox" name="science_technology_allow_follow" value="1" class="form-check-input science-technology-flow-field" id="stAllowFollow" @checked(old('science_technology_allow_follow', data_get($post->meta, 'science_technology_allow_follow', false)))>
            <span class="form-check-label">Allow follow</span>
        </label>
        <label class="form-check border rounded py-2 px-3 bg-white mb-0" for="stAllowCollaborate">
            <input type="checkbox" name="science_technology_allow_collaborate" value="1" class="form-check-input science-technology-flow-field" id="stAllowCollaborate" @checked(old('science_technology_allow_collaborate', data_get($post->meta, 'science_technology_allow_collaborate', false)))>
            <span class="form-check-label">Allow collaboration requests</span>
        </label>
    </div>
</div>
