<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Documents</h5>
            <p class="text-muted mb-0 small">Upload supporting files such as resumes, presentations, reports, or portfolios.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <label class="form-label" for="youthCornerDocuments">Upload documents</label>
    <input
        type="file"
        name="youth_corner_documents[]"
        id="youthCornerDocuments"
        class="form-control youth-corner-flow-field"
        accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/zip"
        multiple
    >
    <small class="text-muted d-block mt-2">
        Allowed: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, ZIP. Up to 6 files, max 20 MB each.
    </small>
    @if(!empty(data_get($post->meta, 'youth_corner_documents')))
        <div class="mt-3 d-flex flex-column gap-2">
            @foreach(data_get($post->meta, 'youth_corner_documents', []) as $document)
                <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                    <input type="checkbox" name="removed_youth_corner_documents[]" value="{{ data_get($document, 'path') }}" class="form-check-input youth-corner-flow-field">
                    <span class="form-check-label d-flex align-items-center gap-2">
                        <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
                        Remove {{ data_get($document, 'name', 'document') }}
                    </span>
                </label>
            @endforeach
        </div>
    @endif
</div>
