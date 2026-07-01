@php
    $selectedRsCategory = old('religion_spirituality_category', data_get($post->meta, 'religion_spirituality_category', $post->category));
    $flowPlacement = $placement ?? 'all';
    $showRsSetup = in_array($flowPlacement, ['all', 'setup'], true);
    $showRsRest = in_array($flowPlacement, ['all', 'rest'], true);
@endphp

@if($showRsSetup)
<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-primary-subtle mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Objective</h5>
            <p class="text-muted mb-0 small">{{ \App\Support\CommunityContentTaxonomy::religionSpiritualityObjective() }}</p>
        </div>
        <span class="badge bg-primary text-white">Religion &amp; Spirituality</span>
    </div>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Post type</h5>
            <p class="text-muted mb-0 small">This determines the workflow for your Religion &amp; Spirituality post.</p>
        </div>
        <span class="badge bg-danger text-white">Required</span>
    </div>
    <label class="form-label" for="religionSpiritualityPostType">Post type <span class="text-danger">*</span></label>
    <select name="religion_spirituality_post_type" id="religionSpiritualityPostType" class="form-select religion-spirituality-required" required>
        <option value="">Select post type</option>
        @foreach(\App\Support\CommunityContentTaxonomy::religionSpiritualityPostTypes() as $postType)
            <option value="{{ $postType }}" @selected(old('religion_spirituality_post_type', data_get($post->meta, 'religion_spirituality_post_type')) === $postType)>{{ $postType }}</option>
        @endforeach
    </select>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Religion &amp; Spirituality category</h5>
            <p class="text-muted mb-0 small">Choose the primary theme for this post.</p>
        </div>
        <span class="badge bg-primary text-white">Required</span>
    </div>
    <label class="form-label" for="religionSpiritualityCategory">Main category <span class="text-danger">*</span></label>
    <select name="religion_spirituality_category" id="religionSpiritualityCategory" class="form-select religion-spirituality-required" required>
        <option value="">Select main category</option>
        @foreach(\App\Support\CommunityContentTaxonomy::religionSpiritualityMainCategories() as $category)
            <option value="{{ $category }}" @selected($selectedRsCategory === $category)>{{ $category }}</option>
        @endforeach
    </select>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Religious tradition</h5>
            <p class="text-muted mb-0 small">Optional — used only for categorization, never for ranking or discrimination.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Optional</span>
    </div>
    <label class="form-label" for="religionSpiritualityTradition">Religious tradition</label>
    <select name="religion_spirituality_tradition" id="religionSpiritualityTradition" class="form-select religion-spirituality-flow-field">
        <option value="">Select tradition (optional)</option>
        @foreach(\App\Support\CommunityContentTaxonomy::religionSpiritualityTraditions() as $tradition)
            <option value="{{ $tradition }}" @selected(old('religion_spirituality_tradition', data_get($post->meta, 'religion_spirituality_tradition')) === $tradition)>{{ $tradition }}</option>
        @endforeach
    </select>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Target audience</h5>
            <p class="text-muted mb-0 small">Select all groups this content is meant for.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Multiple selection</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::religionSpiritualityTargetAudiences() as $audience)
            <label class="form-check border rounded py-2 px-3 bg-white mb-0">
                <input type="checkbox" name="religion_spirituality_target_audience[]" value="{{ $audience }}" class="form-check-input religion-spirituality-flow-field" @checked(in_array($audience, (array) old('religion_spirituality_target_audience', data_get($post->meta, 'religion_spirituality_target_audience', [])), true))>
                <span class="form-check-label">{{ $audience }}</span>
            </label>
        @endforeach
    </div>
</div>
@endif

@if($showRsRest)
@include('backend.community-posts.partials.religion-spirituality-conditional-sections', ['post' => $post])
@include('backend.community-posts.partials.religion-spirituality-unique-features-fields', ['post' => $post])
@include('backend.community-posts.partials.religion-spirituality-media-fields', ['post' => $post])
@include('backend.community-posts.partials.religion-spirituality-engagement-fields', ['post' => $post])
@endif
