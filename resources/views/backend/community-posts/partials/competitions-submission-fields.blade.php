<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Submission type</h5>
            <p class="text-muted mb-0 small">What can participants submit? Select all that apply.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Multiple selection</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::competitionsSubmissionTypes() as $type)
            <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                <input type="checkbox" name="competitions_submission_types[]" value="{{ $type }}" class="form-check-input competitions-flow-field" @checked(in_array($type, (array) old('competitions_submission_types', data_get($post->meta, 'competitions_submission_types', [])), true))>
                <span class="form-check-label">{{ $type }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">File limits</h5>
            <p class="text-muted mb-0 small">Admin-configurable upload restrictions for participants.</p>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label" for="competitionsMaxFiles">Maximum files</label>
            <input type="number" name="competitions_max_files" id="competitionsMaxFiles" class="form-control competitions-flow-field" min="1" max="50" placeholder="e.g. 5" value="{{ old('competitions_max_files', data_get($post->meta, 'competitions_max_files')) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="competitionsMaxFileSize">Maximum size</label>
            <input type="text" name="competitions_max_file_size" id="competitionsMaxFileSize" class="form-control competitions-flow-field" maxlength="40" placeholder="e.g. 10 MB" value="{{ old('competitions_max_file_size', data_get($post->meta, 'competitions_max_file_size')) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="competitionsAllowedFormats">Allowed formats</label>
            <input type="text" name="competitions_allowed_formats" id="competitionsAllowedFormats" class="form-control competitions-flow-field" maxlength="255" placeholder="e.g. JPG, PNG, PDF, MP4" value="{{ old('competitions_allowed_formats', data_get($post->meta, 'competitions_allowed_formats')) }}">
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Location</h5>
            <p class="text-muted mb-0 small">Competition level or geographic scope.</p>
        </div>
    </div>
    <label class="form-label" for="competitionsLevel">Competition level</label>
    <select name="competitions_level" id="competitionsLevel" class="form-select competitions-flow-field">
        <option value="">Select level (optional)</option>
        @foreach(\App\Support\CommunityContentTaxonomy::competitionsLevels() as $level)
            <option value="{{ $level }}" @selected(old('competitions_level', data_get($post->meta, 'competitions_level')) === $level)>{{ $level }}</option>
        @endforeach
    </select>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Entry details</h5>
            <p class="text-muted mb-0 small">What participants will upload when submitting entries.</p>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::competitionsEntryFields() as $field)
            <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                <input type="checkbox" name="competitions_entry_fields[]" value="{{ $field }}" class="form-check-input competitions-flow-field" @checked(in_array($field, (array) old('competitions_entry_fields', data_get($post->meta, 'competitions_entry_fields', [])), true))>
                <span class="form-check-label">{{ $field }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Supporting documents</h5>
            <p class="text-muted mb-0 small">Documents participants may need to upload.</p>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::competitionsSupportingDocumentTypes() as $doc)
            <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                <input type="checkbox" name="competitions_supporting_documents[]" value="{{ $doc }}" class="form-check-input competitions-flow-field" @checked(in_array($doc, (array) old('competitions_supporting_documents', data_get($post->meta, 'competitions_supporting_documents', [])), true))>
                <span class="form-check-label">{{ $doc }}</span>
            </label>
        @endforeach
    </div>
</div>
