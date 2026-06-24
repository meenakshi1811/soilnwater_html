@php
    $selectedYouthCornerCategory = old('youth_corner_category', data_get($post->meta, 'youth_corner_category', $post->category));
    $selectedYouthCornerAudiences = old('youth_corner_target_audience', data_get($post->meta, 'youth_corner_target_audience', []));
    $flowPlacement = $placement ?? 'all';
    $showYouthCornerSetup = in_array($flowPlacement, ['all', 'setup'], true);
    $showYouthCornerRest = in_array($flowPlacement, ['all', 'rest'], true);
@endphp

@if($showYouthCornerSetup)
<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Main category</h5>
            <p class="text-muted mb-0 small">Choose the primary topic for this Youth Corner post.</p>
        </div>
        <span class="badge bg-primary text-white">Main category</span>
    </div>
    <label class="form-label" for="youthCornerCategory">Main category <span class="text-danger">*</span></label>
    <select name="youth_corner_category" id="youthCornerCategory" class="form-select youth-corner-required" required>
        <option value="">Select main category</option>
        @foreach(\App\Support\CommunityContentTaxonomy::youthCornerMainCategories() as $category)
            <option value="{{ $category }}" @selected($selectedYouthCornerCategory === $category)>{{ $category }}</option>
        @endforeach
    </select>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Content type</h5>
            <p class="text-muted mb-0 small">How this content should be classified for readers.</p>
        </div>
        <span class="badge bg-danger text-white">Very important</span>
    </div>
    <label class="form-label" for="youthCornerContentType">Content type <span class="text-danger">*</span></label>
    <select name="youth_corner_content_type" id="youthCornerContentType" class="form-select youth-corner-required" required>
        <option value="">Select content type</option>
        @foreach(\App\Support\CommunityContentTaxonomy::youthCornerContentTypes() as $contentType)
            <option value="{{ $contentType }}" @selected(old('youth_corner_content_type', data_get($post->meta, 'youth_corner_content_type')) === $contentType)>{{ $contentType }}</option>
        @endforeach
    </select>
</div>
@endif

@if($showYouthCornerRest)
<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Youth profile</h5>
            <p class="text-muted mb-0 small">Optional context about the author or subject.</p>
        </div>
        <span class="badge bg-light text-dark border">Optional</span>
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label" for="youthCornerAgeGroup">Age group</label>
            <select name="youth_corner_age_group" id="youthCornerAgeGroup" class="form-select youth-corner-flow-field">
                <option value="">Select age group (optional)</option>
                @foreach(\App\Support\CommunityContentTaxonomy::youthCornerAgeGroups() as $ageGroup)
                    <option value="{{ $ageGroup }}" @selected(old('youth_corner_age_group', data_get($post->meta, 'youth_corner_age_group')) === $ageGroup)>{{ $ageGroup }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="youthCornerOccupation">Occupation</label>
            <select name="youth_corner_occupation" id="youthCornerOccupation" class="form-select youth-corner-flow-field">
                <option value="">Select occupation (optional)</option>
                @foreach(\App\Support\CommunityContentTaxonomy::youthCornerOccupations() as $occupation)
                    <option value="{{ $occupation }}" @selected(old('youth_corner_occupation', data_get($post->meta, 'youth_corner_occupation')) === $occupation)>{{ $occupation }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="youthCornerEducationLevel">Education level</label>
            <select name="youth_corner_education_level" id="youthCornerEducationLevel" class="form-select youth-corner-flow-field">
                <option value="">Select education level (optional)</option>
                @foreach(\App\Support\CommunityContentTaxonomy::youthCornerEducationLevels() as $educationLevel)
                    <option value="{{ $educationLevel }}" @selected(old('youth_corner_education_level', data_get($post->meta, 'youth_corner_education_level')) === $educationLevel)>{{ $educationLevel }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card story-flow-card--audience border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Target audience</h5>
            <p class="text-muted mb-0 small">Select all groups this content is meant for.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Multiple selection</span>
    </div>
    <div class="row g-2 community-flow-checklist">
        @foreach(\App\Support\CommunityContentTaxonomy::youthCornerTargetAudiences() as $audience)
            <div class="col-md-4 col-sm-6">
                <label class="form-check border rounded py-2 px-3 bg-white h-100 mb-0">
                    <input
                        type="checkbox"
                        name="youth_corner_target_audience[]"
                        value="{{ $audience }}"
                        class="form-check-input youth-corner-flow-field"
                        @checked(in_array($audience, (array) $selectedYouthCornerAudiences, true))
                    >
                    <span class="form-check-label">{{ $audience }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>

@include('backend.community-posts.partials.youth-corner-project-fields', ['post' => $post])
@include('backend.community-posts.partials.youth-corner-media-fields', ['post' => $post])
@include('backend.community-posts.partials.youth-corner-documents-fields', ['post' => $post])
@include('backend.community-posts.partials.youth-corner-topic-fields', ['post' => $post])
@include('backend.community-posts.partials.youth-corner-engagement-fields', ['post' => $post])
@include('backend.community-posts.partials.youth-corner-achievements-fields', ['post' => $post])
@include('backend.community-posts.partials.youth-corner-privacy-fields', ['post' => $post])
@endif
