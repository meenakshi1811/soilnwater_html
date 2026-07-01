@php
    $rsPollOptions = old(
        'religion_spirituality_poll_options',
        data_get($post->meta, 'religion_spirituality_poll_options', \App\Support\CommunityContentTaxonomy::religionSpiritualityDefaultPollOptions())
    );
    if (is_string($rsPollOptions)) {
        $rsPollOptions = array_values(array_filter(array_map('trim', preg_split('/\R/', $rsPollOptions))));
    }
@endphp

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-primary-subtle mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Related community service</h5>
            <p class="text-muted mb-0 small">Unique SoilnWater feature — connect spirituality with social responsibility.</p>
        </div>
        <span class="badge bg-primary text-white">SoilnWater unique</span>
    </div>
    <label class="form-label d-block">Link to community activities</label>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::religionSpiritualityRelatedServiceActions() as $action)
            <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                <input type="checkbox" name="religion_spirituality_related_service_actions[]" value="{{ $action }}" class="form-check-input religion-spirituality-flow-field" @checked(in_array($action, (array) old('religion_spirituality_related_service_actions', data_get($post->meta, 'religion_spirituality_related_service_actions', [])), true))>
                <span class="form-check-label">{{ $action }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <label class="form-label" for="rsAskCommunity">Ask the community</label>
    <textarea name="religion_spirituality_ask_community" id="rsAskCommunity" class="form-control religion-spirituality-flow-field" rows="2" maxlength="500" placeholder="e.g. What spiritual practices help you maintain inner peace?">{{ old('religion_spirituality_ask_community', data_get($post->meta, 'religion_spirituality_ask_community')) }}</textarea>
    <small class="text-muted">Questions should encourage learning and respectful discussion.</small>
</div>

<div id="rsPollWrap" class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Poll section</h5>
            <p class="text-muted mb-0 small">Optional — e.g. &ldquo;Which topic would you like to learn more about?&rdquo;</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <label class="form-check border rounded p-3 bg-white mb-3" for="rsAllowPoll">
        <input type="checkbox" name="religion_spirituality_allow_poll" value="1" class="form-check-input religion-spirituality-flow-field" id="rsAllowPoll" @checked(old('religion_spirituality_allow_poll', data_get($post->meta, 'religion_spirituality_allow_poll', false)))>
        <span class="form-check-label">Include a poll on this post</span>
    </label>
    <div id="rsPollFields" style="display:none;">
        <label class="form-label" for="rsPollQuestion">Poll question</label>
        <input type="text" name="religion_spirituality_poll_question" id="rsPollQuestion" class="form-control religion-spirituality-flow-field mb-3" maxlength="255" value="{{ old('religion_spirituality_poll_question', data_get($post->meta, 'religion_spirituality_poll_question')) }}" placeholder="e.g. Which topic would you like to learn more about?">
        <label class="form-label" for="rsPollOptions">Poll options (one per line)</label>
        <textarea name="religion_spirituality_poll_options" id="rsPollOptions" class="form-control religion-spirituality-flow-field" rows="3" maxlength="2000">{{ is_array($rsPollOptions) ? implode("\n", $rsPollOptions) : $rsPollOptions }}</textarea>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Comments settings</h5>
            <p class="text-muted mb-0 small">Comments should be actively moderated.</p>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::religionSpiritualityCommentSettings() as $setting)
            <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                <input type="checkbox" name="religion_spirituality_comment_settings[]" value="{{ $setting }}" class="form-check-input religion-spirituality-flow-field" @checked(in_array($setting, (array) old('religion_spirituality_comment_settings', data_get($post->meta, 'religion_spirituality_comment_settings', [])), true))>
                <span class="form-check-label">{{ $setting }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Community reactions</h5>
            <p class="text-muted mb-0 small">Use respectful reactions. Avoid reactions that encourage competition between beliefs.</p>
        </div>
        <span class="badge bg-primary text-white">Reactions</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::religionSpiritualityReactionOptions() as $reaction => $icon)
            <span class="badge bg-light text-dark border py-2 px-3"><i class="{{ $icon }} me-1" aria-hidden="true"></i>{{ $reaction }}</span>
        @endforeach
    </div>
    <small class="text-muted d-block mt-2">These reactions are enabled automatically for Religion &amp; Spirituality posts.</small>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-warning-subtle mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Declaration</h5>
            <p class="text-muted mb-0 small">Mandatory — confirm responsible sharing of spiritual and cultural content.</p>
        </div>
        <span class="badge bg-danger text-white">Mandatory</span>
    </div>
    <div class="d-flex flex-column gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::religionSpiritualityDeclarationStatements() as $field => $label)
            <label class="form-check border rounded p-3 bg-white mb-0">
                <input type="checkbox" name="{{ $field }}" id="{{ $field }}" class="form-check-input religion-spirituality-declaration-required" value="1" @checked(old($field, data_get($post->meta, $field, false))) required>
                <span class="form-check-label">{{ $label }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Community guidelines</h5>
            <p class="text-muted mb-0 small">Displayed before publishing — mandatory notice.</p>
        </div>
        <span class="badge bg-secondary text-white">Always shown</span>
    </div>
    <div class="alert alert-light border mb-0 small">
        {{ \App\Support\CommunityContentTaxonomy::religionSpiritualityGuidelinesText() }}
    </div>
</div>
