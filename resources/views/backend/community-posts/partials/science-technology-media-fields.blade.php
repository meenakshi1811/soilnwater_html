<div class="news-flow-card story-flow-card story-flow-card--cover border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Featured image</h5>
            <p class="text-muted mb-0 small">Used in listings, sharing, and the innovation showcase.</p>
        </div>
        <span class="badge bg-danger text-white">Recommended</span>
    </div>
    <div id="communityScienceTechnologyFeaturedImagesSlot"></div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Image gallery</h5>
            <p class="text-muted mb-0 small">Upload prototypes, circuits, graphs, models, lab photos, and more.</p>
        </div>
        <span class="badge bg-info text-dark">Gallery</span>
    </div>
    @foreach(\App\Support\CommunityContentTaxonomy::scienceTechnologyGalleryCategories() as $galleryKey => $galleryLabel)
        @php
            $inputId = 'stGallery' . \Illuminate\Support\Str::studly($galleryKey);
            $existingGallery = data_get($post->meta, 'science_technology_gallery.'.$galleryKey, []);
        @endphp
        <div class="mb-3">
            <label class="form-label" for="{{ $inputId }}">{{ $galleryLabel }}</label>
            <input type="file" name="science_technology_gallery_{{ $galleryKey }}[]" id="{{ $inputId }}" class="form-control science-technology-flow-field" accept="image/*" multiple>
            @if(!empty($existingGallery))
                <div class="mt-2 d-flex flex-column gap-2">
                    @foreach($existingGallery as $photo)
                        <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                            <input type="checkbox" name="removed_science_technology_gallery_{{ $galleryKey }}[]" value="{{ data_get($photo, 'path') }}" class="form-check-input science-technology-flow-field">
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
            <p class="text-muted mb-0 small">Project demos, tutorials, experiments, product demos, and research presentations.</p>
        </div>
        <span class="badge bg-warning text-dark">Recommended</span>
    </div>
    <label class="form-label" for="stVideoType">Video type</label>
    <select name="science_technology_video_type" id="stVideoType" class="form-select science-technology-flow-field mb-3">
        <option value="">Select video type (optional)</option>
        @foreach(\App\Support\CommunityContentTaxonomy::scienceTechnologyVideoExamples() as $videoType)
            <option value="{{ $videoType }}" @selected(old('science_technology_video_type', data_get($post->meta, 'science_technology_video_type')) === $videoType)>{{ $videoType }}</option>
        @endforeach
    </select>
    <div id="communityScienceTechnologyVideoSlot"></div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Document upload</h5>
            <p class="text-muted mb-0 small">Research papers, datasheets, reports — PDF, DOC, PPT, XLS supported.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <label class="form-label" for="stDocuments">Upload documents</label>
    <input type="file" name="science_technology_documents[]" id="stDocuments" class="form-control science-technology-flow-field" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" multiple>
    <small class="text-muted d-block mt-2">Up to 8 files, max 20 MB each.</small>
    @if(!empty(data_get($post->meta, 'science_technology_documents')))
        <div class="mt-3 d-flex flex-column gap-2">
            @foreach(data_get($post->meta, 'science_technology_documents', []) as $document)
                <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                    <input type="checkbox" name="removed_science_technology_documents[]" value="{{ data_get($document, 'path') }}" class="form-check-input science-technology-flow-field">
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
            <p class="text-muted mb-0 small">Maximum 10 tags. Examples: {{ implode(', ', \App\Support\CommunityContentTaxonomy::scienceTechnologyTagExamples()) }}.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div id="communityScienceTechnologyTagsSlot"></div>
</div>
