<div class="news-flow-card story-flow-card story-flow-card--cover border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Featured image</h5>
            <p class="text-muted mb-0 small">Large cover image — required for Creative Corner posts.</p>
        </div>
        <span class="badge bg-danger text-white">Required</span>
    </div>
    <div id="communityCreativeCornerFeaturedImagesSlot"></div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Gallery</h5>
            <p class="text-muted mb-0 small">Multiple upload. Examples: {{ implode(', ', \App\Support\CommunityContentTaxonomy::creativeCornerGalleryExamples()) }}.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <label class="form-label" for="ccGalleryImages">Gallery images</label>
    <input type="file" name="creative_corner_gallery[]" id="ccGalleryImages" class="form-control creative-corner-flow-field" accept="image/*" multiple>
    <small class="text-muted d-block mt-2">Upload work in progress, final work, different angles, behind the scenes, or making process shots.</small>
    @if(!empty(data_get($post->meta, 'creative_corner_gallery')))
        <div class="mt-3 d-flex flex-column gap-2">
            @foreach(data_get($post->meta, 'creative_corner_gallery', []) as $image)
                <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                    <input type="checkbox" name="removed_creative_corner_gallery[]" value="{{ data_get($image, 'path') }}" class="form-check-input creative-corner-flow-field">
                    <span class="form-check-label">Remove {{ data_get($image, 'name', 'image') }}</span>
                </label>
            @endforeach
        </div>
    @endif
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Video section</h5>
            <p class="text-muted mb-0 small">Creative process, time-lapse, performance, tutorials, and more.</p>
        </div>
        <span class="badge bg-warning text-dark">Optional</span>
    </div>
    <label class="form-label" for="ccVideoType">Video type</label>
    <select name="creative_corner_video_type" id="ccVideoType" class="form-select creative-corner-flow-field mb-3">
        <option value="">Select video type (optional)</option>
        @foreach(\App\Support\CommunityContentTaxonomy::creativeCornerVideoExamples() as $videoType)
            <option value="{{ $videoType }}" @selected(old('creative_corner_video_type', data_get($post->meta, 'creative_corner_video_type')) === $videoType)>{{ $videoType }}</option>
        @endforeach
    </select>
    <div id="communityCreativeCornerVideoSlot"></div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Audio section</h5>
            <p class="text-muted mb-0 small">For songs, instrumental music, voice performance, and sound design.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <label class="form-label" for="ccAudioType">Audio type</label>
    <select name="creative_corner_audio_type" id="ccAudioType" class="form-select creative-corner-flow-field mb-3">
        <option value="">Select audio type (optional)</option>
        @foreach(\App\Support\CommunityContentTaxonomy::creativeCornerAudioExamples() as $audioType)
            <option value="{{ $audioType }}" @selected(old('creative_corner_audio_type', data_get($post->meta, 'creative_corner_audio_type')) === $audioType)>{{ $audioType }}</option>
        @endforeach
    </select>
    <label class="form-label" for="ccAudioFile">Upload audio</label>
    <input type="file" name="creative_corner_audio_file" id="ccAudioFile" class="form-control creative-corner-flow-field" accept="audio/mpeg,audio/mp3,audio/wav,audio/webm,audio/ogg,.mp3,.wav,.webm,.ogg">
    <small class="text-muted d-block mt-2">MP3 or other audio formats. Maximum size: 20 MB.</small>
    @if(filled(data_get($post->meta, 'creative_corner_audio')))
        <div class="mt-3 border rounded p-3 bg-white">
            <p class="small mb-2 fw-semibold">Current audio</p>
            <audio controls class="w-100" src="{{ data_get($post->meta, 'creative_corner_audio.url') }}"></audio>
            <label class="form-check mt-2 mb-0">
                <input type="checkbox" name="remove_creative_corner_audio" value="1" class="form-check-input creative-corner-flow-field" id="removeCreativeCornerAudio">
                <span class="form-check-label">Remove existing audio</span>
            </label>
            <input type="hidden" name="keep_existing_creative_corner_audio" id="keepExistingCreativeCornerAudio" value="1">
        </div>
    @endif
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Documents</h5>
            <p class="text-muted mb-0 small">PDF, PPT, DOC, design files, CAD drawings, project reports, or portfolios.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div class="mb-3">
        <label class="form-label d-block">Document types included</label>
        <div class="d-flex flex-wrap gap-2">
            @foreach(\App\Support\CommunityContentTaxonomy::creativeCornerDocumentTypes() as $docType)
                <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                    <input type="checkbox" name="creative_corner_document_types[]" value="{{ $docType }}" class="form-check-input creative-corner-flow-field" @checked(in_array($docType, (array) old('creative_corner_document_types', data_get($post->meta, 'creative_corner_document_types', [])), true))>
                    <span class="form-check-label">{{ $docType }}</span>
                </label>
            @endforeach
        </div>
    </div>
    <label class="form-label" for="ccDocuments">Upload documents</label>
    <input type="file" name="creative_corner_documents[]" id="ccDocuments" class="form-control creative-corner-flow-field" accept=".pdf,.doc,.docx,.ppt,.pptx,application/pdf" multiple>
    <small class="text-muted d-block mt-2">Up to 8 files, max 20 MB each.</small>
    @if(!empty(data_get($post->meta, 'creative_corner_documents')))
        <div class="mt-3 d-flex flex-column gap-2">
            @foreach(data_get($post->meta, 'creative_corner_documents', []) as $document)
                <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                    <input type="checkbox" name="removed_creative_corner_documents[]" value="{{ data_get($document, 'path') }}" class="form-check-input creative-corner-flow-field">
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
            <p class="text-muted mb-0 small">Maximum 10 tags. Examples: {{ implode(', ', \App\Support\CommunityContentTaxonomy::creativeCornerTagExamples()) }}.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div id="communityCreativeCornerTagsSlot"></div>
</div>
