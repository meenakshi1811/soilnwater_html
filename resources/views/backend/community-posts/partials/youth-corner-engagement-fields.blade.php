@php
    $youthCornerPollOptions = old(
        'youth_corner_poll_options',
        data_get($post->meta, 'youth_corner_poll_options', \App\Support\CommunityContentTaxonomy::youthCornerDefaultPollOptions())
    );
    if (is_string($youthCornerPollOptions)) {
        $youthCornerPollOptions = array_values(array_filter(array_map('trim', preg_split('/\R/', $youthCornerPollOptions))));
    }
    $selectedMentorshipRequests = old('youth_corner_mentorship_requests', data_get($post->meta, 'youth_corner_mentorship_requests', []));
@endphp

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3" id="youthCornerPollWrap">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Poll section</h5>
            <p class="text-muted mb-0 small">Highly engaging — ask fellow youth a question with multiple choice answers.</p>
        </div>
        <span class="badge bg-warning text-dark">Highly engaging</span>
    </div>
    <div class="community-flow-stack d-flex flex-column gap-2 mb-3">
        <label class="form-check border rounded p-3 bg-white mb-0" for="youthCornerAllowPoll">
            <input type="checkbox" name="allow_poll" value="1" class="form-check-input youth-corner-flow-field" id="youthCornerAllowPoll" @checked(old('allow_poll', $post->allow_poll ?? false))>
            <span class="form-check-label">Enable poll on this Youth Corner post</span>
            <small class="text-muted d-block mt-1">Example: What topic matters most to you right now?</small>
        </label>
    </div>
    <div id="youthCornerPollFields">
        <label class="form-label" for="youthCornerPollQuestion">Poll question</label>
        <input
            type="text"
            name="youth_corner_poll_question"
            id="youthCornerPollQuestion"
            class="form-control youth-corner-flow-field mb-3"
            maxlength="255"
            value="{{ old('youth_corner_poll_question', data_get($post->meta, 'youth_corner_poll_question')) }}"
            placeholder="What topic matters most to you right now?"
        >
        <label class="form-label" for="youthCornerPollOptions">Poll options</label>
        <textarea
            name="youth_corner_poll_options"
            id="youthCornerPollOptions"
            class="form-control youth-corner-flow-field"
            rows="4"
            placeholder="One option per line"
        >{{ old('youth_corner_poll_options', is_array($youthCornerPollOptions) ? implode("\n", $youthCornerPollOptions) : $youthCornerPollOptions) }}</textarea>
        <small class="text-muted d-block mt-2">Example options: {{ implode(', ', \App\Support\CommunityContentTaxonomy::youthCornerDefaultPollOptions()) }}.</small>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-primary-subtle mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Ask the community</h5>
            <p class="text-muted mb-0 small">Invite fellow youth to share experiences and advice in comments.</p>
        </div>
        <span class="badge bg-primary text-white">Very powerful</span>
    </div>
    <label class="form-label" for="youthCornerAskCommunity">Community question</label>
    <textarea
        name="youth_corner_ask_community"
        id="youthCornerAskCommunity"
        class="form-control youth-corner-flow-field"
        rows="3"
        maxlength="500"
        placeholder="How did you land your first internship?"
    >{{ old('youth_corner_ask_community', data_get($post->meta, 'youth_corner_ask_community')) }}</textarea>
    <small class="text-muted d-block mt-2">Encourages discussion when comments are enabled.</small>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Mentorship request</h5>
            <p class="text-muted mb-0 small">Let the community know what kind of guidance you are seeking.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::youthCornerMentorshipRequests() as $mentorshipRequest)
            <div class="col-md-6">
                <label class="form-check border rounded py-2 px-3 bg-light h-100 mb-0">
                    <input
                        type="checkbox"
                        name="youth_corner_mentorship_requests[]"
                        value="{{ $mentorshipRequest }}"
                        class="form-check-input youth-corner-flow-field"
                        @checked(in_array($mentorshipRequest, (array) $selectedMentorshipRequests, true))
                    >
                    <span class="form-check-label">{{ $mentorshipRequest }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3" id="communityYouthCornerTagsSlotWrap">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Tags</h5>
            <p class="text-muted mb-0 small">Add up to 10 tags to help readers discover this content.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Maximum 10 tags</span>
    </div>
    <div id="communityYouthCornerTagsSlot"></div>
    <small class="text-muted d-block mt-2">
        Examples: {{ implode(', ', \App\Support\CommunityContentTaxonomy::youthCornerTagExamples()) }}.
    </small>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3" id="youthCornerParticipationWrap">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Comments settings</h5>
            <p class="text-muted mb-0 small">Choose how readers can engage with this post.</p>
        </div>
        <span class="badge bg-primary text-white">Engagement</span>
    </div>
    <p class="small text-muted mb-2">Allow:</p>
    <div class="community-flow-stack d-flex flex-column gap-2">
        <label class="form-check border rounded p-3 bg-light mb-0" for="youthCornerAllowComments">
            <input type="checkbox" name="allow_comments" value="1" class="form-check-input youth-corner-flow-field" id="youthCornerAllowComments" @checked(old('allow_comments', $post->allow_comments ?? true))>
            <span class="form-check-label">Comments</span>
        </label>
        <label class="form-check border rounded p-3 bg-light mb-0" for="youthCornerAllowQuestions">
            <input type="checkbox" name="allow_questions" value="1" class="form-check-input youth-corner-flow-field" id="youthCornerAllowQuestions" @checked(old('allow_questions', $post->allow_questions ?? true))>
            <span class="form-check-label">Questions</span>
        </label>
        <label class="form-check border rounded p-3 bg-light mb-0" for="youthCornerAllowFeedback">
            <input type="checkbox" name="allow_feedback" value="1" class="form-check-input youth-corner-flow-field" id="youthCornerAllowFeedback" @checked(old('allow_feedback', $post->allow_feedback ?? false))>
            <span class="form-check-label">Peer Discussion</span>
        </label>
        <label class="form-check border rounded p-3 bg-light mb-0" for="youthCornerAllowSharing">
            <input type="checkbox" name="allow_sharing" value="1" class="form-check-input youth-corner-flow-field" id="youthCornerAllowSharing" @checked(old('allow_sharing', $post->allow_sharing ?? true))>
            <span class="form-check-label">Sharing</span>
        </label>
    </div>
</div>

<div class="news-flow-card story-flow-card story-flow-card--location border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Location</h5>
            <p class="text-muted mb-0 small">Optional — useful for local opportunities, events, and regional youth initiatives.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div id="communityYouthCornerLocationSlot"></div>
</div>
