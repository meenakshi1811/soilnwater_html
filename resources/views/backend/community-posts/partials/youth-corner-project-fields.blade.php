@php
    $isProjectShowcase = old(
        'youth_corner_content_type',
        data_get($post->meta, 'youth_corner_content_type')
    ) === \App\Support\CommunityContentTaxonomy::youthCornerProjectContentType();
@endphp

<div id="youthCornerProjectSection" class="youth-corner-project-section" style="{{ $isProjectShowcase ? '' : 'display:none;' }}">
    <div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
            <div>
                <h5 class="mb-1">Project showcase</h5>
                <p class="text-muted mb-0 small">Share your project details when content type is Project Showcase.</p>
            </div>
            <span class="badge bg-primary text-white">Project Showcase</span>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="youthCornerProjectTitle">Project title <span class="text-danger">*</span></label>
                <input
                    type="text"
                    name="youth_corner_project_title"
                    id="youthCornerProjectTitle"
                    class="form-control youth-corner-project-field youth-corner-project-required"
                    maxlength="200"
                    value="{{ old('youth_corner_project_title', data_get($post->meta, 'youth_corner_project_title')) }}"
                    placeholder="Enter your project title"
                >
            </div>
            <div class="col-md-6">
                <label class="form-label" for="youthCornerProjectCategory">Category <span class="text-danger">*</span></label>
                <select
                    name="youth_corner_project_category"
                    id="youthCornerProjectCategory"
                    class="form-select youth-corner-project-field youth-corner-project-required"
                >
                    <option value="">Select project category</option>
                    @foreach(\App\Support\CommunityContentTaxonomy::youthCornerProjectCategories() as $projectCategory)
                        <option value="{{ $projectCategory }}" @selected(old('youth_corner_project_category', data_get($post->meta, 'youth_corner_project_category')) === $projectCategory)>{{ $projectCategory }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label" for="youthCornerProjectDescription">Project description <span class="text-danger">*</span></label>
                <textarea
                    name="youth_corner_project_description"
                    id="youthCornerProjectDescription"
                    class="form-control youth-corner-project-field youth-corner-project-required"
                    rows="4"
                    maxlength="5000"
                    placeholder="Describe your project, approach, and key work"
                >{{ old('youth_corner_project_description', data_get($post->meta, 'youth_corner_project_description')) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label" for="youthCornerProjectOutcome">Project outcome</label>
                <textarea
                    name="youth_corner_project_outcome"
                    id="youthCornerProjectOutcome"
                    class="form-control youth-corner-project-field"
                    rows="3"
                    maxlength="3000"
                    placeholder="Results, findings, or impact of your project (optional)"
                >{{ old('youth_corner_project_outcome', data_get($post->meta, 'youth_corner_project_outcome')) }}</textarea>
            </div>
        </div>
    </div>
</div>
