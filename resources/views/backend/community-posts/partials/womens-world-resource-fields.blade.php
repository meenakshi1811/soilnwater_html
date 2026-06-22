<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Resource section</h5>
            <p class="text-muted mb-0 small">Optional links and references for readers.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="womensWorldUsefulWebsites">Useful websites</label>
            <textarea
                name="womens_world_useful_websites"
                id="womensWorldUsefulWebsites"
                class="form-control"
                rows="3"
                maxlength="5000"
                placeholder="One URL or resource per line"
            >{{ old('womens_world_useful_websites', data_get($post->meta, 'womens_world_useful_websites')) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="womensWorldGovernmentSchemes">Government schemes</label>
            <textarea
                name="womens_world_government_schemes"
                id="womensWorldGovernmentSchemes"
                class="form-control"
                rows="3"
                maxlength="5000"
                placeholder="Scheme names or links"
            >{{ old('womens_world_government_schemes', data_get($post->meta, 'womens_world_government_schemes')) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="womensWorldTrainingPrograms">Training programs</label>
            <textarea
                name="womens_world_training_programs"
                id="womensWorldTrainingPrograms"
                class="form-control"
                rows="3"
                maxlength="5000"
                placeholder="Program names or links"
            >{{ old('womens_world_training_programs', data_get($post->meta, 'womens_world_training_programs')) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="womensWorldScholarships">Scholarships</label>
            <textarea
                name="womens_world_scholarships"
                id="womensWorldScholarships"
                class="form-control"
                rows="3"
                maxlength="5000"
                placeholder="Scholarship names or links"
            >{{ old('womens_world_scholarships', data_get($post->meta, 'womens_world_scholarships')) }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label" for="womensWorldSupportOrganizations">Support organizations</label>
            <textarea
                name="womens_world_support_organizations"
                id="womensWorldSupportOrganizations"
                class="form-control"
                rows="3"
                maxlength="5000"
                placeholder="Organizations, helplines, or support groups"
            >{{ old('womens_world_support_organizations', data_get($post->meta, 'womens_world_support_organizations')) }}</textarea>
        </div>
    </div>
</div>
