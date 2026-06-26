@php
    $selectedIssueType = old('local_voice_issue_type', data_get($post->meta, 'local_voice_issue_type'));
    $selectedAffectedCommunities = old('local_voice_affected_communities', data_get($post->meta, 'local_voice_affected_communities', []));
    $selectedImpactLevel = old('local_voice_impact_level', data_get($post->meta, 'local_voice_impact_level'));
@endphp

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Issue type</h5>
            <p class="text-muted mb-0 small">If applicable — how this post should be classified for civic engagement.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <label class="form-label" for="localVoiceIssueType">Issue type</label>
    <select name="local_voice_issue_type" id="localVoiceIssueType" class="form-select local-voices-flow-field">
        <option value="">Select issue type (optional)</option>
        @foreach(\App\Support\CommunityContentTaxonomy::localVoiceIssueTypes() as $issueType)
            <option value="{{ $issueType }}" @selected($selectedIssueType === $issueType)>{{ $issueType }}</option>
        @endforeach
    </select>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Affected community</h5>
            <p class="text-muted mb-0 small">Select all groups impacted by this issue or topic.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Multiple selection</span>
    </div>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::localVoiceAffectedCommunities() as $community)
            <div class="col-md-4 col-sm-6">
                <label class="form-check border rounded py-2 px-3 bg-light h-100 mb-0">
                    <input
                        type="checkbox"
                        name="local_voice_affected_communities[]"
                        value="{{ $community }}"
                        class="form-check-input local-voices-flow-field"
                        @checked(in_array($community, (array) $selectedAffectedCommunities, true))
                    >
                    <span class="form-check-label">{{ $community }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Impact level</h5>
            <p class="text-muted mb-0 small">Useful for prioritization and community attention.</p>
        </div>
        <span class="badge bg-warning text-dark">Recommended</span>
    </div>
    <label class="form-label" for="localVoiceImpactLevel">Impact level</label>
    <select name="local_voice_impact_level" id="localVoiceImpactLevel" class="form-select local-voices-flow-field">
        <option value="">Select impact level (optional)</option>
        @foreach(\App\Support\CommunityContentTaxonomy::localVoiceImpactLevels() as $impactLevel)
            <option value="{{ $impactLevel }}" @selected($selectedImpactLevel === $impactLevel)>{{ $impactLevel }}</option>
        @endforeach
    </select>
</div>
