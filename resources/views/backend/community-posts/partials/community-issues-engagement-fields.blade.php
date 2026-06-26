@php
    $communityIssuePollOptions = old(
        'community_issue_poll_options',
        data_get($post->meta, 'community_issue_poll_options', \App\Support\CommunityContentTaxonomy::communityIssueDefaultPollOptions())
    );
    if (is_string($communityIssuePollOptions)) {
        $communityIssuePollOptions = array_values(array_filter(array_map('trim', preg_split('/\R/', $communityIssuePollOptions))));
    }
@endphp

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3" id="communityIssuePollWrap">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Poll section</h5>
            <p class="text-muted mb-0 small">Example: Do you think this issue requires urgent action?</p>
        </div>
        <span class="badge bg-warning text-dark">Recommended</span>
    </div>
    <label class="form-check border rounded p-3 bg-light mb-3" for="communityIssueAllowPoll">
        <input type="checkbox" name="allow_poll" value="1" class="form-check-input community-issues-flow-field" id="communityIssueAllowPoll" @checked(old('allow_poll', $post->allow_poll ?? false))>
        <span class="form-check-label">Enable poll on this Community Issue post</span>
    </label>
    <div id="communityIssuePollFields">
        <label class="form-label" for="communityIssuePollQuestion">Poll question</label>
        <input
            type="text"
            name="community_issue_poll_question"
            id="communityIssuePollQuestion"
            class="form-control community-issues-flow-field mb-3"
            maxlength="255"
            value="{{ old('community_issue_poll_question', data_get($post->meta, 'community_issue_poll_question')) }}"
            placeholder="Example: Do you think this issue requires urgent action?"
        >
        <label class="form-label" for="communityIssuePollOptions">Poll options</label>
        <textarea
            name="community_issue_poll_options"
            id="communityIssuePollOptions"
            class="form-control community-issues-flow-field"
            rows="3"
            placeholder="One option per line"
        >{{ old('community_issue_poll_options', is_array($communityIssuePollOptions) ? implode("\n", $communityIssuePollOptions) : $communityIssuePollOptions) }}</textarea>
        <small class="text-muted d-block mt-2">Example: Yes, No, Needs Further Review.</small>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-success-subtle mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Community support campaign</h5>
            <p class="text-muted mb-0 small">Unique SoilnWater feature — readers can click “I Support This Issue”.</p>
        </div>
        <span class="badge bg-success text-white">Petition</span>
    </div>
    <label class="form-check border rounded p-3 bg-white mb-3" for="communityIssueAllowCampaign">
        <input
            type="checkbox"
            name="community_issue_allow_campaign"
            value="1"
            class="form-check-input community-issues-flow-field"
            id="communityIssueAllowCampaign"
            @checked(old('community_issue_allow_campaign', data_get($post->meta, 'community_issue_allow_campaign', true)))
        >
        <span class="form-check-label">Create community support campaign</span>
        <small class="text-muted d-block mt-1">Supporter count is shown publicly, e.g. 1,250 Community Supporters.</small>
    </label>
    <label class="form-check border rounded p-3 bg-white mb-3" for="communityIssueAllowSupport">
        <input
            type="checkbox"
            name="community_issue_allow_support"
            value="1"
            class="form-check-input community-issues-flow-field"
            id="communityIssueAllowSupport"
            @checked(old('community_issue_allow_support', data_get($post->meta, 'community_issue_allow_support', true)))
        >
        <span class="form-check-label">Enable “I Support This Issue” button</span>
    </label>
    <label class="form-check border rounded p-3 bg-white mb-3" for="communityIssueAllowFollow">
        <input
            type="checkbox"
            name="community_issue_allow_follow"
            value="1"
            class="form-check-input community-issues-flow-field"
            id="communityIssueAllowFollow"
            @checked(old('community_issue_allow_follow', data_get($post->meta, 'community_issue_allow_follow', true)))
        >
        <span class="form-check-label">Enable “Follow issue”</span>
        <small class="text-muted d-block mt-1">Users can subscribe to updates and receive notifications.</small>
    </label>
    <label class="form-check border rounded p-3 bg-white mb-3" for="communityIssueAllowVerification">
        <input
            type="checkbox"
            name="community_issue_allow_verification"
            value="1"
            class="form-check-input community-issues-flow-field"
            id="communityIssueAllowVerification"
            @checked(old('community_issue_allow_verification', data_get($post->meta, 'community_issue_allow_verification', true)))
        >
        <span class="form-check-label">Allow community verification</span>
        <small class="text-muted d-block mt-1">Residents can confirm the issue, add photos, evidence, and comments.</small>
    </label>
    <label class="form-label" for="communityIssueEscalationThreshold">Escalation threshold (supporters)</label>
    <input
        type="number"
        name="community_issue_escalation_threshold"
        id="communityIssueEscalationThreshold"
        class="form-control community-issues-flow-field"
        min="10"
        max="10000"
        value="{{ old('community_issue_escalation_threshold', data_get($post->meta, 'community_issue_escalation_threshold', \App\Support\CommunityContentTaxonomy::communityIssueDefaultEscalationThreshold())) }}"
    >
    <small class="text-muted d-block mt-2">When support exceeds this number, the issue is flagged as high priority (example: 100+ supporters).</small>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3" id="communityIssueParticipationWrap">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Community actions</h5>
            <p class="text-muted mb-0 small">Allow support, comments, evidence, sharing, and volunteer responses.</p>
        </div>
        <span class="badge bg-primary text-white">Engagement</span>
    </div>
    <div class="community-flow-stack d-flex flex-column gap-2">
        <label class="form-check border rounded p-3 bg-white mb-0" for="communityIssueAllowComments">
            <input type="checkbox" name="allow_comments" value="1" class="form-check-input community-issues-flow-field" id="communityIssueAllowComments" @checked(old('allow_comments', $post->allow_comments ?? true))>
            <span class="form-check-label">Comment</span>
        </label>
        <label class="form-check border rounded p-3 bg-white mb-0" for="communityIssueAllowSuggestions">
            <input type="checkbox" name="allow_suggestions" value="1" class="form-check-input community-issues-flow-field" id="communityIssueAllowSuggestions" @checked(old('allow_suggestions', $post->allow_suggestions ?? true))>
            <span class="form-check-label">Suggestions</span>
        </label>
        <label class="form-check border rounded p-3 bg-white mb-0" for="communityIssueAllowFeedback">
            <input type="checkbox" name="allow_feedback" value="1" class="form-check-input community-issues-flow-field" id="communityIssueAllowFeedback" @checked(old('allow_feedback', $post->allow_feedback ?? true))>
            <span class="form-check-label">Add evidence</span>
        </label>
        <label class="form-check border rounded p-3 bg-white mb-0" for="communityIssueAllowQuestions">
            <input type="checkbox" name="allow_questions" value="1" class="form-check-input community-issues-flow-field" id="communityIssueAllowQuestions" @checked(old('allow_questions', $post->allow_questions ?? true))>
            <span class="form-check-label">Questions</span>
        </label>
        <label class="form-check border rounded p-3 bg-white mb-0" for="communityIssueAllowSharing">
            <input type="checkbox" name="allow_sharing" value="1" class="form-check-input community-issues-flow-field" id="communityIssueAllowSharing" @checked(old('allow_sharing', $post->allow_sharing ?? true))>
            <span class="form-check-label">Share</span>
        </label>
    </div>
</div>
