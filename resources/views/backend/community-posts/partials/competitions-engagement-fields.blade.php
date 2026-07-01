<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Comments</h5>
            <p class="text-muted mb-0 small">Allow community interaction on this competition listing.</p>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::competitionsCommentSettings() as $setting)
            <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                <input type="checkbox" name="competitions_comment_settings[]" value="{{ $setting }}" class="form-check-input competitions-flow-field" @checked(in_array($setting, (array) old('competitions_comment_settings', data_get($post->meta, 'competitions_comment_settings', [])), true))>
                <span class="form-check-label">{{ $setting }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Tags</h5>
            <p class="text-muted mb-0 small">Maximum 10 tags. Use the tags field above on the form.</p>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::competitionsTagExamples() as $tag)
            <span class="badge bg-white text-dark border py-2 px-3">{{ $tag }}</span>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-warning-subtle mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Participant declaration</h5>
            <p class="text-muted mb-0 small">I declare that:</p>
        </div>
        <span class="badge bg-danger text-white">Mandatory</span>
    </div>
    <div class="d-flex flex-column gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::competitionsDeclarationStatements() as $field => $label)
            <label class="form-check border rounded p-3 bg-white mb-0">
                <input type="checkbox" name="{{ $field }}" id="{{ $field }}" class="form-check-input competitions-declaration-required" value="1" @checked(old($field, data_get($post->meta, $field, false))) required>
                <span class="form-check-label">{{ $label }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">AI disclosure</h5>
            <p class="text-muted mb-0 small">Very important — disclose any AI assistance.</p>
        </div>
        <span class="badge bg-info text-white">Important</span>
    </div>
    <label class="form-label d-block">Was AI used?</label>
    <div class="d-flex flex-wrap gap-3 mb-3">
        @foreach(\App\Support\CommunityContentTaxonomy::competitionsAiUsageOptions() as $option)
            <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                <input type="radio" name="competitions_ai_used" value="{{ $option }}" class="form-check-input competitions-flow-field" @checked(old('competitions_ai_used', data_get($post->meta, 'competitions_ai_used', 'No')) === $option)>
                <span class="form-check-label">{{ $option }}</span>
            </label>
        @endforeach
    </div>
    <div id="competitionsAiFields" style="display:none;">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="competitionsAiTool">Tool used</label>
                <input type="text" name="competitions_ai_tool" id="competitionsAiTool" class="form-control competitions-flow-field" maxlength="160" value="{{ old('competitions_ai_tool', data_get($post->meta, 'competitions_ai_tool')) }}">
            </div>
            <div class="col-12">
                <label class="form-label" for="competitionsAiExtent">Extent of AI assistance</label>
                <textarea name="competitions_ai_extent" id="competitionsAiExtent" class="form-control competitions-flow-field" rows="2" maxlength="2000">{{ old('competitions_ai_extent', data_get($post->meta, 'competitions_ai_extent')) }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Copyright option</h5>
            <p class="text-muted mb-0 small">How content rights are handled.</p>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::competitionsCopyrightOptions() as $option)
            <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                <input type="checkbox" name="competitions_copyright_options[]" value="{{ $option }}" class="form-check-input competitions-flow-field" @checked(in_array($option, (array) old('competitions_copyright_options', data_get($post->meta, 'competitions_copyright_options', [])), true))>
                <span class="form-check-label">{{ $option }}</span>
            </label>
        @endforeach
    </div>
</div>
