@php
    $selectedWomensWorldVisibility = old(
        'womens_world_visibility',
        data_get($post->meta, 'womens_world_visibility', \App\Support\CommunityContentTaxonomy::womensWorldDefaultVisibilitySetting())
    );
    $selectedPublishAs = old('publish_as', $post->publish_as ?: \App\Models\CommunityPost::PUBLISH_AS_PUBLIC_PROFILE);
    $privateLinkToken = data_get($post->meta, 'womens_world_private_link_token');
    $privateLinkUrl = $post->exists && filled($privateLinkToken) && $selectedWomensWorldVisibility === 'private_link'
        ? $post->womensWorldPrivateLinkUrl()
        : null;
@endphp

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-warning-subtle mb-3" id="womensWorldPrivacyWrap">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Privacy options</h5>
            <p class="text-muted mb-0 small">Many women may prefer anonymous posting for sensitive topics.</p>
        </div>
        <span class="badge bg-warning text-dark">Very important</span>
    </div>

    <div class="border rounded-3 p-3 bg-white mb-3" id="womensWorldPublishAsWrap">
        <label class="form-label mb-2">Publish as</label>
        <div class="d-flex flex-wrap gap-2">
            @foreach(\App\Support\CommunityContentTaxonomy::womensWorldPublishAsOptions() as $value => $label)
                <label class="form-check border rounded py-2 px-3 bg-light mb-0" for="womensWorldPublishAs{{ \Illuminate\Support\Str::studly($value) }}">
                    <input
                        type="radio"
                        class="form-check-input"
                        name="publish_as"
                        id="womensWorldPublishAs{{ \Illuminate\Support\Str::studly($value) }}"
                        value="{{ $value }}"
                        @checked($selectedPublishAs === $value)
                    >
                    <span class="form-check-label">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        <div class="mt-3" id="womensWorldPenNameWrap" style="display:none;">
            <label class="form-label" for="womensWorldPenNameInput">Pen name <span class="text-danger">*</span></label>
            <input
                type="text"
                name="pen_name"
                id="womensWorldPenNameInput"
                class="form-control"
                value="{{ old('pen_name', $post->pen_name) }}"
                maxlength="120"
                placeholder="Enter the pen name readers will see"
            >
            <small class="text-muted">This name is shown instead of your real name.</small>
        </div>
    </div>

    <div class="border rounded-3 p-3 bg-white">
        <label class="form-label mb-2" for="womensWorldVisibility">Visibility</label>
        <select name="womens_world_visibility" id="womensWorldVisibility" class="form-select womens-world-required" required>
            @foreach(\App\Support\CommunityContentTaxonomy::womensWorldVisibilitySettings() as $value => $label)
                <option value="{{ $value }}" @selected($selectedWomensWorldVisibility === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <small class="text-muted d-block mt-2">
            Public posts appear in listings. Registered Users and Women Community Only require sign-in. Private Link posts are hidden from listings and only open with the share link.
        </small>
        <div id="womensWorldPrivateLinkInfo" class="alert alert-info py-2 px-3 small mt-3 mb-0" style="display:none;">
            @if($privateLinkUrl)
                <strong>Private link:</strong>
                <div class="input-group input-group-sm mt-2">
                    <input type="text" class="form-control" id="womensWorldPrivateLinkUrl" value="{{ $privateLinkUrl }}" readonly>
                    <button type="button" class="btn btn-outline-secondary" id="womensWorldCopyPrivateLinkBtn">Copy</button>
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
            <p class="text-muted mb-0 small">Positive reactions only — readers can respond supportively on the public page.</p>
        </div>
        <span class="badge bg-success text-white">Enabled</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::womensWorldReactionOptions() as $reaction => $icon)
            <span class="badge bg-light text-dark border">
                <i class="{{ $icon }} me-1" aria-hidden="true"></i>{{ $reaction }}
            </span>
        @endforeach
    </div>
</div>
