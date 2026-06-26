@php
    $selectedIssueCategory = old('community_issue_category', data_get($post->meta, 'community_issue_category', $post->category));
    $flowPlacement = $placement ?? 'all';
    $showCommunityIssuesSetup = in_array($flowPlacement, ['all', 'setup'], true);
    $showCommunityIssuesRest = in_array($flowPlacement, ['all', 'rest'], true);
@endphp

@if($showCommunityIssuesSetup)
<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Issue category</h5>
            <p class="text-muted mb-0 small">Choose the main topic for this community issue.</p>
        </div>
        <span class="badge bg-primary text-white">Main category</span>
    </div>
    <label class="form-label" for="communityIssueCategory">Issue category <span class="text-danger">*</span></label>
    <select name="community_issue_category" id="communityIssueCategory" class="form-select community-issues-required" required>
        <option value="">Select main category</option>
        @foreach(\App\Support\CommunityContentTaxonomy::communityIssueMainCategories() as $category)
            <option value="{{ $category }}" @selected($selectedIssueCategory === $category)>{{ $category }}</option>
        @endforeach
    </select>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Issue type</h5>
            <p class="text-muted mb-0 small">How would you classify this issue for the community?</p>
        </div>
        <span class="badge bg-danger text-white">Required</span>
    </div>
    <label class="form-label" for="communityIssueType">Issue type <span class="text-danger">*</span></label>
    <select name="community_issue_type" id="communityIssueType" class="form-select community-issues-required" required>
        <option value="">Select issue type</option>
        @foreach(\App\Support\CommunityContentTaxonomy::communityIssueTypes() as $issueType)
            <option value="{{ $issueType }}" @selected(old('community_issue_type', data_get($post->meta, 'community_issue_type')) === $issueType)>{{ $issueType }}</option>
        @endforeach
    </select>
</div>

@include('backend.community-posts.partials.community-issues-metadata-fields', ['post' => $post])
@endif

@if($showCommunityIssuesRest)
<div class="news-flow-card story-flow-card story-flow-card--location border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Location details</h5>
            <p class="text-muted mb-0 small">Country, state, district, city/town/village, locality, and landmark. GPS and map pin are optional.</p>
        </div>
        <span class="badge bg-danger text-white">Most important</span>
    </div>
    <p class="small text-muted mb-3">Example: Prem Nagar, Near SBI Bank.</p>
    <div id="communityCommunityIssuesLocationSlot"></div>
    <div class="mt-3">
        <label class="form-label" for="communityIssueLandmark">Landmark</label>
        <input
            type="text"
            name="location_landmark"
            id="communityIssueLandmark"
            class="form-control community-issues-flow-field"
            maxlength="160"
            value="{{ old('location_landmark', data_get($post->meta, 'location_landmark')) }}"
            placeholder="e.g. Near SBI Bank"
        >
    </div>
</div>

@include('backend.community-posts.partials.community-issues-evidence-fields', ['post' => $post])
@include('backend.community-posts.partials.community-issues-timeline-fields', ['post' => $post])
@include('backend.community-posts.partials.community-issues-civic-fields', ['post' => $post])
@include('backend.community-posts.partials.community-issues-solution-fields', ['post' => $post])
@include('backend.community-posts.partials.community-issues-engagement-fields', ['post' => $post])
@include('backend.community-posts.partials.community-issues-privacy-fields', ['post' => $post])
@endif
