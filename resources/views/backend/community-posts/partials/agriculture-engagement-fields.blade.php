@php
    $agriculturePollOptions = old(
        'agriculture_poll_options',
        data_get($post->meta, 'agriculture_poll_options', \App\Support\CommunityContentTaxonomy::agricultureDefaultPollOptions())
    );
    if (is_string($agriculturePollOptions)) {
        $agriculturePollOptions = array_values(array_filter(array_map('trim', preg_split('/\R/', $agriculturePollOptions))));
    }
@endphp

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-primary-subtle mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Farmer knowledge exchange</h5>
            <p class="text-muted mb-0 small">A flagship SoilnWater initiative — ask questions, share experiences, discuss market prices, and exchange water conservation practices.</p>
        </div>
        <span class="badge bg-primary text-white">SoilnWater unique</span>
    </div>
    <p class="small text-muted mb-3">Farmers can ask questions, share experiences, upload crop problems, get expert guidance, discuss market prices, and share water conservation practices.</p>
    <label class="form-check border rounded p-3 bg-white mb-3" for="agricultureEnableKnowledgeExchange">
        <input
            type="checkbox"
            name="agriculture_enable_knowledge_exchange"
            value="1"
            class="form-check-input agriculture-flow-field"
            id="agricultureEnableKnowledgeExchange"
            @checked(old('agriculture_enable_knowledge_exchange', data_get($post->meta, 'agriculture_enable_knowledge_exchange', true)))
        >
        <span class="form-check-label">Enable Farmer Knowledge Exchange on this post</span>
    </label>
    <label class="form-check border rounded p-3 bg-success-subtle mb-0" for="agricultureEnableCropDoctor">
        <input
            type="checkbox"
            name="agriculture_enable_crop_doctor"
            value="1"
            class="form-check-input agriculture-flow-field"
            id="agricultureEnableCropDoctor"
            @checked(old('agriculture_enable_crop_doctor', data_get($post->meta, 'agriculture_enable_crop_doctor', false)))
        >
        <span class="form-check-label"><strong>Crop Doctor</strong> — allow crop photo uploads and responses from agricultural experts and experienced farmers</span>
    </label>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Ask the community</h5>
            <p class="text-muted mb-0 small">Very important — pose a clear question for fellow farmers and experts.</p>
        </div>
        <span class="badge bg-danger text-white">Very important</span>
    </div>
    <label class="form-label" for="agricultureAskCommunity">Your question</label>
    <textarea
        name="agriculture_ask_community"
        id="agricultureAskCommunity"
        class="form-control agriculture-flow-field"
        rows="3"
        maxlength="500"
        placeholder="Example: Has anyone successfully controlled this pest? Which wheat variety performs best in Uttarakhand?"
    >{{ old('agriculture_ask_community', data_get($post->meta, 'agriculture_ask_community')) }}</textarea>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Target audience</h5>
            <p class="text-muted mb-0 small">Who should read and respond to this agriculture post?</p>
        </div>
        <span class="badge bg-secondary text-white">Audience</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::agricultureTargetAudiences() as $audience)
            <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                <input
                    type="checkbox"
                    name="agriculture_target_audiences[]"
                    value="{{ $audience }}"
                    class="form-check-input agriculture-flow-field"
                    @checked(in_array($audience, (array) old('agriculture_target_audiences', data_get($post->meta, 'agriculture_target_audiences', [])), true))
                >
                <span class="form-check-label">{{ $audience }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3" id="agriculturePollWrap">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Poll section</h5>
            <p class="text-muted mb-0 small">Example: Which irrigation method do you use?</p>
        </div>
        <span class="badge bg-warning text-dark">Recommended</span>
    </div>
    <label class="form-check border rounded p-3 bg-light mb-3" for="agricultureAllowPoll">
        <input type="checkbox" name="allow_poll" value="1" class="form-check-input agriculture-flow-field" id="agricultureAllowPoll" @checked(old('allow_poll', $post->allow_poll ?? false))>
        <span class="form-check-label">Enable poll on this Agriculture post</span>
    </label>
    <div id="agriculturePollFields">
        <label class="form-label" for="agriculturePollQuestion">Poll question</label>
        <input
            type="text"
            name="agriculture_poll_question"
            id="agriculturePollQuestion"
            class="form-control agriculture-flow-field mb-3"
            maxlength="255"
            value="{{ old('agriculture_poll_question', data_get($post->meta, 'agriculture_poll_question')) }}"
            placeholder="Example: Which irrigation method do you use?"
        >
        <label class="form-label" for="agriculturePollOptions">Poll options</label>
        <textarea
            name="agriculture_poll_options"
            id="agriculturePollOptions"
            class="form-control agriculture-flow-field"
            rows="3"
            placeholder="One option per line"
        >{{ old('agriculture_poll_options', is_array($agriculturePollOptions) ? implode("\n", $agriculturePollOptions) : $agriculturePollOptions) }}</textarea>
        <small class="text-muted d-block mt-2">Example: Drip, Sprinkler, Flood, Rainfed.</small>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Community reactions</h5>
            <p class="text-muted mb-0 small">Agriculture-specific reactions readers can use on the public page.</p>
        </div>
        <span class="badge bg-success text-white">Enabled</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::agricultureReactionOptions() as $reaction => $icon)
            <span class="badge bg-light text-dark border">
                <i class="{{ $icon }} me-1" aria-hidden="true"></i>{{ $reaction }}
            </span>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3" id="agricultureParticipationWrap">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Comments settings</h5>
            <p class="text-muted mb-0 small">Choose what logged-in readers can submit on the public page.</p>
        </div>
        <span class="badge bg-primary text-white">Engagement</span>
    </div>
    <div class="community-flow-stack d-flex flex-column gap-2">
        <label class="form-check border rounded p-3 bg-white mb-0" for="agricultureAllowComments">
            <input type="checkbox" name="allow_comments" value="1" class="form-check-input agriculture-flow-field" id="agricultureAllowComments" @checked(old('allow_comments', $post->allow_comments ?? true))>
            <span class="form-check-label">Comments</span>
        </label>
        <label class="form-check border rounded p-3 bg-white mb-0" for="agricultureAllowQuestions">
            <input type="checkbox" name="allow_questions" value="1" class="form-check-input agriculture-flow-field" id="agricultureAllowQuestions" @checked(old('allow_questions', $post->allow_questions ?? true))>
            <span class="form-check-label">Questions</span>
        </label>
        <label class="form-check border rounded p-3 bg-white mb-0" for="agricultureAllowAdvice">
            <input type="checkbox" name="allow_suggestions" value="1" class="form-check-input agriculture-flow-field" id="agricultureAllowAdvice" @checked(old('allow_suggestions', $post->allow_suggestions ?? true))>
            <span class="form-check-label">Advice</span>
        </label>
        <label class="form-check border rounded p-3 bg-white mb-0" for="agricultureAllowExperienceSharing">
            <input type="checkbox" name="allow_feedback" value="1" class="form-check-input agriculture-flow-field" id="agricultureAllowExperienceSharing" @checked(old('allow_feedback', $post->allow_feedback ?? true))>
            <span class="form-check-label">Experience sharing</span>
        </label>
        <label class="form-check border rounded p-3 bg-white mb-0" for="agricultureAllowSharing">
            <input type="checkbox" name="allow_sharing" value="1" class="form-check-input agriculture-flow-field" id="agricultureAllowSharing" @checked(old('allow_sharing', $post->allow_sharing ?? true))>
            <span class="form-check-label">Sharing</span>
        </label>
    </div>
</div>
