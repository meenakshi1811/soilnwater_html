@php
    $ccPollOptions = old(
        'creative_corner_poll_options',
        data_get($post->meta, 'creative_corner_poll_options', \App\Support\CommunityContentTaxonomy::creativeCornerDefaultPollOptions())
    );
    if (is_string($ccPollOptions)) {
        $ccPollOptions = array_values(array_filter(array_map('trim', preg_split('/\R/', $ccPollOptions))));
    }
@endphp

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <label class="form-label" for="ccAskCommunity">Ask the community</label>
    <textarea name="creative_corner_ask_community" id="ccAskCommunity" class="form-control creative-corner-flow-field" rows="2" maxlength="500" placeholder="e.g. How can I improve this design? Which color combination works better?">{{ old('creative_corner_ask_community', data_get($post->meta, 'creative_corner_ask_community')) }}</textarea>
    <small class="text-muted">Encourage feedback, suggestions, and creative discussion.</small>
</div>

<div id="ccPollWrap" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Poll section</h5>
            <p class="text-muted mb-0 small">Optional — e.g. &ldquo;Which version do you prefer?&rdquo;</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <label class="form-check border rounded p-3 bg-white mb-3" for="ccAllowPoll">
        <input type="checkbox" name="creative_corner_allow_poll" value="1" class="form-check-input creative-corner-flow-field" id="ccAllowPoll" @checked(old('creative_corner_allow_poll', data_get($post->meta, 'creative_corner_allow_poll', false)))>
        <span class="form-check-label">Include a poll on this post</span>
    </label>
    <div id="ccPollFields" style="display:none;">
        <label class="form-label" for="ccPollQuestion">Poll question</label>
        <input type="text" name="creative_corner_poll_question" id="ccPollQuestion" class="form-control creative-corner-flow-field mb-3" maxlength="255" value="{{ old('creative_corner_poll_question', data_get($post->meta, 'creative_corner_poll_question')) }}" placeholder="e.g. Which version do you prefer?">
        <label class="form-label" for="ccPollOptions">Poll options (one per line)</label>
        <textarea name="creative_corner_poll_options" id="ccPollOptions" class="form-control creative-corner-flow-field" rows="3" maxlength="2000">{{ is_array($ccPollOptions) ? implode("\n", $ccPollOptions) : $ccPollOptions }}</textarea>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Comments settings</h5>
            <p class="text-muted mb-0 small">Choose what kind of community interaction you welcome.</p>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::creativeCornerCommentSettings() as $setting)
            <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                <input type="checkbox" name="creative_corner_comment_settings[]" value="{{ $setting }}" class="form-check-input creative-corner-flow-field" @checked(in_array($setting, (array) old('creative_corner_comment_settings', data_get($post->meta, 'creative_corner_comment_settings', [])), true))>
                <span class="form-check-label">{{ $setting }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Creative license</h5>
            <p class="text-muted mb-0 small">Optional — how others may use or share your work.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::creativeCornerCreativeLicenses() as $license)
            <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                <input type="checkbox" name="creative_corner_creative_licenses[]" value="{{ $license }}" class="form-check-input creative-corner-flow-field" @checked(in_array($license, (array) old('creative_corner_creative_licenses', data_get($post->meta, 'creative_corner_creative_licenses', [])), true))>
                <span class="form-check-label">{{ $license }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-primary-subtle mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Collaboration request</h5>
            <p class="text-muted mb-0 small">Users can request collaboration in these roles.</p>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::creativeCornerCollaborationRoles() as $role)
            <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                <input type="checkbox" name="creative_corner_collaboration_roles[]" value="{{ $role }}" class="form-check-input creative-corner-flow-field" @checked(in_array($role, (array) old('creative_corner_collaboration_roles', data_get($post->meta, 'creative_corner_collaboration_roles', [])), true))>
                <span class="form-check-label">{{ $role }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Community reactions</h5>
            <p class="text-muted mb-0 small">Creative-specific reactions enabled for Creative Corner posts.</p>
        </div>
        <span class="badge bg-primary text-white">Reactions</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::creativeCornerReactionOptions() as $reaction => $icon)
            <span class="badge bg-light text-dark border py-2 px-3"><i class="{{ $icon }} me-1" aria-hidden="true"></i>{{ $reaction }}</span>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-warning-subtle mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Declaration</h5>
            <p class="text-muted mb-0 small">Mandatory — confirm copyright and publishing rights.</p>
        </div>
        <span class="badge bg-danger text-white">Mandatory</span>
    </div>
    <div class="d-flex flex-column gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::creativeCornerDeclarationStatements() as $field => $label)
            <label class="form-check border rounded p-3 bg-white mb-0">
                <input type="checkbox" name="{{ $field }}" id="{{ $field }}" class="form-check-input creative-corner-declaration-required" value="1" @checked(old($field, data_get($post->meta, $field, false))) required>
                <span class="form-check-label">{{ $label }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">AI disclosure</h5>
            <p class="text-muted mb-0 small">Highly recommended — promotes transparency as AI-generated content becomes common.</p>
        </div>
        <span class="badge bg-info text-white">Recommended</span>
    </div>
    <label class="form-label d-block">Was AI used in creating this work?</label>
    <div class="d-flex flex-wrap gap-3 mb-3">
        @foreach(\App\Support\CommunityContentTaxonomy::creativeCornerAiUsageOptions() as $option)
            <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                <input type="radio" name="creative_corner_ai_used" value="{{ $option }}" class="form-check-input creative-corner-flow-field" @checked(old('creative_corner_ai_used', data_get($post->meta, 'creative_corner_ai_used', 'No')) === $option)>
                <span class="form-check-label">{{ $option }}</span>
            </label>
        @endforeach
    </div>
    <div id="ccAiFields" style="display:none;">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="ccAiTool">AI tool used</label>
                <input type="text" name="creative_corner_ai_tool" id="ccAiTool" class="form-control creative-corner-flow-field" maxlength="160" value="{{ old('creative_corner_ai_tool', data_get($post->meta, 'creative_corner_ai_tool')) }}">
            </div>
            <div class="col-12">
                <label class="form-label" for="ccAiDescription">Description of AI assistance</label>
                <textarea name="creative_corner_ai_description" id="ccAiDescription" class="form-control creative-corner-flow-field" rows="2" maxlength="2000">{{ old('creative_corner_ai_description', data_get($post->meta, 'creative_corner_ai_description')) }}</textarea>
            </div>
        </div>
    </div>
</div>
