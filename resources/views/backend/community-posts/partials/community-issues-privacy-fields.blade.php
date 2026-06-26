@php
    $selectedVisibility = old(
        'community_issue_visibility',
        data_get($post->meta, 'community_issue_visibility', \App\Support\CommunityContentTaxonomy::communityIssueDefaultVisibilitySetting())
    );
    $selectedPublishAs = old('publish_as', $post->publish_as ?: \App\Models\CommunityPost::PUBLISH_AS_PUBLIC_PROFILE);
    $privateLinkToken = data_get($post->meta, 'community_issue_private_link_token');
    $privateLinkUrl = $post->exists && filled($privateLinkToken) && $selectedVisibility === 'private_link'
        ? $post->communityIssuePrivateLinkUrl()
        : null;
@endphp

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-warning-subtle mb-3" id="communityIssuePrivacyWrap">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Author details &amp; privacy</h5>
            <p class="text-muted mb-0 small">For civic complaints, anonymous posting may encourage participation.</p>
        </div>
        <span class="badge bg-warning text-dark">Very important</span>
    </div>

    <div class="border rounded-3 p-3 bg-white mb-3" id="communityIssuePublishAsWrap">
        <label class="form-label mb-2">Publish as</label>
        <div class="d-flex flex-wrap gap-2">
            @foreach(\App\Support\CommunityContentTaxonomy::communityIssuePublishAsOptions() as $value => $label)
                <label class="form-check border rounded py-2 px-3 bg-light mb-0" for="communityIssuePublishAs{{ \Illuminate\Support\Str::studly($value) }}">
                    <input
                        type="radio"
                        class="form-check-input community-issues-flow-field"
                        name="publish_as"
                        id="communityIssuePublishAs{{ \Illuminate\Support\Str::studly($value) }}"
                        value="{{ $value }}"
                        @checked($selectedPublishAs === $value)
                    >
                    <span class="form-check-label">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        <div class="mt-3" id="communityIssuePenNameWrap" style="display:none;">
            <label class="form-label" for="communityIssuePenNameInput">Pen name <span class="text-danger">*</span></label>
            <input
                type="text"
                name="pen_name"
                id="communityIssuePenNameInput"
                class="form-control community-issues-flow-field"
                value="{{ old('pen_name', $post->pen_name) }}"
                maxlength="120"
                placeholder="Enter the pen name readers will see"
            >
        </div>
    </div>

    <div class="border rounded-3 p-3 bg-white">
        <label class="form-label mb-2" for="communityIssueVisibility">Visibility settings</label>
        <select name="community_issue_visibility" id="communityIssueVisibility" class="form-select community-issues-required community-issues-flow-field" required>
            @foreach(\App\Support\CommunityContentTaxonomy::communityIssueVisibilitySettings() as $value => $label)
                <option value="{{ $value }}" @selected($selectedVisibility === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <div id="communityIssuePrivateLinkInfo" class="alert alert-info py-2 px-3 small mt-3 mb-0" style="display:none;">
            @if($privateLinkUrl)
                <strong>Private link:</strong>
                <div class="input-group input-group-sm mt-2">
                    <input type="text" class="form-control" id="communityIssuePrivateLinkUrl" value="{{ $privateLinkUrl }}" readonly>
                    <button type="button" class="btn btn-outline-secondary" id="communityIssueCopyPrivateLinkBtn">Copy</button>
                </div>
            @else
                A private share link will be generated when this post is saved with Private Link visibility.
            @endif
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Community reactions</h5>
            <p class="text-muted mb-0 small">Issue-specific reactions readers can use on the public page.</p>
        </div>
        <span class="badge bg-success text-white">Enabled</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::communityIssueReactionOptions() as $reaction => $icon)
            <span class="badge bg-light text-dark border">
                <i class="{{ $icon }} me-1" aria-hidden="true"></i>{{ $reaction }}
            </span>
        @endforeach
    </div>
</div>
