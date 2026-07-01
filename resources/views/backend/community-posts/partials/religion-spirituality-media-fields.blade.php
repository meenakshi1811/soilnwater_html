<div class="news-flow-card story-flow-card story-flow-card--cover border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Image gallery</h5>
            <p class="text-muted mb-0 small">Examples: {{ implode(', ', \App\Support\CommunityContentTaxonomy::religionSpiritualityImageExamples()) }}.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div id="communityReligionSpiritualityFeaturedImagesSlot"></div>
    <label class="form-label mt-3" for="rsGalleryImages">Additional gallery images</label>
    <input type="file" name="religion_spirituality_gallery[]" id="rsGalleryImages" class="form-control religion-spirituality-flow-field" accept="image/*" multiple>
    <small class="text-muted d-block mt-2">Upload extra images for festivals, architecture, pilgrimage, or cultural events.</small>
    @if(!empty(data_get($post->meta, 'religion_spirituality_gallery')))
        <div class="mt-3 d-flex flex-column gap-2">
            @foreach(data_get($post->meta, 'religion_spirituality_gallery', []) as $image)
                <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                    <input type="checkbox" name="removed_religion_spirituality_gallery[]" value="{{ data_get($image, 'path') }}" class="form-check-input religion-spirituality-flow-field">
                    <span class="form-check-label">Remove {{ data_get($image, 'name', 'image') }}</span>
                </label>
            @endforeach
        </div>
    @endif
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Video section</h5>
            <p class="text-muted mb-0 small">Religious lectures, meditation sessions, festival celebrations, and more.</p>
        </div>
        <span class="badge bg-warning text-dark">Optional</span>
    </div>
    <label class="form-label" for="rsVideoType">Video type</label>
    <select name="religion_spirituality_video_type" id="rsVideoType" class="form-select religion-spirituality-flow-field mb-3">
        <option value="">Select video type (optional)</option>
        @foreach(\App\Support\CommunityContentTaxonomy::religionSpiritualityVideoExamples() as $videoType)
            <option value="{{ $videoType }}" @selected(old('religion_spirituality_video_type', data_get($post->meta, 'religion_spirituality_video_type')) === $videoType)>{{ $videoType }}</option>
        @endforeach
    </select>
    <div id="communityReligionSpiritualityVideoSlot"></div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Audio section</h5>
            <p class="text-muted mb-0 small">Examples: {{ implode(', ', \App\Support\CommunityContentTaxonomy::religionSpiritualityAudioExamples()) }}.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <label class="form-label" for="rsAudioType">Audio type</label>
    <select name="religion_spirituality_audio_type" id="rsAudioType" class="form-select religion-spirituality-flow-field mb-3">
        <option value="">Select audio type (optional)</option>
        @foreach(\App\Support\CommunityContentTaxonomy::religionSpiritualityAudioExamples() as $audioType)
            <option value="{{ $audioType }}" @selected(old('religion_spirituality_audio_type', data_get($post->meta, 'religion_spirituality_audio_type')) === $audioType)>{{ $audioType }}</option>
        @endforeach
    </select>
    <label class="form-label" for="rsAudioFile">Upload audio</label>
    <input type="file" name="religion_spirituality_audio_file" id="rsAudioFile" class="form-control religion-spirituality-flow-field" accept="audio/mpeg,audio/mp3,audio/wav,audio/webm,audio/ogg,.mp3,.wav,.webm,.ogg">
    <small class="text-muted d-block mt-2">MP3 or other audio formats. Maximum size: 20 MB.</small>
    @if(filled(data_get($post->meta, 'religion_spirituality_audio')))
        <div class="mt-3 border rounded p-3 bg-light">
            <p class="small mb-2 fw-semibold">Current audio</p>
            <audio controls class="w-100" src="{{ data_get($post->meta, 'religion_spirituality_audio.url') }}"></audio>
            <label class="form-check mt-2 mb-0">
                <input type="checkbox" name="remove_religion_spirituality_audio" value="1" class="form-check-input religion-spirituality-flow-field" id="removeReligionSpiritualityAudio">
                <span class="form-check-label">Remove existing audio</span>
            </label>
            <input type="hidden" name="keep_existing_religion_spirituality_audio" id="keepExistingReligionSpiritualityAudio" value="1">
        </div>
    @endif
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Documents</h5>
            <p class="text-muted mb-0 small">Upload PDFs, books, research papers, historical documents, or presentations.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div class="mb-3">
        <label class="form-label d-block">Document types included</label>
        <div class="d-flex flex-wrap gap-2">
            @foreach(\App\Support\CommunityContentTaxonomy::religionSpiritualityDocumentTypes() as $docType)
                <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                    <input type="checkbox" name="religion_spirituality_document_types[]" value="{{ $docType }}" class="form-check-input religion-spirituality-flow-field" @checked(in_array($docType, (array) old('religion_spirituality_document_types', data_get($post->meta, 'religion_spirituality_document_types', [])), true))>
                    <span class="form-check-label">{{ $docType }}</span>
                </label>
            @endforeach
        </div>
    </div>
    <label class="form-label" for="rsDocuments">Upload documents</label>
    <input type="file" name="religion_spirituality_documents[]" id="rsDocuments" class="form-control religion-spirituality-flow-field" accept=".pdf,.doc,.docx,.ppt,.pptx,application/pdf" multiple>
    <small class="text-muted d-block mt-2">Up to 8 files, max 20 MB each.</small>
    @if(!empty(data_get($post->meta, 'religion_spirituality_documents')))
        <div class="mt-3 d-flex flex-column gap-2">
            @foreach(data_get($post->meta, 'religion_spirituality_documents', []) as $document)
                <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                    <input type="checkbox" name="removed_religion_spirituality_documents[]" value="{{ data_get($document, 'path') }}" class="form-check-input religion-spirituality-flow-field">
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
            <p class="text-muted mb-0 small">Maximum 10 tags. Examples: {{ implode(', ', \App\Support\CommunityContentTaxonomy::religionSpiritualityTagExamples()) }}.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div id="communityReligionSpiritualityTagsSlot"></div>
</div>
