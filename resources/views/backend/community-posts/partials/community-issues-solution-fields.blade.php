@php
    $selectedStatus = old('community_issue_status_tracker', data_get($post->meta, 'community_issue_status_tracker'));
@endphp

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-primary-subtle mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Suggested solution</h5>
            <p class="text-muted mb-0 small">Community-driven solution field.</p>
        </div>
        <span class="badge bg-primary text-white">Recommended</span>
    </div>
    <label class="form-label" for="communityIssueSuggestedSolution">Suggested solution</label>
    <textarea
        name="community_issue_suggested_solution"
        id="communityIssueSuggestedSolution"
        class="form-control community-issues-flow-field"
        rows="3"
        maxlength="3000"
        placeholder="Example: Install a new drainage channel and repair existing culverts before monsoon season."
    >{{ old('community_issue_suggested_solution', data_get($post->meta, 'community_issue_suggested_solution')) }}</textarea>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Status tracking</h5>
            <p class="text-muted mb-0 small">Track where this issue stands — the most important civic feature.</p>
        </div>
        <span class="badge bg-success text-white">Most important</span>
    </div>
    <label class="form-label" for="communityIssueStatusTracker">Current status</label>
    <select name="community_issue_status_tracker" id="communityIssueStatusTracker" class="form-select community-issues-flow-field mb-3">
        <option value="">Select status (optional)</option>
        @foreach(\App\Support\CommunityContentTaxonomy::communityIssueStatusSteps() as $status)
            <option value="{{ $status }}" @selected($selectedStatus === $status)>{{ $status }}</option>
        @endforeach
    </select>
    <label class="form-label" for="communityIssueResolutionTimeline">Resolution tracker timeline</label>
    <textarea
        name="community_issue_resolution_timeline"
        id="communityIssueResolutionTimeline"
        class="form-control community-issues-flow-field"
        rows="4"
        maxlength="4000"
        placeholder="One milestone per line, e.g.&#10;15 June - Reported&#10;17 June - Community Verified&#10;20 June - Forwarded to Municipality"
    >{{ old('community_issue_resolution_timeline', data_get($post->meta, 'community_issue_resolution_timeline')) }}</textarea>
    <small class="text-muted d-block mt-2">Example: 15 June - Reported, 17 June - Community Verified, 20 June - Forwarded to Municipality.</small>
</div>
