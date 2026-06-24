<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Startup &amp; entrepreneurship</h5>
            <p class="text-muted mb-0 small">Optional startup or business journey details.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="youthCornerStartupName">Startup name</label>
            <input
                type="text"
                name="youth_corner_startup_name"
                id="youthCornerStartupName"
                class="form-control youth-corner-flow-field"
                maxlength="200"
                value="{{ old('youth_corner_startup_name', data_get($post->meta, 'youth_corner_startup_name')) }}"
                placeholder="Your startup or venture name"
            >
        </div>
        <div class="col-md-6">
            <label class="form-label" for="youthCornerStartupIndustry">Industry</label>
            <input
                type="text"
                name="youth_corner_startup_industry"
                id="youthCornerStartupIndustry"
                class="form-control youth-corner-flow-field"
                maxlength="160"
                value="{{ old('youth_corner_startup_industry', data_get($post->meta, 'youth_corner_startup_industry')) }}"
                placeholder="e.g. Technology, Agriculture, Education"
            >
        </div>
        <div class="col-12">
            <label class="form-label" for="youthCornerBusinessIdea">Business idea</label>
            <textarea
                name="youth_corner_business_idea"
                id="youthCornerBusinessIdea"
                class="form-control youth-corner-flow-field"
                rows="3"
                maxlength="3000"
                placeholder="Briefly describe your business idea or venture"
            >{{ old('youth_corner_business_idea', data_get($post->meta, 'youth_corner_business_idea')) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="youthCornerFundingStage">Funding stage</label>
            <select name="youth_corner_funding_stage" id="youthCornerFundingStage" class="form-select youth-corner-flow-field">
                <option value="">Select funding stage (optional)</option>
                @foreach(\App\Support\CommunityContentTaxonomy::youthCornerFundingStages() as $fundingStage)
                    <option value="{{ $fundingStage }}" @selected(old('youth_corner_funding_stage', data_get($post->meta, 'youth_corner_funding_stage')) === $fundingStage)>{{ $fundingStage }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12">
            <label class="form-label" for="youthCornerStartupChallenges">Challenges faced</label>
            <textarea
                name="youth_corner_startup_challenges"
                id="youthCornerStartupChallenges"
                class="form-control youth-corner-flow-field"
                rows="3"
                maxlength="3000"
                placeholder="Key challenges during your startup journey"
            >{{ old('youth_corner_startup_challenges', data_get($post->meta, 'youth_corner_startup_challenges')) }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label" for="youthCornerStartupLessons">Lessons learned</label>
            <textarea
                name="youth_corner_startup_lessons"
                id="youthCornerStartupLessons"
                class="form-control youth-corner-flow-field"
                rows="3"
                maxlength="3000"
                placeholder="Key takeaways from your entrepreneurial journey"
            >{{ old('youth_corner_startup_lessons', data_get($post->meta, 'youth_corner_startup_lessons')) }}</textarea>
        </div>
    </div>
</div>
