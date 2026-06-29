<div class="news-flow-card story-flow-card story-flow-card--cover border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Featured image</h5>
            <p class="text-muted mb-0 small">Examples: {{ implode(', ', \App\Support\CommunityContentTaxonomy::astroConsultancyImageExamples()) }}.</p>
        </div>
        <span class="badge bg-danger text-white">Recommended</span>
    </div>
    <div id="communityAstroConsultancyFeaturedImagesSlot"></div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Video section</h5>
            <p class="text-muted mb-0 small">Educational lectures, horoscope readings, meditation sessions, and more.</p>
        </div>
        <span class="badge bg-warning text-dark">Optional</span>
    </div>
    <label class="form-label" for="astroVideoType">Video type</label>
    <select name="astro_consultancy_video_type" id="astroVideoType" class="form-select astro-consultancy-flow-field mb-3">
        <option value="">Select video type (optional)</option>
        @foreach(\App\Support\CommunityContentTaxonomy::astroConsultancyVideoExamples() as $videoType)
            <option value="{{ $videoType }}" @selected(old('astro_consultancy_video_type', data_get($post->meta, 'astro_consultancy_video_type')) === $videoType)>{{ $videoType }}</option>
        @endforeach
    </select>
    <div id="communityAstroConsultancyVideoSlot"></div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Documents</h5>
            <p class="text-muted mb-0 small">Upload PDFs, research papers, books, presentations, or charts.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div class="mb-3">
        <label class="form-label d-block">Document types included</label>
        <div class="d-flex flex-wrap gap-2">
            @foreach(\App\Support\CommunityContentTaxonomy::astroConsultancyDocumentTypes() as $docType)
                <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                    <input type="checkbox" name="astro_consultancy_document_types[]" value="{{ $docType }}" class="form-check-input astro-consultancy-flow-field" @checked(in_array($docType, (array) old('astro_consultancy_document_types', data_get($post->meta, 'astro_consultancy_document_types', [])), true))>
                    <span class="form-check-label">{{ $docType }}</span>
                </label>
            @endforeach
        </div>
    </div>
    <label class="form-label" for="astroDocuments">Upload documents</label>
    <input type="file" name="astro_consultancy_documents[]" id="astroDocuments" class="form-control astro-consultancy-flow-field" accept=".pdf,.doc,.docx,.ppt,.pptx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation" multiple>
    <small class="text-muted d-block mt-2">Up to 8 files, max 20 MB each.</small>
    @if(!empty(data_get($post->meta, 'astro_consultancy_documents')))
        <div class="mt-3 d-flex flex-column gap-2">
            @foreach(data_get($post->meta, 'astro_consultancy_documents', []) as $document)
                <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                    <input type="checkbox" name="removed_astro_consultancy_documents[]" value="{{ data_get($document, 'path') }}" class="form-check-input astro-consultancy-flow-field">
                    <span class="form-check-label d-flex align-items-center gap-2">
                        <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
                        Remove {{ data_get($document, 'name', 'document') }}
                    </span>
                </label>
            @endforeach
        </div>
    @endif
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Tags</h5>
            <p class="text-muted mb-0 small">Maximum 10 tags. Examples: {{ implode(', ', \App\Support\CommunityContentTaxonomy::astroConsultancyTagExamples()) }}.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div id="communityAstroConsultancyTagsSlot"></div>
</div>
