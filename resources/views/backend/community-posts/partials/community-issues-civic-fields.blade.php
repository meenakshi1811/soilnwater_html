@php
    $selectedAuthority = old('community_issue_authority', data_get($post->meta, 'community_issue_authority'));
    $alreadyReported = old('community_issue_already_reported', data_get($post->meta, 'community_issue_already_reported'));
    $selectedSupportRequests = old('community_issue_support_requests', data_get($post->meta, 'community_issue_support_requests', []));
@endphp

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Responsible authority</h5>
            <p class="text-muted mb-0 small">Which department or authority should address this issue?</p>
        </div>
        <span class="badge bg-primary text-white">Recommended</span>
    </div>
    <label class="form-label" for="communityIssueAuthority">Responsible authority</label>
    <select name="community_issue_authority" id="communityIssueAuthority" class="form-select community-issues-flow-field">
        <option value="">Select authority (optional)</option>
        @foreach(\App\Support\CommunityContentTaxonomy::communityIssueAuthorities() as $authority)
            <option value="{{ $authority }}" @selected($selectedAuthority === $authority)>{{ $authority }}</option>
        @endforeach
    </select>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3" id="communityIssuePriorActionsWrap">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Prior actions taken</h5>
            <p class="text-muted mb-0 small">Have you already reported this issue to an authority?</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <label class="form-label d-block">Have you already reported this issue?</label>
    <div class="d-flex flex-wrap gap-2 mb-3">
        @foreach(['yes' => 'Yes', 'no' => 'No'] as $value => $label)
            <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                <input
                    type="radio"
                    name="community_issue_already_reported"
                    value="{{ $value }}"
                    class="form-check-input community-issues-flow-field"
                    id="communityIssueReported{{ ucfirst($value) }}"
                    @checked($alreadyReported === $value)
                >
                <span class="form-check-label">{{ $label }}</span>
            </label>
        @endforeach
    </div>
    <div id="communityIssuePriorReportFields">
        <label class="form-label" for="communityIssueComplaintNumber">Complaint number</label>
        <input
            type="text"
            name="community_issue_complaint_number"
            id="communityIssueComplaintNumber"
            class="form-control community-issues-flow-field mb-3"
            maxlength="120"
            value="{{ old('community_issue_complaint_number', data_get($post->meta, 'community_issue_complaint_number')) }}"
        >
        <label class="form-label" for="communityIssueComplaintDate">Complaint date</label>
        <input
            type="date"
            name="community_issue_complaint_date"
            id="communityIssueComplaintDate"
            class="form-control community-issues-flow-field mb-3"
            value="{{ old('community_issue_complaint_date', data_get($post->meta, 'community_issue_complaint_date')) }}"
        >
        <label class="form-label" for="communityIssueDepartmentContacted">Department contacted</label>
        <input
            type="text"
            name="community_issue_department_contacted"
            id="communityIssueDepartmentContacted"
            class="form-control community-issues-flow-field"
            maxlength="160"
            value="{{ old('community_issue_department_contacted', data_get($post->meta, 'community_issue_department_contacted')) }}"
        >
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Community support request</h5>
            <p class="text-muted mb-0 small">What support are you seeking from the community?</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Multiple selection</span>
    </div>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::communityIssueSupportRequests() as $request)
            <div class="col-md-6 col-sm-6">
                <label class="form-check border rounded py-2 px-3 bg-light h-100 mb-0">
                    <input
                        type="checkbox"
                        name="community_issue_support_requests[]"
                        value="{{ $request }}"
                        class="form-check-input community-issues-flow-field"
                        @checked(in_array($request, (array) $selectedSupportRequests, true))
                    >
                    <span class="form-check-label">{{ $request }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>
