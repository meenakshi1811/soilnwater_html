<div class="news-flow-card story-flow-card story-flow-card--cover border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Featured image</h5>
            <p class="text-muted mb-0 small">Used in listing cards, social sharing, and homepage.</p>
        </div>
        <span class="badge bg-warning text-dark">Recommended</span>
    </div>
    <ul class="small text-muted mb-3">
        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Listing cards</li>
        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Social sharing</li>
        <li><i class="fa-solid fa-check text-success me-1" aria-hidden="true"></i>Homepage</li>
    </ul>
    <div id="communityLocalVoicesFeaturedImagesSlot"></div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Photo evidence</h5>
            <p class="text-muted mb-0 small">Highly recommended — multiple uploads to support your local voice.</p>
        </div>
        <span class="badge bg-info text-dark">Highly recommended</span>
    </div>
    <p class="small text-muted mb-3">Examples: {{ implode(', ', \App\Support\CommunityContentTaxonomy::localVoicePhotoEvidenceExamples()) }}.</p>
    <label class="form-label" for="localVoicePhotoEvidence">Upload photos</label>
    <input type="file" name="local_voice_photo_evidence[]" id="localVoicePhotoEvidence" class="form-control local-voices-flow-field" accept="image/*" multiple>
    <small class="text-muted d-block mt-2">JPG, PNG, WebP, or GIF. Up to 10 images, max 4 MB each.</small>
    @if(!empty(data_get($post->meta, 'local_voice_photo_evidence')))
        <div class="mt-3 d-flex flex-column gap-2">
            @foreach(data_get($post->meta, 'local_voice_photo_evidence', []) as $photo)
                <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                    <input type="checkbox" name="removed_local_voice_photo_evidence[]" value="{{ data_get($photo, 'path') }}" class="form-check-input local-voices-flow-field">
                    <span class="form-check-label">Remove {{ data_get($photo, 'name', 'photo') }}</span>
                </label>
            @endforeach
        </div>
    @endif
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Video evidence</h5>
            <p class="text-muted mb-0 small">Optional video evidence or community footage.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <label class="form-label" for="localVoiceVideoType">Video type</label>
    <select name="local_voice_video_type" id="localVoiceVideoType" class="form-select local-voices-flow-field mb-3">
        <option value="">Select video type (optional)</option>
        @foreach(\App\Support\CommunityContentTaxonomy::localVoiceVideoTypes() as $videoType)
            <option value="{{ $videoType }}" @selected(old('local_voice_video_type', data_get($post->meta, 'local_voice_video_type')) === $videoType)>{{ $videoType }}</option>
        @endforeach
    </select>
    <small class="text-muted d-block mb-3">Useful for: {{ implode(', ', \App\Support\CommunityContentTaxonomy::localVoiceVideoTypes()) }}.</small>
    <div id="communityLocalVoicesVideoSlot"></div>
</div>

@include('backend.community-posts.partials.local-voices-documents-fields', ['post' => $post])

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Tags</h5>
            <p class="text-muted mb-0 small">Maximum 10 tags. Examples: {{ implode(', ', \App\Support\CommunityContentTaxonomy::localVoiceTagExamples()) }}.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div id="communityLocalVoicesTagsSlot"></div>
</div>
