<div class="news-flow-card story-flow-card story-flow-card--cover border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Featured image</h5>
            <p class="text-muted mb-0 small">Used in listing cards, social sharing, and homepage.</p>
        </div>
        <span class="badge bg-warning text-dark">Recommended</span>
    </div>
    <div id="communityCommunityIssuesFeaturedImagesSlot"></div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-danger-subtle mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Evidence upload</h5>
            <p class="text-muted mb-0 small">Highly important — upload photos that show the issue clearly.</p>
        </div>
        <span class="badge bg-danger text-white">Highly important</span>
    </div>
    <p class="small text-muted mb-3">Examples: {{ implode(', ', \App\Support\CommunityContentTaxonomy::communityIssuePhotoEvidenceExamples()) }}.</p>
    <label class="form-label" for="communityIssuePhotoEvidence">Images (multiple upload)</label>
    <input type="file" name="community_issue_photo_evidence[]" id="communityIssuePhotoEvidence" class="form-control community-issues-flow-field" accept="image/*" multiple>
    <small class="text-muted d-block mt-2">JPG, PNG, WebP, or GIF. Up to 10 images, max 4 MB each.</small>
    @if(!empty(data_get($post->meta, 'community_issue_photo_evidence')))
        <div class="mt-3 d-flex flex-column gap-2">
            @foreach(data_get($post->meta, 'community_issue_photo_evidence', []) as $photo)
                <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                    <input type="checkbox" name="removed_community_issue_photo_evidence[]" value="{{ data_get($photo, 'path') }}" class="form-check-input community-issues-flow-field">
                    <span class="form-check-label">Remove {{ data_get($photo, 'name', 'photo') }}</span>
                </label>
            @endforeach
        </div>
    @endif
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Videos</h5>
            <p class="text-muted mb-0 small">Upload a video or add a video link as evidence.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div id="communityCommunityIssuesVideoSlot"></div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Documents</h5>
            <p class="text-muted mb-0 small">Complaint letters, government notices, RTI responses, or survey reports.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <label class="form-label" for="communityIssueDocuments">Upload documents</label>
    <input
        type="file"
        name="community_issue_documents[]"
        id="communityIssueDocuments"
        class="form-control community-issues-flow-field"
        accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
        multiple
    >
    <small class="text-muted d-block mt-2">
        PDF, DOC, or DOCX. Examples: {{ implode(', ', \App\Support\CommunityContentTaxonomy::communityIssueDocumentExamples()) }}. Up to 6 files, max 20 MB each.
    </small>
    @if(!empty(data_get($post->meta, 'community_issue_documents')))
        <div class="mt-3 d-flex flex-column gap-2">
            @foreach(data_get($post->meta, 'community_issue_documents', []) as $document)
                <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                    <input type="checkbox" name="removed_community_issue_documents[]" value="{{ data_get($document, 'path') }}" class="form-check-input community-issues-flow-field">
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
            <p class="text-muted mb-0 small">Maximum 10 tags. Examples: {{ implode(', ', \App\Support\CommunityContentTaxonomy::communityIssueTagExamples()) }}.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div id="communityCommunityIssuesTagsSlot"></div>
</div>
