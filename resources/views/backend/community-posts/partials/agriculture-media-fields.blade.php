<div class="news-flow-card story-flow-card story-flow-card--cover border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Featured image</h5>
            <p class="text-muted mb-0 small">Used in listing cards, social sharing, and homepage.</p>
        </div>
        <span class="badge bg-warning text-dark">Recommended</span>
    </div>
    <div id="communityAgricultureFeaturedImagesSlot"></div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Image gallery</h5>
            <p class="text-muted mb-0 small">Upload farm photos by category — crop stages, equipment, irrigation, and harvest.</p>
        </div>
        <span class="badge bg-info text-dark">Gallery</span>
    </div>
    @foreach(\App\Support\CommunityContentTaxonomy::agricultureGalleryCategories() as $galleryKey => $galleryLabel)
        @php
            $inputId = 'agricultureGallery' . \Illuminate\Support\Str::studly($galleryKey);
            $existingGallery = data_get($post->meta, 'agriculture_gallery.'.$galleryKey, []);
        @endphp
        <div class="mb-3">
            <label class="form-label" for="{{ $inputId }}">{{ $galleryLabel }}</label>
            <input type="file" name="agriculture_gallery_{{ $galleryKey }}[]" id="{{ $inputId }}" class="form-control agriculture-flow-field" accept="image/*" multiple>
            @if(!empty($existingGallery))
                <div class="mt-2 d-flex flex-column gap-2">
                    @foreach($existingGallery as $photo)
                        <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                            <input type="checkbox" name="removed_agriculture_gallery_{{ $galleryKey }}[]" value="{{ data_get($photo, 'path') }}" class="form-check-input agriculture-flow-field">
                            <span class="form-check-label">Remove {{ data_get($photo, 'name', 'photo') }}</span>
                        </label>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach
    <small class="text-muted">Up to 6 images per category, max 4 MB each.</small>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Video section</h5>
            <p class="text-muted mb-0 small">Highly recommended — field demonstrations, farm tours, and irrigation setups.</p>
        </div>
        <span class="badge bg-warning text-dark">Highly recommended</span>
    </div>
    <p class="small text-muted mb-3">Examples: {{ implode(', ', \App\Support\CommunityContentTaxonomy::agricultureVideoExamples()) }}.</p>
    <label class="form-label" for="agricultureVideoType">Video type</label>
    <select name="agriculture_video_type" id="agricultureVideoType" class="form-select agriculture-flow-field mb-3">
        <option value="">Select video type (optional)</option>
        @foreach(\App\Support\CommunityContentTaxonomy::agricultureVideoExamples() as $videoType)
            <option value="{{ $videoType }}" @selected(old('agriculture_video_type', data_get($post->meta, 'agriculture_video_type')) === $videoType)>{{ $videoType }}</option>
        @endforeach
    </select>
    <div id="communityAgricultureVideoSlot"></div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Document upload</h5>
            <p class="text-muted mb-0 small">Supported: PDF, DOC, DOCX, PPT, XLS.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <p class="small text-muted mb-3">Examples: {{ implode(', ', \App\Support\CommunityContentTaxonomy::agricultureDocumentExamples()) }}.</p>
    <label class="form-label" for="agricultureDocuments">Upload documents</label>
    <input
        type="file"
        name="agriculture_documents[]"
        id="agricultureDocuments"
        class="form-control agriculture-flow-field"
        accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
        multiple
    >
    <small class="text-muted d-block mt-2">Up to 8 files, max 20 MB each.</small>
    @if(!empty(data_get($post->meta, 'agriculture_documents')))
        <div class="mt-3 d-flex flex-column gap-2">
            @foreach(data_get($post->meta, 'agriculture_documents', []) as $document)
                <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                    <input type="checkbox" name="removed_agriculture_documents[]" value="{{ data_get($document, 'path') }}" class="form-check-input agriculture-flow-field">
                    <span class="form-check-label d-flex align-items-center gap-2">
                        <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
                        Remove {{ data_get($document, 'name', 'document') }}
                    </span>
                </label>
            @endforeach
        </div>
    @endif
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Tags</h5>
            <p class="text-muted mb-0 small">Maximum 10 tags. Examples: {{ implode(', ', \App\Support\CommunityContentTaxonomy::agricultureTagExamples()) }}.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div id="communityAgricultureTagsSlot"></div>
</div>
