@php
    $selectedEnvironmentCategory = old('environment_category', data_get($post->meta, 'environment_category', $post->category));
    $flowPlacement = $placement ?? 'all';
    $showEnvironmentSetup = in_array($flowPlacement, ['all', 'setup'], true);
    $showEnvironmentRest = in_array($flowPlacement, ['all', 'rest'], true);
@endphp

@if($showEnvironmentSetup)
<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Environment post type</h5>
            <p class="text-muted mb-0 small">This controls the rest of the form — choose what you are sharing.</p>
        </div>
        <span class="badge bg-danger text-white">Required</span>
    </div>
    <label class="form-label" for="environmentPostType">Environment post type <span class="text-danger">*</span></label>
    <select name="environment_post_type" id="environmentPostType" class="form-select environment-required" required>
        <option value="">Select post type</option>
        @foreach(\App\Support\CommunityContentTaxonomy::environmentPostTypes() as $postType)
            <option value="{{ $postType }}" @selected(old('environment_post_type', data_get($post->meta, 'environment_post_type')) === $postType)>{{ $postType }}</option>
        @endforeach
    </select>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Main category</h5>
            <p class="text-muted mb-0 small">Choose the primary environmental topic for this post.</p>
        </div>
        <span class="badge bg-primary text-white">Main category</span>
    </div>
    <label class="form-label" for="environmentCategory">Main category <span class="text-danger">*</span></label>
    <select name="environment_category" id="environmentCategory" class="form-select environment-required" required>
        <option value="">Select main category</option>
        @foreach(\App\Support\CommunityContentTaxonomy::environmentMainCategories() as $category)
            <option value="{{ $category }}" @selected($selectedEnvironmentCategory === $category)>{{ $category }}</option>
        @endforeach
    </select>
</div>
@endif

@if($showEnvironmentRest)
<div class="news-flow-card story-flow-card story-flow-card--location border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Location details</h5>
            <p class="text-muted mb-0 small">Environmental content should always be geo-tagged. Country, state, district, city/town/village, and locality are required.</p>
        </div>
        <span class="badge bg-danger text-white">Most important</span>
    </div>
    <div id="communityEnvironmentLocationSlot"></div>
    <div class="row g-3 mt-1">
        <div class="col-md-6">
            <label class="form-label" for="environmentNaturalFeatureName">Forest / river / lake name</label>
            <input
                type="text"
                name="environment_natural_feature_name"
                id="environmentNaturalFeatureName"
                class="form-control environment-flow-field"
                maxlength="160"
                value="{{ old('environment_natural_feature_name', data_get($post->meta, 'environment_natural_feature_name')) }}"
                placeholder="e.g. Chambal River, Keoladeo Wetland"
            >
            <small class="text-muted">Optional</small>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="environmentMapPinType">Map pin type</label>
            <select name="environment_map_pin_type" id="environmentMapPinType" class="form-select environment-flow-field">
                <option value="">Select feature type</option>
                @foreach(\App\Support\CommunityContentTaxonomy::environmentMapPinTypes() as $pinType)
                    <option value="{{ $pinType }}" @selected(old('environment_map_pin_type', data_get($post->meta, 'environment_map_pin_type')) === $pinType)>{{ $pinType }}</option>
                @endforeach
            </select>
            <small class="text-muted">Optional — pin exact location of pond, lake, river, forest, dump site, or plantation area on the map below.</small>
        </div>
    </div>
</div>

@include('backend.community-posts.partials.environment-conditional-sections', ['post' => $post])
@include('backend.community-posts.partials.environment-media-fields', ['post' => $post])
@include('backend.community-posts.partials.environment-engagement-fields', ['post' => $post])
@endif
