<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Documents</h5>
            <p class="text-muted mb-0 small">Optional supporting documents.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <label class="form-label" for="localVoiceDocuments">Upload documents</label>
    <input
        type="file"
        name="local_voice_documents[]"
        id="localVoiceDocuments"
        class="form-control local-voices-flow-field"
        accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
        multiple
    >
    <small class="text-muted d-block mt-2">
        PDF, DOC, or DOCX. Examples: Complaint Letters, Government Notices, RTI Responses, Community Reports. Up to 6 files, max 20 MB each.
    </small>
    @if(!empty(data_get($post->meta, 'local_voice_documents')))
        <div class="mt-3 d-flex flex-column gap-2">
            @foreach(data_get($post->meta, 'local_voice_documents', []) as $document)
                <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                    <input type="checkbox" name="removed_local_voice_documents[]" value="{{ data_get($document, 'path') }}" class="form-check-input local-voices-flow-field">
                    <span class="form-check-label d-flex align-items-center gap-2">
                        <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
                        Remove {{ data_get($document, 'name', 'document') }}
                    </span>
                </label>
            @endforeach
        </div>
    @endif
</div>
