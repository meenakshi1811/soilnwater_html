@php
    $selectedAgricultureCategory = old('agriculture_category', data_get($post->meta, 'agriculture_category', $post->category));
    $flowPlacement = $placement ?? 'all';
    $showAgricultureSetup = in_array($flowPlacement, ['all', 'setup'], true);
    $showAgricultureRest = in_array($flowPlacement, ['all', 'rest'], true);
@endphp

@if($showAgricultureSetup)
<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Share type</h5>
            <p class="text-muted mb-0 small">What would you like to share with the farming community?</p>
        </div>
        <span class="badge bg-danger text-white">Required</span>
    </div>
    <label class="form-label" for="agricultureShareType">What would you like to share? <span class="text-danger">*</span></label>
    <select name="agriculture_share_type" id="agricultureShareType" class="form-select agriculture-required" required>
        <option value="">Select share type</option>
        @foreach(\App\Support\CommunityContentTaxonomy::agricultureShareTypes() as $shareType)
            <option value="{{ $shareType }}" @selected(old('agriculture_share_type', data_get($post->meta, 'agriculture_share_type')) === $shareType)>{{ $shareType }}</option>
        @endforeach
    </select>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Agriculture category</h5>
            <p class="text-muted mb-0 small">Choose the main topic for this agriculture post.</p>
        </div>
        <span class="badge bg-primary text-white">Main category</span>
    </div>
    <label class="form-label" for="agricultureCategory">Main category <span class="text-danger">*</span></label>
    <select name="agriculture_category" id="agricultureCategory" class="form-select agriculture-required" required>
        <option value="">Select main category</option>
        @foreach(\App\Support\CommunityContentTaxonomy::agricultureMainCategories() as $category)
            <option value="{{ $category }}" @selected($selectedAgricultureCategory === $category)>{{ $category }}</option>
        @endforeach
    </select>
</div>

@include('backend.community-posts.partials.agriculture-crop-fields', ['post' => $post])
@endif

@if($showAgricultureRest)
<div class="news-flow-card story-flow-card story-flow-card--location border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Location details</h5>
            <p class="text-muted mb-0 small">Country, state, district, and village/town. Climate zone and soil type help readers understand your farm context.</p>
        </div>
        <span class="badge bg-danger text-white">Very important</span>
    </div>
    <div id="communityAgricultureLocationSlot"></div>
    <div class="row g-3 mt-1">
        <div class="col-md-6">
            <label class="form-label" for="agricultureClimateZone">Climate zone</label>
            <input
                type="text"
                name="agriculture_climate_zone"
                id="agricultureClimateZone"
                class="form-control agriculture-flow-field"
                maxlength="120"
                value="{{ old('agriculture_climate_zone', data_get($post->meta, 'agriculture_climate_zone')) }}"
                placeholder="e.g. Semi-arid, Tropical, Temperate"
            >
            <small class="text-muted">Optional</small>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="agricultureSoilType">Soil type</label>
            <select name="agriculture_soil_type" id="agricultureSoilType" class="form-select agriculture-flow-field">
                <option value="">Select soil type</option>
                @foreach(\App\Support\CommunityContentTaxonomy::agricultureSoilTypes() as $soilType)
                    <option value="{{ $soilType }}" @selected(old('agriculture_soil_type', data_get($post->meta, 'agriculture_soil_type')) === $soilType)>{{ $soilType }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

@include('backend.community-posts.partials.agriculture-farm-fields', ['post' => $post])

@include('backend.community-posts.partials.agriculture-water-fields', ['post' => $post])
@include('backend.community-posts.partials.agriculture-soil-health-fields', ['post' => $post])
@include('backend.community-posts.partials.agriculture-conditional-sections', ['post' => $post])
@include('backend.community-posts.partials.agriculture-weather-fields', ['post' => $post])
@include('backend.community-posts.partials.agriculture-media-fields', ['post' => $post])
@include('backend.community-posts.partials.agriculture-engagement-fields', ['post' => $post])
@endif
