@php
    $selectedStCategory = old('science_technology_category', data_get($post->meta, 'science_technology_category', $post->category));
    $flowPlacement = $placement ?? 'all';
    $showStSetup = in_array($flowPlacement, ['all', 'setup'], true);
    $showStRest = in_array($flowPlacement, ['all', 'rest'], true);
@endphp

@if($showStSetup)
<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Post type</h5>
            <p class="text-muted mb-0 small">This controls the remaining form — choose what you are sharing.</p>
        </div>
        <span class="badge bg-danger text-white">Required</span>
    </div>
    <label class="form-label" for="scienceTechnologyPostType">Post type <span class="text-danger">*</span></label>
    <select name="science_technology_post_type" id="scienceTechnologyPostType" class="form-select science-technology-required" required>
        <option value="">Select post type</option>
        @foreach(\App\Support\CommunityContentTaxonomy::scienceTechnologyPostTypes() as $postType)
            <option value="{{ $postType }}" @selected(old('science_technology_post_type', data_get($post->meta, 'science_technology_post_type')) === $postType)>{{ $postType }}</option>
        @endforeach
    </select>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Science &amp; technology category</h5>
            <p class="text-muted mb-0 small">Choose the primary topic for this post.</p>
        </div>
        <span class="badge bg-primary text-white">Required</span>
    </div>
    <label class="form-label" for="scienceTechnologyCategory">Main category <span class="text-danger">*</span></label>
    <select name="science_technology_category" id="scienceTechnologyCategory" class="form-select science-technology-required" required>
        <option value="">Select main category</option>
        @foreach(\App\Support\CommunityContentTaxonomy::scienceTechnologyMainCategories() as $category)
            <option value="{{ $category }}" @selected($selectedStCategory === $category)>{{ $category }}</option>
        @endforeach
    </select>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Target audience</h5>
            <p class="text-muted mb-0 small">Select all groups this content is meant for.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Multiple selection</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::scienceTechnologyTargetAudiences() as $audience)
            <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                <input type="checkbox" name="science_technology_target_audience[]" value="{{ $audience }}" class="form-check-input science-technology-flow-field" @checked(in_array($audience, (array) old('science_technology_target_audience', data_get($post->meta, 'science_technology_target_audience', [])), true))>
                <span class="form-check-label">{{ $audience }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white h-100">
            <label class="form-label" for="scienceTechnologyLevel">Technology level</label>
            <select name="science_technology_level" id="scienceTechnologyLevel" class="form-select science-technology-flow-field">
                <option value="">Select level (optional)</option>
                @foreach(\App\Support\CommunityContentTaxonomy::scienceTechnologyLevels() as $level)
                    <option value="{{ $level }}" @selected(old('science_technology_level', data_get($post->meta, 'science_technology_level')) === $level)>{{ $level }}</option>
                @endforeach
            </select>
            <small class="text-muted">Useful for filtering.</small>
        </div>
    </div>
    <div class="col-md-6">
        <div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light h-100">
            <label class="form-label d-block">Scientific field</label>
            <div class="d-flex flex-wrap gap-2">
                @foreach(\App\Support\CommunityContentTaxonomy::scienceTechnologyScientificFields() as $field)
                    <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                        <input type="checkbox" name="science_technology_scientific_fields[]" value="{{ $field }}" class="form-check-input science-technology-flow-field" @checked(in_array($field, (array) old('science_technology_scientific_fields', data_get($post->meta, 'science_technology_scientific_fields', [])), true))>
                        <span class="form-check-label">{{ $field }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Location fields</h5>
            <p class="text-muted mb-0 small">Country, state, district, city, and map pin for this science &amp; technology post.</p>
        </div>
        <span class="badge bg-success-subtle text-success border">Critical for SoilnWater</span>
    </div>
    <div id="communityScienceTechnologyLocationSlot"></div>
</div>
@endif

@if($showStRest)
@include('backend.community-posts.partials.science-technology-conditional-sections', ['post' => $post])
@include('backend.community-posts.partials.science-technology-media-fields', ['post' => $post])
@include('backend.community-posts.partials.science-technology-engagement-fields', ['post' => $post])
@endif
