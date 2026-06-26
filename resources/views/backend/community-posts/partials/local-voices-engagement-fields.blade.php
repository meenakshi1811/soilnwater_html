@php
    $localVoicePollOptions = old(
        'local_voice_poll_options',
        data_get($post->meta, 'local_voice_poll_options', \App\Support\CommunityContentTaxonomy::localVoiceDefaultPollOptions())
    );
    if (is_string($localVoicePollOptions)) {
        $localVoicePollOptions = array_values(array_filter(array_map('trim', preg_split('/\R/', $localVoicePollOptions))));
    }
@endphp

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3" id="localVoicePollWrap">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Poll section</h5>
            <p class="text-muted mb-0 small">Highly recommended — polls dramatically increase engagement.</p>
        </div>
        <span class="badge bg-warning text-dark">Highly recommended</span>
    </div>
    <label class="form-check border rounded p-3 bg-light mb-3" for="localVoiceAllowPoll">
        <input type="checkbox" name="allow_poll" value="1" class="form-check-input local-voices-flow-field" id="localVoiceAllowPoll" @checked(old('allow_poll', $post->allow_poll ?? false))>
        <span class="form-check-label">Enable poll on this Local Voice post</span>
    </label>
    <div id="localVoicePollFields">
        <label class="form-label" for="localVoicePollQuestion">Poll question</label>
        <input
            type="text"
            name="local_voice_poll_question"
            id="localVoicePollQuestion"
            class="form-control local-voices-flow-field mb-3"
            maxlength="255"
            value="{{ old('local_voice_poll_question', data_get($post->meta, 'local_voice_poll_question')) }}"
            placeholder="Example: Do you think our area needs better waste management?"
        >
        <label class="form-label" for="localVoicePollOptions">Poll options</label>
        <textarea
            name="local_voice_poll_options"
            id="localVoicePollOptions"
            class="form-control local-voices-flow-field"
            rows="3"
            placeholder="One option per line"
        >{{ old('local_voice_poll_options', is_array($localVoicePollOptions) ? implode("\n", $localVoicePollOptions) : $localVoicePollOptions) }}</textarea>
        <small class="text-muted d-block mt-2">Example: Yes, No, Not Sure.</small>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-success-subtle mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Community support feature</h5>
            <p class="text-muted mb-0 small">Unique SoilnWater feature — readers can click “I Support This”.</p>
        </div>
        <span class="badge bg-success text-white">Recommended</span>
    </div>
    <label class="form-check border rounded p-3 bg-white mb-3" for="localVoiceAllowSupport">
        <input
            type="checkbox"
            name="local_voice_allow_support"
            value="1"
            class="form-check-input local-voices-flow-field"
            id="localVoiceAllowSupport"
            @checked(old('local_voice_allow_support', data_get($post->meta, 'local_voice_allow_support', true)))
        >
        <span class="form-check-label">Enable “I Support This” button</span>
        <small class="text-muted d-block mt-1">Supporter count is shown publicly, e.g. Supporters: 1,248.</small>
    </label>
    <label class="form-check border rounded p-3 bg-white mb-0" for="localVoiceAllowFollow">
        <input
            type="checkbox"
            name="local_voice_allow_follow"
            value="1"
            class="form-check-input local-voices-flow-field"
            id="localVoiceAllowFollow"
            @checked(old('local_voice_allow_follow', data_get($post->meta, 'local_voice_allow_follow', true)))
        >
        <span class="form-check-label">Enable “Follow this issue”</span>
        <small class="text-muted d-block mt-1">Users can subscribe to updates and receive notifications.</small>
    </label>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3" id="localVoiceParticipationWrap">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Comments settings</h5>
            <p class="text-muted mb-0 small">Choose what logged-in readers can submit on the public page.</p>
        </div>
        <span class="badge bg-primary text-white">Engagement</span>
    </div>
    <div class="community-flow-stack d-flex flex-column gap-2">
        <label class="form-check border rounded p-3 bg-white mb-0" for="localVoiceAllowComments">
            <input type="checkbox" name="allow_comments" value="1" class="form-check-input local-voices-flow-field" id="localVoiceAllowComments" @checked(old('allow_comments', $post->allow_comments ?? true))>
            <span class="form-check-label">Comments</span>
        </label>
        <label class="form-check border rounded p-3 bg-white mb-0" for="localVoiceAllowSuggestions">
            <input type="checkbox" name="allow_suggestions" value="1" class="form-check-input local-voices-flow-field" id="localVoiceAllowSuggestions" @checked(old('allow_suggestions', $post->allow_suggestions ?? true))>
            <span class="form-check-label">Suggestions</span>
        </label>
        <label class="form-check border rounded p-3 bg-white mb-0" for="localVoiceAllowFeedback">
            <input type="checkbox" name="allow_feedback" value="1" class="form-check-input local-voices-flow-field" id="localVoiceAllowFeedback" @checked(old('allow_feedback', $post->allow_feedback ?? true))>
            <span class="form-check-label">Additional evidence</span>
        </label>
        <label class="form-check border rounded p-3 bg-white mb-0" for="localVoiceAllowQuestions">
            <input type="checkbox" name="allow_questions" value="1" class="form-check-input local-voices-flow-field" id="localVoiceAllowQuestions" @checked(old('allow_questions', $post->allow_questions ?? true))>
            <span class="form-check-label">Questions</span>
        </label>
        <label class="form-check border rounded p-3 bg-white mb-0" for="localVoiceAllowSharing">
            <input type="checkbox" name="allow_sharing" value="1" class="form-check-input local-voices-flow-field" id="localVoiceAllowSharing" @checked(old('allow_sharing', $post->allow_sharing ?? true))>
            <span class="form-check-label">Sharing</span>
        </label>
    </div>
</div>
