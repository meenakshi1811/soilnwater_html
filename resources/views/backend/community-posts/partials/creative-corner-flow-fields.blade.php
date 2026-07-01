@php
    $selectedCcCategory = old('creative_corner_category', data_get($post->meta, 'creative_corner_category', $post->category));
    $flowPlacement = $placement ?? 'all';
    $showCcSetup = in_array($flowPlacement, ['all', 'setup'], true);
    $showCcRest = in_array($flowPlacement, ['all', 'rest'], true);
@endphp

@if($showCcSetup)
<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Post type</h5>
            <p class="text-muted mb-0 small">This controls the entire form.</p>
        </div>
        <span class="badge bg-danger text-white">Required</span>
    </div>
    <label class="form-label" for="creativeCornerPostType">Post type <span class="text-danger">*</span></label>
    <select name="creative_corner_post_type" id="creativeCornerPostType" class="form-select creative-corner-required" required>
        <option value="">Select post type</option>
        @foreach(\App\Support\CommunityContentTaxonomy::creativeCornerPostTypes() as $postType)
            <option value="{{ $postType }}" @selected(old('creative_corner_post_type', data_get($post->meta, 'creative_corner_post_type')) === $postType)>{{ $postType }}</option>
        @endforeach
    </select>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-light mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Creative category</h5>
            <p class="text-muted mb-0 small">Choose the main category for this creative work.</p>
        </div>
        <span class="badge bg-primary text-white">Required</span>
    </div>
    <label class="form-label" for="creativeCornerCategory">Main category <span class="text-danger">*</span></label>
    <select name="creative_corner_category" id="creativeCornerCategory" class="form-select creative-corner-required" required>
        <option value="">Select main category</option>
        @foreach(\App\Support\CommunityContentTaxonomy::creativeCornerMainCategories() as $category)
            <option value="{{ $category }}" @selected($selectedCcCategory === $category)>{{ $category }}</option>
        @endforeach
    </select>
</div>

<div class="news-flow-card story-flow-card border rounded-3 p-3 p-md-4 bg-white mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
        <div>
            <h5 class="mb-1">Target audience</h5>
            <p class="text-muted mb-0 small">Select all groups this creative work is meant for.</p>
        </div>
        <span class="badge bg-secondary-subtle text-secondary border">Multiple selection</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @foreach(\App\Support\CommunityContentTaxonomy::creativeCornerTargetAudiences() as $audience)
            <label class="form-check border rounded py-2 px-3 bg-light mb-0">
                <input type="checkbox" name="creative_corner_target_audience[]" value="{{ $audience }}" class="form-check-input creative-corner-flow-field" @checked(in_array($audience, (array) old('creative_corner_target_audience', data_get($post->meta, 'creative_corner_target_audience', [])), true))>
                <span class="form-check-label">{{ $audience }}</span>
            </label>
        @endforeach
    </div>
</div>
@endif

@if($showCcRest)
@include('backend.community-posts.partials.creative-corner-details-fields', ['post' => $post])
@include('backend.community-posts.partials.creative-corner-media-fields', ['post' => $post])
@include('backend.community-posts.partials.creative-corner-engagement-fields', ['post' => $post])
@endif
