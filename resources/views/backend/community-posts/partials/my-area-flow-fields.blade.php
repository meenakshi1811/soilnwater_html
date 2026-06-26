@php
    $selectedTopic = old('my_area_topic_category', data_get($post->meta, 'my_area_topic_category', $post->category));
    $selectedActivity = old('my_area_activity_type', data_get($post->meta, 'my_area_activity_type'));
    $selectedAffected = old('my_area_affected_communities', data_get($post->meta, 'my_area_affected_communities', []));
    $selectedAuthorities = old('my_area_authorities', data_get($post->meta, 'my_area_authorities', []));
    $selectedStatus = old('my_area_status_tracker', data_get($post->meta, 'my_area_status_tracker'));
    $selectedVisibility = old('my_area_visibility', data_get($post->meta, 'my_area_visibility', \App\Support\CommunityContentTaxonomy::myAreaDefaultVisibilitySetting()));
    $pollOptions = old('my_area_poll_options', data_get($post->meta, 'my_area_poll_options', \App\Support\CommunityContentTaxonomy::myAreaDefaultPollOptions()));
    if (is_string($pollOptions)) {
        $pollOptions = array_values(array_filter(array_map('trim', preg_split('/\R/', $pollOptions))));
    }
    $flowPlacement = $placement ?? 'all';
    $showSetup = in_array($flowPlacement, ['all', 'setup'], true);
    $showRest = in_array($flowPlacement, ['all', 'rest'], true);
    $privateLinkToken = data_get($post->meta, 'my_area_private_link_token');
    $privateLinkUrl = $post->exists && filled($privateLinkToken) && $selectedVisibility === 'private_link'
        ? $post->myAreaPrivateLinkUrl()
        : null;
@endphp

@if($showSetup)
<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-primary-subtle mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">My Area activity</h5>
            <p class="text-muted mb-0 small">What would you like to do for your local community?</p>
        </div>
        <span class="badge bg-primary text-white">Required</span>
    </div>
    <label class="form-label" for="myAreaActivityType">Activity type <span class="text-danger">*</span></label>
    <select name="my_area_activity_type" id="myAreaActivityType" class="form-select my-area-required" required>
        <option value="">Select activity</option>
        @foreach(\App\Support\CommunityContentTaxonomy::myAreaActivityTypes() as $activity)
            <option value="{{ $activity }}" @selected($selectedActivity === $activity)>{{ $activity }}</option>
        @endforeach
    </select>
    <div class="row g-2 mt-3">
        @foreach(\App\Support\CommunityContentTaxonomy::myAreaActivityTypes() as $activity)
            <div class="col-md-4 col-sm-6">
                <span class="badge bg-light text-dark border w-100 py-2">{{ $activity }}</span>
            </div>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <label class="form-label" for="myAreaTopicCategory">Topic category <span class="text-danger">*</span></label>
    <select name="my_area_topic_category" id="myAreaTopicCategory" class="form-select my-area-required" required>
        <option value="">Select topic</option>
        @foreach(\App\Support\CommunityContentTaxonomy::myAreaTopicCategories() as $topic)
            <option value="{{ $topic }}" @selected($selectedTopic === $topic)>{{ $topic }}</option>
        @endforeach
    </select>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <label class="form-label" for="myAreaImpactLevel">Impact level</label>
    <select name="my_area_impact_level" id="myAreaImpactLevel" class="form-select my-area-flow-field">
        <option value="">Select impact (optional)</option>
        @foreach(\App\Support\CommunityContentTaxonomy::myAreaImpactLevels() as $level)
            <option value="{{ $level }}" @selected(old('my_area_impact_level', data_get($post->meta, 'my_area_impact_level')) === $level)>{{ $level }}</option>
        @endforeach
    </select>
</div>
@endif

@if($showRest)
<div class="news-flow-card story-flow-card story-flow-card--location border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Location details</h5>
            <p class="text-muted mb-0 small">My Area is location-centric — power location-based feeds and area discussions.</p>
        </div>
        <span class="badge bg-danger text-white">Required</span>
    </div>
    <div id="communityMyAreaLocationSlot"></div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <h5 class="mb-3">Featured image</h5>
    <div id="communityMyAreaFeaturedImagesSlot"></div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <h5 class="mb-2">Photo evidence</h5>
    <input type="file" name="my_area_photo_evidence[]" class="form-control my-area-flow-field" accept="image/*" multiple>
    @if(!empty(data_get($post->meta, 'my_area_photo_evidence')))
        <div class="mt-3 d-flex flex-column gap-2">
            @foreach(data_get($post->meta, 'my_area_photo_evidence', []) as $photo)
                <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                    <input type="checkbox" name="removed_my_area_photo_evidence[]" value="{{ data_get($photo, 'path') }}" class="form-check-input">
                    <span class="form-check-label">Remove {{ data_get($photo, 'name', 'photo') }}</span>
                </label>
            @endforeach
        </div>
    @endif
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <h5 class="mb-2">Video evidence</h5>
    <div id="communityMyAreaVideoSlot"></div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <h5 class="mb-2">Documents</h5>
    <input type="file" name="my_area_documents[]" class="form-control my-area-flow-field" accept=".pdf,.doc,.docx" multiple>
    @if(!empty(data_get($post->meta, 'my_area_documents')))
        <div class="mt-3 d-flex flex-column gap-2">
            @foreach(data_get($post->meta, 'my_area_documents', []) as $document)
                <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                    <input type="checkbox" name="removed_my_area_documents[]" value="{{ data_get($document, 'path') }}" class="form-check-input">
                    <span class="form-check-label">Remove {{ data_get($document, 'name', 'document') }}</span>
                </label>
            @endforeach
        </div>
    @endif
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <h5 class="mb-2">Tags</h5>
    <div id="communityMyAreaTagsSlot"></div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-success-subtle mb-3">
    <h5 class="mb-2">Resolution &amp; authority</h5>
    <label class="form-label" for="myAreaStatusTracker">Status tracker</label>
    <select name="my_area_status_tracker" id="myAreaStatusTracker" class="form-select my-area-flow-field mb-3">
        <option value="">Select status (optional)</option>
        @foreach(\App\Support\CommunityContentTaxonomy::myAreaStatusTrackerSteps() as $status)
            <option value="{{ $status }}" @selected($selectedStatus === $status)>{{ $status }}</option>
        @endforeach
    </select>
    <label class="form-label d-block">Authority concerned</label>
    <div class="row g-2">
        @foreach(\App\Support\CommunityContentTaxonomy::myAreaAuthorities() as $authority)
            <div class="col-md-4 col-sm-6">
                <label class="form-check border rounded py-2 px-3 bg-white h-100 mb-0">
                    <input type="checkbox" name="my_area_authorities[]" value="{{ $authority }}" class="form-check-input" @checked(in_array($authority, (array) $selectedAuthorities, true))>
                    <span class="form-check-label">{{ $authority }}</span>
                </label>
            </div>
        @endforeach
    </div>
    <label class="form-label mt-3" for="myAreaSuggestedSolution">Suggested solution</label>
    <textarea name="my_area_suggested_solution" id="myAreaSuggestedSolution" class="form-control my-area-flow-field" rows="3" maxlength="3000">{{ old('my_area_suggested_solution', data_get($post->meta, 'my_area_suggested_solution')) }}</textarea>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-warning-subtle mb-3" id="myAreaHeroSection" style="{{ $selectedActivity === 'Recognize Heroes' ? '' : 'display:none;' }}">
    <h5 class="mb-3">Recognize a local hero</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="myAreaHeroName">Person name</label>
            <input type="text" name="my_area_hero_name" id="myAreaHeroName" class="form-control my-area-flow-field" value="{{ old('my_area_hero_name', data_get($post->meta, 'my_area_hero_name')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="myAreaHeroLocation">Location</label>
            <input type="text" name="my_area_hero_location" id="myAreaHeroLocation" class="form-control my-area-flow-field" value="{{ old('my_area_hero_location', data_get($post->meta, 'my_area_hero_location')) }}">
        </div>
        <div class="col-12">
            <label class="form-label" for="myAreaHeroContribution">Contribution</label>
            <textarea name="my_area_hero_contribution" id="myAreaHeroContribution" class="form-control my-area-flow-field" rows="3">{{ old('my_area_hero_contribution', data_get($post->meta, 'my_area_hero_contribution')) }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label" for="myAreaHeroImages">Hero images</label>
            <input type="file" name="my_area_hero_images[]" id="myAreaHeroImages" class="form-control my-area-flow-field" accept="image/*" multiple>
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3" id="myAreaAchievementSection" style="{{ $selectedActivity === 'Share Local Achievements' ? '' : 'display:none;' }}">
    <h5 class="mb-3">Local achievement</h5>
    <label class="form-label" for="myAreaAchievementTitle">Achievement title</label>
    <input type="text" name="my_area_achievement_title" id="myAreaAchievementTitle" class="form-control my-area-flow-field mb-3" value="{{ old('my_area_achievement_title', data_get($post->meta, 'my_area_achievement_title')) }}">
    <label class="form-label" for="myAreaAchievementDescription">Description</label>
    <textarea name="my_area_achievement_description" id="myAreaAchievementDescription" class="form-control my-area-flow-field" rows="3">{{ old('my_area_achievement_description', data_get($post->meta, 'my_area_achievement_description')) }}</textarea>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <h5 class="mb-2">Affected community</h5>
    <div class="row g-2">
        @foreach(\App\Support\CommunityContentTaxonomy::myAreaAffectedCommunities() as $community)
            <div class="col-md-4 col-sm-6">
                <label class="form-check border rounded py-2 px-3 bg-light h-100 mb-0">
                    <input type="checkbox" name="my_area_affected_communities[]" value="{{ $community }}" class="form-check-input" @checked(in_array($community, (array) $selectedAffected, true))>
                    <span class="form-check-label">{{ $community }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3" id="myAreaPollWrap">
    <label class="form-check border rounded p-3 bg-light mb-3" for="myAreaAllowPoll">
        <input type="checkbox" name="allow_poll" value="1" class="form-check-input" id="myAreaAllowPoll" @checked(old('allow_poll', $post->allow_poll ?? false))>
        <span class="form-check-label">Enable community voting (poll)</span>
    </label>
    <div id="myAreaPollFields">
        <input type="text" name="my_area_poll_question" class="form-control my-area-flow-field mb-2" placeholder="Poll question" value="{{ old('my_area_poll_question', data_get($post->meta, 'my_area_poll_question')) }}">
        <textarea name="my_area_poll_options" class="form-control my-area-flow-field" rows="3" placeholder="One option per line">{{ old('my_area_poll_options', is_array($pollOptions) ? implode("\n", $pollOptions) : $pollOptions) }}</textarea>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3" id="myAreaParticipationWrap">
    <h5 class="mb-3">Area discussions</h5>
    <div class="d-flex flex-column gap-2">
        <label class="form-check border rounded p-3 bg-white mb-0"><input type="checkbox" name="allow_comments" value="1" class="form-check-input" @checked(old('allow_comments', $post->allow_comments ?? true))><span class="form-check-label">Comments</span></label>
        <label class="form-check border rounded p-3 bg-white mb-0"><input type="checkbox" name="allow_suggestions" value="1" class="form-check-input" @checked(old('allow_suggestions', $post->allow_suggestions ?? true))><span class="form-check-label">Suggestions</span></label>
        <label class="form-check border rounded p-3 bg-white mb-0"><input type="checkbox" name="allow_feedback" value="1" class="form-check-input" @checked(old('allow_feedback', $post->allow_feedback ?? true))><span class="form-check-label">Additional evidence</span></label>
        <label class="form-check border rounded p-3 bg-white mb-0"><input type="checkbox" name="allow_questions" value="1" class="form-check-input" @checked(old('allow_questions', $post->allow_questions ?? true))><span class="form-check-label">Questions</span></label>
        <label class="form-check border rounded p-3 bg-white mb-0"><input type="checkbox" name="allow_sharing" value="1" class="form-check-input" @checked(old('allow_sharing', $post->allow_sharing ?? true))><span class="form-check-label">Sharing</span></label>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-warning-subtle mb-3" id="myAreaPrivacyWrap">
    <h5 class="mb-3">Privacy</h5>
    <div class="border rounded-3 p-3 bg-white mb-3" id="myAreaPublishAsWrap">
        <label class="form-label mb-2">Publish as</label>
        <div class="d-flex flex-wrap gap-2">
            @foreach(\App\Support\CommunityContentTaxonomy::myAreaPublishAsOptions() as $value => $label)
                <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                    <input type="radio" class="form-check-input" name="publish_as" value="{{ $value }}" @checked(old('publish_as', $post->publish_as ?: \App\Models\CommunityPost::PUBLISH_AS_PUBLIC_PROFILE) === $value)>
                    <span class="form-check-label">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        <div class="mt-3" id="myAreaPenNameWrap" style="display:none;">
            <input type="text" name="pen_name" id="myAreaPenNameInput" class="form-control" value="{{ old('pen_name', $post->pen_name) }}" placeholder="Pen name">
        </div>
    </div>
    <select name="my_area_visibility" id="myAreaVisibility" class="form-select my-area-required" required>
        @foreach(\App\Support\CommunityContentTaxonomy::myAreaVisibilitySettings() as $value => $label)
            <option value="{{ $value }}" @selected($selectedVisibility === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <div id="myAreaPrivateLinkInfo" class="alert alert-info py-2 px-3 small mt-3 mb-0" style="display:none;">
        @if($privateLinkUrl)
            <input type="text" class="form-control form-control-sm" id="myAreaPrivateLinkUrl" value="{{ $privateLinkUrl }}" readonly>
        @else
            A private link will be generated when saved with Private Link visibility.
        @endif
    </div>
</div>
@endif
