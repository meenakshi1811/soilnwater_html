@php
    $environmentPollOptions = old(
        'environment_poll_options',
        data_get($post->meta, 'environment_poll_options', \App\Support\CommunityContentTaxonomy::environmentDefaultPollOptions())
    );
    if (is_string($environmentPollOptions)) {
        $environmentPollOptions = array_values(array_filter(array_map('trim', preg_split('/\R/', $environmentPollOptions))));
    }
@endphp

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-primary-subtle mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Environmental impact calculator</h5>
            <p class="text-muted mb-0 small">A unique SoilnWater feature — optionally record measurable outcomes from your initiative.</p>
        </div>
        <span class="badge bg-primary text-white">SoilnWater unique</span>
    </div>
    <label class="form-check border rounded p-3 bg-white mb-3" for="environmentEnableImpactCalculator">
        <input type="checkbox" name="environment_enable_impact_calculator" value="1" class="form-check-input environment-flow-field" id="environmentEnableImpactCalculator" @checked(old('environment_enable_impact_calculator', data_get($post->meta, 'environment_enable_impact_calculator', false)))>
        <span class="form-check-label">Enable Environmental Impact Calculator on this post</span>
    </label>
    <div id="environmentImpactCalculatorFields">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label" for="environmentDataTreesPlanted">Trees planted</label>
                <input type="text" name="environment_data_trees_planted" id="environmentDataTreesPlanted" class="form-control environment-flow-field" maxlength="80" value="{{ old('environment_data_trees_planted', data_get($post->meta, 'environment_data_trees_planted')) }}" placeholder="e.g. 500">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="environmentDataAreaCovered">Area covered</label>
                <input type="text" name="environment_data_area_covered" id="environmentDataAreaCovered" class="form-control environment-flow-field" maxlength="80" value="{{ old('environment_data_area_covered', data_get($post->meta, 'environment_data_area_covered')) }}" placeholder="e.g. 2,500 sq. m.">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="environmentDataWaterSaved">Water saved</label>
                <input type="text" name="environment_data_water_saved" id="environmentDataWaterSaved" class="form-control environment-flow-field" maxlength="80" value="{{ old('environment_data_water_saved', data_get($post->meta, 'environment_data_water_saved')) }}" placeholder="e.g. 1,00,000 litres">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="environmentDataWasteCollected">Waste collected</label>
                <input type="text" name="environment_data_waste_collected" id="environmentDataWasteCollected" class="form-control environment-flow-field" maxlength="80" value="{{ old('environment_data_waste_collected', data_get($post->meta, 'environment_data_waste_collected')) }}" placeholder="e.g. 800 kg">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="environmentDataPeopleParticipated">People participated</label>
                <input type="text" name="environment_data_people_participated" id="environmentDataPeopleParticipated" class="form-control environment-flow-field" maxlength="80" value="{{ old('environment_data_people_participated', data_get($post->meta, 'environment_data_people_participated')) }}" placeholder="e.g. 120">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="environmentDataCarbonReduction">Carbon reduction (estimated)</label>
                <input type="text" name="environment_data_carbon_reduction" id="environmentDataCarbonReduction" class="form-control environment-flow-field" maxlength="80" value="{{ old('environment_data_carbon_reduction', data_get($post->meta, 'environment_data_carbon_reduction')) }}" placeholder="e.g. 2.5 tonnes CO₂">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="environmentDataSpeciesRecorded">Species recorded</label>
                <input type="text" name="environment_data_species_recorded" id="environmentDataSpeciesRecorded" class="form-control environment-flow-field" maxlength="80" value="{{ old('environment_data_species_recorded', data_get($post->meta, 'environment_data_species_recorded')) }}" placeholder="e.g. 42">
            </div>
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Environmental data</h5>
            <p class="text-muted mb-0 small">Optional summary metrics shown alongside your post content.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <p class="small text-muted mb-0">Use the impact calculator fields above, or add supporting detail in the rich text body.</p>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Community participation</h5>
            <p class="text-muted mb-0 small">Tell readers how they can get involved.</p>
        </div>
        <span class="badge bg-success text-white">Engagement</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::environmentParticipationRequests() as $request)
            <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                <input type="checkbox" name="environment_participation_requests[]" value="{{ $request }}" class="form-check-input environment-flow-field" @checked(in_array($request, (array) old('environment_participation_requests', data_get($post->meta, 'environment_participation_requests', [])), true))>
                <span class="form-check-label">{{ $request }}</span>
            </label>
        @endforeach
    </div>
</div>

<div id="environmentEventSection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Event details</h5>
            <p class="text-muted mb-0 small">For drives, campaigns, and community environmental events.</p>
        </div>
        <span class="badge bg-primary text-white">Event</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="environmentEventCampaignName">Campaign name</label>
            <input type="text" name="environment_event_campaign_name" id="environmentEventCampaignName" class="form-control environment-flow-field" maxlength="160" value="{{ old('environment_event_campaign_name', data_get($post->meta, 'environment_event_campaign_name')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="environmentEventOrganizer">Organizer</label>
            <input type="text" name="environment_event_organizer" id="environmentEventOrganizer" class="form-control environment-flow-field" maxlength="160" value="{{ old('environment_event_organizer', data_get($post->meta, 'environment_event_organizer')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="environmentEventVenue">Venue</label>
            <input type="text" name="environment_event_venue" id="environmentEventVenue" class="form-control environment-flow-field" maxlength="160" value="{{ old('environment_event_venue', data_get($post->meta, 'environment_event_venue')) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="environmentEventDate">Date</label>
            <input type="date" name="environment_event_date" id="environmentEventDate" class="form-control environment-flow-field" value="{{ old('environment_event_date', data_get($post->meta, 'environment_event_date')) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="environmentEventTime">Time</label>
            <input type="text" name="environment_event_time" id="environmentEventTime" class="form-control environment-flow-field" maxlength="40" value="{{ old('environment_event_time', data_get($post->meta, 'environment_event_time')) }}" placeholder="7:30 AM">
        </div>
        <div class="col-12">
            <label class="form-label" for="environmentEventRegistrationLink">Registration link</label>
            <input type="url" name="environment_event_registration_link" id="environmentEventRegistrationLink" class="form-control environment-flow-field" maxlength="255" value="{{ old('environment_event_registration_link', data_get($post->meta, 'environment_event_registration_link')) }}" placeholder="https://">
        </div>
    </div>
</div>

<div id="environmentSchemeSection" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3" style="display:none;">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Government scheme</h5>
            <p class="text-muted mb-0 small">Share scheme details to help communities access support.</p>
        </div>
        <span class="badge bg-secondary text-white">Optional</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="environmentSchemeName">Scheme name</label>
            <input type="text" name="environment_scheme_name" id="environmentSchemeName" class="form-control environment-flow-field" maxlength="160" value="{{ old('environment_scheme_name', data_get($post->meta, 'environment_scheme_name')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="environmentSchemeDepartment">Department</label>
            <input type="text" name="environment_scheme_department" id="environmentSchemeDepartment" class="form-control environment-flow-field" maxlength="160" value="{{ old('environment_scheme_department', data_get($post->meta, 'environment_scheme_department')) }}">
        </div>
        <div class="col-12">
            <label class="form-label" for="environmentSchemeEligibility">Eligibility</label>
            <textarea name="environment_scheme_eligibility" id="environmentSchemeEligibility" class="form-control environment-flow-field" rows="2" maxlength="2000">{{ old('environment_scheme_eligibility', data_get($post->meta, 'environment_scheme_eligibility')) }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label" for="environmentSchemeBenefits">Benefits</label>
            <textarea name="environment_scheme_benefits" id="environmentSchemeBenefits" class="form-control environment-flow-field" rows="2" maxlength="2000">{{ old('environment_scheme_benefits', data_get($post->meta, 'environment_scheme_benefits')) }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label" for="environmentSchemeOfficialLink">Official link</label>
            <input type="url" name="environment_scheme_official_link" id="environmentSchemeOfficialLink" class="form-control environment-flow-field" maxlength="255" value="{{ old('environment_scheme_official_link', data_get($post->meta, 'environment_scheme_official_link')) }}" placeholder="https://">
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Ask the community</h5>
            <p class="text-muted mb-0 small">Pose a question to fellow environmental champions.</p>
        </div>
        <span class="badge bg-danger text-white">Recommended</span>
    </div>
    <label class="form-label" for="environmentAskCommunity">Your question</label>
    <textarea name="environment_ask_community" id="environmentAskCommunity" class="form-control environment-flow-field" rows="3" maxlength="500" placeholder="Example: How can we reduce plastic waste in our locality? Which native tree species should we plant?">{{ old('environment_ask_community', data_get($post->meta, 'environment_ask_community')) }}</textarea>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3" id="environmentPollWrap">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Poll section</h5>
            <p class="text-muted mb-0 small">Example: Would you participate in a tree plantation drive?</p>
        </div>
        <span class="badge bg-warning text-dark">Optional</span>
    </div>
    <label class="form-check border rounded p-3 bg-light mb-3" for="environmentAllowPoll">
        <input type="checkbox" name="allow_poll" value="1" class="form-check-input environment-flow-field" id="environmentAllowPoll" @checked(old('allow_poll', $post->allow_poll ?? false))>
        <span class="form-check-label">Enable poll on this Environment post</span>
    </label>
    <div id="environmentPollFields">
        <label class="form-label" for="environmentPollQuestion">Poll question</label>
        <input type="text" name="environment_poll_question" id="environmentPollQuestion" class="form-control environment-flow-field mb-3" maxlength="255" value="{{ old('environment_poll_question', data_get($post->meta, 'environment_poll_question')) }}" placeholder="Would you participate in a tree plantation drive?">
        <label class="form-label" for="environmentPollOptions">Poll options</label>
        <textarea name="environment_poll_options" id="environmentPollOptions" class="form-control environment-flow-field" rows="3" placeholder="One option per line">{{ old('environment_poll_options', is_array($environmentPollOptions) ? implode("\n", $environmentPollOptions) : $environmentPollOptions) }}</textarea>
        <small class="text-muted d-block mt-2">Example: Yes, No, Maybe.</small>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-success-subtle mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Green Map of India</h5>
            <p class="text-muted mb-0 small">Display this initiative on the interactive community environmental map.</p>
        </div>
        <span class="badge bg-success text-white">SoilnWater unique</span>
    </div>
    <label class="form-check border rounded p-3 bg-white mb-3" for="environmentShowOnGreenMap">
        <input type="checkbox" name="environment_show_on_green_map" value="1" class="form-check-input environment-flow-field" id="environmentShowOnGreenMap" @checked(old('environment_show_on_green_map', data_get($post->meta, 'environment_show_on_green_map', true)))>
        <span class="form-check-label">Show on Green Map of India</span>
    </label>
    <p class="small text-muted mb-2">Map categories include:</p>
    <ul class="small text-muted mb-0">
        @foreach(\App\Support\CommunityContentTaxonomy::environmentGreenMapCategories() as $mapCategory)
            <li>{{ $mapCategory }}</li>
        @endforeach
    </ul>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-primary-subtle mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Green Leader Program</h5>
            <p class="text-muted mb-0 small">Eligible contributors can earn badges for sustained environmental action.</p>
        </div>
        <span class="badge bg-primary text-white">SoilnWater unique</span>
    </div>
    <label class="form-check border rounded p-3 bg-white mb-3" for="environmentEnableGreenLeader">
        <input type="checkbox" name="environment_enable_green_leader" value="1" class="form-check-input environment-flow-field" id="environmentEnableGreenLeader" @checked(old('environment_enable_green_leader', data_get($post->meta, 'environment_enable_green_leader', false)))>
        <span class="form-check-label">Enable Green Leader badge eligibility for this post</span>
    </label>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::environmentGreenLeaderBadges() as $badge)
            <span class="badge bg-light text-dark border">{{ $badge }}</span>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-info-subtle mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Impact tracker</h5>
            <p class="text-muted mb-0 small">Automatically displayed on the public page when impact data is available.</p>
        </div>
        <span class="badge bg-info text-dark">Unique feature</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::environmentImpactTrackerMetrics() as $metric)
            <span class="badge bg-white text-dark border">{{ $metric }}</span>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Community action buttons</h5>
            <p class="text-muted mb-0 small">Choose which actions readers can take on the public page.</p>
        </div>
        <span class="badge bg-primary text-white">Actions</span>
    </div>
    <div class="community-flow-stack d-flex flex-column gap-2">
        <label class="form-check border rounded p-3 bg-light mb-0" for="environmentAllowJoinCampaign">
            <input type="checkbox" name="environment_allow_join_campaign" value="1" class="form-check-input environment-flow-field" id="environmentAllowJoinCampaign" @checked(old('environment_allow_join_campaign', data_get($post->meta, 'environment_allow_join_campaign', true)))>
            <span class="form-check-label">Join Campaign</span>
        </label>
        <label class="form-check border rounded p-3 bg-light mb-0" for="environmentAllowVolunteer">
            <input type="checkbox" name="environment_allow_volunteer" value="1" class="form-check-input environment-flow-field" id="environmentAllowVolunteer" @checked(old('environment_allow_volunteer', data_get($post->meta, 'environment_allow_volunteer', true)))>
            <span class="form-check-label">Volunteer</span>
        </label>
        <label class="form-check border rounded p-3 bg-light mb-0" for="environmentAllowDonate">
            <input type="checkbox" name="environment_allow_donate" value="1" class="form-check-input environment-flow-field" id="environmentAllowDonate" @checked(old('environment_allow_donate', data_get($post->meta, 'environment_allow_donate', false)))>
            <span class="form-check-label">Donate</span>
        </label>
        <label class="form-check border rounded p-3 bg-light mb-0" for="environmentAllowSupportInitiative">
            <input type="checkbox" name="environment_allow_support_initiative" value="1" class="form-check-input environment-flow-field" id="environmentAllowSupportInitiative" @checked(old('environment_allow_support_initiative', data_get($post->meta, 'environment_allow_support_initiative', true)))>
            <span class="form-check-label">Support Initiative</span>
        </label>
        <label class="form-check border rounded p-3 bg-light mb-0" for="environmentAllowFollowCampaign">
            <input type="checkbox" name="environment_allow_follow_campaign" value="1" class="form-check-input environment-flow-field" id="environmentAllowFollowCampaign" @checked(old('environment_allow_follow_campaign', data_get($post->meta, 'environment_allow_follow_campaign', true)))>
            <span class="form-check-label">Follow Campaign</span>
        </label>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Community reactions</h5>
            <p class="text-muted mb-0 small">Environment-specific reactions readers can use on the public page.</p>
        </div>
        <span class="badge bg-success text-white">Enabled</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::environmentReactionOptions() as $reaction => $icon)
            <span class="badge bg-light text-dark border">
                <i class="{{ $icon }} me-1" aria-hidden="true"></i>{{ $reaction }}
            </span>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3" id="environmentParticipationWrap">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Comments settings</h5>
            <p class="text-muted mb-0 small">Choose what logged-in readers can submit on the public page.</p>
        </div>
        <span class="badge bg-primary text-white">Engagement</span>
    </div>
    <div class="community-flow-stack d-flex flex-column gap-2">
        <label class="form-check border rounded p-3 bg-white mb-0" for="environmentAllowComments">
            <input type="checkbox" name="allow_comments" value="1" class="form-check-input environment-flow-field" id="environmentAllowComments" @checked(old('allow_comments', $post->allow_comments ?? true))>
            <span class="form-check-label">Comments</span>
        </label>
        <label class="form-check border rounded p-3 bg-white mb-0" for="environmentAllowSuggestions">
            <input type="checkbox" name="allow_suggestions" value="1" class="form-check-input environment-flow-field" id="environmentAllowSuggestions" @checked(old('allow_suggestions', $post->allow_suggestions ?? true))>
            <span class="form-check-label">Suggestions</span>
        </label>
        <label class="form-check border rounded p-3 bg-white mb-0" for="environmentAllowQuestions">
            <input type="checkbox" name="allow_questions" value="1" class="form-check-input environment-flow-field" id="environmentAllowQuestions" @checked(old('allow_questions', $post->allow_questions ?? true))>
            <span class="form-check-label">Questions</span>
        </label>
        <label class="form-check border rounded p-3 bg-white mb-0" for="environmentAllowVolunteerRegistration">
            <input type="checkbox" name="environment_allow_volunteer_registration" value="1" class="form-check-input environment-flow-field" id="environmentAllowVolunteerRegistration" @checked(old('environment_allow_volunteer_registration', data_get($post->meta, 'environment_allow_volunteer_registration', true)))>
            <span class="form-check-label">Volunteer registration</span>
        </label>
        <label class="form-check border rounded p-3 bg-white mb-0" for="environmentAllowSharing">
            <input type="checkbox" name="allow_sharing" value="1" class="form-check-input environment-flow-field" id="environmentAllowSharing" @checked(old('allow_sharing', $post->allow_sharing ?? true))>
            <span class="form-check-label">Share</span>
        </label>
    </div>
</div>
