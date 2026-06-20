@php
    $selectedActionItems = old('awareness_action_items', data_get($post->meta, 'awareness_action_items', []));
    $selectedImpactCategories = old('awareness_social_impact_categories', data_get($post->meta, 'awareness_social_impact_categories', []));
    $pledgeOptions = old(
        'awareness_pledge_options',
        data_get($post->meta, 'awareness_pledge_options', \App\Support\CommunityContentTaxonomy::awarenessPledgeExamples())
    );
    if (is_string($pledgeOptions)) {
        $pledgeOptions = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $pledgeOptions))));
    }
@endphp

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-primary-subtle mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Call to action</h5>
            <p class="text-muted mb-0 small">The most important section — tell readers exactly what to do.</p>
        </div>
        <span class="badge bg-danger text-white">Most important</span>
    </div>
    <label class="form-label" for="awarenessCallToAction">Primary call to action <span class="text-danger">*</span></label>
    <textarea
        name="awareness_call_to_action"
        id="awarenessCallToAction"
        class="form-control awareness-required"
        rows="3"
        maxlength="1000"
        placeholder="What is the single most important action you want readers to take?"
        required
    >{{ old('awareness_call_to_action', data_get($post->meta, 'awareness_call_to_action')) }}</textarea>
    <label class="form-label d-block mt-3">Suggested action items</label>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::awarenessCallToActionExamples() as $actionItem)
            <div class="col-md-4 col-sm-6">
                <label class="form-check border rounded py-2 px-3 bg-white h-100 mb-0">
                    <input
                        type="checkbox"
                        name="awareness_action_items[]"
                        value="{{ $actionItem }}"
                        class="form-check-input"
                        @checked(in_array($actionItem, (array) $selectedActionItems, true))
                    >
                    <span class="form-check-label">{{ $actionItem }}</span>
                </label>
            </div>
        @endforeach
    </div>
    <small class="text-muted d-block mt-2">Examples: Save Water Daily, Plant One Tree, Use Helmets, Avoid Plastic, Get Health Checkups.</small>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Volunteer / participation</h5>
            <p class="text-muted mb-0 small">Let people join your awareness campaign.</p>
        </div>
    </div>
    <div class="community-flow-stack d-flex flex-column gap-2">
        <label class="form-check border rounded p-3 bg-white mb-0" for="awarenessAllowCampaignJoin">
            <input
                type="checkbox"
                name="awareness_allow_campaign_join"
                value="1"
                class="form-check-input"
                id="awarenessAllowCampaignJoin"
                @checked(old('awareness_allow_campaign_join', data_get($post->meta, 'awareness_allow_campaign_join', false)))
            >
            <span class="form-check-label">Allow people to join this campaign</span>
            <small class="text-muted d-block mt-1">When enabled, readers can submit name, mobile, email, and city to volunteer for this campaign.</small>
        </label>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Event details</h5>
            <p class="text-muted mb-0 small">If this awareness post is linked to an event.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div class="community-flow-stack d-flex flex-column gap-2 mb-3">
        <label class="form-check border rounded p-3 bg-white mb-0" for="awarenessHasEvent">
            <input
                type="checkbox"
                name="awareness_has_event"
                value="1"
                class="form-check-input"
                id="awarenessHasEvent"
                @checked(old('awareness_has_event', data_get($post->meta, 'awareness_has_event', false)))
            >
            <span class="form-check-label">This awareness post is linked to an event</span>
        </label>
    </div>
    <div id="awarenessEventFields">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="awarenessEventType">Event type</label>
                <select name="awareness_event_type" id="awarenessEventType" class="form-select">
                    <option value="">Select event type</option>
                    @foreach(\App\Support\CommunityContentTaxonomy::awarenessEventTypes() as $eventType)
                        <option value="{{ $eventType }}" @selected(old('awareness_event_type', data_get($post->meta, 'awareness_event_type')) === $eventType)>{{ $eventType }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="awarenessEventDate">Date</label>
                <input type="date" name="awareness_event_date" id="awarenessEventDate" class="form-control" value="{{ old('awareness_event_date', data_get($post->meta, 'awareness_event_date')) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="awarenessEventVenue">Venue</label>
                <input type="text" name="awareness_event_venue" id="awarenessEventVenue" class="form-control" maxlength="160" value="{{ old('awareness_event_venue', data_get($post->meta, 'awareness_event_venue')) }}" placeholder="e.g. Community Hall, Jaipur">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="awarenessEventTime">Time</label>
                <input type="text" name="awareness_event_time" id="awarenessEventTime" class="form-control" maxlength="40" value="{{ old('awareness_event_time', data_get($post->meta, 'awareness_event_time')) }}" placeholder="e.g. 10:00 AM">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="awarenessEventOrganizer">Organizer</label>
                <input type="text" name="awareness_event_organizer" id="awarenessEventOrganizer" class="form-control" maxlength="160" value="{{ old('awareness_event_organizer', data_get($post->meta, 'awareness_event_organizer')) }}">
            </div>
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Social impact category</h5>
            <p class="text-muted mb-0 small">Optional tags describing the social impact area.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::awarenessSocialImpactCategories() as $category)
            <div class="col-md-4 col-sm-6">
                <label class="form-check border rounded py-2 px-3 bg-light h-100 mb-0">
                    <input
                        type="checkbox"
                        name="awareness_social_impact_categories[]"
                        value="{{ $category }}"
                        class="form-check-input"
                        @checked(in_array($category, (array) $selectedImpactCategories, true))
                    >
                    <span class="form-check-label">{{ $category }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3" id="awarenessParticipationWrap">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Community interaction</h5>
            <p class="text-muted mb-0 small">Choose how readers can engage with this awareness post.</p>
        </div>
        <span class="badge bg-primary text-white">Engagement</span>
    </div>
    <div class="community-flow-stack d-flex flex-column gap-2">
        <label class="form-check border rounded p-3 bg-white mb-0" for="awarenessAllowComments">
            <input type="checkbox" name="allow_comments" value="1" class="form-check-input" id="awarenessAllowComments" @checked(old('allow_comments', $post->allow_comments ?? true))>
            <span class="form-check-label">Comments</span>
        </label>
        <label class="form-check border rounded p-3 bg-white mb-0" for="awarenessAllowQuestions">
            <input type="checkbox" name="allow_questions" value="1" class="form-check-input" id="awarenessAllowQuestions" @checked(old('allow_questions', $post->allow_questions ?? true))>
            <span class="form-check-label">Questions</span>
        </label>
        <label class="form-check border rounded p-3 bg-white mb-0" for="awarenessAllowSuggestions">
            <input type="checkbox" name="allow_suggestions" value="1" class="form-check-input" id="awarenessAllowSuggestions" @checked(old('allow_suggestions', $post->allow_suggestions ?? false))>
            <span class="form-check-label">Suggestions</span>
        </label>
        <label class="form-check border rounded p-3 bg-white mb-0" for="awarenessAllowSharing">
            <input type="checkbox" name="allow_sharing" value="1" class="form-check-input" id="awarenessAllowSharing" @checked(old('allow_sharing', $post->allow_sharing ?? true))>
            <span class="form-check-label">Sharing</span>
        </label>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Community support button</h5>
            <p class="text-muted mb-0 small">Unique SoilnWater feature — readers can click “I Support This Cause”.</p>
        </div>
        <span class="badge bg-success text-white">Recommended</span>
    </div>
    <div class="community-flow-stack d-flex flex-column gap-2">
        <label class="form-check border rounded p-3 bg-white mb-0" for="awarenessAllowCauseSupport">
            <input
                type="checkbox"
                name="awareness_allow_cause_support"
                value="1"
                class="form-check-input"
                id="awarenessAllowCauseSupport"
                @checked(old('awareness_allow_cause_support', data_get($post->meta, 'awareness_allow_cause_support', true)))
            >
            <span class="form-check-label">Enable “I Support This Cause” button</span>
            <small class="text-muted d-block mt-1">Supporter count is shown publicly, e.g. 1,250 Supporters.</small>
        </label>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Pledge system</h5>
            <p class="text-muted mb-0 small">Let users digitally pledge support for your cause.</p>
        </div>
        <span class="badge bg-warning text-dark">Powerful</span>
    </div>
    <div class="community-flow-stack d-flex flex-column gap-2 mb-3">
        <label class="form-check border rounded p-3 bg-white mb-0" for="awarenessAllowPledges">
            <input
                type="checkbox"
                name="awareness_allow_pledges"
                value="1"
                class="form-check-input"
                id="awarenessAllowPledges"
                @checked(old('awareness_allow_pledges', data_get($post->meta, 'awareness_allow_pledges', false)))
            >
            <span class="form-check-label">Enable digital pledges</span>
        </label>
    </div>
    <label class="form-label" for="awarenessPledgeOptions">Pledge options</label>
    <textarea
        name="awareness_pledge_options"
        id="awarenessPledgeOptions"
        class="form-control"
        rows="4"
        placeholder="One pledge per line"
    >{{ old('awareness_pledge_options', is_array($pledgeOptions) ? implode("\n", $pledgeOptions) : $pledgeOptions) }}</textarea>
    <small class="text-muted d-block mt-2">Examples: I Pledge to Save Water, I Pledge to Plant Trees, I Pledge to Follow Road Safety Rules.</small>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3" id="awarenessPollWrap">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Poll section</h5>
            <p class="text-muted mb-0 small">Optional reader poll with Yes / No / Planning To answers.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div class="community-flow-stack d-flex flex-column gap-2 mb-3">
        <label class="form-check border rounded p-3 bg-white mb-0" for="awarenessAllowPoll">
            <input type="checkbox" name="allow_poll" value="1" class="form-check-input" id="awarenessAllowPoll" @checked(old('allow_poll', $post->allow_poll ?? false))>
            <span class="form-check-label">Enable poll on this awareness post</span>
            <small class="text-muted d-block mt-1">Readers choose: Yes, No, or Planning To.</small>
        </label>
    </div>
    <div id="awarenessPollQuestionWrap">
        <label class="form-label" for="awarenessPollQuestion">Poll question</label>
        <input
            type="text"
            name="awareness_poll_question"
            id="awarenessPollQuestion"
            class="form-control"
            maxlength="255"
            value="{{ old('awareness_poll_question', data_get($post->meta, 'awareness_poll_question')) }}"
            placeholder="e.g. Do you practice rainwater harvesting?"
        >
        <small class="text-muted d-block mt-2">Readers choose: Yes, No, or Planning To.</small>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Campaign impact metrics</h5>
            <p class="text-muted mb-0 small">Optional numbers displayed on the public page.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label" for="awarenessImpactTreesPlanted">Trees planted</label>
            <input type="number" name="awareness_impact_trees_planted" id="awarenessImpactTreesPlanted" class="form-control" min="0" value="{{ old('awareness_impact_trees_planted', data_get($post->meta, 'awareness_impact_trees_planted')) }}" placeholder="e.g. 500">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="awarenessImpactVolunteersJoined">Volunteers joined</label>
            <input type="number" name="awareness_impact_volunteers_joined" id="awarenessImpactVolunteersJoined" class="form-control" min="0" value="{{ old('awareness_impact_volunteers_joined', data_get($post->meta, 'awareness_impact_volunteers_joined')) }}" placeholder="e.g. 120">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="awarenessImpactPeopleReached">People reached</label>
            <input type="number" name="awareness_impact_people_reached" id="awarenessImpactPeopleReached" class="form-control" min="0" value="{{ old('awareness_impact_people_reached', data_get($post->meta, 'awareness_impact_people_reached')) }}" placeholder="e.g. 10000">
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Author information</h5>
            <p class="text-muted mb-0 small">Shown on the public page from your profile and organization details.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Auto display</span>
    </div>
    <ul class="list-unstyled small text-muted mb-0">
        <li><i class="fa-solid fa-user me-1 text-success" aria-hidden="true"></i>Author name from your account profile</li>
        <li><i class="fa-solid fa-building me-1 text-success" aria-hidden="true"></i>Organization from the organization details section above</li>
        <li><i class="fa-solid fa-id-badge me-1 text-success" aria-hidden="true"></i>Profile link shown when publishing with your public profile</li>
    </ul>
</div>
