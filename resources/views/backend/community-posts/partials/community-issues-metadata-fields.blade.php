@php
    $selectedSeverity = old('community_issue_severity', data_get($post->meta, 'community_issue_severity'));
    $selectedPopulation = old('community_issue_affected_population', data_get($post->meta, 'community_issue_affected_population'));
    $selectedAffectedGroups = old('community_issue_affected_groups', data_get($post->meta, 'community_issue_affected_groups', []));
@endphp

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Issue severity</h5>
            <p class="text-muted mb-0 small">How urgent or serious is this issue?</p>
        </div>
        <span class="badge bg-danger text-white">Required</span>
    </div>
    <label class="form-label" for="communityIssueSeverity">Issue severity <span class="text-danger">*</span></label>
    <select name="community_issue_severity" id="communityIssueSeverity" class="form-select community-issues-required community-issues-flow-field" required>
        <option value="">Select severity</option>
        @foreach(\App\Support\CommunityContentTaxonomy::communityIssueSeverityLevels() as $severity)
            <option value="{{ $severity }}" @selected($selectedSeverity === $severity)>{{ $severity }}</option>
        @endforeach
    </select>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Issue impact</h5>
            <p class="text-muted mb-0 small">Who is affected and how many people are impacted?</p>
        </div>
        <span class="badge bg-warning text-dark">Recommended</span>
    </div>
    <label class="form-label" for="communityIssueAffectedPopulation">Affected population</label>
    <select name="community_issue_affected_population" id="communityIssueAffectedPopulation" class="form-select community-issues-flow-field mb-3">
        <option value="">Select affected population (optional)</option>
        @foreach(\App\Support\CommunityContentTaxonomy::communityIssueAffectedPopulationRanges() as $range)
            <option value="{{ $range }}" @selected($selectedPopulation === $range)>{{ $range }}</option>
        @endforeach
    </select>
    <label class="form-label">Affected groups</label>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::communityIssueAffectedGroups() as $group)
            <div class="col-md-4 col-sm-6">
                <label class="form-check border rounded py-2 px-3 bg-white h-100 mb-0">
                    <input
                        type="checkbox"
                        name="community_issue_affected_groups[]"
                        value="{{ $group }}"
                        class="form-check-input community-issues-flow-field"
                        @checked(in_array($group, (array) $selectedAffectedGroups, true))
                    >
                    <span class="form-check-label">{{ $group }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>
