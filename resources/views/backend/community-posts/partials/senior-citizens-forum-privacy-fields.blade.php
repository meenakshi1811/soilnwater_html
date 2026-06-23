@php
    $selectedSeniorCitizensForumVisibility = old(
        'senior_citizens_forum_visibility',
        data_get($post->meta, 'senior_citizens_forum_visibility', \App\Support\CommunityContentTaxonomy::seniorCitizensForumDefaultVisibilitySetting())
    );
    $selectedIntergenerationalConnections = old(
        'senior_citizens_forum_intergenerational_connections',
        data_get($post->meta, 'senior_citizens_forum_intergenerational_connections', [])
    );
    $preserveDigitalLegacy = old(
        'senior_citizens_forum_preserve_digital_legacy',
        data_get($post->meta, 'senior_citizens_forum_preserve_digital_legacy', false)
    );
    $privateLinkToken = data_get($post->meta, 'senior_citizens_forum_private_link_token');
    $privateLinkUrl = $post->exists && filled($privateLinkToken) && $selectedSeniorCitizensForumVisibility === 'private_link'
        ? $post->seniorCitizensForumPrivateLinkUrl()
        : null;
@endphp

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-warning-subtle mb-3" id="seniorCitizensForumPrivacyWrap">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Visibility</h5>
            <p class="text-muted mb-0 small">Control who can discover and read this post.</p>
        </div>
        <span class="badge bg-warning text-dark">Important</span>
    </div>
    <label class="form-label mb-2" for="seniorCitizensForumVisibility">Visibility</label>
    <select name="senior_citizens_forum_visibility" id="seniorCitizensForumVisibility" class="form-select senior-citizens-forum-required" required>
        @foreach(\App\Support\CommunityContentTaxonomy::seniorCitizensForumVisibilitySettings() as $value => $label)
            <option value="{{ $value }}" @selected($selectedSeniorCitizensForumVisibility === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <small class="text-muted d-block mt-2">
        Public posts appear in listings. Registered Users and Senior Citizens Community require sign-in. Private Link posts are hidden from listings and only open with the share link.
    </small>
    <div id="seniorCitizensForumPrivateLinkInfo" class="alert alert-info py-2 px-3 small mt-3 mb-0" style="display:none;">
        @if($privateLinkUrl)
            <strong>Private link:</strong>
            <div class="input-group input-group-sm mt-2">
                <input type="text" class="form-control" id="seniorCitizensForumPrivateLinkUrl" value="{{ $privateLinkUrl }}" readonly>
                <button type="button" class="btn btn-outline-secondary" id="seniorCitizensForumCopyPrivateLinkBtn">Copy</button>
            </div>
        @else
            A private share link will be generated when this post is saved with Private Link visibility.
        @endif
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Community reactions</h5>
            <p class="text-muted mb-0 small">Positive reactions only — readers can respond supportively on the public page.</p>
        </div>
        <span class="badge bg-success text-white">Enabled</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::seniorCitizensForumReactionOptions() as $reaction => $icon)
            <span class="badge bg-light text-dark border">
                <i class="{{ $icon }} me-1" aria-hidden="true"></i>{{ $reaction }}
            </span>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Intergenerational connections</h5>
            <p class="text-muted mb-0 small">Unique SoilnWater feature — tag content so younger users can discover wisdom-based posts.</p>
        </div>
        <span class="badge bg-primary text-white">Unique feature</span>
    </div>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::seniorCitizensForumIntergenerationalConnections() as $connection)
            <div class="col-md-6">
                <label class="form-check border rounded py-2 px-3 bg-white h-100 mb-0">
                    <input
                        type="checkbox"
                        name="senior_citizens_forum_intergenerational_connections[]"
                        value="{{ $connection }}"
                        class="form-check-input"
                        @checked(in_array($connection, (array) $selectedIntergenerationalConnections, true))
                    >
                    <span class="form-check-label">{{ $connection }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Digital legacy option</h5>
            <p class="text-muted mb-0 small">Preserve your story as a lasting digital legacy for family and future generations.</p>
        </div>
        <span class="badge bg-danger text-white">Very unique</span>
    </div>
    <label class="form-check border rounded p-3 bg-light mb-3" for="seniorCitizensForumPreserveDigitalLegacy">
        <input
            type="checkbox"
            name="senior_citizens_forum_preserve_digital_legacy"
            value="1"
            class="form-check-input"
            id="seniorCitizensForumPreserveDigitalLegacy"
            @checked($preserveDigitalLegacy)
        >
        <span class="form-check-label fw-semibold">Preserve as Digital Legacy</span>
    </label>
    <p class="small text-muted mb-2">Benefits:</p>
    <ul class="small text-muted mb-0 ps-3">
        @foreach(\App\Support\CommunityContentTaxonomy::seniorCitizensForumDigitalLegacyBenefits() as $benefit)
            <li>{{ $benefit }}</li>
        @endforeach
    </ul>
</div>
