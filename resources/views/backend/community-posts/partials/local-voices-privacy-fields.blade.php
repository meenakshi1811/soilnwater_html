@php
    $selectedVisibility = old(
        'local_voice_visibility',
        data_get($post->meta, 'local_voice_visibility', \App\Support\CommunityContentTaxonomy::localVoiceDefaultVisibilitySetting())
    );
    $selectedPublishAs = old('publish_as', $post->publish_as ?: \App\Models\CommunityPost::PUBLISH_AS_PUBLIC_PROFILE);
    $privateLinkToken = data_get($post->meta, 'local_voice_private_link_token');
    $privateLinkUrl = $post->exists && filled($privateLinkToken) && $selectedVisibility === 'private_link'
        ? $post->localVoicePrivateLinkUrl()
        : null;
@endphp

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-warning-subtle mb-3" id="localVoicePrivacyWrap">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Privacy settings</h5>
            <p class="text-muted mb-0 small">For complaints and civic issues, anonymous posting may encourage participation.</p>
        </div>
        <span class="badge bg-warning text-dark">Very important</span>
    </div>

    <div class="border rounded-3 p-3 bg-white mb-3" id="localVoicePublishAsWrap">
        <label class="form-label mb-2">Publish as</label>
        <div class="d-flex flex-wrap gap-2">
            @foreach(\App\Support\CommunityContentTaxonomy::localVoicePublishAsOptions() as $value => $label)
                <label class="form-check border rounded py-2 px-3 bg-light mb-0" for="localVoicePublishAs{{ \Illuminate\Support\Str::studly($value) }}">
                    <input
                        type="radio"
                        class="form-check-input local-voices-flow-field"
                        name="publish_as"
                        id="localVoicePublishAs{{ \Illuminate\Support\Str::studly($value) }}"
                        value="{{ $value }}"
                        @checked($selectedPublishAs === $value)
                    >
                    <span class="form-check-label">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        <div class="mt-3" id="localVoicePenNameWrap" style="display:none;">
            <label class="form-label" for="localVoicePenNameInput">Pen name <span class="text-danger">*</span></label>
            <input
                type="text"
                name="pen_name"
                id="localVoicePenNameInput"
                class="form-control local-voices-flow-field"
                value="{{ old('pen_name', $post->pen_name) }}"
                maxlength="120"
                placeholder="Enter the pen name readers will see"
            >
        </div>
    </div>

    <div class="border rounded-3 p-3 bg-white">
        <label class="form-label mb-2" for="localVoiceVisibility">Visibility</label>
        <select name="local_voice_visibility" id="localVoiceVisibility" class="form-select local-voices-required local-voices-flow-field" required>
            @foreach(\App\Support\CommunityContentTaxonomy::localVoiceVisibilitySettings() as $value => $label)
                <option value="{{ $value }}" @selected($selectedVisibility === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <div id="localVoicePrivateLinkInfo" class="alert alert-info py-2 px-3 small mt-3 mb-0" style="display:none;">
            @if($privateLinkUrl)
                <strong>Private link:</strong>
                <div class="input-group input-group-sm mt-2">
                    <input type="text" class="form-control" id="localVoicePrivateLinkUrl" value="{{ $privateLinkUrl }}" readonly>
                    <button type="button" class="btn btn-outline-secondary" id="localVoiceCopyPrivateLinkBtn">Copy</button>
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
            <p class="text-muted mb-0 small">Meaningful reactions readers can use on the public page.</p>
        </div>
        <span class="badge bg-success text-white">Enabled</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::localVoiceReactionOptions() as $reaction => $icon)
            <span class="badge bg-light text-dark border">
                <i class="{{ $icon }} me-1" aria-hidden="true"></i>{{ $reaction }}
            </span>
        @endforeach
    </div>
</div>
